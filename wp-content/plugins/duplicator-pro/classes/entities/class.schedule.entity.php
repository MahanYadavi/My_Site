<?php

/**
 * Enity layer for schedules
 *
 * Standard: Missing
 *
 * @package    DUP_PRO
 * @subpackage classes/entities
 * @copyright  (c) 2017, Snapcreek LLC
 * @license    https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since      3.0.0
 *
 * @todo Finish Docs
 */

use Duplicator\Libs\Snap\SnapLog;
use Duplicator\Libs\Snap\SnapUtil;
use Duplicator\Libs\Snap\SnapWP;
use VendorDuplicator\Cron\CronExpression;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

abstract class DUP_PRO_Schedule_Repeat_Types
{
    const Daily   = 0;
    const Weekly  = 1;
    const Monthly = 2;
    const Hourly  = 3;
}

abstract class DUP_PRO_Schedule_Days
{
    const Monday    = 1;
    const Tuesday   = 2;
    const Wednesday = 4;
    const Thursday  = 8;
    const Friday    = 16;
    const Saturday  = 32;
    const Sunday    = 64;
}

class DUP_PRO_Schedule_Entity extends DUP_PRO_JSON_Entity_Base
{
    public $name        = '';
    public $template_id = -1;
    public $start_ticks;
    public $repeat_type     = DUP_PRO_Schedule_Repeat_Types::Weekly;
    public $active          = true;
    public $next_run_time   = -1;
    public $run_every       = 1;
    public $weekly_days     = 0;    // for now just using a single day but they can be bit encoded here
    public $day_of_month    = 1;
    public $cron_string     = '';
    public $last_run_time   = -1;
    public $last_run_status = DUP_PRO_Package_Build_Outcome::FAILURE;
    public $times_run       = 0;
    public $storage_ids;

    public function __construct()
    {
        parent::__construct();
        $this->verifiers['name'] = new DUP_PRO_Required_Verifier("Name must not be blank");
        //  $this->start_ticks = time();

        $this->storage_ids = array();
        array_push($this->storage_ids, DUP_PRO_Virtual_Storage_IDs::Default_Local);

        $this->name = DUP_PRO_U::__('New Schedule');
    }

    public static function create_from_data($data)
    {
        $instance = new DUP_PRO_Schedule_Entity();

        $instance->name          = $data->name;
        $instance->template_id   = $data->template_id;
        $instance->start_ticks   = $data->start_ticks;
        $instance->repeat_type   = $data->repeat_type;
        $instance->active        = $data->active;
        $instance->cron_string   = $data->cron_string;
        $instance->next_run_time = $instance->get_next_run_time();
        $instance->run_every     = $data->run_every;
        // for now just using a single day but they can be bit encoded here
        $instance->weekly_days     = $data->weekly_days;
        $instance->day_of_month    = $data->day_of_month;
        $instance->last_run_time   = -1;
        $instance->last_run_status = DUP_PRO_Package_Build_Outcome::FAILURE;
        $instance->times_run       = 0;
        $instance->storage_ids     = $data->storage_ids;

        return $instance;
    }

    /**
     * If it should run, queue up a package then update the run time
     *
     * @return void
     */
    public function process()
    {
        DUP_PRO_Log::trace("process");
        $now = time();

        if ($this->next_run_time == -1) {
            return;
        }

        if ($this->active && ($this->next_run_time <= $now)) {
            $exception = null;
            try {
                $next_run_time_string = DUP_PRO_DATE::getLocalTimeFromGMTTicks($this->next_run_time);
                $now_string           = DUP_PRO_DATE::getLocalTimeFromGMTTicks($this->next_run_time);

                DUP_PRO_Log::trace("NEXT RUN IS NOW! $next_run_time_string <= $now_string so trying to queue package");

                $this->insert_new_package();

                $this->next_run_time = $this->get_next_run_time();
                $this->save();

                $next_run_time_string = DUP_PRO_DATE::getLocalTimeFromGMTTicks($this->next_run_time);
                DUP_PRO_Log::trace("******PACKAGE JUST CREATED. UPDATED NEXT RUN TIME TO $next_run_time_string");
            } catch (Exception $e) {
                $exception = $e;
            } catch (Error $e) {
                $exception = $e;
            }

            if (!is_null($exception)) {
                $msg  = "Start schedule error " . $exception->getMessage() . "\n";
                $msg .= SnapLog::getTextException($exception);
                error_log($msg);
                \DUP_PRO_Log::trace($msg);
                $system_global                  = DUP_PRO_System_Global_Entity::getInstance();
                $system_global->schedule_failed = true;
                $system_global->save();
            }
        } else {
            DUP_PRO_Log::trace("active and runtime=$this->next_run_time >= $now");
        }
    }

