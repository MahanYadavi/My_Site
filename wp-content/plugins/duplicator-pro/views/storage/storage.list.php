<?php

use Duplicator\Ajax\ServicesStorage;
use Duplicator\Controllers\SettingsPageController;
use Duplicator\Controllers\StoragePageController;
use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Core\Views\TplMng;

defined("ABSPATH") or die("");

$tplData = TplMng::getInstance()->getGlobalData();

$nonce_action  = 'duppro-storage-list';
$display_edit  = false;
$storages      = DUP_PRO_Storage_Entity::get_all();
$storage_count = count($storages);

$edit_default_storage_url = ControllersManager::getMenuLink(
    ControllersManager::STORAGE_SUBMENU_SLUG,
    SettingsPageController::L2_SLUG_STORAGE,
    null,
    array(
        'inner_page' => 'edit-default'
    )
);
$edit_storage_url         = ControllersManager::getMenuLink(
    ControllersManager::STORAGE_SUBMENU_SLUG,
    SettingsPageController::L2_SLUG_STORAGE,
    null,
    array(
            'inner_page' => 'edit'
    )
);
$storage_tab_url          = ControllersManager::getMenuLink(
    ControllersManager::STORAGE_SUBMENU_SLUG,
    SettingsPageController::L2_SLUG_STORAGE
);

$baseCopyUrl = ControllersManager::getMenuLink(
    ControllersManager::STORAGE_SUBMENU_SLUG,
    SettingsPageController::L2_SLUG_STORAGE,
    null,
    array(
        'inner_page' => 'edit',
        'action' => $tplData['actions']['copy-storage']->getKey(),
        '_wpnonce' => $tplData['actions']['copy-storage']->getNonce()
    )
);

?>

