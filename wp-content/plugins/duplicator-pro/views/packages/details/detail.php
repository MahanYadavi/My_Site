<?php
defined("ABSPATH") or die("");

use Duplicator\Addons\ProBase\License\License;
use Duplicator\Installer\Core\Descriptors\ArchiveConfig;
use Duplicator\Libs\Snap\SnapJson;

/**
 * Variables
 *
 * @var string $error_display
 * @var int $package_id
 */

DUP_PRO_U::hasCapability('export');
$package = DUP_PRO_Package::get_by_id($package_id);
$global  = DUP_PRO_Global_Entity::getInstance();

$ui_css_general = (DUP_PRO_UI_ViewState::getValue('dup-package-dtl-general-panel') ? 'display:block' : 'display:none');
$ui_css_storage = (DUP_PRO_UI_ViewState::getValue('dup-package-dtl-storage-panel') ? 'display:block' : 'display:none');
$ui_css_archive = (DUP_PRO_UI_ViewState::getValue('dup-package-dtl-archive-panel') ? 'display:block' : 'display:none');
$ui_css_install = (DUP_PRO_UI_ViewState::getValue('dup-package-dtl-install-panel') ? 'display:block' : 'display:none');

$archiveDownloadURL   = $package->getLocalPackageFileURL(DUP_PRO_Package_File_Type::Archive);
$logDownloadURL       = $package->getLocalPackageFileURL(DUP_PRO_Package_File_Type::Log);
$installerDownloadURL = $package->getLocalPackageFileURL(DUP_PRO_Package_File_Type::Installer);
$showLinksDialogJson  = SnapJson::jsonEncodeEscAttr(array(
        "archive"   => $archiveDownloadURL,
        "log"       => $logDownloadURL,
        "installer" => $installerDownloadURL,
    ));

$brand          = (isset($package->Brand) && !empty($package->Brand) && is_string($package->Brand) ? $package->Brand : 'unknown');
$lang_notset    = DUP_PRO_U::__("- not set -");
$archive_exists = ($package->getLocalPackageFilePath(DUP_PRO_Package_File_Type::Archive) !== false);
?>