    public function copy_from_source_id($source_id)
    {

        /* @var $source_schedule DUP_PRO_Schedule_Entity */
        $source_schedule = self::get_by_id($source_id);

        $this->active          = $source_schedule->active;
        $this->cron_string     = $source_schedule->cron_string;
        $this->day_of_month    = $source_schedule->day_of_month;
        $this->last_run_status = $source_schedule->last_run_status;
        $this->name            = sprintf(DUP_PRO_U::__('%1$s - Copy'), $source_schedule->name);
        //$this->last_run_time = $source_schedule->last_run_time;
        //$this->next_run_time = $source_schedule
        $this->repeat_type = $source_schedule->repeat_type;
        $this->run_every   = $source_schedule->run_every;
        $this->start_ticks = $source_schedule->start_ticks;
        $this->storage_ids = $source_schedule->storage_ids; // Arrays are copied
        $this->template_id = $source_schedule->template_id;
        //$this->times_run;
        $this->weekly_days = $source_schedule->weekly_days;
    }

    public function insert_new_package($run_now = false)
    {
        global $wp_version;

        $global = DUP_PRO_Global_Entity::getInstance();

        DUP_PRO_Log::trace("archive build mode before calling insert new package " . $global->archive_build_mode);

        DUP_PRO_Log::trace("Inserting new package for schedule $this->name");

        /* @var $schedule DUP_PRO_Schedule_Entity */
        $template = DUP_PRO_Package_Template_Entity::getById((int) $this->template_id);

        if ($template != null) {
            $package = new DUP_PRO_Package();

            $dbversion  = DUP_PRO_DB::getVersion();
            $dbversion  = (empty($dbversion) ? '- unknown -' : $dbversion);
            $dbcomments = DUP_PRO_DB::getVariable('version_comment');
            $dbcomments = is_null($dbcomments) ? '- unknown -' : $dbcomments;

            //PACKAGE
            $package->Created    = gmdate("Y-m-d H:i:s");
            $package->Version    = DUPLICATOR_PRO_VERSION;
            $package->VersionOS  = defined('PHP_OS') ? PHP_OS : 'unknown';
            $package->VersionWP  = $wp_version;
            $package->VersionPHP = phpversion();
            $package->VersionDB  = $dbversion;
            $package->Name       = $this->generate_package_name();
            $package->Hash       = $package->make_hash();
            $package->NameHash   = "{$package->Name}_{$package->Hash}";
            $package->notes      = sprintf(DUP_PRO_U::esc_html__('Created by schedule %1$s'), $this->name);
            if ($run_now) {
                $package->Type = DUP_PRO_PackageType::RUN_NOW;
            } else {
                $package->Type = DUP_PRO_PackageType::SCHEDULED;
            }

            //BRAND
            $brand_data = DUP_PRO_Brand_Entity::getByIdOrDefault((int) $template->installer_opts_brand);
            $brand_data->prepare_attachments_to_installer();

            $package->Brand    = $brand_data->name;
            $package->Brand_ID = $brand_data->getId();

            //MULTISITE
            $package->Multisite->FilterSites = $template->filter_sites;

            //ARCHIVE
            if ($global->archive_build_mode == DUP_PRO_Archive_Build_Mode::DupArchive) {
                $package->Archive->Format = 'DAF';
            } else {
                $package->Archive->Format = 'ZIP';
            }

            $package->Archive->ExportOnlyDB = $template->archive_export_onlydb;
            $package->Archive->FilterOn     = $template->archive_filter_on;
            $package->Archive->FilterDirs   = $template->archive_filter_dirs;
            $package->Archive->FilterExts   = $template->archive_filter_exts;
            $package->Archive->FilterFiles  = $template->archive_filter_files;

            //INSTALLER
            $package->Installer->OptsDBHost   = $template->installer_opts_db_host;
            $package->Installer->OptsDBName   = $template->installer_opts_db_name;
            $package->Installer->OptsDBUser   = $template->installer_opts_db_user;
            $package->Installer->OptsSecureOn = $template->installer_opts_secure_on;
            $package->Installer->passowrd     = $template->installerPassowrd;
            $package->Installer->OptsSkipScan = $template->installer_opts_skip_scan;

            // CPANEL
            $package->Installer->OptsCPNLHost    = $template->installer_opts_cpnl_host;
            $package->Installer->OptsCPNLUser    = $template->installer_opts_cpnl_user;
            $package->Installer->OptsCPNLPass    = $template->installer_opts_cpnl_pass;
            $package->Installer->OptsCPNLEnable  = $template->installer_opts_cpnl_enable;
            $package->Installer->OptsCPNLConnect = false;
            //CPANEL DB
            //1 = Create New, 2 = Connect Remove
            $package->Installer->OptsCPNLDBAction = $template->installer_opts_cpnl_db_action;
            $package->Installer->OptsCPNLDBHost   = $template->installer_opts_cpnl_db_host;
            $package->Installer->OptsCPNLDBName   = $template->installer_opts_cpnl_db_name;
            $package->Installer->OptsCPNLDBUser   = $template->installer_opts_cpnl_db_user;

            //DATABASE
            $package->Database->FilterOn     = $template->database_filter_on;
            $package->Database->FilterTables = $template->database_filter_tables;
            $package->Database->Comments     = esc_html($dbcomments);

            $package->Status      = DUP_PRO_PackageStatus::PRE_PROCESS;
            $package->schedule_id = $this->id;
            $package->add_upload_infos($this->storage_ids);

            $package->build_progress->setBuildMode();

            $system_global = DUP_PRO_System_Global_Entity::getInstance();
            $system_global->clearFixes();
            $system_global->package_check_ts = 0;
            $system_global->save();

            DUP_PRO_Log::trace('NEW PACKAGE NAME ' . $package->Name);
            if ($package->save(false) == false) {
                throw new Exception("Duplicator is unable to insert a package record into the database table from schedule {$this->name}.");
            }
        } else {
            DUP_PRO_Log::traceError("No settings object exists for schedule {$this->name}!");
        }

        DUP_PRO_Log::trace("archive build mode after calling insert new package " . $global->archive_build_mode);
    }

