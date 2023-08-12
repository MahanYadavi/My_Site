<?php

use Duplicator\Ajax\ServicesSchedule;
use Duplicator\Controllers\SchedulePageController;
use Duplicator\Core\Controllers\ControllersManager;

defined("ABSPATH") or die("");
DUP_PRO_U::hasCapability('export');
$nonce_action = 'duppro-schedule-list';

$active_schedules = DUP_PRO_Schedule_Entity::get_active();
$active_count     = count($active_schedules);

$schedules      = DUP_PRO_Schedule_Entity::get_all();
$schedule_count = count($schedules);

$active_package     = DUP_PRO_Package::get_next_active_package();
$active_schedule_id = -1;

if ($active_package != null) {
    $active_schedule_id = $active_package->schedule_id;
}

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
?>

<style>
    /*Detail Tables */
    table.schedule-tbl td {
        height: 45px
    }

    table.schedule-tbl a.name {
        font-weight: bold
    }

    table.schedule-tbl input[type='checkbox'] {
        margin-left: 5px
    }

    table.schedule-tbl div.sub-menu {
        margin: 5px 0 0 2px;
        display: none
    }

    table.schedule-tbl div.sub-menu a:hover {
        text-decoration: underline
    }

    tr.schedule-detail {
        display: none;
    }

    tr.schedule-detail td {
        padding: 2px 2px 2px 15px;
        margin: -5px 0 2px 0;
        height: 22px
    }

    td.dpro-no-data {
        text-align: center;
        background: #fff;
        padding: 40px;
        line-height: 30px
    }
</style>

<!-- ====================
TOOL-BAR -->
<table class="dpro-edit-toolbar">
    <tr>
        <td>
            <select id="bulk_action">
                <option value="-1" selected="selected">
                    <?php _e("Bulk Actions", 'duplicator-pro'); ?>
                </option>
                <option value="<?php echo ServicesSchedule::SCHEDULE_BULK_ACTIVATE; ?>" title="Activate selected schedules(s)">
                    <?php _e("Activate", 'duplicator-pro'); ?>
                </option>
                <option value="<?php echo ServicesSchedule::SCHEDULE_BULK_DEACTIVATE; ?>" title="Deactivate selected schedules(s)">
                    <?php _e("Deactivate", 'duplicator-pro'); ?>
                </option>
                <option value="<?php echo ServicesSchedule::SCHEDULE_BULK_DELETE; ?>" title="Delete selected schedules(s)">
                    <?php _e("Delete", 'duplicator-pro'); ?>
                </option>
            </select>
            <input type="button" id="dup-schedule-bulk-apply" class="button action" value="<?php DUP_PRO_U::esc_attr_e("Apply") ?>" onclick="DupPro.Schedule.BulkAction()">
            <span class="btn-separator"></span>
            <a href="admin.php?page=duplicator-pro-settings&tab=schedule" class="button grey-icon dup-schedule-settings"
               title="<?php DUP_PRO_U::esc_attr_e("Settings") ?>">
                <i class="fas fa-sliders-h fa-fw"></i>
            </a>
            <a href="admin.php?page=duplicator-pro-tools&tab=templates" id="btn-logs-dialog" class="button dup-schedule-templates" title="<?php DUP_PRO_U::esc_attr_e("Templates") ?>"><i class="far fa-clone"></i></a>
        </td>
        <td>
            <div class="btnnav">
                <a href="<?php echo $edit_schedule_url; ?>" class="button dup-schedule-add-new"><?php DUP_PRO_U::esc_attr_e("Add New"); ?></a>
            </div>
        </td>
    </tr>
</table>

