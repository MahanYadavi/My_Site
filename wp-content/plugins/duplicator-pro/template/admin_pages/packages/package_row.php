<?php

/**
 * @package Duplicator
 */

use Duplicator\Addons\ProBase\License\License;
use Duplicator\Controllers\PackagesPageController;

defined("ABSPATH") or die("");

/**
 * Variables
 *
 * @var Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var Duplicator\Core\Views\TplMng $tplMng
 * @var array<string, mixed> $tplData
 * @var ?DUP_PRO_Package $package
 */
$package     = $tplData['package'];
$global      = DUP_PRO_Global_Entity::getInstance();
$pack_dbonly = false;

$isRecoveable   = DUP_PRO_Package_Recover::isPackageIdRecoveable($package->ID);
$isRecoverPoint = (DUP_PRO_Package_Recover::getRecoverPackageId() === $package->ID);

global $packagesViewData;

if (is_object($package)) {
    $pack_name         = $package->Name;
    $pack_archive_size = $package->Archive->Size;
    $pack_namehash     = $package->NameHash;
    $pack_dbonly       = $package->Archive->ExportOnlyDB;
    $pack_format       = strtolower($package->Archive->Format);
    $brand             = $package->Brand;
} else {
    $pack_archive_size = 0;
    $pack_name         = 'unknown';
    $pack_namehash     = 'unknown';
    $pack_format       = 'unknown';
    $brand             = 'unknown';
}

//Links
$uniqueid         = $package->NameHash;
$remote_display   = $package->contains_non_default_storage();
$storage_problem  = $package->transferWasInterrupted();
$archive_exists   = ($package->getLocalPackageFilePath(DUP_PRO_Package_File_Type::Archive) != false);
$installer_exists = ($package->getLocalPackageFilePath(DUP_PRO_Package_File_Type::Installer) != false);
$progress_error   = '';
$remote_style     = ($remote_display && $storage_problem) ? 'remote-data-fail' : '';

//ROW CSS
$rowClasses   = array('');
$rowClasses[] = ($package->Status >= DUP_PRO_PackageStatus::COMPLETE) ? 'dup-row-complete' : 'dup-row-incomplete';
$rowClasses[] = ($packagesViewData['rowCount'] % 2 == 0) ? 'dup-row-alt-dark' : 'dup-row-alt-light';
$rowClasses[] = ($isRecoverPoint) ? 'dup-recovery-package' : '';
$rowCSS       = trim(implode(' ', $rowClasses));


//ArchiveInfo
$archive_name         = $package->Archive->File;
$archiveDownloadURL   = $package->getLocalPackageFileURL(DUP_PRO_Package_File_Type::Archive);
$installerDownloadURL = $package->getLocalPackageFileURL(DUP_PRO_Package_File_Type::Installer);
$installerFullName    = $package->Installer->getInstallerName();
$defaultStorage       = '?page=duplicator-pro-storage&tab=storage&inner_page=edit-default';

//Lang Values
$txt_DBOnly          = __('DB Only', 'duplicator-pro');
$txt_DatabaseOnly    = __('Database Only', 'duplicator-pro');
$txt_Download        = __("Download", 'duplicator-pro');
$txt_DownloadNoFiles = __("No local files found for this package!", 'duplicator-pro');
$txt_NoRemoteStores  = __("No remote storage configured for package! ", 'duplicator-pro');
$txt_RequiresRemote  = sprintf(
    "%s <a href='{$defaultStorage}' target='_blank'>%s <i class='far fa-hdd fa-fw fa-sm'></i></a>",
    __('This option requires the package to use the built-in default', 'duplicator-pro'),
    __('storage location', 'duplicator-pro')
);