    /**
     *
     * @return boolean|\DUP_PRO_Package_Template_Entity
     */
    public function getTemplate()
    {
        if ($this->template_id > 0) {
            $template = DUP_PRO_Package_Template_Entity::getById($this->template_id);
        } else {
            $template = null;
        }

        if (!$template instanceof DUP_PRO_Package_Template_Entity) {
            return false;
        }

        return $template;
    }

    /**
     * Returns the edit url of the current schedule
     *
     * @return string
     */
    public function getEditURL()
    {
        //Have to use admin_url because menu pages are not initialized if calling in an ajax hook
        $baseUrl = admin_url('admin.php?page=' . DUP_PRO_Constants::$SCHEDULES_SUBMENU_SLUG);
        return $baseUrl . '&' . http_build_query(array(
                'tab'         => 'schedules',
                'inner_page'  => 'edit',
                '_wpnonce'    => wp_create_nonce('edit-schedule'),
                'schedule_id' => $this->id
            ));
    }

    public function recoveableHtmlInfo($isList = false)
    {
        if (($template = $this->getTemplate()) === false) {
            return false;
        }

        $schedule = $this;
        require DUPLICATOR____PATH . '/views/tools/templates/widget/recoveable-template-info.php';
    }

    private function generate_package_name()
    {
        $ticks = time() + SnapWP::getGMTOffset();

        //Remove specail_chars from final result
        $sanitize_special_chars = array(
            ".", "-", "?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&",
            "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}", "%", "+", chr(0)
        );

        $scheduleName = SnapUtil::sanitizeNSCharsNewlineTabs($this->name);
        $scheduleName = trim(str_replace($sanitize_special_chars, '', $scheduleName), '_');
        DUP_PRO_Log::trace('SCHEDULE NAME ' . $scheduleName);
        $blogName = sanitize_title(SnapUtil::sanitizeNSCharsNewlineTabs(get_bloginfo('name', 'display')));
        $blogName = trim(str_replace($sanitize_special_chars, '', $blogName), '_');
        DUP_PRO_Log::trace('BLOG NAME NAME ' . $blogName);

        $name = date('Ymd_His', $ticks) . '_' . $scheduleName . '_' .  $blogName;

        return substr($name, 0, 40);
    }