<style>
    /*Detail Tables */
    table.storage-tbl td {height: 45px}
    table.storage-tbl a.name {font-weight: bold}
    table.storage-tbl input[type='checkbox'] {margin-left: 5px}
    table.storage-tbl div.sub-menu {margin: 5px 0 0 2px; display: none}
    table tr.storage-detail {display:none; margin: 0;}
    table tr.storage-detail td { padding: 3px 0 5px 20px}
    table tr.storage-detail div {line-height: 20px; padding: 2px 2px 2px 15px}
    table tr.storage-detail td button {margin:5px 0 5px 0 !important; display: block}
    tr.storage-detail label {min-width: 150px; display: inline-block; font-weight: bold}

    div.dpro-dlg-confirm-txt div.store-items {margin-top:10px !important}
    div.dpro-dlg-confirm-txt div.store-items div.item {padding:5px; font-size:13px}
    div.dpro-dlg-confirm-txt div.store-items span.lbl {display: inline-block; width:50px; font-weight: bold}
    div.dpro-dlg-confirm-txt div.store-items div.icon {float:left; padding:5px 10px 0 0}

    div.dpro-dlg-confirm-txt div.store-items,
    div.dpro-dlg-confirm-txt div.schedule-progress
    {padding:10px; border:1px dotted silver; overflow-y: scroll; height:120px; margin:5px 0 0 0; background:#FFFFF3}

    div.dpro-dlg-confirm-txt div.schedule-area {padding:15px 0 0 0}
    div.dpro-dlg-confirm-txt div.schedule-progress {}
    div.dpro-dlg-confirm-txt div.schedule-item {padding:5px}
</style>

<!-- ====================
TOOL-BAR -->
<table class="dpro-edit-toolbar">
    <tr>
        <td>
            <select id="bulk_action">
                <option value="-1" ><?php _e("Bulk Actions"); ?></option>
                <option value="<?php echo ServicesStorage::STORAGE_BULK_DELETE; ?>" title="Delete selected storage endpoint(s)">
                    <?php _e("Delete"); ?>
                </option>
            </select>
            <input type="button" class="button action" value="<?php DUP_PRO_U::esc_attr_e("Apply") ?>" onclick="DupPro.Storage.BulkAction()">
            <span class="btn-separator"></span>
            <a href="admin.php?page=duplicator-pro-settings&tab=storage" class="button grey-icon" title="<?php DUP_PRO_U::esc_attr_e("Settings") ?>">
                <i class="fas fa-sliders-h fa-fw"></i>
            </a>
        </td>
        <td>
            <div class="btnnav">
                <a href="<?php echo esc_url($edit_storage_url); ?>" id="duplicator-pro-add-new-storage" class="button">
                    <?php DUP_PRO_U::esc_html_e('Add New'); ?>
                </a>
            </div>
        </td>
    </tr>
</table>

<form id="dup-storage-form" action="<?php echo $storage_tab_url; ?>" method="post">
    <?php wp_nonce_field($nonce_action); ?>
    <input type="hidden" id="dup-storage-form-action" name="action" value=""/>
    <input type="hidden" id="dup-selected-storage" name="storage_id" value="null"/>

    <!-- ====================
    LIST ALL STORAGE -->
    <table class="widefat storage-tbl">
        <thead>
            <tr>
                <th style='width:10px;'><input type="checkbox" id="dpro-chk-all" title="Select all storage endpoints" onclick="DupPro.Storage.SetAll(this)"></th>
                <th style='width:275px;'>Name</th>
                <th><?php DUP_PRO_U::esc_html_e('Type'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 0;
            foreach ($storages as $storage) :
                /* @var $storage DUP_PRO_Storage_Entity */
                $i++;
                $type_name        = $storage->get_storage_type_string();
                $type_id          = $storage->get_storage_type();
                $isDefaultStorage = ($storage->id == '-2');
                ?>
                <tr id="main-view-<?php echo $storage->id ?>"
                    class="storage-row <?php echo ($i % 2) ? 'alternate' : ''; ?>"
                    data-id="<?php echo $storage->id ?>"
                    data-name="<?php echo $storage->name ?>"
                    data-typeid="<?php echo $type_id ?>"
                    data-typename="<?php echo $type_name ?>">
                    <td>
                        <?php if ($storage->editable) : ?>
                            <input name="selected_id[]" type="checkbox" value="<?php echo $storage->id; ?>" class="item-chk" />
                        <?php else : ?>
                            <input type="checkbox" disabled="disabled" />
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($storage->editable) : ?>                                                
                            <a href="javascript:void(0);" onclick="DupPro.Storage.Edit('<?php echo $storage->id; ?>')"><b><?php echo $storage->name; ?></b></a>
                            <div class="sub-menu">
                                <a href="javascript:void(0);" onclick="DupPro.Storage.Edit('<?php echo $storage->id; ?>')"><?php DUP_PRO_U::esc_html_e('Edit'); ?></a> |
                                <a href="javascript:void(0);" onclick="DupPro.Storage.View('<?php echo $storage->id; ?>');"><?php DUP_PRO_U::esc_html_e('Quick View'); ?></a> |
                                <a href="javascript:void(0);" onclick="DupPro.Storage.CopyEdit('<?php echo $storage->id; ?>');"><?php DUP_PRO_U::esc_html_e('Copy'); ?></a> |
                                <a href="javascript:void(0);" onclick="DupPro.Storage.deleteSingle('<?php echo $storage->id; ?>');">
                                    <?php _e('Delete', 'duplicator-pro'); ?>
                                </a>
                            </div>
                        <?php else : ?>
                            <a href="javascript:void(0);" onclick="DupPro.Storage.EditDefault()"><b><?php DUP_PRO_U::esc_html_e('Default'); ?></b></a>
                            <div class="sub-menu">
                                <a href="javascript:void(0);" onclick="DupPro.Storage.EditDefault()"><?php DUP_PRO_U::esc_html_e('Edit'); ?></a> |
                                <a href="javascript:void(0);" onclick="DupPro.Storage.CopyEdit('<?php echo $storage->id; ?>');"><?php DUP_PRO_U::esc_html_e('Copy'); ?></a> |
                                <a href="javascript:void(0);" onclick="DupPro.Storage.View('<?php echo $storage->id; ?>');"><?php DUP_PRO_U::esc_html_e('Quick View'); ?></a>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td>

                        <?php
                            echo ($isDefaultStorage)
                                ? '<i class="far fa-hdd fa-fw"></i>&nbsp;'
                                : DUP_PRO_Storage_Entity::getStorageIcon($type_id) . '&nbsp;';
                            echo esc_html($type_name);
                        ?>
                    </td>
                </tr>
                <?php
                    ob_start();
                try { ?>
                    <tr id='quick-view-<?php echo intval($storage->id); ?>' class='<?php echo ($i % 2) ? 'alternate' : ''; ?> storage-detail'>
                        <td colspan="3">
                            <b><?php DUP_PRO_U::esc_html_e('QUICK VIEW') ?></b> <br/>
                            <div>
                                <label><?php DUP_PRO_U::esc_html_e('Name') ?>:</label>
                            <?php echo esc_html($storage->name); ?>
                            </div>
                            <div>
                                <label><?php DUP_PRO_U::esc_html_e('Notes') ?>:</label>
                            <?php echo (strlen($storage->notes)) ? esc_html($storage->notes) : DUP_PRO_U::__('(no notes)'); ?>
                            </div>
                            <div>
                                <label><?php DUP_PRO_U::esc_html_e('Type') ?>:</label>
                            <?php echo esc_html($storage->get_storage_type_string()); ?>
                            </div>
                            <?php
                            switch ($type_name) {
                                case 'Local':
                                    ?>
                                        <div>
                                            <label><?php DUP_PRO_U::esc_html_e('Location') ?>:</label>
                                        <?php echo esc_html($storage->get_storage_location_string()); ?>
                                        </div>
                                        <?php
                                    break;
                                case 'Dropbox':
                                    ?>
                                        <div>
                                            <label><?php DUP_PRO_U::esc_html_e('Location') ?>:</label>
                                            <?php
                                            $url = $storage->get_storage_location_string();
                                            echo "<a href='" . esc_url($url) . "' target='_blank'>" . esc_url($url) . "</a>";
                                            ?>
                                        </div>
                                        <?php
                                    break;
                                case 'FTP':
                                    ?>
                                        <div>
                                            <label><?php DUP_PRO_U::esc_html_e('Server') ?>:</label>
                                            <?php echo esc_html($storage->ftp_server); ?>:<?php echo esc_html($storage->ftp_port); ?> <br/>
                                            <label><?php DUP_PRO_U::esc_html_e('Location') ?>:</label>
                                            <?php
                                            $url = $storage->get_storage_location_string();
                                            echo "<a href='" . esc_url($url) . "' target='_blank'>" . esc_url($url) . "</a>";
                                            ?>
                                        </div>
                                        <?php
                                    break;
                                case 'SFTP':
                                    ?>
                                        <div>
                                            <label><?php DUP_PRO_U::esc_html_e('Server') ?>:</label>
                                            <?php echo esc_html($storage->sftp_server); ?>:<?php echo esc_html($storage->sftp_port); ?> <br/>
                                            <label><?php DUP_PRO_U::esc_html_e('Location') ?>:</label>
                                            <?php
                                            $url = $storage->get_storage_location_string();
                                            echo "<a href='" . esc_url($url) . "' target='_blank'>" . esc_url($url) . "</a>";
                                            ?>
                                        </div>
                                        <?php
                                    break;
                                case 'Google Drive':
                                    ?>
                                        <div>
                                            <label><?php DUP_PRO_U::esc_html_e('Location') ?>:</label>
                                            <?php echo $storage->get_storage_location_string(); ?>
                                        </div>
                                        <?php
                                    break;
                                case 'Amazon S3':
                                    ?>
                                        <div>
                                            <label><?php DUP_PRO_U::esc_html_e('Location') ?>:</label>
                                            <?php  echo $storage->get_storage_location_string(); ?>
                                        </div>
                                        <?php
                                    break;
                            }
                            ?>
                            <button type="button" class="button" onclick="DupPro.Storage.View('<?php echo intval($storage->id); ?>');">
                            <?php DUP_PRO_U::esc_html_e('Close') ?>
                            </button>
                        </td>
                    </tr>
                    <?php
                } catch (Exception $e) {
                    ob_clean(); ?>
                    <tr id='quick-view-<?php echo intval($storage->id); ?>' class='<?php echo ($i % 2) ? 'alternate' : ''; ?>'>
                        <td colspan="3">
                           <?php
                            echo StoragePageController::getErrorMsg($e);
                            ?>
                            <br><br>
                           <button type="button" class="button" onclick="DupPro.Storage.View('<?php echo intval($storage->id); ?>');">
                           <?php DUP_PRO_U::esc_html_e('Close') ?>
                           </button>
                        </td>
                    </tr>
                    <?php
                }
                $rowStr = ob_get_clean();
                echo $rowStr;
            endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="8" style="text-align:right; font-size:12px">                       
                    <?php echo DUP_PRO_U::__('Total') . ': ' . $storage_count; ?>
                </th>
            </tr>
        </tfoot>
    </table>

</form>
<?php
    //Select Action Alert
    $alert1          = new DUP_PRO_UI_Dialog();
    $alert1->title   = DUP_PRO_U::__('Bulk Action Required');
    $alert1->message = DUP_PRO_U::__('Please select an action from the "Bulk Actions" drop down menu!');
    $alert1->initAlert();

    //Select Storage Alert
    $alert2          = new DUP_PRO_UI_Dialog();
    $alert2->title   = DUP_PRO_U::__('Selection Required');
    $alert2->message = DUP_PRO_U::__('Please select at least one storage to delete!');
    $alert2->initAlert();

    //Delete Dialog
    $dlgDelete               = new DUP_PRO_UI_Dialog();
    $dlgDelete->height       = 525;
    $dlgDelete->title        = DUP_PRO_U::__('Delete Storage(s)?');
    $dlgDelete->progressText = DUP_PRO_U::__('Removing Storages, Please Wait...');
    $dlgDelete->jsCallback   = 'DupPro.Storage.deleteAjax()';
    $dlgDelete->initConfirm();
    $storage_bulk_action_nonce = wp_create_nonce("duplicator_pro_storage_bulk_actions");
?>
<script>
jQuery(document).ready(function($) {

    //Shows detail view
    DupPro.Storage.EditDefault = function () {
        document.location.href = '<?php echo $edit_default_storage_url; ?>';
    };

    //Shows detail view
    DupPro.Storage.Edit = function (id) {
        document.location.href = '<?php echo "$edit_storage_url&storage_id="; ?>' + id;
    };

    //Copy and edit
    DupPro.Storage.CopyEdit = function (id) {
        document.location.href = <?php echo json_encode($baseCopyUrl); ?> + '&duppro-source-storage-id=' + id;
    };

    //Shows detail view
    DupPro.Storage.View = function (id) {
        $('#quick-view-' + id).toggle();
        $('#main-view-' + id).toggle();
    };

    //Select all checked items
    DupPro.Storage.SelectedList = function () {
        var arr = [];
        $("input[name^='selected_id[]']").each(function () {
            if ($(this).is(':checked')) {
                arr.push($(this).val());
            }
        });
        return arr;
    };

    //Sets all for deletion
    DupPro.Storage.SetAll = function (chkbox) {
        $('.item-chk').each(function () {
            this.checked = chkbox.checked;
        });
    };

    // Bulk action
    DupPro.Storage.BulkAction = function () {
        var list = DupPro.Storage.SelectedList();
        var action = $('#bulk_action').val();

        if (list.length === 0) {
            <?php $alert2->showAlert(); ?>
            return;
        }

        switch (action) {
            case '<?php echo ServicesStorage::STORAGE_BULK_DELETE; ?>':
                  DupPro.Storage.deleteConfirm(list);
                break;
            default:
            <?php $alert1->showAlert(); ?>
                break;
        }
    };

    //Delete via the delete link
    DupPro.Storage.deleteSingle = function(id) {
       $('#dup-selected-storage').val(id);
       DupPro.Storage.deleteConfirm([id]);
    };

    //Load the delete confirm dialog
    DupPro.Storage.deleteConfirm = function(idList) {

        var $rowData, $icon;
        var name, id, typeName, html;

        var storeCount  = idList.length;
        var isSingle    = (storeCount == 1) ? true : false;
        var dlgID       = "<?php echo $dlgDelete->getID(); ?>";
        var $content    = $(`#${dlgID}_message`);

        html =  (isSingle)
                ? "<i><?php _e('Are you sure you want to delete this storage item?</i>', 'duplicator-pro')?>"
                : `<i><?php _e('Are you sure you want to delete these ${storeCount} storage items?</i>', 'duplicator-pro')?>`;

        // Build storage item html
        html += '<div class="store-items">';
        idList.forEach(v => {
            $rowData    = $('#main-view-' + v);
            name        = $rowData.data('name');
            id          = $rowData.data('id');
            typeName    = $rowData.data('typename');
            $icon       = Duplicator.Storage.getFontAwesomeIcon($rowData.data('typeid'), 'fa-2x');
            html        += `<div class='item'>
                            <div class='icon'>${$icon}</div>
                            <span class='lbl'><?php _e('Name', 'duplicator-pro')?>:</span> "${name}" <br/>
                            <span class='lbl'><?php _e('Type', 'duplicator-pro')?>:</span> ${typeName}
                          </div>`;
        });
        html     +=  '</div>';

        $content.html(html);
        <?php $dlgDelete->showConfirm(); ?>

        html  = `<div class="schedule-area">
                    <b><?php _e('Linked Schedules', 'duplicator-pro')?>:</b><br/>
                    <small><?php DUP_PRO_U::esc_html_e("Schedules linked to the storage items above");  ?>:</small>
                    <div class="schedule-progress" id="${dlgID}-schedule-progress">
                        <i class="fas fa-circle-notch fa-spin"></i>
                        <?php _e('Finding Schedule Links...  Please wait', 'duplicator-pro')?>
                    </div>
                    <small>
                        <?php
                            _e("To remove storage items and unlink schedules click OK. ", 'duplicator-pro');
                            _e("Schedules with asterisk<span class='maroon'>*</span> will be deactivated if storage is removed.", 'duplicator-pro');
                        ?>
                    </small>
                 </div>`;
        $content.append(html);

        function loadSchedules(idList, dlgID){
            let result = DupPro.Storage.getScheduleData(idList);
            (result != null)
                ? $(`#${dlgID}-schedule-progress`).html(result)
                : $(`#${dlgID}-schedule-progress`).html("<?php _e('- No linked schedules found -', 'duplicator-pro')?>");
        }
        setTimeout(loadSchedules, 100, idList, dlgID);
    };

    //Get the linked schedule data
    DupPro.Storage.getScheduleData = function(storageIDs) {

        var result  = null;
        var html;

        $.ajax({
            type: "POST",
            url: ajaxurl,
             async: false,
            dataType: "json",
            data: {
                action: 'duplicator_pro_storage_bulk_actions',
                perform: <?php echo ServicesStorage::STORAGE_GET_SCHEDULES; ?>,
                storage_ids: storageIDs,
                nonce: '<?php echo $storage_bulk_action_nonce; ?>'
            }
        })
        .done(function (data) {
            //__sleepFor(1000); //Test delays
           if (data.schedules !== undefined && data.schedules.length > 0) {
               html = '';
               data.schedules.forEach(function (schedule) {
                   let asterisk = schedule.hasOneStorage ? "*" : "";
                   html += `<div class="schedule-item">
                               <i class="far fa-clock"></i> <a href="${schedule.editURL}">${schedule.name}</a> <span class="maroon">${asterisk}</span>
                            </div>`;
               });
               result = html;
           }
        })
        .fail(function() {
            result =  '<i class="fas fa-exclamation-triangle"></i> <?php _e('Unable to get schedule data.', 'duplicator-pro')?>';
        });
        return result;
    };


    //Perform the delete via ajax
    DupPro.Storage.deleteAjax = function ()  {

        var dlgID   = "<?php echo $dlgDelete->getID(); ?>";
        var list    = DupPro.Storage.SelectedList();

        //Delete from the quick link
        if (list.length == 0) {
           var singleID = $('#dup-selected-storage').val();
           list = (singleID > 0) ? [singleID] : null;
        }

        $(`#${dlgID}_message`).hide();

        $.ajax({
            type: "POST",
            url: ajaxurl,
            dataType: "json",
            data: {
                action: 'duplicator_pro_storage_bulk_actions',
                perform: <?php echo ServicesStorage::STORAGE_BULK_DELETE; ?>,
                storage_ids: list,
                nonce: '<?php echo $storage_bulk_action_nonce; ?>'
            }
        })
        .done(function()   {$('#dup-storage-form').submit()})
        .always(function() {$('#dup-selected-storage').val(null)});
    };


    //--------------------------
    //INIT
    //Name hover show menu
    $("tr.storage-row").hover(
        function () {
            $(this).find(".sub-menu").show();
        },
        function () {
            $(this).find(".sub-menu").hide();
        }
    );
});

//Used to test ajax delays
function __sleepFor(sleepDuration){
    var now = new Date().getTime();
    while(new Date().getTime() < now + sleepDuration){ /* Do nothing */ }
}
</script>