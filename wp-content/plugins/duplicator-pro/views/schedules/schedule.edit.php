<?php

use Duplicator\Addons\ProBase\License\License;
use Duplicator\Controllers\SchedulePageController;
use Duplicator\Core\Controllers\ControllersManager;

defined("ABSPATH") or die("");
DUP_PRO_U::hasCapability('export');

global $wp_version;
global $wpdb;

$nonce_action = 'duppro-schedule-edit';

$schedules_tab_url = ControllersManager::getMenuLink(
    ControllersManager::SCHEDULES_SUBMENU_SLUG,
    SchedulePageController::L2_SLUG_MAIN_SCHEDULES
);
$edit_schedule_url = ControllersManager::getMenuLink(
    ControllersManager::SCHEDULES_SUBMENU_SLUG,
    SchedulePageController::L2_SLUG_MAIN_SCHEDULES,
    null,
    array(
        'inner_page' => 'edit',
        '_wpnonce'   => wp_create_nonce('edit-schedule')
    )
);

$was_updated = false;
$schedule_id = isset($_REQUEST['schedule_id']) ? sanitize_text_field($_REQUEST['schedule_id']) : -1;

$frequency_note = DUP_PRO_U::__(
    'If you have a large site, it\'s recommended you schedule backups during lower traffic periods. ' .
        'If you\'re on a shared host then be aware that running multiple schedules too close together (i.e. every 10 minutes) ' .
        'may alert your host to a spike in system resource usage.  Be sure that your schedules do not overlap and give them plenty of time to run.'
);

if ($schedule_id == -1) {
    $schedule = new DUP_PRO_Schedule_Entity();
} else {
    $schedule = DUP_PRO_Schedule_Entity::get_by_id($schedule_id);
}

$min_frequency      = 0;
$max_frequency      = (License::can(License::CAPABILITY_SHEDULE_HOURLY) ? DUP_PRO_Schedule_Repeat_Types::Hourly : DUP_PRO_Schedule_Repeat_Types::Monthly);
$frequencyUpgradMsg = sprintf(
    __(
        'Hourly frequency isn\'t available at the <b>%1$s</b> license level.',
        'duplicator-pro'
    ),
    License::getLicenseToString()
) .
' <b>' .
sprintf(
    _x(
        'To enable this option %1$supgrade%2$s the License.',
        '%1$s and %2$s represents the opening and closing HTML tags for an anchor or link',
        'duplicator-pro'
    ),
    '<a href="' . esc_url(License::getUpsellURL()) . '" target="_blank">',
    '</a>'
) .
'</b>';

if (isset($_REQUEST['action'])) {
    // $_POST changed to the $_REQUEST because individual schedule copy can't verify nonce
    DUP_PRO_U::verifyNonce($_REQUEST['_wpnonce'], $nonce_action);
    if ($_REQUEST['action'] == 'save') {
        if (isset($_REQUEST['_storage_ids']) == false) {
            $_REQUEST['_storage_ids'] = array();
            array_push($_REQUEST['_storage_ids'], DUP_PRO_Virtual_Storage_IDs::Default_Local);
        }

        // Checkboxes don't set post values when off so have to manually set these
        $schedule->active = isset($_REQUEST['_active']);

        switch ($_REQUEST['repeat_type']) {
            case DUP_PRO_Schedule_Repeat_Types::Hourly:
                $_REQUEST['run_every'] = $_REQUEST['_run_every_hours'];
                DUP_PRO_Log::trace("run every hours: " . $_REQUEST['_run_every_hours']);
                break;

            case DUP_PRO_Schedule_Repeat_Types::Daily:
                $_REQUEST['run_every'] = $_REQUEST['_run_every_days'];
                DUP_PRO_Log::trace("run every days: " . $_REQUEST['_run_every_days']);
                break;

            case DUP_PRO_Schedule_Repeat_Types::Monthly:
                $_REQUEST['run_every'] = $_REQUEST['_run_every_months'];
                DUP_PRO_Log::trace("run every months: " . $_REQUEST['_run_every_months']);
                break;

            case DUP_PRO_Schedule_Repeat_Types::Weekly:
                $schedule->set_weekdays_from_request($_REQUEST);
                break;
        }

        $schedule->storage_ids = $_REQUEST['_storage_ids'];
        $schedule->set_start_date_time($_REQUEST['_start_time']);
        $schedule->set_post_variables($_REQUEST);
        $schedule->build_cron_string();
        $schedule->next_run_time = $schedule->get_next_run_time();
        $schedule->save();
        $was_updated = true;
    } elseif ($_REQUEST['action'] == 'copy-schedule') {
        $source_id = $_REQUEST['duppro-source-schedule-id'];

        if ($source_id != -1) {
            $schedule->copy_from_source_id($source_id);
            $schedule->save();
        }
    }
}

