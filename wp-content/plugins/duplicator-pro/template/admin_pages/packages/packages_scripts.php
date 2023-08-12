<?php

/**
 * Duplicator package row in table packages list
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

defined("ABSPATH") or die("");

/**
 * Variables
 *
 * @var \Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */

use Duplicator\Controllers\ToolsPageController;
use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Snap\SnapURL;

$delete_nonce = wp_create_nonce('duplicator_pro_package_delete');
$gift_nonce   = wp_create_nonce('DUP_PRO_CTRL_Package_toggleGiftFeatureButton');

require_once DUPLICATOR____PATH . '/views/tools/recovery/widget/recovery-widget-scripts.php';
?>

<!-- ==========================================
THICK-BOX DIALOGS: -->
<?php
/* ------------------------------------------
 * ALERT:  Remote > Storage items          */
$remoteDlg           = new DUP_PRO_UI_Dialog();
$remoteDlg->width    = 750;
$remoteDlg->height   = 475;
$remoteDlg->title    = DUP_PRO_U::__('Remote Storage Locations');
$remoteDlg->message  = DUP_PRO_U::__('Loading Please Wait...');
$remoteDlg->boxClass = 'dup-packs-remote-store-dlg';
$remoteDlg->initAlert();

/* ------------------------------------------
 * ALERT:  Bulk action > no selection      */
$alert1           = new DUP_PRO_UI_Dialog();
$alert1->title    = DUP_PRO_U::__('Bulk Action Required');
$alert1->message  = '<i class="fa fa-exclamation-triangle fa-sm"></i>&nbsp;';
$alert1->message .= DUP_PRO_U::__('No selections made! Please select an action from the "Bulk Actions" drop down menu!');
$alert1->initAlert();

/* ------------------------------------------
 * ALERT:  Bulk action > no package selected  */
$alert2                      = new DUP_PRO_UI_Dialog();
$alert2->title               = DUP_PRO_U::__('Selection Required');
$alert2->wrapperClassButtons = 'dpro-dlg-nopackage-sel-bulk-action-btns';
$alert2->message             = '<i class="fa fa-exclamation-triangle fa-sm"></i>&nbsp;';
$alert2->message            .= DUP_PRO_U::__('No selections made! Please select at least one package to delete!');
$alert2->initAlert();

/* ------------------------------------------
 * ALERT: Process > Error undefined        */
$alert4          = new DUP_PRO_UI_Dialog();
$alert4->title   = DUP_PRO_U::__('ERROR!');
$alert4->message = DUP_PRO_U::__('Got an error or a warning: undefined');
$alert4->initAlert();

/* ------------------------------------------
 * ALERT: Process > Error no details       */
$alert5          = new DUP_PRO_UI_Dialog();
$alert5->title   = $alert4->title;
$alert5->message = DUP_PRO_U::__('Failed to get details.');
$alert5->initAlert();

/* ------------------------------------------
 * ALERT: Download > No storage items        */
$alert6          = new DUP_PRO_UI_Dialog();
$alert6->height  = 350;
$alert6->width   = 600;
$alert6->title   = DUP_PRO_U::__('Download Status');
$alert6->message =
         sprintf(
             '%s <br/><br/> <i class="fas fa-server fa-xs"></i>&nbsp;<b>%s:</b> %s <br/><br/> '
             . '<i class="far fa-hdd"></i>&nbsp;<b>%s:</b> %s <br/><br/> <small><i>%s</i></small><br/>',
             __('No package files found at the \'Default\' storage location on this server.', 'duplicator-pro'),
             __('Remote', 'duplicator-pro'),
             __('For packages stored remotely check the remote storage button next to the download button.', 'duplicator-pro'),
             __('Local', 'duplicator-pro'),
             __('To enable the direct download button be sure the local \'Default\' or a non-default, but \'Local\''
                . 'storage type is enabled when creating a package.', 'duplicator-pro'),
             __("Note: If the Storage &#10095; Default &#10095; 'Max Packages' is set then packages will be removed but the entry will still be visible "
                . "on the packages screen.", 'duplicator-pro')
         );
$alert6->initAlert();

/* ------------------------------------------
 * CONFIRM: Delete packages?               */