    /**
     * Save
     *
     * @return bool
     */
    public function save()
    {
        DUP_PRO_Log::trace("saving schedule $this->id");
        // If we are inactive clear out any packages associated with us

        return parent::save();
    }

    // Return the next run time in UTC
    public function get_next_run_time()
    {
        if ($this->active) {
            $nextMinute = time() + 60; // We look ahead starting from next minute
            $date       = new DateTime();
            $date->setTimestamp($nextMinute + SnapWP::getGMTOffset());//Add timezone specific offset

            //Get next run time relative to $date
            $nextRunTime = CronExpression::factory($this->cron_string)->getNextRunDate($date)->getTimestamp();

            // Have to negate the offset and add. For instance for az time -7
            // we want the next run time to be 7 ahead in UTC time
            $nextRunTime -= SnapWP::getGMTOffset();

            // Handling DST problem that happens when there is a change of DST between $nextMinute and $nextRunTime.
            // The problem does not happen if manual offset is selected, because in that case there is no DST.
            $timezoneString = SnapWP::getTimeZoneString();
            if ($timezoneString) {
                // User selected particular timezone (not manual offset), so the problem needs to be handled.
                $DST_NextMinute           = SnapWP::getDST($nextMinute);
                $DST_NextRunTime          = SnapWP::getDST($nextRunTime);
                $DST_NextRunTime_HourBack = SnapWP::getDST($nextRunTime - 3600);
                if ($DST_NextMinute && !$DST_NextRunTime) {
                    $nextRunTime += 3600; // Move one hour ahead because of DST change
                } elseif (!$DST_NextMinute && $DST_NextRunTime && $DST_NextRunTime_HourBack) {
                    $nextRunTime -= 3600; // Move one hour back because of DST change
                }
            }
            return $nextRunTime;
        } else {
            return -1;
        }
    }

    public function set_weekdays_from_request($request)
    {
        $weekday = $request['weekday'];
        if (in_array('mon', $weekday)) {
            $this->weekly_days |= DUP_PRO_Schedule_Days::Monday;
        } else {
            $this->weekly_days &= ~DUP_PRO_Schedule_Days::Monday;
        }

        if (in_array('tue', $weekday)) {
            $this->weekly_days |= DUP_PRO_Schedule_Days::Tuesday;
        } else {
            $this->weekly_days &= ~DUP_PRO_Schedule_Days::Tuesday;
        }

        if (in_array('wed', $weekday)) {
            $this->weekly_days |= DUP_PRO_Schedule_Days::Wednesday;
        } else {
            $this->weekly_days &= ~DUP_PRO_Schedule_Days::Wednesday;
        }

        if (in_array('thu', $weekday)) {
            $this->weekly_days |= DUP_PRO_Schedule_Days::Thursday;
        } else {
            $this->weekly_days &= ~DUP_PRO_Schedule_Days::Thursday;
        }

        if (in_array('fri', $weekday)) {
            $this->weekly_days |= DUP_PRO_Schedule_Days::Friday;
        } else {
            $this->weekly_days &= ~DUP_PRO_Schedule_Days::Friday;
        }

        if (in_array('sat', $weekday)) {
            $this->weekly_days |= DUP_PRO_Schedule_Days::Saturday;
        } else {
            $this->weekly_days &= ~DUP_PRO_Schedule_Days::Saturday;
        }

        if (in_array('sun', $weekday)) {
            $this->weekly_days |= DUP_PRO_Schedule_Days::Sunday;
        } else {
            $this->weekly_days &= ~DUP_PRO_Schedule_Days::Sunday;
        }
    }

