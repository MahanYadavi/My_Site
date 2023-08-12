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

/* @var $importObj DUP_PRO_Package_Importer */
$importObj = $tplData['importObj'];

if ($importObj instanceof DUP_PRO_Package_Importer) {
    $name             = $importObj->getName();
    $size             = $importObj->getSize();
    $created          = $importObj->getCreated();
    $archivePath      = $importObj->getFullPath();
    $htmlDetails      = $importObj->getHtmlDetails(false);
    $installPakageUrl = $importObj->getInstallerPageLink();
    $isImportable     = $importObj->isImportable();
    $funcsEnalbed     = true;
} else {
    $name             = '';
    $size             = '';
    $created          = '';
    $archivePath      = '';
    $htmlDetails      = '';
    $installPakageUrl = '';
    $isImportable     = false;
    $funcsEnalbed     = false;
}

$idHtml                 = strlen($tplData['idRow']) ?  'id="' . esc_attr($tplData['idRow']) . '" ' : '' ;
$rowClasses             = array('dup-pro-import-package');
$installerActionClasses = array('dup-pro-import-action-install', 'button', 'button-primary');
if ($isImportable) {
    $rowClasses[] = 'is-importable';
} else {
    $installerActionClasses[] = 'disabled';
}
?>
<tr <?php echo $idHtml; ?> class="<?php echo implode(' ', $rowClasses) ?>" data-path="<?php echo esc_attr($archivePath); ?>" >
    <td class="name">
        <span class="text"><?php DUP_PRO_U::esc_html_e($name); ?></span>
        <div class="dup-pro-import-package-detail no-display" >
            <?php echo $htmlDetails; ?>
        </div>
    </td>
    <td class="size">
        <span title="<?php printf(DUP_PRO_U::esc_attr__('Total %d bytes'), $size); ?>" >
            <?php esc_html_e(DUP_PRO_U::byteSize($size)); ?>
        </span>
    </td>
    <td class="created">
        <?php esc_html_e($created); ?>
    </td>
    <td class="funcs">
        <div class="actions <?php echo $funcsEnalbed ? '' : 'no-display'; ?>" >
            <button type="button" class="button dup-pro-import-action-package-detail-toggle" >
                <i class="fa fa-caret-down"></i> <?php DUP_PRO_U::esc_html_e('Details'); ?>
            </button> 
            <span class="separator" ></span>
            <button type="button" class="dup-pro-import-action-remove button button-secondary" >
                <i class="fa fa-ban"></i> <?php DUP_PRO_U::esc_html_e('Remove'); ?>
            </button>
           <span class="separator" ></span>
            <button type="button" class="dup-pro-import-action-install button button-primary" 
                data-install-url="<?php echo esc_url($installPakageUrl); ?>" 
                <?php echo $isImportable ? '' : 'disabled'; ?>>
                <i class="fa fa-bolt fa-sm"></i> <?php DUP_PRO_U::esc_html_e('Continue'); ?>
            </button>
        </div>
        <div class="invalid no-display" >
            Package invalid
        </div>
        <div class="dup-pro-loader no-display" >
            <div class="dup-pro-meter-wrapper" >
                <div class="dup-pro-meter blue">
                    <span style="width: 0%"></span>
                </div>
                <span class="text">0%</span>
            </div>
            <a href="" class="dup-pro-import-action-cancel-upload button button-cancel" >
                <i class="fa fa-ban"></i> <?php DUP_PRO_U::esc_html_e('Cancel'); ?>
            </a>
        </div>
    </td>
</tr> 