$confirm1                      = new DUP_PRO_UI_Dialog();
$confirm1->height              = 280;
$confirm1->title               = DUP_PRO_U::__('Delete Packages?');
$confirm1->wrapperClassButtons = 'dpro-dlg-detete-packages-btns';
$confirm1->message             = DUP_PRO_U::__('Are you sure you want to delete the selected package(s)?');
$confirm1->message            .= '<br/><br/>';
$confirm1->message            .= DUP_PRO_U::__(
    '<small><i>Note: This action removes only packages located on this server. ' .
    'If a remote package was created then it will not be removed or affected.</i></small>'
);
$confirm1->progressText        = DUP_PRO_U::__('Removing Packages, Please Wait...');
$confirm1->jsCallback          = 'DupPro.Pack.Delete()';
$confirm1->initConfirm();

/* ------------------------------------------
 * CONFIRM: Recovery > package overivew > button */
$confirmRestoreBk                      = new DUP_PRO_UI_Dialog();
$confirmRestoreBk->title               = DUP_PRO_U::__('Restore selected backup?');
$confirmRestoreBk->wrapperClassButtons = 'dpro-dlg-restore-bk-btns';
$confirmRestoreBk->message             = DUP_PRO_U::__('Are you sure you want restore the selected package backup ?');
$confirmRestoreBk->message            .= '<br/>';
$confirmRestoreBk->message            .= DUP_PRO_U::__(
    'This function runs the installer of the selected package by deleting all the current data of the site.<br><br>'
    . '<b>Once the operation has started there is no possibility to go back and the restore of the backup must '
    . 'be terminated otherwise the site will not be reachable.</b>'
);
$confirmRestoreBk->jsCallback          = 'DupPro.Pack.BackupRestore();';
$confirmRestoreBk->initConfirm();

/* ------------------------------------------
 * ALERT: Recovery > toolbar button        */
$toolBarRecoveryButtonInfo              = new DUP_PRO_UI_Dialog();
$toolBarRecoveryButtonInfo->showButtons = false;
$toolBarRecoveryButtonInfo->height      = 600;
$toolBarRecoveryButtonInfo->width       = 600;
$toolBarRecoveryButtonInfo->title       = __('Recovery Point', 'duplicator-pro');
$toolBarRecoveryButtonInfo->message     = $tplMng->render('admin_pages/packages/recovery_info/info', array(), false);
$toolBarRecoveryButtonInfo->initAlert();

/* ------------------------------------------
 * ALERT: Recovery                         */
$menuRecoveryRecoveryBox              = new DUP_PRO_UI_Dialog();
$menuRecoveryRecoveryBox->title       = __('Package: Recovery Point', 'duplicator-pro');
$menuRecoveryRecoveryBox->boxClass    = 'dup-recovery-box-info';
$menuRecoveryRecoveryBox->showButtons = false;
$menuRecoveryRecoveryBox->width       = 600;
$menuRecoveryRecoveryBox->height      = 600;
$menuRecoveryRecoveryBox->message     = '';
$menuRecoveryRecoveryBox->initAlert();

/* ------------------------------------------
 * ALERT: Package overeview > Help   */
$linkInfoDlg          = new DUP_PRO_UI_Dialog();
$linkInfoDlg->width   = 700;
$linkInfoDlg->height  = 550;
$linkInfoDlg->title   = DUP_PRO_U::__('Duplicator Pro Tutorial');
$linkInfoDlg->message = $tplMng->render('admin_pages/packages/packages_overview_help', array(), false);
$linkInfoDlg->initAlert();