$schedules      = DUP_PRO_Schedule_Entity::get_all();
$schedule_count = count($schedules);

$langLocalDefaultMsg = __('Recovery Point Capable', 'duplicator-pro');
?>

<style>
    table.dpro-edit-toolbar select {
        float: left
    }

    table.package-tbl thead th {
        padding: 8px
    }

    table.package-tbl tbody td {
        padding: 8px
    }

    table.package-tbl .package-row label:hover {
        font-weight: bold;
    }

    input[type=text].date {
        width: 115px
    }

    .ui-datepicker-trigger {
        border: none;
        background: none;
    }

    div#repeat-daily-area {
        display: none
    }

    div#repeat-weekly-area {
        display: none;
        width: 480px;
        height: 78px;
        padding-left: 5px;
        margin-left: -5px;
    }

    div#repeat-monthly-area {
        display: none
    }

    div#repeat-weekly-area table td {
        padding-left: 0px;
    }

    div.repeater-area {
        margin: 3px 0 0 3px;
        line-height: 35px;
        min-height: 42px
    }

    .schedule-template td {
        vertical-align: top;
        padding: 0;
    }

    .schedule-template .dup-recovery-template {
        padding: 7px 20px;
    }

    #schedule-name,
    #schedule-template {
        width: 350px
    }

    select#schedule-template {
        margin-left: -1px
    }

    .weekday-div {
        float: left;
        margin-right: 15px;
        width: 105px;
    }

    a.pack-temp-btns {
        margin-top: 2px !important;
        font-size: 12px !important;
        line-height: 24px !important;
        height: 26px !important;
    }
</style>