    public function is_day_set($day_string)
    {
        $day_bit = 0;

        switch ($day_string) {
            case 'mon':
                $day_bit = DUP_PRO_Schedule_Days::Monday;
                break;

            case 'tue':
                $day_bit = DUP_PRO_Schedule_Days::Tuesday;
                break;

            case 'wed':
                $day_bit = DUP_PRO_Schedule_Days::Wednesday;
                break;

            case 'thu':
                $day_bit = DUP_PRO_Schedule_Days::Thursday;
                break;

            case 'fri':
                $day_bit = DUP_PRO_Schedule_Days::Friday;
                break;

            case 'sat':
                $day_bit = DUP_PRO_Schedule_Days::Saturday;
                break;

            case 'sun':
                $day_bit = DUP_PRO_Schedule_Days::Sunday;
                break;
        }

        $is_set = (($this->weekly_days & $day_bit) != 0);

        $isset_string = $is_set ? 'yes' : 'no';

        //DUP_PRO_U::debug("is_day_set:daystring=$day_string daybit=$day_bit)");
        // DUP_PRO_U::debug("is set = $isset_string");

        return $is_set;
    }

    /**
     * Returns a list of all schedules
     *
     * @return DUP_PRO_Schedule_Entity[]
     */
    public static function get_all()
    {
        return self::get_by_type(get_class());
    }

    public static function delete_by_id($list_id)
    {
        parent::delete_by_id_base($list_id);
    }

    /**
     * Returns a list of all schedules associated with a storage
     *
     * @param int $storageID The storage id
     *
     * @return DUP_PRO_Schedule_Entity[]
     */
    public static function get_schedules_by_storage_id($storageID)
    {
        return array_filter(self::get_all(), function ($schedule) use ($storageID) {
            return  in_array($storageID, $schedule->storage_ids);
        });
    }

    /**
     * Runs the callback on all schedules
     *
     * @param $callback
     *
     * @return void
     */
    public static function run_on_all($callback)
    {
        if (!is_callable($callback)) {
            throw new Exception('No callback function passed');
        }

        foreach (self::get_all() as $schedule) {
            call_user_func($callback, $schedule);
        }
    }

    /**
     * Get active schedule
     *
     * @return self[]
     */
    public static function get_active()
    {
        $schedules        = self::get_all();
        $active_schedules = array();

        foreach ($schedules as $schedule) {
            /* @var $schedule DUP_PRO_Schedule_Entity */
            if ($schedule->active) {
                array_push($active_schedules, $schedule);
            }
        }
        return $active_schedules;
    }

    /**
     * Get stazrt time piece
     *
     * @param int $piece 0 = hour; 1 = minute;
     *
     * @return int
     */
    public function get_start_time_piece($piece)
    {
        switch ($piece) {
            case 0:
                return (int) date('G', $this->start_ticks);
            case 1:
                return (int) date('i', $this->start_ticks);
            default:
                return -1;
        }
    }

    public function get_next_run_time_string()
    {
        if ($this->next_run_time == -1) {
            return DUP_PRO_U::__('Unscheduled');
        } else {
            $date_portion   = SnapWP::getDateInWPTimezone(
                get_option('date_format', 'n/j/y') . ' G:i',
                $this->next_run_time
            );
            $repeat_portion = $this->get_repeat_text();
            return "$date_portion - $repeat_portion";
        }
    }

    public function get_last_ran_string()
    {
        if ($this->last_run_time == -1) {
            return DUP_PRO_U::__('Never Ran');
        } else {
            $date_portion   = SnapWP::getDateInWPTimezone(
                get_option('date_format', 'n/j/y') . ' G:i',
                $this->last_run_time
            );
            $status_portion = (($this->last_run_status == DUP_PRO_Package_Build_Outcome::SUCCESS) ? DUP_PRO_U::__('Success') : DUP_PRO_U::__('Failed'));
            return "$date_portion - $status_portion";
        }
    }

    public function set_start_date_time($start_time_string, $start_date_string = '2015/1/1')
    {
        $this->start_ticks = strtotime("$start_date_string $start_time_string");
        DUP_PRO_Log::trace("start ticks = $this->start_ticks for $start_time_string $start_date_string");
    }