switch ($package->Type) {
    case DUP_PRO_PackageType::MANUAL:
        $package_type_string = DUP_PRO_U::__('Manual');
        break;
    case DUP_PRO_PackageType::SCHEDULED:
        $package_type_string = DUP_PRO_U::__('Schedule');
        break;
    case DUP_PRO_PackageType::RUN_NOW:
        $lang_schedule       = DUP_PRO_U::__('Schedule');
        $lang_title          = DUP_PRO_U::__('This package was started manually from the schedules page.');
        $package_type_string = "{$lang_schedule}<span><sup>&nbsp;<i class='fas fa-cog fa-sm pointer' title='{$lang_title}'></i>&nbsp;</sup><span>";
        break;
    default:
        $package_type_string = DUP_PRO_U::__('Unknown');
        break;
}

$packageDetailsURL = PackagesPageController::getInstance()->getPackageDetailsURL($package->ID);

//===============================================
//COMPLETED: Rows with good data
//===============================================
if ($package->Status >= DUP_PRO_PackageStatus::COMPLETE) :?>
    <tr class="<?php echo $rowCSS; ?>" id="dup-row-pack-id-<?php echo $package->ID; ?>">
        <td class="dup-cell-chk">
            <label for="<?php echo $package->ID; ?>">
            <input 
                name="delete_confirm" 
                type="checkbox" 
                id="<?php echo $package->ID; ?>" 
                data-archive-name="<?php echo esc_attr($archive_name); ?>" 
                data-installer-name="<?php echo esc_attr($installerFullName); ?>" />
            </label>
        </td>
        <td>
            <?php
            echo $package_type_string;
            if ($pack_dbonly) {
                echo "<sup title='{$txt_DatabaseOnly}'>&nbsp;&nbsp;DB</sup>";
            }
            if ($isRecoveable) {
                $title = ($isRecoverPoint ? DUP_PRO_U::esc_attr__('Active Recovery Point') : DUP_PRO_U::esc_attr__('Recovery Point Capable'));
                echo "<sup>&nbsp;&nbsp;<i class='dup-pro-recoverable-status fas fa-undo-alt' data-tooltip='{$title}'></i></sup>";
            }
            ?>
        </td>
        <td><?php echo DUP_PRO_Package::format_and_get_local_date_time($package->Created, $packagesViewData['package_ui_created']); ?></td>
        <td><?php echo DUP_PRO_U::byteSize($pack_archive_size); ?></td>
        <td class="dup-cell-name">
            <?php
            echo $pack_name;
            if ($isRecoverPoint) {
                echo ' ';
                $recoverPackage = DUP_PRO_Package_Recover::getRecoverPackage();
                require(DUPLICATOR____PATH . '/views/tools/recovery/recovery-small-icon.php');
            }
            ?>
        </td>
        <td class="dup-cell-btns">
            <?php
            //=====================
            //DOWNLOAD BUTTON
            if ($archive_exists) : ?>
                <nav class="dup-dnload-menu">
                   <button
                       class="dup-dnload-btn button no-select"
                       type="button" aria-haspopup="true">
                       <i class="fa fa-download"></i>&nbsp;
                       <span><?php echo $txt_Download ?></span>
                   </button>

                   <nav class="dup-dnload-menu-items">
                       <button
                           aria-label="<?php DUP_PRO_U::esc_html_e("Download Installer and Archive") ?>"
                           title="<?php echo ($installer_exists ? '' : DUP_PRO_U::__("Unable to locate both package files!")); ?>"
                           onclick="DupPro.Pack.DownloadFile('<?php echo esc_attr($archiveDownloadURL); ?>',
                                   '<?php echo esc_attr($package->get_archive_filename()); ?>');
                                    setTimeout(function () {DupPro.Pack.DownloadFile('<?php echo esc_attr($installerDownloadURL); ?>');}, 700);
                                    jQuery(this).parent().hide();
                                    return false;"
                            class="dup-dnload-both"
                            >
                               <i class="fa fa-fw <?php echo ($installer_exists ? 'fa-download' : 'fa-exclamation-triangle') ?>"></i>
                               &nbsp;<?php DUP_PRO_U::esc_html_e("Both Files") ?>
                       </button>
                       <button
                           aria-label="<?php DUP_PRO_U::esc_html_e("Download Installer") ?>"
                           title="<?php echo ($installer_exists) ? '' : DUP_PRO_U::__("Unable to locate installer package file!"); ?>"
                           onclick="DupPro.Pack.DownloadFile('<?php echo esc_attr($installerDownloadURL); ?>');
                                    jQuery(this).parent().hide();
                                    return false;"
                            class="dup-dnload-installer">
                           <i class="fa fa-fw <?php echo ($installer_exists ? 'fa-bolt' : 'fa-exclamation-triangle') ?>"></i>&nbsp;
                           <?php DUP_PRO_U::esc_html_e("Installer") ?>
                       </button>
                       <button
                           aria-label="<?php DUP_PRO_U::esc_html_e("Download Archive") ?>"
                           onclick="DupPro.Pack.DownloadFile('<?php echo esc_attr($archiveDownloadURL); ?>',
                                   '<?php echo esc_attr($package->get_archive_filename()); ?>');
                                    jQuery(this).parent().hide();
                                    return false;"
                                    
                            class="dup-dnload-archive">
                               <i class="fa-fw far fa-file-archive"></i>&nbsp;
                               <?php echo DUP_PRO_U::__("Archive") . " ({$pack_format})" ?>
                       </button>
                   </nav>
                </nav>
            <?php else : ?>
                <div class="dup-dnload-btn-disabled" title="<?php echo $txt_DownloadNoFiles; ?>" onclick="DupPro.Pack.DownloadNotice()">
                    <i class="fas fa-download fa-fw"></i> <?php echo $txt_Download ?>
                </div>
            <?php endif; ?>
        </td>
        <?php
        //=====================
        //REMOTE STORE BUTTON
        if ($storage_problem) : ?>
            <td class="dup-cell-btns dup-cell-store-btn"
                aria-label="<?php DUP_PRO_U::esc_attr_e("Remote Storages") ?>"
                onclick="DupPro.Pack.ShowRemote(<?php echo "$package->ID, '$package->NameHash'"; ?>);"
                title="<?php DUP_PRO_U::esc_attr_e("Error during storage transfer.") ?>">
                <span class="button button-link">
                    <i class="fas fa-server <?php echo ($remote_style); ?>"></i>
                </span>
            </td>
        <?php elseif ($remote_display) : ?>
            <td class="dup-cell-btns dup-cell-store-btn"
                onclick="DupPro.Pack.ShowRemote(<?php echo "$package->ID, '$package->NameHash'"; ?>);"
                aria-label="<?php DUP_PRO_U::esc_attr_e("Remote Storages") ?>">
                <span class="button button-link">
                    <i class="fas fa-server <?php echo ($remote_style); ?>"></i>
                </span>
            </td>
        <?php else : ?>
            <td 
                class="dup-cell-btns dup-cell-store-btn disabled"
                title="<?php echo $txt_NoRemoteStores; ?>"
            >
                    <i class="fas fa-server"></i>
            </td>
        <?php endif; ?>
        </td>
        <td class="dup-cell-btns dup-cell-toggle-btn dup-toggle-details">
            <span class="button button-link">
                <i class="fas fa-chevron-left"></i>
            </span>
        </td>
    </tr>

    <tr id="dup-row-pack-id-<?php echo $package->ID; ?>-details" class="dup-row-details">
        <td colspan="8">
            <div class="dup-ovr-hdr">
                <label  onclick="DupPro.Pack.openLinkDetails()">
                    <i class="fas fa-archive"></i>
                    <?php _e('Package Overview', 'duplicator-pro'); ?>
                </label>
            </div>

            <div class="dup-ovr-bar-flex-box">
                <div class="divider">
                    <label><?php DUP_PRO_U::esc_html_e('WordPress');?></label><br/>
                    <?php echo ($package->VersionWP); ?> &nbsp;
                </div>
                <div>
                    <label><?php DUP_PRO_U::esc_html_e('Format');?></label><br/>
                    <?php echo strtoupper($pack_format); ?>
                </div>
                <div>
                    <label><?php DUP_PRO_U::esc_html_e('Files');?></label><br/>
                    <?php echo ($pack_dbonly)
                        ? "<i>{$txt_DBOnly}</i>"
                        : number_format($package->Archive->FileCount); ?>
                </div>
                <div class="divider">
                    <label><?php DUP_PRO_U::esc_html_e('Folders');?></label><br/>
                    <?php echo ($pack_dbonly)
                        ? "<i>{$txt_DBOnly}</i>"
                        :  number_format($package->Archive->DirCount) ?>
                </div>
                <div class="divider">
                    <label><?php DUP_PRO_U::esc_html_e('Tables');?></label><br/>
                    <?php echo "{$package->Database->info->tablesFinalCount} of {$package->Database->info->tablesBaseCount}"; ?>
                </div>
            </div>

            <div class="dup-ovr-ctrls-flex-box">

                <div class="flex-item">
                    <div class="dup-ovr-ctrls-hdrs">
                        <i class="fas fa-link fa-fw"></i>
                        <b><?php DUP_PRO_U::esc_html_e('Install Resources');?> <br/></b>
                        <span class="dup-info-msg01">
                            <?php DUP_PRO_U::esc_html_e('Links are sensitive. Keep them safe!');?>
                        </span>

                        <?php if ($remote_display) : ?>
                            <a class="dup-ovr-ref-links-more no-outline" href="javascript:void(0)"
                            onclick="DupPro.Pack.ShowRemote(<?php echo "$package->ID, '$package->NameHash'"; ?>);">
                                <i class="fas fa-server fa-xs"></i>
                                <?php _e('remote links...', 'duplicator-pro');?>
                            </a>
                        <?php else : ?>
                            <span class="dup-ovr-ref-links-more disabled" title="<?php echo $txt_NoRemoteStores; ?>">
                                <i class="fas fa-server fa-xs"></i>
                                <?php _e('remote links...', 'duplicator-pro');?>
                            </span>
                        <?php endif; ?>
                    </div>


                    <!-- =======================
                    ARCHIVE FILE: -->
                    <div class="dup-ovr-copy-flex-box">
                        <div class="flex-item">
                            <i class="far fa-file-archive fa-fw"></i>
                            <b><?php DUP_PRO_U::esc_html_e('Archive File');?></b>
                            <sup>
                                <?php
                                $archiveFileToolTipTitle = sprintf(
                                    __('This link is used with the <a href=\'%1$s\'>%2$s</a> %3$s', 'duplicator-pro'),
                                    get_admin_url(null, 'admin.php?page=duplicator-pro-import'),
                                    __('Import Link Install', 'duplicator-pro'),
                                    __(
                                        'feature. Use the Copy Link button to copy this URL archive file link to import on another WordPress site.',
                                        'duplicator-pro'
                                    )
                                );?>
                                <i class="fas fa-question-circle fa-xs fa-fw dup-archive-help"
                                    data-tooltip-title="<?php _e("Archive File", 'duplicator-pro'); ?>"
                                    data-tooltip="<?php echo $archiveFileToolTipTitle;?>"></i>
                            </sup>


                        </div>
                        <div class="flex-item"></div>
                    </div>

                    <div class="dup-ovr-copy-flex-box dup-box-file">
                        <?php if ($archive_exists) : ?>
                            <div class="flex-item">
                            <input type="text" class="dup-ovr-ref-links" readonly="readonly"
                                value="<?php echo esc_attr($archiveDownloadURL); ?>"
                                title="<?php echo esc_attr($archiveDownloadURL); ?>"
                                onfocus="jQuery(this).select();" />
                            <span class="fas fa-arrow-alt-circle-down dup-ovr-ref-links-icon"
                                    title="<?php _e('Archive Import Link (URL)', 'duplicator-pro');?>"></span>
                            </div>
                            <div class="flex-item">
                            <span onclick="jQuery(this).parent().parent().find('.dup-ovr-ref-links').select();">
                                <span data-dup-copy-value="<?php echo esc_attr($archiveDownloadURL); ?>"
                                        class="dup-ovr-ref-copy no-select">
                                    <i class='far fa-copy dup-cursor-pointer'></i>
                                    <?php DUP_PRO_U::esc_html_e('Copy Link');?>
                                </span>
                            </span>
                            <span class="dup-ovr-ref-dwnld"
                                aria-label="<?php DUP_PRO_U::esc_html_e("Download Archive") ?>"
                                onclick="DupPro.Pack.DownloadFile('<?php echo esc_attr($archiveDownloadURL); ?>',
                                        '<?php echo esc_attr($package->get_archive_filename()); ?>');">
                                <i class="fas fa-download"></i> <?php DUP_PRO_U::esc_html_e('Download');?>
                            </span>
                            </div>
                        <?php else : ?>
                            <div class="flex-item maroon">
                                <?php echo $txt_RequiresRemote; ?>.
                            </div>
                        <?php endif; ?>
                    </div><br/>

                    <!-- =======================
                    ARCHIVE INSTALLER: -->
                    <?php
                    switch ($global->installer_name_mode) {
                        case DUP_PRO_Global_Entity::INSTALLER_NAME_MODE_SIMPLE:
                            $lockIcon              = 'fa-lock-open';
                            $installerToolTipTitle = sprintf(
                                DUP_PRO_U::__('Using standard installer name. To improve security, '
                                    . 'switch to hashed change in <a href="%1$s">%2$s</a>'),
                                get_admin_url(null, 'admin.php?page=duplicator-pro-settings&tab=package'),
                                DUP_PRO_U::__('Settings')
                            );
                            break;

                        case DUP_PRO_Global_Entity::INSTALLER_NAME_MODE_WITH_HASH:
                        default:
                            $lockIcon              = 'fa-lock';
                            $installerToolTipTitle = DUP_PRO_U::__('Using more secure, hashed installer name.');
                            break;
                    }
                    $installerName = $package->Installer->getDownloadName();
                    ?>

                    <i class="fas fa-bolt fa-fw"></i>
                    <b><?php DUP_PRO_U::esc_html_e('Archive Installer');?></b>
                    <sup>
                        <i class="fas <?php echo $lockIcon; ?> dup-cursor-pointer fa-fw fa-xs dup-installer-help"
                        style="padding-left:3px"
                        data-tooltip="<?php echo esc_html($installerToolTipTitle); ?>"></i>
                    </sup>
                    <div class="dup-ovr-copy-flex-box dup-box-installer">
                        <?php if ($installer_exists) : ?>
                            <div class="flex-item">
                                <input type="text" class="dup-ovr-ref-links" readonly="readonly"
                                    value="<?php echo esc_attr($installerName); ?>"
                                    title="<?php echo esc_attr($installerName); ?>"
                                    onfocus="jQuery(this).select();" /><br/>
                                <span class="dup-info-msg01">
                                &nbsp;<?php DUP_PRO_U::esc_html_e('These links contain highly sensitive data. Share with extra caution!');?>
                                </span>
                            </div>
                            <div class="flex-item">
                                <span onclick="jQuery(this).parent().parent().find('.dup-ovr-ref-links').select();">
                                    <span data-dup-copy-value="<?php echo $installerName; ?>" class="dup-ovr-ref-copy no-select">
                                        <i class='far fa-copy dup-cursor-pointer'></i>
                                        <?php DUP_PRO_U::esc_html_e('Copy Name');?>
                                    </span>
                                </span>
                                <span class="dup-ovr-ref-dwnld"
                                    aria-label="<?php DUP_PRO_U::esc_html_e("Download Installer") ?>"
                                    onclick="DupPro.Pack.DownloadFile('<?php echo esc_attr($installerDownloadURL); ?>');">
                                    <i class="fas fa-download"></i> <?php DUP_PRO_U::esc_html_e('Download');?>
                                </span>
                            </div>
                        <?php else : ?>
                            <div class="flex-item maroon">
                                <?php echo $txt_RequiresRemote; ?>.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- OPTIONS -->
                <div class="flex-item dup-ovr-opts">
                    <div class="dup-ovr-ctrls-hdrs">
                        <br/><b><?php DUP_PRO_U::esc_html_e('Options');?></b>
                    </div>
                    <a
                        aria-label="<?php DUP_PRO_U::esc_attr_e("Go to package details screen") ?>"
                        class="button dup-details"
                        href="<?php echo esc_url($packageDetailsURL); ?>"
                    >
                        <span><i class="fas fa-search"></i> <?php DUP_PRO_U::esc_html_e("View Details") ?></span>
                    </a>
                    <?php if ($archive_exists) : ?>
                        <button class="button dup-transfer"
                            aria-label="<?php _e('Go to package transfer screen', 'duplicator-pro') ?>"
                            onclick="DupPro.Pack.OpenPackTransfer(<?php echo "$package->ID"; ?>); return false;">
                            <span><i class="fa fa-exchange-alt fa-fw"></i> <?php DUP_PRO_U::esc_html_e("Transfer Package") ?></span>
                        </button>
                    <?php else : ?>
                        <span title="<?php _e('Transfer packages requires the use of built-in default storage!', 'duplicator-pro') ?>">
                            <button class="button disabled" >
                                <span><i class="fa fa-exchange-alt fa-fw"></i> <?php DUP_PRO_U::esc_html_e("Transfer Package") ?></span>
                            </button>
                        </span>
                    <?php endif; ?>

                    <?php $recovetBoxContent = $tplMng->render('admin_pages/packages/recovery_info/row_recovery_box', array(), false); ?>
                    <button
                        aria-label="<?php DUP_PRO_U::esc_attr_e("Recover this Package") ?>"
                        class="button dpro-btn-open-recovery-box <?php echo ($isRecoveable) ? '' : 'maroon'?>"
                        data-package-id="<?php echo $package->ID; ?>"
                        data-recovery-box="<?php echo esc_attr($recovetBoxContent); ?>">
                        <?php
                            echo ($isRecoveable)
                                ? '<i class="fas fa-undo-alt fa-fw"></i>&nbsp;'
                                : '<i class="fa fa-info-circle fa-fw"></i>&nbsp;';
                                _e("Recovery Point...", 'duplicator-pro') ?>
                    </button>
                </div>
            </div>

            <!-- LOGO BACK -->
            <div class="dup-wp-back-logo">
                <div>
                    <i class="fab fa-wordpress fa-lg"></i>
                </div>
            </div>
        </td>
    </tr>