<style>
    /*COMMON*/
    div.tabs-panel {padding: 10px !important}
    div.toggle-box {float:right; margin: 5px 5px 5px 0}
    div.dup-box {margin-top: 15px; font-size:14px; clear: both}
    div.dup-box-panel {padding-bottom:40px}
    table.dup-dtl-data {width:100%}
    table.dup-dtl-data tr {vertical-align: top}
    table.dup-dtl-data tr:first-child td {margin:0; padding-top:0}
    table.dup-dtl-data td {padding:5px 0 5px 0;}
    table.dup-dtl-data td:first-child {font-weight: bold; width:130px !important;}

    table.dup-pack-dtls-sublist {margin-top:10px;}
    table.dup-pack-dtls-sublist td:first-child {white-space: nowrap; vertical-align: middle; width: 70px !important;}
    table.dup-pack-dtls-sublist td {white-space: nowrap; vertical-align:top; padding:2px; font-size:13px}
    div.section-hdr {font-size:16px; display:block; border-bottom: 1px solid #dedede; margin:5px 0 10px 0; font-weight: bold; padding: 0 0 3px 0}
    tr.sub-item td {line-height:22px; font-size:13px}
    table.dup-dtl-data i.fa-filter {display:inline-block; margin-right:3px; width:15px}

    tr.sub-item-disabled td {color:silver}
    td.sub-section {border-bottom: 1px solid #efefef}
    td.sub-notes {font-weight: normal !important; font-style: italic; color:#999; padding-top:10px;}
    div.sub-filter-hdr {padding:5px 0 3px 0; font-weight: bold;}
    div.sub-filter-data {padding:0 0 5px 15px}
    div.filter-info {width:95%; height:250px; overflow-y:scroll; background-color:#FFFFF3; font-size:13px; display:none;
                padding:5px 10px; border:1px solid silver; border-radius:2px; line-height:22px; margin:5px 0 5px 0}
    div.sub-filter-data a {outline: none; box-shadow:none; display: inline-block; padding:7px 1px 7px 7px}

    /*GENERAL*/
    div.dup-link-data {display: none;line-height:24px; margin:5px 0 0 10px}
    div.dup-link-data b {display: inline-block; min-width: 75px}
    div#dpro-downloads-area {padding: 5px 0 5px 0;  }
    div#dpro-downloads-msg {margin-bottom:-5px; font-style: italic}
    textarea.file-info {width:95%; height:200px; font-size:12px; margin:7px 0 5px 0}

    /*ARCHIVE*/
    div#dup-package-dtl-archive-panel {padding-bottom:40px}

    /*INSTALLER*/
    div#dpro-pass-toggle {position: relative; margin:0; width:273px}
    input#secure-pass {border-radius:4px 0 0 4px; width:217px; height: 23px; min-height: auto; margin:0; padding: 0 4px;}
    
    span#dpro-install-secure-lock {color:#A62426; font-size:14px}
    span#dpro-install-secure-unlock {color:#A62426; font-size:14px}
</style>

<?php if ($package_id == 0) : ?>
    <div class="error below-h2"><p><?php DUP_PRO_U::esc_html_e("Invalid Package ID request.  Please try again!"); ?></p></div>
<?php endif; ?>

<div class="toggle-box">
    <a href="javascript:void(0)" onclick="DupPro.Pack.OpenAll()">[<?php DUP_PRO_U::esc_html_e('open all'); ?>]</a> &nbsp;
    <a href="javascript:void(0)" onclick="DupPro.Pack.CloseAll()">[<?php DUP_PRO_U::esc_html_e('close all'); ?>]</a>
</div>

<!-- ===============================
GENERAL -->
<div class="dup-box dup-box-general">
<div class="dup-box-title">
    <i class="fa fa-archive fa-sm"></i> <?php DUP_PRO_U::esc_html_e('General') ?>
    <button class="dup-box-arrow">
        <span class="screen-reader-text"><?php DUP_PRO_U::esc_html_e('Toggle panel:') ?> <?php DUP_PRO_U::esc_html_e('General') ?></span>
    </button>
</div>          
<div class="dup-box-panel" id="dup-package-dtl-general-panel" style="<?php echo $ui_css_general ?>">
    <table class='dup-dtl-data'>
        <tr>
            <td><?php DUP_PRO_U::esc_html_e("Name") ?>:</td>
            <td>
                <a href="javascript:void(0);" onclick="jQuery(this).parent().find('.dup-link-data').toggle()" class="dup-toggle-name">
                    <?php echo $package->Name ?>
                </a> 
                <div class="dup-link-data">
                    <b><?php DUP_PRO_U::esc_html_e("ID") ?>:</b> <?php echo absint($package->ID); ?><br/>
                    <b><?php DUP_PRO_U::esc_html_e("Hash") ?>:</b> <?php echo esc_html($package->Hash); ?><br/>
                    <b><?php DUP_PRO_U::esc_html_e("Full Name") ?>:</b> <?php echo esc_html($package->NameHash); ?><br/>
                </div>
            </td>
        </tr>
        <tr>
            <td><?php DUP_PRO_U::esc_html_e("Notes") ?>:</td>
            <td><?php echo strlen($package->notes) ? esc_html($package->notes) : DUP_PRO_U::__("- no notes -") ?></td>
        </tr>
        <tr>
            <td><?php DUP_PRO_U::esc_html_e("Created") ?>:</td>
            <td>
                <?php if (strlen($package->Created)) : ?>
                    <a href="javascript:void(0);" onclick="jQuery(this).parent().find('.dup-link-data').toggle()" class="dup-toggle-created">
                        <?php echo get_date_from_gmt($package->Created) ?>
                    </a>

                      <div class="dup-link-data dup-link-data-created">
                        <?php
                        $datetime1 = new DateTime($package->Created);
                        $datetime2 = new DateTime(date("Y-m-d H:i:s"));
                        $diff      = $datetime1->diff($datetime2);

                        $fulldate = $diff->y . DUP_PRO_U::__(' years, ') . $diff->m . DUP_PRO_U::__(' months, ') . $diff->d . DUP_PRO_U::__(' days');
                        $fulldays = $diff->format('%a') . DUP_PRO_U::__(' days');
                        ?>
                        <b><?php DUP_PRO_U::esc_html_e("Full Age"); ?>: </b> <?php echo esc_html($fulldate); ?> <br/>
                        <b><?php DUP_PRO_U::esc_html_e("Days Old"); ?>: </b> <?php echo esc_html($fulldays); ?> <br/>
                    </div>
                <?php else : ?>
                    <?php DUP_PRO_U::esc_html_e("- not set in this version -"); ?>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td><?php DUP_PRO_U::esc_html_e("Versions") ?>:</td>
            <td>
                <a href="javascript:void(0);" onclick="jQuery(this).parent().find('.dup-link-data').toggle()" class="dup-toggle-versions">
                    <?php echo $package->Version ?>
                </a>
                  <div class="dup-link-data dup-link-data-versions">
                    <b><?php DUP_PRO_U::esc_html_e("WordPress") ?>:</b> <?php echo strlen($package->VersionWP) ? esc_html($package->VersionWP) : DUP_PRO_U::esc_html__("- unknown -") ?><br/>
                    <b><?php DUP_PRO_U::esc_html_e("PHP") ?>:</b> <?php echo strlen($package->VersionPHP) ? esc_html($package->VersionPHP) : DUP_PRO_U::esc_html__("- unknown -") ?><br/>
                    <b><?php DUP_PRO_U::esc_html_e("OS") ?>:</b> <?php echo strlen($package->VersionOS) ? esc_html($package->VersionOS) : DUP_PRO_U::esc_html__("- unknown -") ?><br/>
                    <b><?php DUP_PRO_U::esc_html_e("Mysql") ?>:</b> 
                    <?php echo strlen($package->VersionDB) ? $package->VersionDB : DUP_PRO_U::esc_html__("- unknown -") ?> |
                    <?php echo strlen($package->Database->Comments) ? $package->Database->Comments : DUP_PRO_U::esc_html__('- unknown -') ?><br/>
                </div>
            </td>
        </tr>       
        <tr>
            <td><?php DUP_PRO_U::esc_html_e("Runtime") ?>:</td>
            <td>
                <?php
                $search_types = array('sec.', ',');
                $minute_view  = trim(str_replace($search_types, '', $package->Runtime));
                if (is_numeric($minute_view)) {
                    $minute_view = gmdate("H:i:s", (int) $minute_view);
                }
                echo strlen($package->Runtime) ? $package->Runtime . " &nbsp; <i>({$minute_view})</i>" : DUP_PRO_U::esc_html__("error running");
                ?>
            </td>
        </tr>
        <tr>
            <td><?php DUP_PRO_U::esc_html_e("Type") ?>:</td>
            <td><?php echo $package->get_type_string(); ?></td>
        </tr>           
        <tr>
            <td><?php DUP_PRO_U::esc_html_e("Files") ?>:</td>
            <td>
            <div id="dpro-downloads-area">
            <?php if ($error_display == 'none') : ?>
                <?php if ($package->contains_storage_type(DUP_PRO_Storage_Types::Local) && $archive_exists) : ?>
                    <button class="button dup-downloads-installer" onclick="DupPro.Pack.DownloadFile('<?php echo esc_attr($installerDownloadURL); ?>');return false;">
                        <i class="fa fa-bolt fa-sm"></i> Installer
                    </button>
                    <button class="button dup-downloads-archive" onclick="DupPro.Pack.DownloadFile('<?php echo esc_attr($archiveDownloadURL); ?>');return false;">
                        <i class="far fa-file-archive fa-sm"></i> Archive - <?php echo $package->ZipSize ?>
                    </button>
                    <button class="button thickbox dup-downloads-share-links" onclick="DupPro.Pack.ShowLinksDialog(<?php echo $showLinksDialogJson; ?>);">
                        <i class="fas fa-share-alt fa-sm"></i>
                        <?php DUP_PRO_U::esc_html_e("Share File Links") ?>
                    </button>
                    <table class="dup-pack-dtls-sublist">
                        <tr>
                            <td><?php DUP_PRO_U::esc_html_e("Archive") ?>: </td>
                            <td>
                                <a href="<?php echo esc_attr($archiveDownloadURL); ?>" target="file_results" class="dup-link-archive" download="<?php echo $package->Archive->File ?>">
                                    <?php echo $package->Archive->File ?> 
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td><?php DUP_PRO_U::esc_html_e("Installer") ?>: </td>
                            <td>
                                <a class="dup-link-installer" href="<?php echo $installerDownloadURL; ?>"><?php echo $package->Installer->getInstallerName(); ?></a>
                            </td>
                        </tr>
                        <tr>
                            <td><?php DUP_PRO_U::esc_html_e("Build Log") ?>: </td>
                            <td><a class="dup-link-build-log" href="<?php echo esc_attr($logDownloadURL); ?>" target="file_results"><?php echo $package->get_log_filename(); ?></a></td>
                        </tr>
                        <tr>
                            <td class="sub-notes">
                                <i class="fas fa-download"></i> <?php _e("Click links to download", 'duplicator-pro') ?>
                            </td>
                        </tr>
                    </table>
                <?php else : ?>
                    <!-- CLOUD ONLY FILES -->
                    <div id="dpro-downloads-msg">
                        <i class="fas fa-server"></i>
                        <?php _e("The package files are in remote storage location(s).  Please visit the storage provider to download.", 'duplicator-pro') ?>
                    </div> <br/>
                    <button class="button" disabled="true">
                        <i class="fa fa-exclamation-triangle fa-sm"></i> Installer - <?php echo DUP_PRO_U::byteSize($package->Installer->Size) ?>
                    </button>
                    <button class="button" disabled="true">
                        <i class="fa fa-exclamation-triangle fa-sm"></i> Archive - <?php echo $package->ZipSize ?>
                    </button>
                    <div class="margin-top-1">
                        <b><?php DUP_PRO_U::esc_html_e("Build Log") ?>:</b>&nbsp;
                        <a href="<?php echo esc_attr($logDownloadURL); ?>" target="file_results"><?php echo $package->get_log_filename(); ?></a>
                    </div>
                <?php endif; ?>
            <?php else : ?>
                <div class="maroon">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php _e("Package files were not created succesfully.  Please see the build log for more details.", 'duplicator-pro') ?>
                </div><br/>
                <b><?php DUP_PRO_U::esc_html_e("Build Log") ?>:</b>&nbsp;
                <a href="<?php echo esc_attr($logDownloadURL); ?>" target="file_results"><?php echo $package->get_log_filename(); ?></a>
            <?php endif; ?>
            </div>
            </td>
        </tr>   
    </table>
</div>
</div>

<!-- ==========================================
DIALOG: SHARE LINKS -->
<?php add_thickbox(); ?>
<div id="dup-dlg-quick-path" title="<?php DUP_PRO_U::esc_attr_e('Download Links'); ?>" style="display:none">
    <p class="maroon">
        <i class="fa fa-lock fa-sm"></i>
        <?php DUP_PRO_U::esc_html_e("The following links contain sensitive data.  Please share with caution!"); ?>
    </p>
    
    <div style="padding: 0px 15px 15px 15px;">
        <a href="javascript:void(0)" style="display:inline-block; text-align:right" onclick="DupPro.Pack.GetLinksText()">[<?php DUP_PRO_U::esc_html_e('Select & Copy'); ?>]</a> <br/>
        <textarea id="dpro-dlg-quick-path-data" style='border:1px solid silver; border-radius:3px; width:99%; height:230px; font-size:11px'></textarea><br/>
        <i style='font-size:11px'>
            <?php
                printf(
                    "%s <a href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-trouble-052-q' target='_blank'>%s</a>",
                    DUP_PRO_U::esc_html__("An exact copy of the database SQL and installer file can both be found inside of the archive.zip/daf file.  "
                        . "Download and extract the archive file to get a copy of the installer which will be named 'installer-backup.php'. "
                        . "For details on how to extract a archive.daf file please see: "),
                    DUP_PRO_U::esc_html__("How do I work with DAF files and the DupArchive extraction tool?")
                );
                ?>
        </i>
    </div>
</div>

<!-- ===============================
STORAGE -->
<div class="dup-box dup-box-storage">
    <div class="dup-box-title">
        <i class="fas fa-server fa-sm"></i> <?php DUP_PRO_U::esc_html_e('Storage') ?>
        <button class="dup-box-arrow">
            <span class="screen-reader-text"><?php DUP_PRO_U::esc_html_e('Toggle panel:') ?> <?php DUP_PRO_U::esc_html_e('Storage Options') ?></span>
        </button>
    </div>          
    <div class="dup-box-panel" id="dup-package-dtl-storage-panel" style="<?php echo $ui_css_storage ?>">
        <table class="widefat package-tbl">
            <thead>
                <tr>
                    <th style='width:175px'><?php DUP_PRO_U::esc_html_e('Type') ?></th>
                    <th style='width:275px'><?php DUP_PRO_U::esc_html_e('Name') ?></th>
                    <th style="white-space: nowrap"><?php DUP_PRO_U::esc_html_e('Location') ?></th>
                </tr>
            </thead>
            <?php
            $i                   = 0;
            $latest_upload_infos = $package->get_latest_upload_infos();
            foreach ($latest_upload_infos as $upload_info) :
                if ($upload_info->has_completed(true) == false) {
                    // For now not displaying any cancelled or failed storages
                    continue;
                }

                $i++;
                $store             = DUP_PRO_Storage_Entity::get_by_id($upload_info->storage_id);
                $store_type        = $store->get_storage_type_string();
                $store_id          = $store->get_storage_type();
                $store_location    = $store->get_storage_location_string();
                $row_style         = ($i % 2) ? 'alternate' : '';
                 $isDefaultStorage = ($store->id == '-2');
                ?>
                <tr class="package-row <?php echo $row_style ?>">
                    <td>
                        <?php
                            echo ($isDefaultStorage)
                                ? '<i class="far fa-hdd fa-fw"></i>&nbsp;'
                                : DUP_PRO_Storage_Entity::getStorageIcon($store_id) . '&nbsp;';
                            echo $store_type;
                        ?>
                    </td>
                    <td>
                        <?php
                        $storage_edit_url       = admin_url('admin.php?page=duplicator-pro-storage&tab=storage&inner_page=edit&storage_id=' . $store->id);
                        $storage_edit_nonce_url = wp_nonce_url($storage_edit_url, 'edit-storage');
                        ?>
                        <a href="<?php echo $storage_edit_nonce_url; ?>" target="_blank">
                            <?php  echo "{$store->name}"; ?>
                        </a>
                    </td>
                    <td> <?php echo $store->getHtmlLocationLink();?></td>
                </tr>
            <?php endforeach; ?>    
            <?php if ($i == 0) : ?>
                <tr>
                    <td colspan="3" style="text-align: center">
                        <?php DUP_PRO_U::esc_html_e('- No storage locations associated with this package -'); ?>
                    </td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
</div>


<!-- ===============================
ARCHIVE -->
<div class="dup-box dup-box-archive">
    <div class="dup-box-title">
        <i class="far fa-file-archive fa-sm"></i> <?php DUP_PRO_U::esc_html_e('Archive') ?>
        <button class="dup-box-arrow">
            <span class="screen-reader-text"><?php DUP_PRO_U::esc_html_e('Toggle panel:') ?> <?php DUP_PRO_U::esc_html_e('Archive') ?></span>
        </button>
    </div>          
    <div class="dup-box-panel" id="dup-package-dtl-archive-panel" style="<?php echo $ui_css_archive ?>">

        <!-- FILES -->
        <div class="section-hdr">
            <i class="fas fa-folder-open fa-sm"></i>
            <?php DUP_PRO_U::esc_html_e('FILES'); ?>
        </div>
        <table class='dup-dtl-data'>
            <tr>
                <td><?php DUP_PRO_U::esc_html_e("Engine") ?>: </td>
                <td>
                    <?php
                    $zip_mode_string = DUP_PRO_U::__('Unknown');

                    if (isset($package->build_progress->current_build_mode)) {
                        if ($package->build_progress->current_build_mode == DUP_PRO_Archive_Build_Mode::ZipArchive) {
                            $zip_mode_string = DUP_PRO_U::__("ZipArchive");

                            if ($package->ziparchive_mode === DUP_PRO_ZipArchive_Mode::SingleThread) {
                                $zip_mode_string = DUP_PRO_U::__("ZipArchive ST");
                            }
                        } elseif ($package->build_progress->current_build_mode == DUP_PRO_Archive_Build_Mode::Shell_Exec) {
                            $zip_mode_string = DUP_PRO_U::__("Shell Exec");
                        } else {
                            $zip_mode_string = DUP_PRO_U::__("DupArchive");
                        }
                    }

                    echo $zip_mode_string;
                    ?>
                </td>
            </tr>           
            <tr>
                <td><?php DUP_PRO_U::esc_html_e("Filters") ?>: </td>
                <td><?php echo $package->Archive->FilterOn == 1 ? 'On' : 'Off'; ?></td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <div class="sub-filter-hdr">
                        <i class="far fa-folder-open"></i>
                        <?php DUP_PRO_U::esc_html_e("Directories") ?>
                    </div>

                    <div class="sub-filter-data sub-filter-data-directories">
                        <?php
                        //CUSTOM
                        $title = DUP_PRO_U::__("User defined filtered directories");
                        $count = count($package->Archive->FilterInfo->Dirs->Instance);

                        echo "<a href='javascript:void(0)' onclick=\"jQuery(this).parent().children('.filter-info').eq(0).toggle(200)\" title='{$title}'>"
                            . "<i class='fa fa-filter fa-fw fa-xs'></i>" . DUP_PRO_U::__('User Defined') . "</a>  <sup>({$count})</sup><br/>";

                        echo ($count == 0)
                            ? "<div class='filter-info'>" . DUP_PRO_U::__('- filter type not found -') . "</div>"
                            : "<div class='filter-info'>" . implode(";<br/>", $package->Archive->FilterInfo->Dirs->Instance) . "</div>";


                        //UNREADABLE
                        $title = DUP_PRO_U::__("These paths are filtered because they are unreadable by the system");
                        $count = count($package->Archive->FilterInfo->Dirs->Unreadable);
                        echo "<a href='javascript:void(0)' onclick=\"jQuery(this).parent().children('.filter-info').eq(1).toggle(200)\" title='{$title}'>"
                        . "<i class='fa fa-filter fa-fw fa-xs'></i>" . DUP_PRO_U::__('Unreadable') . "</a> <sup>({$count})</sup><br/>";

                        echo ($count == 0)
                            ? "<div class='filter-info'>" . DUP_PRO_U::__('- filter type not found -') . "</div>"
                            : "<div class='filter-info'>" . implode(";<br/>", $package->Archive->FilterInfo->Dirs->Unreadable) . "</div>";

                        ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <div class="sub-filter-hdr">
                        <i class="far fa-file"></i>
                        <?php DUP_PRO_U::esc_html_e("Files") ?>
                    </div>

                    <div class="sub-filter-data sub-filter-data-files">
                        <?php
                        //CUSTOM
                        $title = DUP_PRO_U::__("User defined filtered files");
                        $count = count($package->Archive->FilterInfo->Files->Instance);
                        echo "<a href='javascript:void(0)' onclick=\"jQuery(this).parent().children('.filter-info').eq(0).toggle(200)\" title='{$title}'>"
                        . "<i class='fa fa-filter fa-fw fa-xs'></i>" . DUP_PRO_U::__('User Defined') . "</a> <sup>({$count})</sup><br/>";

                        echo ($count == 0)
                            ? "<div class='filter-info'>" . DUP_PRO_U::__('- filter type not found -') . "</div>"
                            : "<div class='filter-info'>" . implode(";<br/>", $package->Archive->FilterInfo->Files->Instance) . "</div>";


                        //UNREADABLE
                        $title = DUP_PRO_U::__("These paths are filtered because they are unreadable by the system");
                        $count = count($package->Archive->FilterInfo->Files->Unreadable);
                        echo "<a href='javascript:void(0)' onclick=\"jQuery(this).parent().children('.filter-info').eq(1).toggle(200)\" title='{$title}'>"
                            . "<i class='fa fa-filter fa-fw fa-xs'></i>" . DUP_PRO_U::__('Unreadable') . "</a> <sup>({$count})</sup><br/>";
                        echo ($count == 0)
                            ? "<div class='filter-info'>" . DUP_PRO_U::__('- filter type not found -') . "</div>"
                            : "<div class='filter-info'>" . implode(";<br/>", $package->Archive->FilterInfo->Files->Unreadable) . "</div>";
                        ?>
                    </div>
                </td>
            </tr>       
            <tr>
                <td></td>
                <td>
                    <div class="sub-filter-hdr">
                        <i class="far fa-sticky-note"></i>
                        <?php DUP_PRO_U::esc_html_e("Extensions") ?>
                    </div>
                    
                    <div class="sub-filter-data sub-filter-data-extensions">
                        <?php
                        if (count($package->Archive->FilterExtsAll) > 0) {
                            $filter_ext = implode(',', $package->Archive->FilterExtsAll);
                            echo esc_html($filter_ext);
                        } else {
                            DUP_PRO_U::esc_html_e('- no filters -');
                        }
                        ?>
                    </div>

                </td>
            </tr>
        </table><br/>

        <!-- DATABASE -->
        <div class="section-hdr">
            <i class="fas fa-database"></i>
            <?php DUP_PRO_U::esc_html_e('DATABASE'); ?>
        </div>
        <table class='dup-dtl-data'>
            <tr>
                <td><?php DUP_PRO_U::esc_html_e("Name") ?>: </td>
                <td><?php echo $package->Database->info->name ?></td>
            </tr>
            <tr>
                <td><?php DUP_PRO_U::esc_html_e("Type") ?>: </td>
                <td><?php echo $package->Database->Type ?></td>
            </tr>
            <tr>
                <td><?php DUP_PRO_U::esc_html_e("Engine") ?>: </td>
                <td><?php echo $package->Database->info->dbEngine ?></td>
            </tr>            
            <tr>
                <td><?php DUP_PRO_U::esc_html_e("SQL Mode") ?>: </td>
                <td><?php echo $package->Database->DBMode ?></td>
            </tr>           
            <tr>
                <td><?php DUP_PRO_U::esc_html_e("Filters") ?>: </td>
                <td><?php echo $package->Database->FilterOn == 1 ? 'On' : 'Off'; ?></td>
            </tr>
            <tr>
                <td> </td>
                <td>
                    <?php
                        $title = __('User defined table filters.', 'duplicator-pro');
                        $count = (strlen($package->Database->FilterTables))
                            ? count(explode(',', $package->Database->FilterTables))
                            : 0;
                    ?>
                    
                    <div class="sub-filter-hdr">
                        <i class="fas fa-table"></i>
                        <?php _e('Tables', 'duplicator-pro'); ?>
                    </div>
                    
                    <div class="sub-filter-data sub-filter-data-tables">
                        <a href='javascript:void(0)' onclick="jQuery(this).parent().children('.filter-info').eq(0).toggle(200)" title="<?php echo $title; ?>">
                            <i class='fa fa-filter fa-fw fa-xs'></i><?php _e('User Defined', 'duplicator-pro'); ?></a>  
                        <sup>(<?php echo $count; ?>)</sup>

                        <div id="dup-filter-tables" class="filter-info">
                            <?php
                            echo isset($package->Database->FilterTables) && strlen($package->Database->FilterTables)
                                ? trim(str_replace(',', "<br/>", $package->Database->FilterTables))
                                : DUP_PRO_U::__('- no filters -');
                            ?>
                        </div>
                        
                    </div>
                </td>
            </tr>
            <tr>
                <td><?php _e('Size', 'duplicator-pro') ?>: </td>
                <td><?php echo DUP_PRO_U::byteSize($package->Database->info->tablesSizeOnDisk);?></td>
            </tr>
            <tr>
                <td><?php _e('Collations', 'duplicator-pro') ?>: </td>
                <td><?php echo implode("<br/>", $package->Database->info->collationList);?></td>
            </tr>
        </table>
        <br/>

        <!-- SETUP -->
        <div class="section-hdr">
            <i class="fas fa-sliders-h"></i>
            <?php DUP_PRO_U::esc_html_e('SETUP'); ?>
        </div>
        <table class='dup-dtl-data'>
            <tr>
                <td><?php DUP_PRO_U::esc_html_e("Security"); ?>:</td>
                <td>
                <?php
                switch ($package->Installer->OptsSecureOn) {
                    case ArchiveConfig::SECURE_MODE_NONE:
                        esc_html_e('None', 'duplicator-pro');
                        break;
                    case ArchiveConfig::SECURE_MODE_INST_PWD:
                        esc_html_e('Installer password', 'duplicator-pro');
                        break;
                    case ArchiveConfig::SECURE_MODE_ARC_ENCRYPT:
                        esc_html_e('Archive encryption', 'duplicator-pro');
                        break;
                    default:
                        throw new Exception('Invalid secure mode');
                }
                ?>
                </td>
            </tr>
            <tr>
                <td></td>
                <td class="sub-notes">
                    <?php
                        _e('Lost passwords cannot be recovered. A new archive will need to be created.', 'duplicator-pro');
                    ?>
                </td>
            </tr>
        </table>
    </div>
</div>


<!-- ===============================
INSTALLER -->
<div class="dup-box dup-box-installer" style="margin-bottom: 50px">
    <div class="dup-box-title">
        <i class="fa fa-bolt fa-sm"></i> <?php DUP_PRO_U::esc_html_e('Installer') ?>
        <?php if ($package->Installer->isSecure()) { ?>
            <span id="dpro-install-secure-lock" title="<?php DUP_PRO_U::esc_attr_e('Installer password protection is on for this package.') ?>">
                <i class="fa fa-lock fa-sm"></i>
            </span>
        <?php } else { ?>
            <span id="dpro-install-secure-unlock" title="<?php DUP_PRO_U::esc_attr_e('Installer password protection is off for this package.') ?>">
                <i class="fa fa-unlock-alt"></i>
            </span>
        <?php } ?>
        <button class="dup-box-arrow">
            <span class="screen-reader-text"><?php DUP_PRO_U::esc_html_e('Toggle panel:') ?> <?php DUP_PRO_U::esc_html_e('Installer') ?></span>
        </button>
    </div>          
    <div class="dup-box-panel" id="dup-package-dtl-install-panel" style="<?php echo $ui_css_install ?>">
        <br/>

        <table class='dup-dtl-data'>
            <tr>
                <td colspan="2"><div class="dup-package-hdr-1"><?php DUP_PRO_U::esc_html_e("SETUP") ?></div></td>
            </tr>
            <?php if (License::can(License::CAPABILITY_BRAND)) : ?>
                <tr>
                    <td><?php DUP_PRO_U::esc_html_e("Brand"); ?>:</td>
                    <td><span style="color:#AF5E52; font-weight: bold"><?php echo $brand ?></span></td>
                </tr>
            <?php endif; ?>
            <tr>
                <td><?php esc_html_e("Security", 'duplicator-pro'); ?>:</td>
                <td>
                    <?php echo $package->Installer->isSecure() ? esc_html__("On", 'duplicator-pro') : esc_html__("Off", 'duplicator-pro'); ?>
                </td>
            </tr>          
        </table><br/><br/>

        <table style="width:100%">
            <tr>
                <td colspan="2"><div class="dup-package-hdr-1"><?php DUP_PRO_U::esc_html_e("PREFILLS") ?></div></td>
            </tr>
        </table>

        <!-- ===================
        STEP1 TABS -->
        <div data-dpro-tabs="true">
            <ul>
                <li>&nbsp; <?php DUP_PRO_U::esc_html_e('Basic') ?> &nbsp;</li>
                <li id="dpro-cpnl-tab-lbl"><?php DUP_PRO_U::esc_html_e('cPanel') ?></li>
            </ul>

            <!-- ===================
            TAB1: Basic -->
            <div>
                <table class='dup-dtl-data dup-dtl-basic'>
                    <tr>
                        <td><?php DUP_PRO_U::esc_html_e("Host") ?>:</td>
                        <td><?php echo strlen($package->Installer->OptsDBHost) ? $package->Installer->OptsDBHost : $lang_notset ?></td>
                    </tr>
                    <tr>
                        <td><?php DUP_PRO_U::esc_html_e("Database") ?>:</td>
                        <td><?php echo strlen($package->Installer->OptsDBName) ? $package->Installer->OptsDBName : $lang_notset ?></td>
                    </tr>
                    <tr>
                        <td><?php DUP_PRO_U::esc_html_e("User") ?>:</td>
                        <td><?php echo strlen($package->Installer->OptsDBUser) ? $package->Installer->OptsDBUser : $lang_notset ?></td>
                    </tr>
                </table><br/>
            </div>

            <!-- ===================
            TAB2: cPanel -->
            <div style="max-height: 250px" class="dup-dtl-cpanel">
                <table class='dup-dtl-data'>
                    <tr>
                        <td colspan="2" class="sub-section">&nbsp; <b><?php DUP_PRO_U::esc_html_e("cPanel Login") ?></b> &nbsp;</td>
                    </tr>
                    <tr class="sub-item">
                        <td><?php DUP_PRO_U::esc_html_e("Automation") ?>:</td>
                        <td><?php echo ($package->Installer->OptsCPNLEnable) ? 'On' : 'Off' ?></td>
                    </tr>
                    <tr class="sub-item">
                        <td><?php DUP_PRO_U::esc_html_e("Host") ?>:</td>
                        <td><?php echo strlen($package->Installer->OptsCPNLHost) ? $package->Installer->OptsCPNLHost : $lang_notset ?></td>
                    </tr>
                    <tr class="sub-item">
                        <td><?php DUP_PRO_U::esc_html_e("User") ?>:</td>
                        <td><?php echo strlen($package->Installer->OptsCPNLUser) ? $package->Installer->OptsCPNLUser : $lang_notset ?></td>
                    </tr>
                    <tr>
                        <td colspan="2" class="sub-section"><b><?php DUP_PRO_U::esc_html_e("MySQL Server") ?></b></td>
                    </tr>
                    <tr class="sub-item">
                        <td><?php DUP_PRO_U::esc_html_e("Action") ?>:</td>
                        <td><?php echo ($package->Installer->OptsCPNLDBAction == 'create') ? DUP_PRO_U::__("Create A New Database") : DUP_PRO_U::__("Connect to Existing Database and Remove All Data") ?></td>
                    </tr>
                    <tr class="sub-item">
                        <td><?php DUP_PRO_U::esc_html_e("Host") ?>:</td>
                        <td><?php echo strlen($package->Installer->OptsCPNLDBHost) ? $package->Installer->OptsCPNLDBHost : $lang_notset ?></td>
                    </tr>
                    <tr class="sub-item">
                        <td><?php DUP_PRO_U::esc_html_e("Database") ?>:</td>
                        <td><?php echo strlen($package->Installer->OptsCPNLDBName) ? $package->Installer->OptsCPNLDBName : $lang_notset ?></td>
                    </tr>
                    <tr class="sub-item">
                        <td><?php DUP_PRO_U::esc_html_e("User") ?>:</td>
                        <td><?php echo strlen($package->Installer->OptsCPNLDBUser) ? $package->Installer->OptsCPNLDBUser : $lang_notset ?></td>
                    </tr>
                </table><br/>

            </div>
        </div><br/>
    </div>
</div>

<?php if ($global->debug_on) : ?>
    <div style="margin:0">
        <a href="javascript:void(0)" onclick="jQuery(this).parent().find('.dup-pack-debug').toggle()">[<?php DUP_PRO_U::esc_html_e("View Package Object") ?>]</a><br/>
        <pre class="dup-pack-debug" style="display:none"><?php @print_r($package); ?> </pre>
    </div>
<?php endif; ?> 


<script>
    jQuery(document).ready(function ($) 
    {
        /*  Shows the Share 'Download Links' dialog
         *  @param json     JSON containing all links
         */
        DupPro.Pack.ShowLinksDialog = function(json)
        {
            var url = '#TB_inline?width=650&height=400&inlineId=dup-dlg-quick-path';
            tb_show("<?php DUP_PRO_U::esc_html_e('Package File Links') ?>", url);

            var msg = <?php printf(
                '"%s" + "\n\n%s:\n" + json.archive + "\n\n%s:\n" + json.installer + "\n\n%s:\n" + json.log + "\n\n%s";',
                '=========== SENSITIVE INFORMATION START ===========',
                DUP_PRO_U::__("ARCHIVE"),
                DUP_PRO_U::__("INSTALLER"),
                DUP_PRO_U::__("LOG"),
                '=========== SENSITIVE INFORMATION END ==========='
            );
                        ?>
            $("#dpro-dlg-quick-path-data").val(msg);
            return false;
        }

        /*  Open all Panels  */
        DupPro.Pack.OpenAll = function () {
            DupPro.UI.IsSaveViewState = false;
            var states = [];
            $("div.dup-box").each(function () {
                var pan = $(this).find('div.dup-box-panel');
                var panel_open = pan.is(':visible');
                if (!panel_open)
                    $(this).find('div.dup-box-title').trigger("click");
                states.push({
                    key: pan.attr('id'),
                    value: 1
                });
            });
            DupPro.UI.SaveMulViewStatesByPost(states);
            DupPro.UI.IsSaveViewState = true;
        };

        /*  Close all Panels */
        DupPro.Pack.CloseAll = function () {
            DupPro.UI.IsSaveViewState = false;
            var states = [];
            $("div.dup-box").each(function () {
                var pan = $(this).find('div.dup-box-panel');
                var panel_open = pan.is(':visible');
                if (panel_open)
                    $(this).find('div.dup-box-title').trigger("click");
                states.push({
                    key: pan.attr('id'),
                    value: 0
                });
            });
            DupPro.UI.SaveMulViewStatesByPost(states);
            DupPro.UI.IsSaveViewState = true;
        };

        /** 
         * Submits the password for validation
         */
        DupPro.togglePassword = function ()
        {
            var $input = $('#secure-pass');
            var $button = $('#secure-btn');
            if (($input).attr('type') == 'text') {
                $input.attr('type', 'password');
                $button.html('<i class="fas fa-eye fa-sm"></i>');
            } else {
                $input.attr('type', 'text');
                $button.html('<i class="fas fa-eye-slash fa-sm"></i>');
            }
        }

        /*  Selects all text in share dialog */
        DupPro.Pack.GetLinksText = function () {
            $('#dpro-dlg-quick-path-data').select();
            document.execCommand('copy');
        };


    });
</script>