<form id="dup-schedule-form" action="<?php echo $schedules_tab_url; ?>" method="post">
    <?php wp_nonce_field($nonce_action); ?>
    <input type="hidden" id="dup-schedule-form-action" name="action" value="" />
    <input type="hidden" id="dup-schedule-selected-schedule" name="schedule_id" value="-1" />

    <!-- ====================
    LIST ALL SCHEDULES -->
    <table class="widefat schedule-tbl">
        <thead>
            <tr>
                <th style='width:10px;'><input type="checkbox" id="dpro-chk-all" title="Select all packages" onclick="DupPro.Schedule.SetDeleteAll(this)"></th>
                <th style='width:255px;'><?php DUP_PRO_U::esc_html_e('Name'); ?></th>
                <th><?php DUP_PRO_U::esc_html_e('Storage'); ?></th>
                <th><?php DUP_PRO_U::esc_html_e('Runs Next'); ?></th>
                <th><?php DUP_PRO_U::esc_html_e('Last Ran'); ?></th>
                <th><?php DUP_PRO_U::esc_html_e('Active'); ?></th>
                <th class="dup-col-recovery" ><?php _e('Recovery', 'duplicator-pro'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($schedule_count <= 0) : ?>
                <tr>
                    <td colspan="7" class="dpro-no-data">
                        <h2>
                            <i class="far fa-clock fa-sm"></i> <?php DUP_PRO_U::esc_html_e('No Schedules Found') ?> <br />
                            <a href="<?php echo $edit_schedule_url; ?>">[<?php DUP_PRO_U::esc_html_e('Create New Schedule') ?>]</a>
                        </h2>
                    </td>
                </tr>
            <?php endif; ?>

            <?php
            $i = 0;
            foreach ($schedules as $schedule) :
                /* @var $schedule DUP_PRO_Schedule_Entity */
                $i++;
                $icon_display = (($schedule->id == $active_schedule_id) ? 'inline' : 'none');
                ?>
                <tr class="schedule-row <?php echo ($i % 2) ? 'alternate' : ''; ?>">
                    <td>
                        <input name="selected_id[]" type="checkbox" value="<?php echo $schedule->id ?>" class="item-chk" />
                    </td>
                    <td>
                        <i id="<?php echo "icon-{$schedule->id}-status"; ?>" class="fas fa-cog fa-spin schedule-status-icon" style="display:<?php echo $icon_display; ?>; margin-right:4px;"></i>
                        <a id="<?php echo "text-{$schedule->id}"; ?>" href="javascript:void(0);" onclick="DupPro.Schedule.Edit('<?php echo $schedule->id ?>');" class="name"><?php echo $schedule->name; ?></a>
                        <div class="sub-menu">
                            <a href="javascript:void(0);" class="dup-schedule-quick-view" onclick="DupPro.Schedule.QuickView('<?php echo $schedule->id ?>');"><?php DUP_PRO_U::esc_html_e('Quick View'); ?></a> |
                            <a href="javascript:void(0);" class="dup-schedule-edit" onclick="DupPro.Schedule.Edit('<?php echo $schedule->id ?>');"><?php DUP_PRO_U::esc_html_e('Edit'); ?></a> |
                            <a href="javascript:void(0);" class="dup-schedule-copy" onclick="DupPro.Schedule.Copy('<?php echo $schedule->id; ?>');"><?php DUP_PRO_U::esc_html_e('Copy'); ?></a> |
                            <a href="javascript:void(0);" class="dup-schedule-delete" onclick="DupPro.Schedule.Delete('<?php echo $schedule->id; ?>');"><?php DUP_PRO_U::esc_html_e('Delete'); ?></a> |
                            <a href="javascript:void(0);" class="dup-schedule-run-now" onclick="DupPro.Schedule.RunNow('<?php echo $schedule->id; ?>');"><?php DUP_PRO_U::esc_html_e('Run Now'); ?></a>
                        </div>
                    </td>
                    <td>
                        <?php
                        if (count($schedule->storage_ids) > 0 && strlen(implode('', $schedule->storage_ids)) != 0) {
                            foreach ($schedule->storage_ids as $storage_id) {
                                /* @var $storage DUP_PRO_Storage_Entity */
                                $storage = DUP_PRO_Storage_Entity::get_by_id($storage_id);
                                echo $storage->name;
                                echo '<br/>';
                            }
                        } else {
                            $txt_DeleteStorage = __('No Storage', 'duplicator-pro');
                            echo "<a href='javascript:void(0)' onclick='DupPro.Schedule.showDeleteStorageMessage()'>"
                                . "<i class='fa fa-info-circle fa-fw fa-sm'></i>"
                                . "<u>{$txt_DeleteStorage}</u></a>";
                        }
                        ?>
                    </td>
                    <td>
                        <?php echo $schedule->get_next_run_time_string(); ?>
                    </td>
                    <td id="schedule-<?php echo $schedule->id ?>-last-ran-string">
                        <?php echo $schedule->get_last_ran_string(); ?>
                    </td>
                    <td>
                        <b>
                            <?php if ($schedule->active) { ?>
                                <span class="green" ><?php _e('Yes', 'duplicator-pro'); ?></span>
                            <?php } else { ?>
                                <span class="maroon" ><?php _e('No', 'duplicator-pro'); ?></span>
                            <?php } ?>
                        </b>
                    </td>
                    <td class="dup-col-recovery" >
                        <?php $schedule->recoveableHtmlInfo(true); ?>
                    </td>
                </tr>
                <tr id='detail-<?php echo $schedule->id ?>' class='<?php echo ($i % 2) ? 'alternate' : ''; ?> schedule-detail'>
                    <td colspan="5">
                        <?php
                        $template = DUP_PRO_Package_Template_Entity::getById($schedule->template_id);
                        ?>
                        <table style="line-height: 15px">
                            <tr>
                                <td><b><?php echo DUP_PRO_U::__('Package Template:'); ?></b></td>
                                <td colspan="3"><?php echo $template->name; ?></td>
                            </tr>
                            <tr>
                                <td><b><?php echo DUP_PRO_U::__('Summary:'); ?></b></td>
                                <td colspan="3"><?php echo sprintf(DUP_PRO_U::__('Runs %1$s'), $schedule->get_repeat_text()); ?></td>
                            </tr>
                            <tr>
                                <td><b><?php echo DUP_PRO_U::__('Last Ran:') ?></b></td>
                                <td><?php echo $schedule->get_last_ran_string(); ?></td>
                            </tr>
                            <tr>
                                <td><b><?php echo DUP_PRO_U::__('Times Run:') ?></b></td>
                                <td><?php echo $schedule->times_run; ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="7" style="text-align:right; white-space: nowrap; font-size:12px">
                    <?php
                    echo DUP_PRO_U::__('Total') . ': ' . $schedule_count . ' | ';
                    echo DUP_PRO_U::__('Active') . ': ' . $active_count . ' | ';
                    echo DUP_PRO_U::__("Time") . ': ' . '<span id="dpro-clock-container"></span>';
                    ?>
                </th>
            </tr>
        </tfoot>
    </table>
</form>
<?php
$alert1          = new DUP_PRO_UI_Dialog();
$alert1->title   = DUP_PRO_U::__('Bulk Action Required');
$alert1->message = DUP_PRO_U::__('Please select an action from the "Bulk Actions" drop down menu!');
$alert1->initAlert();

$alert2                      = new DUP_PRO_UI_Dialog();
$alert2->title               = DUP_PRO_U::__('Selection Required');
$alert2->wrapperClassButtons = 'dpro-dlg-noschedule-sel-bulk-action-btns';
$alert2->message             = DUP_PRO_U::__('Please select at least one schedule to perform the action on!');
$alert2->initAlert();

$alert3           = new DUP_PRO_UI_Dialog();
$alert3->title    = DUP_PRO_U::__('No Storage');
$alert3->message  = __('All storage types associated with this schedule have been deleted.&nbsp;', 'duplicator-pro');
$alert3->message .= __('To enable this schedule please assign a valid storage type.', 'duplicator-pro');
$alert3->message .= '<br/><br/>';
$alert3->initAlert();

$confirm1                      = new DUP_PRO_UI_Dialog();
$confirm1->title               = DUP_PRO_U::__('Delete Schedule?');
$confirm1->wrapperClassButtons = 'dpro-dlg-delete-schedules-btns';
$confirm1->message             = DUP_PRO_U::__('Are you sure you want to delete the selected schedule(s)?');
$confirm1->message            .= '<br/>';
$confirm1->message            .= DUP_PRO_U::__('<small><i>Note: This action removes all schedules.</i></small>');
$confirm1->progressText        = DUP_PRO_U::__('Removing Schedules, Please Wait...');
$confirm1->jsCallback          = 'DupPro.Schedule.BulkDelete()';
$confirm1->initConfirm();

$confirm4                      = new DUP_PRO_UI_Dialog();
$confirm4->title               = DUP_PRO_U::__('Activate Schedule?');
$confirm4->wrapperClassButtons = 'dpro-dlg-activate-schedules-btns';
$confirm4->message             = DUP_PRO_U::__('Are you sure you want to activate the selected schedule(s)?');
$confirm4->message            .= '<br/>';
$confirm4->message            .= DUP_PRO_U::__('<small><i>Note: This action activates all schedules.</i></small>');
$confirm4->progressText        = DUP_PRO_U::__('Activating Schedules, Please Wait...');
$confirm4->jsCallback          = 'DupPro.Schedule.BulkActivate()';
$confirm4->initConfirm();

$confirm5                      = new DUP_PRO_UI_Dialog();
$confirm5->title               = DUP_PRO_U::__('Deactivate Schedule?');
$confirm5->wrapperClassButtons = 'dpro-dlg-deactivate-schedules-btns';
$confirm5->message             = DUP_PRO_U::__('Are you sure you want to deactivate the selected schedule(s)?');
$confirm5->message            .= '<br/>';
$confirm5->message            .= DUP_PRO_U::__('<small><i>Note: This action deactivates all schedules.</i></small>');
$confirm5->progressText        = DUP_PRO_U::__('Deactivating Schedules, Please Wait...');
$confirm5->jsCallback          = 'DupPro.Schedule.BulkDeactivate()';
$confirm5->initConfirm();

$confirm2               = new DUP_PRO_UI_Dialog();
$confirm2->title        = DUP_PRO_U::__('RUN SCHEDULE?');
$confirm2->message      = DUP_PRO_U::__('Are you sure you want to run schedule now?');
$confirm2->progressText = DUP_PRO_U::__('Running Schedule, Please Wait...');
$confirm2->jsCallback   = 'DupPro.Schedule.Run(this)';
$confirm2->initConfirm();

$confirm3               = new DUP_PRO_UI_Dialog();
$confirm3->title        = $confirm1->title;
$confirm3->message      = DUP_PRO_U::__('Are you sure you want to delete this schedule?');
$confirm3->progressText = $confirm1->progressText;
$confirm3->jsCallback   = 'DupPro.Schedule.DeleteThis(this)';
$confirm3->initConfirm();

$schedule_bulk_action_nonce = wp_create_nonce('duplicator_pro_schedule_bulk_action');
?>
<script>
    jQuery(document).ready(function ($) {

        /*METHOD: Shows quick view summary */
        DupPro.Schedule.QuickView = function (id) {
            $('#detail-' + id).toggle();
        };

        /*METHOD: Run the schedule now and redirect to packages page */
        DupPro.Schedule.RunNow = function (schedule_id) {
<?php $confirm2->showConfirm(); ?>
            $("#<?php echo $confirm2->getID(); ?>-confirm").attr('data-id', schedule_id);
        };

        DupPro.Schedule.Run = function (e) {
            var schedule_id = $(e).attr('data-id');

            $('#icon-' + schedule_id + '-status').show();
            $('#text-' + schedule_id).html("<?php DUP_PRO_U::esc_html_e('Queueing Now - Please Wait...') ?>");
            var data = {
                action: 'duplicator_pro_run_schedule_now',
                schedule_id: schedule_id,
                nonce: '<?php echo wp_create_nonce('duplicator_pro_run_schedule_now'); ?>'
            }
            $.ajax({
                type: "POST",
                url: ajaxurl,
                timeout: 10000000,
                data: data
            }).done(function (respData) {
                try {
                    var data = DupPro.parseJSON(respData);
                } catch (err) {
                    console.error(err);
                    console.error('JSON parse failed for response data: ' + respData);
                    return false;
                }

                window.location.href = '<?php echo self_admin_url("admin.php?page=duplicator-pro"); ?>';
            });
        };

        /*METHOD: Deletes a single schedule */
        DupPro.Schedule.Delete = function (id) {
<?php $confirm3->showConfirm(); ?>
            $("#<?php echo $confirm3->getID(); ?>-confirm").attr('data-id', id);
        };

        DupPro.Schedule.DeleteThis = function (e) {
            var id = $(e).attr('data-id');

            $.ajax({
                type: "POST",
                url: ajaxurl,
                dataType: "json",
                data: {
                    action: 'duplicator_pro_schedule_bulk_action',
                    perform: <?php echo ServicesSchedule::SCHEDULE_BULK_DELETE; ?>,
                    schedule_ids: [id],
                    nonce: '<?php echo $schedule_bulk_action_nonce; ?>'
                },
            }).done(function (data) {
                $('#dup-schedule-form').submit();
            });
        };

        //  Creats a comma seperate list of all selected package ids
        DupPro.Schedule.SelectedList = function () {
            var arr = [];

            $("input[name^='selected_id[]']").each(function () {
                if ($(this).is(':checked')) {
                    arr.push($(this).val());
                }
            });

            return arr;
        };

        // Bulk delete
        DupPro.Schedule.BulkDelete = function () {
            var list = DupPro.Schedule.SelectedList();

            $.ajax({
                type: "POST",
                url: ajaxurl,
                dataType: "json",
                data: {
                    action: 'duplicator_pro_schedule_bulk_action',
                    perform: <?php echo ServicesSchedule::SCHEDULE_BULK_DELETE; ?>,
                    schedule_ids: list,
                    nonce: '<?php echo $schedule_bulk_action_nonce; ?>'
                },
            }).done(function (data) {
                $('#dup-schedule-form').submit();
            });
        };

        // Bulk activate
        DupPro.Schedule.BulkActivate = function () {
            var list = DupPro.Schedule.SelectedList();

            $.ajax({
                type: "POST",
                url: ajaxurl,
                dataType: "json",
                data: {
                    action: 'duplicator_pro_schedule_bulk_action',
                    perform: <?php echo ServicesSchedule::SCHEDULE_BULK_ACTIVATE; ?>,
                    schedule_ids: list,
                    nonce: '<?php echo $schedule_bulk_action_nonce; ?>'
                },
            }).done(function (data) {
                if (data.success) {
                    $('#dup-schedule-form').submit();
                } else {
                    if (data.message.length > 0) {
                        $('#<?php echo $confirm4->getID(); ?>-progress').hide();
                        $('#<?php echo $confirm4->getMessageID(); ?>').html(data.message);
                    }
                }
            });
        };

        // Bulk deactivate
        DupPro.Schedule.BulkDeactivate = function () {
            var list = DupPro.Schedule.SelectedList();

            $.ajax({
                type: "POST",
                url: ajaxurl,
                dataType: "json",
                data: {
                    action: 'duplicator_pro_schedule_bulk_action',
                    perform: <?php echo ServicesSchedule::SCHEDULE_BULK_DEACTIVATE; ?>,
                    schedule_ids: list,
                    nonce: '<?php echo $schedule_bulk_action_nonce; ?>'
                },
            }).done(function (data) {
                $('#dup-schedule-form').submit();
            });
        };

        /*METHOD: Bulk action response */
        DupPro.Schedule.BulkAction = function () {
            var list = DupPro.Schedule.SelectedList();

            if (list.length == 0) {
                <?php $alert2->showAlert(); ?>
                return;
            }

            var action = $('#bulk_action').val(),
                checked = ($('.item-chk:checked').length > 0);

            if (action == -1 ) {
                <?php $alert1->showAlert(); ?>
                return;
            }

            if (checked) {
                switch (action) {
                    case '<?php echo ServicesSchedule::SCHEDULE_BULK_DELETE; ?>':
                    <?php $confirm1->showConfirm(); ?>
                        break;
                    case '<?php echo ServicesSchedule::SCHEDULE_BULK_ACTIVATE; ?>':
                    <?php $confirm4->showConfirm(); ?>
                        break;
                    case '<?php echo ServicesSchedule::SCHEDULE_BULK_DEACTIVATE; ?>':
                    <?php $confirm5->showConfirm(); ?>
                        break;
                    default:
                    <?php $alert2->showAlert(); ?>
                        break;
                }
            }
        };

        /*METHOD: Edit a single schedule */
        DupPro.Schedule.Edit = function (id) {
            document.location.href = '<?php echo "$edit_schedule_url&schedule_id="; ?>' + id;
        };

        /*METHOD: Copy a schedule */
        DupPro.Schedule.Copy = function (id) {
<?php
$params             = array(
    'action=copy-schedule',
    '_wpnonce=' . wp_create_nonce('duppro-schedule-edit'),
    'schedule_id=-1',
    'duppro-source-schedule-id=' // last params get id from js param function
);
$edit_schedule_url .= '&' . implode('&', $params);
?>
            document.location.href = '<?php echo $edit_schedule_url; ?>' + id;
        };

        /*METHOD: Set delete all */
        DupPro.Schedule.SetDeleteAll = function (chkbox) {
            $('.item-chk').each(function () {
                this.checked = chkbox.checked;
            });
        };

        /*METHOD: Shows the delete storage message*/
        DupPro.Schedule.showDeleteStorageMessage = function() {
           <?php $alert3->showAlert(); ?>
        };

        /*METHOD: Enableds the update flag to track proccessing */
        DupPro.Schedule.SetUpdateInterval = function (period) {
            console.log('setting interval to ' + period);
            if (DupPro.Schedule.setIntervalID != -1) {
                clearInterval(DupPro.Schedule.setIntervalID);
                DupPro.Schedule.setIntervalID = -1
            }
            DupPro.Schedule.setIntervalID = setInterval(DupPro.Schedule.UpdateSchedules, period * 1000);
        };

        /*METHOD: Checks the schedule status */
        DupPro.Schedule.UpdateSchedules = function () {

            var data = {
                action: 'duplicator_pro_get_schedule_infos',
                nonce: '<?php echo wp_create_nonce('duplicator_pro_get_schedule_infos'); ?>'
            };

            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: data,
                success: function (respData) {
                    try {
                        var schedule_infos = DupPro.parseJSON(respData);
                    } catch (err) {
                        console.error(err);
                        console.error('JSON parse failed for response data: ' + respData);
                        console.log("error");
                        console.log(data);
                        $(".schedule-status-icon").css('display', 'none');
                        DupPro.Schedule.SetUpdateInterval(60);
                        return false;
                    }

                    activeSchedulePresent = false;
                    for (schedule_info_key in schedule_infos) {
                        var schedule_info = schedule_infos[schedule_info_key];
                        var is_running_selector = "#icon-" + schedule_info.schedule_id + "-status";
                        var last_ran_selector = "#schedule-" + schedule_info.schedule_id + "-last-ran-string";
                        if (schedule_info.is_running) {
                            $(is_running_selector).show();
                            activeSchedulePresent = true;
                        } else {
                            $(is_running_selector).hide();
                        }
                        $(last_ran_selector).text(schedule_info.last_ran_string);
                    }

                    if (activeSchedulePresent) {
                        DupPro.Schedule.SetUpdateInterval(10);
                    } else {

                        DupPro.Schedule.SetUpdateInterval(60);
                    }
                },
                error: function (data) {
                    console.log("error");
                    console.log(data);
                    $(".schedule-status-icon").css('display', 'none');
                    DupPro.Schedule.SetUpdateInterval(60);
                }
            });
        };

        //INIT: startup items
        $("tr.schedule-row").hover(
                function () {
                    $(this).find(".sub-menu").show();
                },
                function () {
                    $(this).find(".sub-menu").hide();
                }
        );

        DupPro.UI.Clock(DupPro._WordPressInitTime);
        DupPro.Schedule.setIntervalID = -1;
        DupPro.Schedule.UpdateSchedules();
    });
</script>