<?php else :
    //===============================================
    //INCOMPLETE: Progress/Failures/Cancelations
    //===============================================

    $cellErrCSS = '';

    if ($package->Status < DUP_PRO_PackageStatus::COPIEDPACKAGE) {
        // In the process of building
        $size      = 0;
        $tmpSearch = glob(DUPLICATOR_PRO_SSDIR_PATH_TMP . "/{$pack_namehash}_*");

        if (is_array($tmpSearch)) {
            $result = @array_map('filesize', $tmpSearch);
            $size   = array_sum($result);
        }
        $pack_archive_size = $size;
    }

    // If its in the pending cancels consider it stopped
    if (in_array($package->ID, $packagesViewData['pending_cancelled_package_ids'])) {
        $status = DUP_PRO_PackageStatus::PENDING_CANCEL;
    } else {
        $status = $package->Status;
    }

    $progress_html    = "<span style='display:none' id='status-{$package->ID}'>{$status}</span>";
    $stop_button_text = DUP_PRO_U::__('Stop');

    if ($status >= 0) {
        if ($status >= 75) {
            $stop_button_text = DUP_PRO_U::__('Stop Transfer');
            $progress_html    = "<i class='fa fa-sync fa-sm fa-spin'></i>&nbsp;<span id='status-progress-{$package->ID}'>0</span>%"
            . "<span style='display:none' id='status-{$package->ID}'>{$status}</span>";
        } elseif ($status > 0) {
            $stop_button_text = DUP_PRO_U::__('Stop Build');
            $progress_html    = "<i class='fa fa-cog fa-sm fa-spin'></i>&nbsp;<span id='status-{$package->ID}'>{$status}</span>%";
        } else {
            // In a pending state
            $stop_button_text = DUP_PRO_U::__('Cancel Pending');
            $progress_html    = "<span style='display:none' id='status-{$package->ID}'>{$status}</span>";
        }
    } else {
        //FAILURES AND CANCELLATIONS
        switch ($status) {
            case DUP_PRO_PackageStatus::ERROR:
                $cellErrCSS     = 'dup-cell-err';
                $progress_error = '<div class="progress-error">'
                . '<a type="button" class="dup-cell-err-btn button" href="' . esc_url($packageDetailsURL) . '">'
                . '<i class="fa fa-exclamation-triangle fa-xs"></i>&nbsp;'
                .  DUP_PRO_U::__('Error Processing') . "</a></div><span style='display:none' id='status-" . $package->ID . "'>$status</span>";
                break;

            case DUP_PRO_PackageStatus::BUILD_CANCELLED:
                $cellErrCSS     = 'dup-cell-cancelled';
                $progress_error = '<div class="progress-error"><i class="fas fa-info-circle  fa-sm"></i>&nbsp;'
                . DUP_PRO_U::__('Build Cancelled') . "</div><span style='display:none' id='status-" . $package->ID . "'>$status</span>";
                break;

            case DUP_PRO_PackageStatus::PENDING_CANCEL:
                $progress_error = '<div class="progress-error"><i class="fas fa-info-circle  fa-sm"></i> '
                . DUP_PRO_U::__('Cancelling Build') . "</div><span style='display:none' id='status-"
                . $package->ID . "'>$status</span>";
                break;

            case DUP_PRO_PackageStatus::REQUIREMENTS_FAILED:
                $package_id            = $package->ID;
                $package               = DUP_PRO_Package::get_by_id($package_id);
                $package_log_store_dir = trailingslashit(dirname($package->StorePath));
                $is_txt_log_file_exist = file_exists("{$package_log_store_dir}{$package->NameHash}_log.txt");
                if ($is_txt_log_file_exist) {
                    $link_log = "{$package->StoreURL}{$package->NameHash}_log.txt";
                } else {
                    // .log is for backward compatibility
                    $link_log = "{$package->StoreURL}{$package->NameHash}.log";
                }
                $progress_error = '<div class="progress-error"><a href="' . esc_url($link_log) . '" target="_blank">'
                . '<i class="fas fa-info-circle"></i> '
                . DUP_PRO_U::__('Requirements Failed') . "</a></div>"
                . "<span style='display:none' id='status-" . $package->ID . "'>$status</span>";
                break;
        }
    }
    ?>

    <tr class="<?php echo $rowCSS; ?>" id="dup-row-pack-id-<?php echo $package->ID; ?>">
        <td class="dup-cell-chk">
            <label for="<?php echo $package->ID; ?>">
            <input name="delete_confirm"
                   type="checkbox" id="<?php echo $package->ID;?>"
                   <?php echo ($status >= DUP_PRO_PackageStatus::PRE_PROCESS) ? 'disabled="disabled"' : ''; ?> />
            </label>
        </td>
        <td>
            <?php
                echo (($package->Type == DUP_PRO_PackageType::MANUAL) ? DUP_PRO_U::__('Manual') : DUP_PRO_U::__('Schedule'));
                echo ($pack_dbonly) ? "<sup title='{$txt_DatabaseOnly}'>&nbsp;&nbsp;<i>DB</i></sup>" : '';
            ?>
        </td>
        <td><?php echo DUP_PRO_Package::format_and_get_local_date_time($package->Created, $packagesViewData['package_ui_created']); ?></td>
        <td><?php echo $package->get_display_size(); ?></td>
        <td class="dup-cell-name"><?php echo $pack_name; ?></td>
        <td class="dup-cell-incomplete <?php echo $cellErrCSS; ?> no-select" colspan="3">
            <?php if ($status >= DUP_PRO_PackageStatus::STORAGE_PROCESSING) : ?>
                <button 
                    id="<?php echo "{$uniqueid}_{$global->installer_base_name}" ?>" 
                    <?php DUP_PRO_UI::echoDisabled(!$installer_exists); ?> 
                    class="button button-link no-select dup-dnload-btn-single"
                    onclick="DupPro.Pack.DownloadFile('<?php echo esc_attr($installerDownloadURL); ?>'); return false;">
                    <i class="fa <?php echo ($installer_exists ? 'fa-bolt' : 'fa-exclamation-triangle maroon') ?>"></i>
                    <?php DUP_PRO_U::esc_html_e("Installer") ?>
                </button>
                <button 
                    id="<?php echo "{$uniqueid}_archive.zip" ?>" 
                    <?php DUP_PRO_UI::echoDisabled(!$archive_exists); ?> 
                    class="button button-link no-select dup-dnload-btn-single"
                    onclick="location.href = '<?php echo $package->Archive->getURL(); ?>'; return false;">
                    <i class="<?php echo ($archive_exists ? 'far fa-file-archive' : 'fa fa-exclamation-triangle maroon') ?>"></i>&nbsp;
                    <?php DUP_PRO_U::esc_html_e("Archive") ?>
                </button>
            <?php else : ?>
                <?php if ($status == 0) : ?>
                    <button onclick="DupPro.Pack.StopBuild(<?php echo $package->ID; ?>); return false;" class="button button-large dup-build-stop-btn">
                        <i class="fa fa-times fa-sm"></i> &nbsp; <?php echo $stop_button_text; ?>
                    </button>
                    <?php echo $progress_html; ?>
                <?php else : ?>
                    <?php
                        echo ($status > 0)
                            ? '<i>' . DUP_PRO_U::__('Building Package Files...') . '</i>'
                            : $progress_error;
                    ?>
                <?php endif; ?>
            <?php endif; ?>
        </td>
    </tr>
    <?php if ($status == DUP_PRO_PackageStatus::PRE_PROCESS) : ?>
    <!--   NO DISPLAY -->
    <?php elseif ($status > DUP_PRO_PackageStatus::PRE_PROCESS) :
        //===============================================
        //PROGRESS BAR DISPLAY AREA
        //=============================================== ?>
    <tr class="dup-row-progress">
        <td colspan="8">
            <div class="wp-filter dup-build-msg">
                <?php if ($status < DUP_PRO_PackageStatus::STORAGE_PROCESSING) : ?>
                    <!-- BUILDING PROGRESS-->
                    <div id='dpro-progress-status-message-build'>
                        <div class='status-hdr'>
                            <?php _e('Building Package', 'duplicator-pro'); ?>&nbsp;<?php echo $progress_html; ?>
                        </div>
                        <small>
                            <?php _e('Please allow it to finish before creating another one.', 'duplicator-pro'); ?>
                        </small>
                    </div>
                <?php else : ?>
                    <!-- TRANSFER PROGRESS -->
                    <div id='dpro-progress-status-message-transfer'>
                        <div class='status-hdr'>
                            <?php _e('Transferring Package', 'duplicator-pro'); ?>&nbsp;<?php echo $progress_html; ?>
                        </div>
                        <small id="dpro-progress-status-message-transfer-msg">
                            <?php _e('Getting Transfer State...', 'duplicator-pro'); ?>
                        </small>
                    </div>
                <?php endif; ?>
                <div id="dup-progress-bar-area">
                    <div class="dup-pro-meter-wrapper">
                        <div class="dup-pro-meter blue dup-pro-fullsize">
                            <span></span>
                        </div>
                        <span class="text"></span>
                    </div>
                </div>
                <button onclick="DupPro.Pack.StopBuild(<?php echo $package->ID; ?>); return false;" class="button button-large dup-build-stop-btn">
                    <i class="fa fa-times fa-sm"></i> &nbsp; <?php echo $stop_button_text; ?>
                </button>
            </div>
        </td>
    </tr>
    <?php else : ?>
    <!--   NO DISPLAY -->
    <?php endif; ?>
<?php endif; ?>
<?php
$packagesViewData['rowCount']++;
