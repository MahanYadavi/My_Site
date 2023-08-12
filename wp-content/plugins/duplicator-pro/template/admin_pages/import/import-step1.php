<?php

/**
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

use Duplicator\Controllers\ImportInstallerPageController;
use Duplicator\Controllers\ImportPageController;

if (!ImportInstallerPageController::getInstance()->isEnabled()) {
    ?>
    <div class="dup-pro-import-header" >
        <h2 class="title">
            <i class="fas fa-arrow-alt-circle-down"></i> <?php DUP_PRO_U::esc_html_e("Import"); ?>
        </h2>
        <hr />
    </div>
    <p>
        <?php DUP_PRO_U::esc_html_e("The import function is disabled"); ?>
    </p>
    <?php
    return;
}

$openTab = $tplData['defSubtab'];

switch ($tplData['viewMode']) {
    case ImportPageController::VIEW_MODE_ADVANCED:
        $viewModeClass = 'view-list-item';
        break;
    case ImportPageController::VIEW_MODE_BASIC:
    default:
        $viewModeClass = 'view-single-item';
        break;
}

if ($tplData['adminMessageViewModeSwtich']) {
    $tplMng->render('admin_pages/import/import-message-view-mode-switch');
}

if (DUP_PRO_Global_Entity::getInstance()->import_chunk_size == 0) {
    $footerChunkInfo = sprintf(DUP_PRO_U::__('<b>Chunk Size:</b> N/A &nbsp;|&nbsp; <b>Max Size:</b> %s'), size_format(wp_max_upload_size()));
    $toolTipContent  = DUP_PRO_U::__('If you need to upload a larger file, go to [Settings > Import] and set Upload Chunk Size');
} else {
    $footerChunkInfo = sprintf(
        DUP_PRO_U::__(
            '<b>Chunk Size:</b> %s &nbsp;|&nbsp; <b>Max Size:</b> No Limit'
        ),
        size_format(ImportPageController::getChunkSize() * 1024)
    );
    $toolTipContent  = DUP_PRO_U::__('The max file size limit is ignored when chunk size is enabled.  '
            . 'Use a large chunk size with fast connections and a small size with slower connections.  '
            . 'You can change the chunk size by going to [Settings > Import].');
}

$hlpUpload = DUP_PRO_U::__('Upload speeds can be affected by various server connections and setups.  Additionally, chunk size can influence the '
    . 'upload speed [Settings > Import].  If changing the chunk size is still slow, try uploading the archive manually with these steps:');

$hlpUpload .= '<ul>' .
    '<li>' . DUP_PRO_U::__('1. Cancel current upload') . '</li>' .
    '<li>' . DUP_PRO_U::__('2. Manually upload archive to:<br/> &nbsp; &nbsp; <i>/wp-content/backups-dup-pro/imports/</i>') . '</li>' .
    '<li>' . DUP_PRO_U::__('3. Refresh the Import screen') . '</li>' .
    '</ul>';
?>

<div class="dup-pro-import-header" >
    <h2 class="title">
        <i class="fas fa-arrow-alt-circle-down"></i> <?php printf(DUP_PRO_U::esc_html__("Step %s of 2: Upload Archive"), '<span class="red">1</span>'); ?>
    </h2>
    <div class="options" >
        <?php $tplMng->render('admin_pages/import/import-views-and-options'); ?>
    </div>
    <hr />
</div>

<!-- ==============================
DRAG/DROP AREA -->
<div id="dup-pro-import-upload-tabs-wrapper" class="dup-pro-tabs-wrapper margin-bottom-2">
    <div id="dup-pro-import-mode-tab-header" class="clearfix margin-bottom-2" >
        <div 
            id="dup-pro-import-mode-upload-tab" 
            class="<?php echo ($openTab == ImportPageController::L2_TAB_UPLOAD ? 'active' : ''); ?>" 
            data-tab-target="dup-pro-import-upload-file-tab" 
        >
            <i class="far fa-file-archive"></i> <?php _e('Import File', 'duplicator-pro'); ?>
        </div>
        <div 
            id="dup-pro-import-mode-remote-tab" 
            class="<?php echo ($openTab == ImportPageController::L2_TAB_REMOTE_URL ? 'active' : ''); ?>" 
            data-tab-target="dup-pro-import-remote-file-tab" 
        >
            <i class="fas fa-link"></i> <?php _e('Import Link', 'duplicator-pro'); ?>
        </div>
    </div>
    <div 
        id="dup-pro-import-upload-file-tab" 
        class="tab-content <?php echo ($openTab == ImportPageController::L2_TAB_UPLOAD ? '' : 'no-display'); ?>" 
    >
        <div id="dup-pro-import-upload-file" class="dup-pro-import-upload-box" ></div>
        <div class="no_display" >
            <div id="dup-pro-import-upload-file-content" class="center-xy" >
                <i class="fa fa-download fa-2x"></i>            
                <span class="dup-drag-drop-message" >
                    <?php esc_html_e("Drag & Drop Archive File Here", 'duplicator-pro'); ?>
                </span>
                <input 
                    id="dup-import-dd-btn" 
                    type="button" 
                    class="button button-large button-default dup-import-button" 
                    name="dpro-files" 
                    value="<?php DUP_PRO_U::esc_attr_e("Select File..."); ?>"
                >
            </div>
        </div>
        <div id="dup-pro-import-upload-file-footer" >
            <i 
                class="fas fa-question-circle fa-sm" 
                data-tooltip-title="<?php DUP_PRO_U::esc_html_e("Upload Chunk Size"); ?>" 
                data-tooltip="<?php echo esc_attr($toolTipContent); ?>"
            ></i>&nbsp;<?php echo $footerChunkInfo; ?>&nbsp;|&nbsp;
            <span 
                class="pointer link-style" 
                data-tooltip-title="<?php DUP_PRO_U::esc_html_e("Improve Upload Speed"); ?>" 
                data-tooltip="<?php echo esc_attr($hlpUpload); ?>" 
            >
                <i><?php DUP_PRO_U::esc_html_e('Slow Upload'); ?></i>&nbsp;<i class="fas fa-question-circle fa-sm" ></i>
            </span>
        </div>
    </div>
    <div 
        id="dup-pro-import-remote-file-tab" 
        class="tab-content <?php echo ($openTab == ImportPageController::L2_TAB_REMOTE_URL ? '' : 'no-display'); ?>"
    >
        <div class="dup-pro-import-upload-box">
            <div class="center-xy" >
                <i class="fa fa-download fa-2x"></i>            
                <span class="dup-drag-drop-message" >
                    <?php esc_html_e("Import From Link", 'duplicator-pro'); ?>
                </span>
                <input 
                    type="text" 
                    id="dup-pro-import-remote-url"
                    placeholder="<?php _e('Enter Full URL to Archive', 'duplicator-pro'); ?>" />
                <button id="dup-pro-import-remote-upload" type="button" class="button button-large button-default dup-import-button" >
                    <?php esc_html_e("Upload", 'duplicator-pro'); ?>
                </button> <br/>
                <small>
                    <?php
                        echo sprintf(
                            '%s <a href=" https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-035-q" target="_blank">%s</a>.',
                            __('This feature is in beta, for additional help visit the', 'duplicator-pro'),
                            __('online faq', 'duplicator-pro')
                        );
                        ?>
                </small>
            </div>
        </div>
    </div>
</div>

<!-- ==============================
PACKAGE DETAILS: Basic/Advanced -->
<div id="dpro-pro-import-available-packages" class="<?php echo $viewModeClass; ?>" >
    <table class="dup-import-avail-packs packages-list">
        <thead>
            <tr>
                <th class="name"><?php DUP_PRO_U::esc_html_e("Archives"); ?></th>
                <th class="size"><?php DUP_PRO_U::esc_html_e("Size"); ?></th>
                <th class="created"><?php DUP_PRO_U::esc_html_e("Created"); ?></th>
                <th class="funcs"><?php DUP_PRO_U::esc_html_e("Status"); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $importObjs = DUP_PRO_Package_Importer::getArchiveObjects();
            if (count($importObjs) === 0) {
                $tplMng->render('admin_pages/import/import-package-row-no-found');
            } else {
                foreach ($importObjs as $importObj) {
                    $tplMng->render(
                        'admin_pages/import/import-package-row',
                        array(
                            'importObj' => $importObj,
                            'idRow'     => ''
                        )
                    );
                }
            }
            ?>
        </tbody>
    </table>
    <div class="no_display" >
        <table id="dup-pro-import-available-packages-templates">
            <?php
            $tplMng->render(
                'admin_pages/import/import-package-row',
                array(
                    'importObj' => null,
                    'idRow'     => 'dup-pro-import-row-template'
                )
            );
            $tplMng->render('admin_pages/import/import-package-row-no-found');
            ?>
        </table>
    </div>
</div>