    /**
     * Get schedule entity by id
     *
     * @param int $id
     *
     * @return null|self return entity or null if id don't exists
     */
    public static function get_by_id($id)
    {
        //Schedule Run Now = -1 don't search for id
        if ($id != -1) {
            return self::get_by_id_and_type($id, get_class());
        } else {
            return null;
        }
    }

    /**
     * Get schedules entity by template id
     *
     * @param int $template_id
     *
     * @return self[]
     */
    public static function get_by_template_id($template_id)
    {
        $schedules          = self::get_all();
        $filtered_schedules = array();

        foreach ($schedules as $schedule) {
            if ($schedule->template_id == $template_id) {
                array_push($filtered_schedules, $schedule);
            }
        }

        DUP_PRO_Log::trace("get by template id $template_id schedules = " . count($filtered_schedules));

        return $filtered_schedules;
    }

    /**
     * Return repeat text
     *
     * @return string
     */
    public function get_repeat_text()
    {
        switch ($this->repeat_type) {
            case DUP_PRO_Schedule_Repeat_Types::Daily:
                return DUP_PRO_U::__('Daily');
            case DUP_PRO_Schedule_Repeat_Types::Weekly:
                return DUP_PRO_U::__('Weekly');
            case DUP_PRO_Schedule_Repeat_Types::Monthly:
                return DUP_PRO_U::__('Monthly');
            case DUP_PRO_Schedule_Repeat_Types::Hourly:
                return DUP_PRO_U::__('Hourly');
            default:
                return DUP_PRO_U::__('Unknown');
        }
    }

    public function build_cron_string()
    {
        // Special cron string for debugging if name set to 'bobtest'
        if ($this->name == 'bobtest') {
            $this->cron_string = '*/5 * * * *';
        } else {
            $start_hour = $this->get_start_time_piece(0);
            $start_min  = $this->get_start_time_piece(1);

            if ($this->run_every == 1) {
                $run_every_string = '*';
            } else {
                $run_every_string = "*/$this->run_every";
            }

            // Generated cron patterns using http://www.cronmaker.com/
            switch ($this->repeat_type) {
                case DUP_PRO_Schedule_Repeat_Types::Hourly:
                    $this->cron_string = "$start_min $run_every_string * * *";
                    break;

                case DUP_PRO_Schedule_Repeat_Types::Daily:
                    $this->cron_string = "$start_min $start_hour $run_every_string * *";
                    break;

                case DUP_PRO_Schedule_Repeat_Types::Weekly:
                    $day_of_week_string = $this->get_day_of_week_string();
                    $this->cron_string  = "$start_min $start_hour * * $day_of_week_string";

                    DUP_PRO_Log::trace("day of week cron string: $this->cron_string");
                    break;

                case DUP_PRO_Schedule_Repeat_Types::Monthly:
                    $this->cron_string = "$start_min $start_hour $this->day_of_month $run_every_string *";
                    break;
            }
        }

        DUP_PRO_Log::trace("cron string = $this->cron_string");
    }

    private function get_day_of_week_string()
    {
        $day_array = array();

        DUP_PRO_Log::trace("weekly days=$this->weekly_days");


        if (($this->weekly_days & DUP_PRO_Schedule_Days::Monday) != 0) {
            array_push($day_array, '1');
        }

        if (($this->weekly_days & DUP_PRO_Schedule_Days::Tuesday) != 0) {
            array_push($day_array, '2');
        }

        if (($this->weekly_days & DUP_PRO_Schedule_Days::Wednesday) != 0) {
            array_push($day_array, '3');
        }

        if (($this->weekly_days & DUP_PRO_Schedule_Days::Thursday) != 0) {
            array_push($day_array, '4');
        }

        if (($this->weekly_days & DUP_PRO_Schedule_Days::Friday) != 0) {
            array_push($day_array, '5');
        }

        if (($this->weekly_days & DUP_PRO_Schedule_Days::Saturday) != 0) {
            array_push($day_array, '6');
        }

        if (($this->weekly_days & DUP_PRO_Schedule_Days::Sunday) != 0) {
            array_push($day_array, '0');
        }
        return implode(',', $day_array);
    }
}