<form id="dup-schedule-form" action="<?php echo esc_url($edit_schedule_url); ?>" method="post" data-parsley-ui-enabled="true">
    <?php wp_nonce_field($nonce_action); ?>
    <input type="hidden" id="dup-schedule-form-action" name="action" value="save">
    <input type="hidden" name="schedule_id" value="<?php echo $schedule->id; ?>">

    <!-- ====================
    TOOL-BAR -->
    <table class="dpro-edit-toolbar">
        <tr>
            <td>
                <?php if ($schedule_count > 0) : ?>
                    <select name="duppro-source-schedule-id">
                        <option value="-1" selected="selected"><?php _e("Copy From"); ?></option>
                        <?php
                        foreach ($schedules as $copy_schedule) {
                            if ($copy_schedule->id != $schedule->id) {
                                echo "<option value='{$copy_schedule->id}'>{$copy_schedule->name}</option>";
                            }
                        }
                        ?>
                    </select>
                    <input type="button" class="button action" value="<?php DUP_PRO_U::esc_html_e("Apply") ?>" onclick="DupPro.Schedule.Copy()">
                <?php else : ?>
                    <select disabled="disabled">
                        <option value="-1" selected="selected"><?php _e("Copy From"); ?></option>
                    </select>
                    <input type="button" class="button action" value="<?php DUP_PRO_U::esc_html_e("Apply") ?>" onclick="DupPro.Schedule.Copy()" disabled="disabled">
                <?php endif; ?>
            </td>
            <td>
                <div class="btnnav">
                    <a href="<?php echo $schedules_tab_url; ?>" class="button dup-schedule-schedules"> <i class="far fa-clock fa-sm"></i> <?php DUP_PRO_U::esc_html_e('Schedules'); ?></a>
                    <?php if ($schedule_id != -1) : ?>
                        <a href="admin.php?page=duplicator-pro-schedules&tab=schedules&inner_page=edit&_wpnonce=<?php echo wp_create_nonce('edit-schedule'); ?>" class="button"><?php DUP_PRO_U::esc_html_e("Add New"); ?></a>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
    </table>
    <hr class="dpro-edit-toolbar-divider" />


    <?php if ($was_updated) : ?>
        <div class="notice notice-success is-dismissible dpro-wpnotice-box">
            <p><?php DUP_PRO_U::esc_html_e("Schedule Updated"); ?></p>
        </div>
    <?php endif; ?>

    <!-- ===============================
    SETTINGS -->
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><label><?php _e('Schedule Name', 'duplicator-pro'); ?></label></th>
            <td>
                <input type="text" id="schedule-name" name="name" value="<?php echo $schedule->name; ?>" required data-parsley-group="standard" autocomplete="off">
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label><?php _e('Package Template', 'duplicator-pro'); ?></label></th>
            <td>
                <table class="schedule-template">
                    <tr>
                        <td>
                            <select id="schedule-template" name="template_id" required>
                                <?php
                                $templates = DUP_PRO_Package_Template_Entity::getAllWithoutManualMode();
                                if (count($templates) == 0) {
                                    $no_templates = __('No Templates Found', 'duplicator-pro');
                                    echo "<option value=''>$no_templates</option>";
                                } else {
                                    echo "<option value='' selected='true'>" . DUP_PRO_U::esc_html__("&lt;Choose A Template&gt;") . "</option>";
                                    foreach ($templates as $template) {
                                        ?>
                                        <option <?php DUP_PRO_UI::echoSelected($schedule->template_id == $template->getId()); ?> value="<?php echo $template->getId(); ?>">
                                            <?php echo $template->name; ?>
                                        </option>
                                        <?php
                                    }
                                }
                                ?>
                            </select>   
                            <br />
                            <small><a href="admin.php?page=duplicator-pro-tools&tab=templates" target="edit-template">[<?php DUP_PRO_U::esc_attr_e("Show All Templates") ?>]</a></small>
                        </td>
                        <td>
                            <a 
                                id="schedule-template-edit-btn" 
                                href="javascript:void(0)" 
                                onclick="DupPro.Schedule.EditTemplate()" 
                                style="display:none" 
                                class="pack-temp-btns button button-small" 
                                title="<?php DUP_PRO_U::esc_attr_e("Edit Selected Template") ?>"
                            >
                                <i class="far fa-edit"></i>
                            </a>
                            <a 
                                id="schedule-template-add-btn" 
                                href="admin.php?page=duplicator-pro-tools&tab=templates&inner_page=edit" 
                                class="pack-temp-btns button button-small" 
                                title="<?php DUP_PRO_U::esc_attr_e("Add New Template") ?>" 
                                target="edit-template"
                            >
                                <i class="far fa-plus-square"></i>
                            </a>
                            <a 
                                id="schedule-template-sync-btn" 
                                href="javascript:window.location.reload()" 
                                class="pack-temp-btns button button-small" 
                                title="<?php DUP_PRO_U::esc_attr_e("Refresh Template List") ?>"
                            >
                                <i class="fas fa-sync-alt"></i>
                            </a>

                            <i class="fas fa-question-circle fa-sm" data-tooltip-title="<?php DUP_PRO_U::esc_attr_e("Template Details"); ?>" data-tooltip="<?php
                            DUP_PRO_U::esc_attr_e('The template specifies which files and database tables should be included in the '
                                . 'archive.<br/><br/>  Choose from an existing template or create a new one by clicking '
                                . 'the "Add New Template" button. To edit a template, select it and then click the "Edit Selected Template" button.');
                            ?>"></i>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <th scope="row"><label><?php _e("Storage"); ?></label></th>
            <td>
                <!-- ===============================
                STORAGE -->
                <table class="widefat package-tbl">
                    <thead>
                        <tr>
                            <th style="width:125px;padding-left:45px"><?php DUP_PRO_U::esc_html_e('Type') ?></th>
                            <th style="width:275px;"><?php DUP_PRO_U::esc_html_e('Name') ?></th>
                            <th><?php DUP_PRO_U::esc_html_e('Location') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i        = 0;
                        $storages = DUP_PRO_Storage_Entity::get_all();
                        foreach ($storages as $storage) :
                            if (!$storage->is_authorized()) {
                                continue;
                            }

                            //Sometime storage is authorized
                            //      then server downgrade to lower php version
                            // For ex. When storage is added PHP CURL extension enabled
                            //      But now It is disabled, It cause to fatal error
                            //          in the Package creation step 1
                            if (!DUP_PRO_StorageSupported::isStorageObjStorageTypeSupported($storage)) {
                                continue;
                            }

                            $i++;
                            $is_valid   = $storage->is_valid();
                            $is_checked = in_array($storage->id, $schedule->storage_ids);
                            $mincheck   = ($i == 1) ? 'data-parsley-mincheck="1" data-parsley-required="true"' : '';
                            $store_type = $storage->get_storage_type_string();
                            $store_id   = $storage->get_storage_type();
                            $lbl_id     = "storage_chk_{$storage->id}";

                            $isDefaultStorage = ($storage->id == '-2');
                            $isLocalStorage   = ($storage->storage_type == 0);
                            $storageEditUrl   = ($isDefaultStorage)
                                        ? "?page=duplicator-pro-storage&tab=storage&inner_page=edit-default"
                                        : "?page=duplicator-pro-storage&tab=storage&inner_page=edit&storage_id={$storage->id}";
                            ?>
                            <tr class="package-row <?php echo ($i % 2) ? 'alternate' : ''; ?>">
                                <td>
                                    <input data-parsley-errors-container="#schedule_storage_error_container" <?php echo $mincheck ?> 
                                           id="<?php echo $lbl_id; ?>" name="_storage_ids[]" type="checkbox" value="<?php echo $storage->id; ?>"
                                           <?php DUP_PRO_UI::echoChecked($is_checked); ?> class="delete-chk" /> &nbsp; &nbsp;
                               
                                    <label for="<?php echo $lbl_id; ?>">
                                        <?php
                                             echo ($isDefaultStorage)
                                                    ? '<i class="far fa-hdd fa-fw"></i>&nbsp;'
                                                    : DUP_PRO_Storage_Entity::getStorageIcon($store_id) . '&nbsp;';
                                            echo $store_type;
                                            echo ($isLocalStorage)
                                                ? "<sup title='{$langLocalDefaultMsg}'><i class='fas fa-undo-alt fa-fw fa-sm'></i></sup>"
                                                : '';
                                        ?>
                                    </label>
                                </td>
                                <td>
                                     <a href="<?php echo $storageEditUrl; ?>" target="_blank">
                                        <?php
                                            echo ($is_valid == false)  ? '<i class="fa fa-exclamation-triangle fa-sm"></i> '  : '';
                                            echo esc_html($storage->name);
                                        ?>
                                    </a>
                                </td>
                                <td><?php echo $storage->getHtmlLocationLink(); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div id="schedule_storage_error_container" class="duplicator-error-container"></div>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label><?php DUP_PRO_U::esc_html_e("Repeats"); ?></label></th>
            <td>
                <select 
                    id="change-mode" 
                    name="repeat_type" 
                    onchange="DupPro.Schedule.ChangeMode()" 
                    data-parsley-range='<?php echo "[$min_frequency, $max_frequency]" ?>' 
                    data-parsley-error-message="<?php echo esc_attr($frequencyUpgradMsg); ?>"
                >
                    <option <?php DUP_PRO_UI::echoSelected($schedule->repeat_type == DUP_PRO_Schedule_Repeat_Types::Hourly) ?> value="<?php echo DUP_PRO_Schedule_Repeat_Types::Hourly; ?>"><?php DUP_PRO_U::esc_html_e("Hourly"); ?></option>
                    <option <?php DUP_PRO_UI::echoSelected($schedule->repeat_type == DUP_PRO_Schedule_Repeat_Types::Daily) ?> value="<?php echo DUP_PRO_Schedule_Repeat_Types::Daily; ?>"><?php DUP_PRO_U::esc_html_e("Daily"); ?></option>
                    <option <?php DUP_PRO_UI::echoSelected($schedule->repeat_type == DUP_PRO_Schedule_Repeat_Types::Weekly) ?> value="<?php echo DUP_PRO_Schedule_Repeat_Types::Weekly; ?>"><?php DUP_PRO_U::esc_html_e("Weekly"); ?></option>
                    <option <?php DUP_PRO_UI::echoSelected($schedule->repeat_type == DUP_PRO_Schedule_Repeat_Types::Monthly) ?> value="<?php echo DUP_PRO_Schedule_Repeat_Types::Monthly; ?>"><?php DUP_PRO_U::esc_html_e("Monthly"); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <th></th>
            <td style="padding-top:0px; padding-bottom:10px;">
                <!-- ===============================
            DAILY -->
                <div id="repeat-hourly-area" class="repeater-area">
                    <?php
                    _e("Every");
                    $hour_intervals = array(1, 2, 4, 6, 12);
                    ?>

                    <select name="_run_every_hours" data-parsley-ui-enabled="false">
                        <?php
                        foreach ($hour_intervals as $hour_interval) {
                            $hour_interval_selected_string = DUP_PRO_UI::getSelected($hour_interval == (int) $schedule->run_every);
                            echo "<option $hour_interval_selected_string>{$hour_interval}</option>";
                        }
                        ?>
                    </select>
                    <?php _e("hours"); ?>
                    <i 
                        class="fas fa-question-circle fa-sm" 
                        data-tooltip-title="<?php DUP_PRO_U::esc_attr_e("Frequency Note"); ?>" 
                        data-tooltip="<?php echo DUP_PRO_U::__('Package will build every x hours starting at 00:00.') . '<br/><br/>' . $frequency_note; ?>">
                    </i>
                    <br />
                </div>

                <!-- ===============================
            DAILY -->
                <div id="repeat-daily-area" class="repeater-area">
                    <?php _e("Every"); ?>
                    <select name="_run_every_days" data-parsley-ui-enabled="false">
                        <?php
                        for ($i = 1; $i < 30; $i++) {
                            $day_selected_string = DUP_PRO_UI::getSelected($i == (int) $schedule->run_every);
                            echo "<option $day_selected_string>{$i}</option>";
                        }
                        ?>
                    </select>
                    <?php _e("days"); ?>
                    <i class="fas fa-question-circle fa-sm" data-tooltip-title="<?php DUP_PRO_U::esc_attr_e("Frequency Note"); ?>" data-tooltip="<?php echo $frequency_note ?>"></i>
                    <br />
                </div>

                <!-- ===============================
                WEEKLY -->
                <div id="repeat-weekly-area" class="repeater-area">
                    <!-- RSR Cron does not support counting by week - just days and months so removing (for now?)-->
                    <div class="weekday-div">
                        <input <?php DUP_PRO_UI::echoChecked($schedule->is_day_set('mon')); ?> value="mon" name="weekday[]" type="checkbox" id="repeat-weekly-mon"
                                data-parsley-group="weekly" required data-parsley-class-handler="#repeat-weekly-area"
                                data-parsley-error-message="<?php DUP_PRO_U::esc_attr_e('At least one day must be checked.'); ?>"
                                data-parsley-no-focus data-parsley-errors-container="#weekday-errors" />
                        <label for="repeat-weekly-mon"><?php _e("Monday"); ?></label>
                    </div>
                    <div class="weekday-div">
                        <input <?php DUP_PRO_UI::echoChecked($schedule->is_day_set('tue')); ?> value="tue" name="weekday[]" type="checkbox" id="repeat-weekly-tue" />
                        <label for="repeat-weekly-tue"><?php _e("Tuesday"); ?></label>
                    </div>
                    <div class="weekday-div">
                        <input <?php DUP_PRO_UI::echoChecked($schedule->is_day_set('wed')); ?> value="wed" name="weekday[]" type="checkbox" id="repeat-weekly-wed" />
                        <label for="repeat-weekly-wed"><?php _e("Wednesday"); ?></label>
                    </div>
                    <div class="weekday-div">
                        <input <?php DUP_PRO_UI::echoChecked($schedule->is_day_set('thu')); ?> value="thu" name="weekday[]" type="checkbox" id="repeat-weekly-thu" />
                        <label for="repeat-weekly-thu"><?php _e("Thursday"); ?></label>
                    </div>
                    <div class="weekday-div" style="clear:both">
                        <input <?php DUP_PRO_UI::echoChecked($schedule->is_day_set('fri')); ?> value="fri" name="weekday[]" type="checkbox" id="repeat-weekly-fri" />
                        <label for="repeat-weekly-fri"><?php _e("Friday"); ?></label>
                    </div>
                    <div class="weekday-div">
                        <input <?php DUP_PRO_UI::echoChecked($schedule->is_day_set('sat')); ?> value="sat" name="weekday[]" type="checkbox" id="repeat-weekly-sat" />
                        <label for="repeat-weekly-sat"><?php _e("Saturday"); ?></label>
                    </div>
                    <div class="weekday-div">
                        <input <?php DUP_PRO_UI::echoChecked($schedule->is_day_set('sun')); ?> value="sun" name="weekday[]" type="checkbox" id="repeat-weekly-sun" />
                        <label for="repeat-weekly-sun"><?php _e("Sunday"); ?></label>
                    </div>
                </div>
                <div style="padding-top:3px; clear:both;" id="weekday-errors"></div>

                <!-- ===============================
                MONTHLY -->
                <div id="repeat-monthly-area" class="repeater-area">

                    <div style="float:left; margin-right:5px;"><?php DUP_PRO_U::esc_html_e('Day'); ?>
                        <select name="day_of_month">
                            <?php
                            for ($i = 1; $i <= 31; $i++) {
                                $day_of_month_selected_string = DUP_PRO_UI::getSelected($i == $schedule->day_of_month);
                                echo "<option $day_of_month_selected_string>{$i}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div style="display:inline-block">
                        <?php _e("of every"); ?>
                        <select name="_run_every_months" data-parsley-ui-enabled="false">
                            <?php
                            for ($i = 1; $i <= 12; $i++) {
                                $month_selected_string = DUP_PRO_UI::getSelected($i == $schedule->run_every);
                                echo "<option $month_selected_string>{$i}</option>";
                            }
                            ?>
                        </select>
                        <?php _e("month(s)"); ?>
                    </div>
                </div>
            </td>
        </tr>

        <tr valign="top" id="start-time-row">
            <th scope="row"><label><?php DUP_PRO_U::esc_html_e('Start Time'); ?></label></th>
            <td>
                <select name="_start_time" style="margin-top:-2px; height:27px">
                    <?php
                    $start_hour = $schedule->get_start_time_piece(0);
                    $start_min  = $schedule->get_start_time_piece(1);
                    $mins       = 0;

                    //Add setting to use 24 hour vs AM/PM
                    // the interval for hours is '1'
                    for ($hours = 0; $hours < 24; $hours++) {
                        ?>
                        <option <?php selected($hours, $start_hour); ?> >
                            <?php printf('%02d:%02d', $hours, $mins); ?>
                        </option>
                    <?php } ?>
                </select>

                <i class="dpro-edit-info">
                    <?php DUP_PRO_U::esc_html_e("Current Server Time Stamp is"); ?>&nbsp;
                    <?php echo date_i18n('Y-m-d H:i:s'); ?>
                </i>
            </td>
        </tr>
        <tr>
            <td>
            </td>
            <td>
                <p class="description" style="width:800px">
                    <?php
                    echo wp_kses(
                        DUP_PRO_U::__('<b>Note:</b> Schedules require web site traffic in order to start a build.  If you set a start time of 06:00 daily but do not get any traffic '
                            . 'till 10:00 then the build will not start until 10:00.  If you have low traffic consider setting up a cron job to periodically hit your site or check out '
                            . 'the free web monitoring tools found on our <a href="https://snapcreek.com/partners/#tools" target="_blank">partners page</a>.'),
                        array(
                            'b' => array(),
                            'a' => array(
                                'href'   => array(),
                                'target' => array()
                                      )
                        )
                    );
                    ?>
                </p>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label><?php _e('Recovery Status', 'duplicator-pro'); ?></label></th>
            <td class="dup-recovery-template">
                <?php
                if (($template = $schedule->getTemplate()) !== false) {
                    $schedule->recoveableHtmlInfo();
                } else {
                    _e('Unavailable', 'duplicator-pro');
                    ?>
                    <i class="fas fa-question-circle fa-sm"
                       data-tooltip-title="<?php DUP_PRO_U::esc_attr_e("Recovery Status"); ?>"
                       data-tooltip="<?php _e('Status is unavailable. Please save the schedule to view recovery status', 'duplicator-pro');
                        ?>"></i>
                <?php } ?>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="schedule-active"><?php _e("Activated"); ?></label></th>
            <td>
                <input name="_active" id="schedule-active" type="checkbox" <?php DUP_PRO_UI::echoChecked($schedule->active); ?>>
                <label for="schedule-active"><?php DUP_PRO_U::esc_html_e('Enable This Schedule'); ?></label><br />
                <i class="dpro-edit-info"> <?php _e("When checked this schedule will run"); ?></i>
            </td>
        </tr>
    </table><br />
    <button 
        id="dup-pro-save-schedule" 
        class="button button-primary" 
        type="submit" 
        onclick="return DupPro.Schedule.Validate();"
        <?php disabled(($schedule->id > 0)); ?>
    >
        <?php DUP_PRO_U::esc_html_e('Save Schedule'); ?>
    </button>

</form>

<script>
    jQuery(document).ready(function ($) {
        DupPro.Schedule.Validate = function () {

        };

        DupPro.Schedule.ChangeMode = function () {
            var mode = $("#change-mode option:selected").val();
            var animate = 400;
            $('#repeat-hourly-area, #repeat-daily-area, #repeat-weekly-area, #repeat-monthly-area').hide();
            n = $("#repeat-weekly-area input:checked").length;

            if (n == 0) {
                // Hack so parsely will ignore weekly if it isnt selected
                $('#repeat-weekly-mon').prop("checked", true);
            }

            switch (mode) {
                case "0":
                    $('#repeat-daily-area').show(animate);
                    $('#start-time-row').show(animate);
                    break;
                case "1":
                    $('#repeat-weekly-area').show(animate);
                    $('#start-time-row').show(animate);
                    break;
                case "2":
                    $('#repeat-monthly-area').show(animate);
                    $('#start-time-row').show(animate);
                    break;
                case "3":
                    $('#repeat-hourly-area').show(animate);
                    $('#start-time-row').hide(animate);
                    break;

            }
        }

        DupPro.Schedule.Copy = function () {
            $("#dup-schedule-form-action").val('copy-schedule');
            $("#dup-schedule-form").parsley().destroy();
            $("#dup-schedule-form").submit();
        };

        DupPro.Schedule.EditTemplate = function () {
            var templateID = $('#schedule-template').val();
<?php
$template_edit_nonce = wp_create_nonce('edit-template');
?>
            var url = '?page=duplicator-pro-tools&tab=templates&inner_page=edit&package_template_id=' + templateID + '&_wpnonce=' + '<?php echo $template_edit_nonce; ?>';
            window.open(url, 'edit-template');
        };

        DupPro.Schedule.ToggleTemplateEditBtn = function () {
            $('#schedule-template-edit-btn, #schedule-template-add-btn, #schedule-template-sync-btn').hide();
            if ($("#schedule-template").val() > 0) {
                $('#schedule-template-edit-btn').show();
            } else {
                $('#schedule-template-add-btn, #schedule-template-sync-btn').show();
            }
        }

        // Toggles Save Schedule button for existing Schedules only
        DupPro.UI.formOnChangeValues($('#dup-schedule-form'), function() {
            $('#dup-pro-save-schedule').prop('disabled', false);
        });

        //INIT
        $('#dup-schedule-form').parsley({
            excluded: ':disabled'
        });

        $("#repeat-daily-date, #repeat-daily-on-date").datepicker({
            showOn: "both",
            buttonText: "<i class='fa fa-calendar'></i>"
        });
        DupPro.Schedule.ChangeMode();
        jQuery('#schedule-name').focus().select();
        DupPro.Schedule.ToggleTemplateEditBtn();
        $("#schedule-template").change(function () {
            DupPro.Schedule.ToggleTemplateEditBtn()
        });
        
    });
</script>