$storageTabURL                      = SnapURL::appendQueryValue(DUP_PRO_U::getMenuPageURL(DUP_PRO_Constants::$STORAGE_SUBMENU_SLUG, false), 'tab', 'storage');
$tempStorageEditURLWithOutStorageId = SnapURL::appendQueryValue($storageTabURL, 'inner_page', 'edit');
?>
<script>
jQuery(document).ready(function($) {

    DupPro.Pack.RestorePackageId = null;
    DupPro.PackagesTable = $('.dup-packtbl');
        
    DupPro.Pack.StorageTypes = {
        local: 0,
        dropbox: 1,
        ftp: 2,
        gdrive: 3,
        s3: 4,
        sftp: 5,
        onedrive: 6,
        onedrivemsgraph: 7
    };
     
    /**
     * Click event to expands each row and show package details
     *
     * @returns void
     */
    $('th#dup-header-chkall').on('click', function() {
        var $this = $(this);
        var $icon = $this.find('i');
        if ($icon.hasClass('fa-chevron-left')) {
            $icon.attr('class', 'fas fa-chevron-down');
            $("tr.dup-row-complete").each(function() {
                $icon = $(this).find('td.dup-cell-toggle-btn i');
                $icon.attr('class', 'fas fa-chevron-down');
                $(this).next('tr').show();
            });
        } else {
            $icon.attr('class', 'fas fa-chevron-left');
            $("tr.dup-row-complete").each(function() {
                $icon = $(this).find('td.dup-cell-toggle-btn i');
                $icon.attr('class', 'fas fa-chevron-left');
            });
            $('tr.dup-row-details').hide();
        }
    });

    /**
     * Click event to expands each row and show package details
     *
     * @returns void
     */
    $('td.dup-cell-toggle-btn').on('click', function(e) {
        var $this = $(this);
        var $icon  = $this.find('i');
        if ($icon.hasClass('fa-chevron-left')) {
            $icon.attr('class', 'fas fa-chevron-down');
            $(this).parent().next('tr').show();
        } else {
            $icon.attr('class', 'fas fa-chevron-left');
            $(this).parent().next('tr').hide();
        }
    });

    $('.dup-pro-quick-fix-notice').on('click', '.dup-pro-quick-fix', function() {
        var $this = $(this),
            params = JSON.parse($this.attr('data-param')),
            toggle = $this.attr('data-toggle'),
            id = $this.attr('data-id'),
            fix = $(toggle),
            button = {
                loading: function() {
                    $this.prop('disabled', true)
                        .addClass('disabled')
                        .html('<i class="fas fa-circle-notch fa-spin fa-fw"></i> <?php DUP_PRO_U::esc_html_e('Please Wait...') ?>');
                },
                reset: function() {
                    $this.prop('disabled', false)
                        .removeClass('disabled')
                        .html("<i class='fa fa-wrench' aria-hidden='true'></i>&nbsp; <?php DUP_PRO_U::esc_html_e('Resolve This') ?>");
                }
            },
            error = {
                message: function(text) {
                    fix.append(
                        "&nbsp; <span style='color:#cc0000' id='" + 
                        toggle.replace('#', '') + 
                        "-error'><i class='fa fa-exclamation-triangle'></i>&nbsp; " + text + "</span>"
                    );
                },
                remove: function() {
                    if ($(toggle + "-error"))
                        $(toggle + "-error").remove();
                }
            };

        error.remove();
        button.loading();

        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: {
                action: 'duplicator_pro_quick_fix',
                setup: params,
                id: id,
                nonce: '<?php echo wp_create_nonce('duplicator_pro_quick_fix'); ?>'
            }
        }).done(function(respData, x) {
            try {
                var parsedData = DupPro.parseJSON(respData);
            } catch (err) {
                console.error(err);
                console.error('JSON parse failed for response data: ' + respData);

                button.reset();
                error.message('<?php DUP_PRO_U::esc_html_e('Unexpected Error!') ?>');
                console.log(respData);
                console.log(x);
                return false;
            }

            console.log(parsedData);
            if (parsedData.success) {
                fix.remove();

                // If there is no fixes and notifications - remove container
                if (typeof parsedData.recommended_fixes != 'undefined') {
                    if (parsedData.recommended_fixes == 0) {
                        $('.dup-pro-quick-fix-notice').remove();
                    }
                }
            } else {
                button.reset();
                error.message(parsedData.message);
            }
        }).fail(function(data, x) {
            button.reset();
            error.message('<?php DUP_PRO_U::esc_html_e('Unexpected Error!') ?>');
            console.log(data);
            console.log(x);
        });
    });

    DupPro.Pack.DownloadNotice = function() {
        <?php $alert6->showAlert(); ?>
        return false;
    };

    $('.dpro-toolbar-recovery-info').click(function () {
        if ($(this).hasClass('dup-recovery-unset')) {
            <?php $toolBarRecoveryButtonInfo->showAlert(); ?>
        } else {
            let openUrl = <?php echo json_encode($ctrlMng->getMenuLink($ctrlMng::TOOLS_SUBMENU_SLUG, ToolsPageController::L2_SLUG_RECOVERY)); ?>;
            window.open(openUrl,"_self");
        }
    });

    //DOWNLOAD MENU
    $('button.dup-dnload-btn').click(function(e) {
        var $menu = $(this).parent().find('nav.dup-dnload-menu-items');

        if ($menu.is(':visible')) {
            $menu.hide();
        } else {
            $('nav.dup-dnload-menu-items').hide();
            $menu.show(200);
        }
        return false;
    });

    $(document).click(function(e) {
        var className = e.target.className;
        if (className != 'dpro-menu-x') {
            $('nav.dup-dnload-menu-items').hide();
        }
    });

    $("nav.dup-dnload-menu-items button").each(function() {
        $(this).addClass('dpro-menu-x');
    });
    $("nav.dup-dnload-menu-items button span").each(function() {
        $(this).addClass('dpro-menu-x');
    });

    /*  Creats a comma seperate list of all selected package ids  */
    DupPro.Pack.GetDeleteList = function() {
        var arr = [];
        $("input[name=delete_confirm]:checked").each(function() {
            arr.push(this.id);
        });
        return arr;
    }

    DupPro.Pack.BackupRestoreConfirm = function() {
        <?php $confirmRestoreBk->showConfirm(); ?>
    }

    DupPro.Pack.openLinkDetails = function() {
          <?php $linkInfoDlg->showAlert(); ?>
    }

    DupPro.Pack.BackupRestore = function() {
        Duplicator.Util.ajaxWrapper({
                action: 'duplicator_pro_restore_backup_prepare',
                packageId: DupPro.Pack.RestorePackageId,
                nonce: '<?php echo wp_create_nonce('duplicator_pro_restore_backup_prepare'); ?>'
            },
            function(result, data, funcData, textStatus, jqXHR) {
                window.location.href = data.funcData;
            },
            function(result, data, funcData, textStatus, jqXHR) {
                alert('FAIL');
            }
        );
    };

    /*  Provides the correct confirmation items when deleting packages */
    DupPro.Pack.ConfirmDelete = function() {
        $('#dpro-dlg-confirm-delete-btns input').removeAttr('disabled');
        if ($("#dup-pack-bulk-actions").val() != "delete") {
            <?php $alert1->showAlert(); ?>
            return;
        }

        var list = DupPro.Pack.GetDeleteList();
        if (list.length == 0) {
            <?php $alert2->showAlert(); ?>
            return;
        }
        <?php $confirm1->showConfirm(); ?>
    }


    /*  Removes all selected package sets with ajax call  */
    DupPro.Pack.Delete = function() {
        var packageIds = DupPro.Pack.GetDeleteList();
        var pageCount = $('#current-page-selector').val();
        var pageItems = $('input[name="delete_confirm"]');
        var data = {
            action: 'duplicator_pro_package_delete',
            package_ids: packageIds,
            nonce: '<?php echo $delete_nonce; ?>'
        };
        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: data,
            success: function(respData) {
                try {
                    var parsedData = DupPro.parseJSON(respData);
                } catch (err) {
                    console.error(err);
                    console.error('JSON parse failed for response data: ' + respData);
                    alert('Failed to delete package with AJAX resp: ' + respData);
                    return false;
                }

                if (parsedData.error.length > 0) {
                    alert("Ajax error: " + parsedData.error);
                    return false;
                }

                //Increment back a page-set if no items are left
                if ($('#form-duplicator-nav').length) {
                    if (pageItems.length == packageIds.length)
                        $('#current-page-selector').val(pageCount - 1);
                    $('#form-duplicator-nav').submit();
                } else {
                    $('#form-duplicator').submit();
                }
            }
        });
    }


    /* Toogles the Bulk Action Check boxes */
    DupPro.Pack.SetDeleteAll = function() {
        var state = $('input#dup-chk-all').is(':checked') ? 1 : 0;
        $("input[name=delete_confirm]").each(function() {
            this.checked = (state) ? true : false;
        });
    }


    /* Stops the build from running */
    DupPro.Pack.StopBuild = function(packageID) {
        $('#action').val('stop-build');
        $('#action-parameter').val(packageID);
        $('#form-duplicator').submit();
        
        $('.dup-build-stop-btn').html('<?php DUP_PRO_U::_e("Cancelling..."); ?>');
        $('.dup-build-stop-btn').prop('disabled', true);
    }

    /*  Redirects to the packages detail screen using the package id */
    DupPro.Pack.OpenPackTransfer = function(id) {
        window.location.href = '?page=duplicator-pro&action=detail&tab=transfer&id=' + id + '&_wpnonce=' + '<?php echo wp_create_nonce('package-transfer'); ?>';
    }

    DupPro.Pack.afterSetRecoveryCallback = function(funcData, data, textStatus, jqXHR) {
        DupPro.PackagesTable.find('.dup-recovery-package').each(function() {
            $(this)
                .removeClass('dup-recovery-package')
                .find('.dup-pro-recovery-package-small-icon').remove();

            $(this)
                .find('.dup-pro-recoverable-status')[0].dataset.tooltip = <?php echo json_encode(DUP_PRO_U::__('Recovery Point Capable')); ?>;
            console.log($(this).find('.dup-pro-recoverable-status').data('tooltip'));
        });

        let icon = $(<?php echo json_encode(SnapIO::getInclude(DUPLICATOR____PATH . '/views/tools/recovery/recovery-small-icon.php')); ?>);
        let newRecoveryRow = DupPro.PackagesTable.find('#dup-row-pack-id-' + funcData.id).addClass('dup-recovery-package');
        newRecoveryRow
            .find('.dup-cell-name')
            .append(icon);

        newRecoveryRow.find('.dup-pro-recoverable-status')[0].dataset.tooltip = <?php echo json_encode(DUP_PRO_U::__('Active Recovery Point')); ?>;

        $('.dpro-toolbar-recovery-info').removeClass('dup-recovery-unset');
        icon.data('dup-copy-value', funcData.recoveryLink);
        <?php $menuRecoveryRecoveryBox->closeAlert(); ?>
        DuplicatorTooltip.reload();
    }

    /* Shows remote storage location dialogs */
    DupPro.Pack.ShowRemote = function(package_id, name) {

        <?php $remoteDlg->showAlert(); ?>
        var data = {
            action: 'duplicator_pro_get_storage_details',
            package_id: package_id,
            nonce: '<?php echo wp_create_nonce('duplicator_pro_get_storage_details'); ?>'
        };

        $.ajax({
            type: "POST",
            url: ajaxurl,
            timeout: 10000000,
            data: data,
            complete: function() {},
            success: function(respData) {
                try {
                    var parsedData = DupPro.parseJSON(respData);
                } catch (err) {
                    console.error(err);
                    console.error('JSON parse failed for response data: ' + respData);
                    <?php $alert5->showAlert(); ?>
                    console.log(respData);
                    return false;
                }

                if (!parsedData.success) {
                    var text = "<?php DUP_PRO_U::esc_html_e('Got an error or a warning'); ?>: " + parsedData.message;
                    $('#TB_window .dpro-dlg-alert-txt').html(text);
                    return false;
                }

                var info = '<div class="dup-dlg-store-remote">';
                for (storage_provider_key in parsedData.storage_providers) {

                    var store = parsedData.storage_providers[storage_provider_key];
                    var failed_string = "";
                    var cancelled_string = "";
                    var storage_container_classes = ['dup-dlg-store-endpoint'];

                    if (store.failed) {
                        failed_string = " <i>(<?php DUP_PRO_U::esc_html_e('failed'); ?>)</i>";
                        storage_container_classes.push('dup-dlg-store-endpoint-failed');
                    }

                    if (store.cancelled) {
                        cancelled_string = " <i>(<?php DUP_PRO_U::esc_html_e('cancelled'); ?>)</i>";
                        storage_container_classes.push('dup-dlg-store-endpoint-cancelled');
                    }

                    var storageEditURL = '<?php echo "$tempStorageEditURLWithOutStorageId&storage_id="; ?>' + store.id;
                    var dupProTestConnLink = `<a href="${storageEditURL}" target='_blank'><?php echo DUP_PRO_U::__('[Test Connection]'); ?></a>`;
                    var storeContainerClasses = storage_container_classes.join(' ');
                    var fullName = `<span>"${store.name}" ${failed_string} ${cancelled_string}</span>`;
                    var iconFA = Duplicator.Storage.getFontAwesomeIcon(parseInt(store.storage_type));
                    info += `<div class="${storeContainerClasses}">`;
                    switch (parseInt(store.storage_type)) {
                        //LOCAL NON-DEFAULT
                        case DupPro.Pack.StorageTypes.local:
                            if ((store.id != -2)) {
                                info += `<h4 class="dup-dlg-store-names">${iconFA} <?php DUP_PRO_U::esc_html_e('Local'); ?>: ${fullName}</h4>
                                         <div class="dup-dlg-store-links">${store.storage_location_string}</div>
                                         <div class="dup-dlg-store-test">${dupProTestConnLink}</div>`;
                            }
                            break;
                        //FTP
                        case DupPro.Pack.StorageTypes.ftp:
                            var ftp_url = "<a href='" + encodeURI(store.storage_location_string) + 
                                "' target='_blank'>" + store.storage_location_string + "</a>";
                            info += `<h4 class="dup-dlg-store-names">${iconFA} <?php DUP_PRO_U::esc_html_e('FTP'); ?>: ${fullName}</h4>
                                    <div class="dup-dlg-store-links">${ftp_url}</div>
                                    <div class="dup-dlg-store-test">${dupProTestConnLink}</div>`;
                            break;
                        //SFTP
                        case DupPro.Pack.StorageTypes.sftp:
                            var sftp_url = "<a href='" + encodeURI(store.storage_location_string) + "' target='_blank'>" + 
                                store.storage_location_string + "</a>";
                            info += `<h4 class="dup-dlg-store-names">${iconFA} <?php DUP_PRO_U::esc_html_e('SFTP'); ?>: ${fullName}</h4>
                                    <div class="dup-dlg-store-links">${sftp_url}</div>
                                    <div class="dup-dlg-store-test">${dupProTestConnLink}</div>`;
                            break;
                        //DROPBOX
                        case DupPro.Pack.StorageTypes.dropbox:
                            var dbox_url = "<a href='" + store.storage_location_string + "' target='_blank'>" + store.storage_location_string + "</a>";
                            info += `<h4 class="dup-dlg-store-names">${iconFA} <?php DUP_PRO_U::esc_html_e('Dropbox'); ?>: ${fullName}</h4>
                                    <div class="dup-dlg-store-links">${dbox_url}</div>
                                    <div class="dup-dlg-store-test">${dupProTestConnLink}</div>`;
                            break;
                        //GDRIVE
                        case DupPro.Pack.StorageTypes.gdrive:
                            var gdrive_url = `<a href="https://drive.google.com/drive/" target='_blank'>${store.storage_location_string}</a>`;
                            info += `<h4 class="dup-dlg-store-names">${iconFA} <?php DUP_PRO_U::esc_html_e('Google Drive'); ?>: ${fullName}</h4>
                                    <div class="dup-dlg-store-links">${gdrive_url}</div>
                                    <div class="dup-dlg-store-test">${dupProTestConnLink}</div>`;
                            break;
                        //S3
                        case DupPro.Pack.StorageTypes.s3:
                            info += `<h4 class="dup-dlg-store-names">${iconFA} <?php DUP_PRO_U::esc_html_e('Amazon S3'); ?>: ${fullName}</h4>
                                    <div class="dup-dlg-store-links">${store.storage_location_string}</div>
                                    <div class="dup-dlg-store-test">${dupProTestConnLink}</div>`;
                            break;
                        //ONEDRIVE
                        case DupPro.Pack.StorageTypes.onedrive:
                        case DupPro.Pack.StorageTypes.onedrivemsgraph:
                            var onedriveLnk =  $(store.storage_location_string).attr('target','_blank').prop('outerHTML');
                            info += `<h4 class="dup-dlg-store-names">${iconFA} <?php DUP_PRO_U::esc_html_e('OneDrive'); ?>: ${fullName}</h4>
                                <div class="dup-dlg-store-links">${onedriveLnk}</div>
                                <div class="dup-dlg-store-test">${dupProTestConnLink}</div>`;
                            break;
                    }
                    info += "</div>";
                }
                info += '</div>';
                info += "<a href='" + parsedData.logURL + "' class='dup-dlg-store-log-link' target='_blank'>" + 
                    '<?php echo DUP_PRO_U::__('[Package Build Log]'); ?>' + "</a>";
                $('#TB_window .dpro-dlg-alert-txt').html(info);
            },
            error: function(data) {
                <?php $alert5->showAlert(); ?>
                console.log(data);
            }
        });
        return false;
    };


    /*  Virtual states that UI uses for easier tracking of the three general states a package can be in*/
    DupPro.Pack.ProcessingStats = {
        PendingCancellation: -3,
        Pending: 0,
        Building: 1,
        Storing: 2,
        Finished: 3,
    }


    DupPro.Pack.packageCount = -1;
    DupPro.Pack.setIntervalID = -1;

    DupPro.Pack.SetUpdateInterval = function(period) {
        if (DupPro.Pack.setIntervalID != -1) {
            clearInterval(DupPro.Pack.setIntervalID);
            DupPro.Pack.setIntervalID = -1
        }
        DupPro.Pack.setIntervalID = setInterval(DupPro.Pack.UpdateUnfinishedPackages, period * 1000);
    }

    $('#btn-logs-gift').on('click touchstart', function(e) {
        e.preventDefault();

        var $this = $(this),
            href = 'admin.php?page=duplicator-pro-settings&subtab=profile',
            data = {
                action: 'DUP_PRO_CTRL_Package_toggleGiftFeatureButton',
                nonce: '<?php echo $gift_nonce; ?>',
                hide_gift_btn: true
            };

        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: data
        }).done(function(respData) {
            try {
                var data = '';

                if (typeof respData === 'string') {
                    data = DupPro.parseJSON(respData);
                } else {
                    data = respData;
                }
            } catch (err) {
                console.error(err);
                console.error('JSON parse failed for response data: ' + respData);
                DupPro.Pack.SetUpdateInterval(60);
                console.log(data);
                return false;
            }
            window.location.href = href;
        }).fail(function(data) {
            DupPro.Pack.SetUpdateInterval(60);
            console.log(data);
        });
    });

    DupPro.Pack.UpdateUnfinishedPackages = function() {
        var data = {
            action: 'duplicator_pro_get_package_statii',
            nonce: '<?php echo wp_create_nonce('duplicator_pro_get_package_statii'); ?>'
        }

        $.ajax({
            type: "POST",
            url: ajaxurl,
            dataType: "text",
            timeout: 10000000,
            data: data,
            complete: function() {},
            success: function(respData) {
                try {
                    var data = DupPro.parseJSON(respData);
                } catch (err) {
                    // console.error(err);
                    console.error('JSON parse failed for response data: ' + respData);
                    DupPro.Pack.SetUpdateInterval(60);
                    console.log(respData);
                    return false;
                }

                var activePackagePresent = false;

                if (DupPro.Pack.packageCount == -1) {
                    DupPro.Pack.packageCount = data.length
                } else {
                    if (DupPro.Pack.packageCount != data.length) {
                        window.location = window.location.href;
                    }
                }

                for (package_info_key in data) {
                    var package_info = data[package_info_key];
                    var statusSelector = '#status-' + package_info.ID;
                    var packageRowSelector = '#dup-row-pack-id-' + package_info.ID;
                    var packageSizeSelector = packageRowSelector + ' td:nth-child(4)';
                    var current_value_string = $(statusSelector).text();
                    var current_value = parseInt(current_value_string);
                    var currentProcessingState;

                    if (current_value == -3) {
                        currentProcessingState = DupPro.Pack.ProcessingStats.PendingCancellation;
                    } else if (current_value == 0) {
                        currentProcessingState = DupPro.Pack.ProcessingStats.Pending;
                    } else if ((current_value >= 0) && (current_value < 75)) {
                        currentProcessingState = DupPro.Pack.ProcessingStats.Building;
                    } else if ((current_value >= 75) && (current_value < 100)) {
                        currentProcessingState = DupPro.Pack.ProcessingStats.Storing;
                    } else {
                        // Has to be negative(error) or 100 - both mean complete
                        currentProcessingState = DupPro.Pack.ProcessingStats.Finished;
                    }
                    if (currentProcessingState == DupPro.Pack.ProcessingStats.Pending) {
                        if (package_info.status != 0) {
                            window.location = window.location.href;
                        }
                    } else if (currentProcessingState == DupPro.Pack.ProcessingStats.Building) {
                        if ((package_info.status >= 75) || (package_info.status < 0)) {
                            // Transitioned to storing so refresh
                            window.location = window.location.href;
                            break;
                        } else {

                            activePackagePresent = true;
                            $(statusSelector).text(package_info.status);
                            $(packageSizeSelector).hide().fadeIn(1000).text(package_info.size);
                        }
                    } else if (currentProcessingState == DupPro.Pack.ProcessingStats.Storing) {
                        if ((package_info.status == 100) || (package_info.status < 0)) {
                            // Transitioned to storing so refresh
                            window.location = window.location.href;
                            break;
                        } else {
                            activePackagePresent = true;
                            $('#dpro-progress-status-message-transfer-msg').html(package_info.status_progress_text);
                            var statusProgressSelector = '#status-progress-' + package_info.ID;
                            $(statusProgressSelector).text(package_info.status_progress);
                            console.log("status progress: " + package_info.status_progress);
                        }
                    } else if (currentProcessingState == DupPro.Pack.ProcessingStats.PendingCancellation) {
                        if ((package_info.status == -2) || (package_info.status == -4)) {
                            // refresh when its gone to cancelled
                            window.location = window.location.href;
                        } else {
                            activePackagePresent = true;
                        }
                    } else if (currentProcessingState == DupPro.Pack.ProcessingStats.Finished) {
                        // IF something caused the package to come out of finished refresh everything (has to be out of finished or error state)
                        if ((package_info.status != 100) && (package_info.status > 0)) {
                            // wait one miutes to prevent a realod loop
                            setTimeout(function() {
                                window.location = window.location.href;
                            }, 60000);
                        }
                    }
                }

                if (activePackagePresent) {
                    $('#dup-pro-create-new').addClass('disabled');
                    DupPro.Pack.SetUpdateInterval(10);
                } else {
                    $('#dup-pro-create-new').removeClass('disabled');
                    // Kick refresh down to 60 seconds if nothing is being actively worked on
                    DupPro.Pack.SetUpdateInterval(60);
                }
            },
            error: function(data) {
                DupPro.Pack.SetUpdateInterval(60);
                console.log(data);
            }
        });
    };

    //Init
    DupPro.UI.Clock(DupPro._WordPressInitTime);
    DupPro.Pack.UpdateUnfinishedPackages();
    
    $('#btn-logs-gift').show(500);

    $('.dpro-restore_bk-btn').click(function() {
        DupPro.Pack.RestorePackageId = $(this).data('package-id');
        <?php $confirmRestoreBk->showConfirm(); ?>
    });

    $('.dpro-btn-open-recovery-box').click(function() {
        var msgActive  = "<?php DUP_PRO_U::_e('Recovery Point - Active');?>";
        var msgBtn_1 = "<?php DUP_PRO_U::_e('This Package is the Recovery Point!');?>";
        var msgBtn_2 = "<?php DUP_PRO_U::_e('Launch Recovery Installer');?>";
        var msgBtn_3 = "<?php DUP_PRO_U::_e('installer will open in new window');?>";
        let boxContent = $(this).data('recovery-box');
        <?php
            $menuRecoveryRecoveryBox->updateMessage('boxContent');
            $menuRecoveryRecoveryBox->showAlert();
        ?>

        if ($(this).closest('.dup-row-complete').hasClass('dup-recovery-package')) {
            $('#dpro-dlg-11_message h3').html(`<b class="green"><i class="fas fa-undo-alt fa-sm"></i> ${msgActive}</b>`);
            $('.dup-recovery-box-info .dpro-btn-set-recovery').addClass('disabled');
            $('.dup-recovery-box-info .dpro-btn-set-recovery').html(`<i class="fas fa-undo-alt"></i> ${msgBtn_1}`);
            $('.dup-recovery-box-info .dpro-btn-set-launch-recovery').html(`<i class="fas fa-bolt"></i> ${msgBtn_2}  <small> ${msgBtn_3}</small>`);
        }

        return false;
    });

    $( "body" ).on( "click", '.dup-recovery-box-info .dpro-btn-set-recovery', function() {
        $('.dup-recovery-box-info .dpro-btn-set-recovery').text('<?php DUP_PRO_U::_e('Processing Please Wait...');?>').prop('disabled','disabled');
        DupPro.Pack.SetRecoveryPoint(
            $(this).data('package-id'), 
            DupPro.Pack.afterSetRecoveryCallback,
            function () {
                <?php $menuRecoveryRecoveryBox->closeAlert(); ?>
            }
        );
    });

    $( "body" ).on( "click", '.dup-recovery-box-info .dpro-btn-set-launch-recovery', function() {
        DupPro.Pack.SetRecoveryPoint(
            $(this).data('package-id'), 
            function (funcData, data, textStatus, jqXHR) {
                DupPro.Pack.afterSetRecoveryCallback(funcData, data, textStatus, jqXHR);
                
                setTimeout(
                    function () {  
                        window.open(funcData.recoveryLink);
                    }, 
                    1000
                );
            },
            function () {
                <?php $menuRecoveryRecoveryBox->closeAlert(); ?>
            }
        );
    });

});
</script>
