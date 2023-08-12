<?php

/**
 * Duplicator package row in table packages list
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Core\Views\TplMng;

/**
 * Variables
 *
 * @var ControllersManager $ctrlMng
 * @var TplMng $tplMng
 * @var array<string, mixed> $tplData
 * @var DUP_PRO_Package $package
 */
$package = $tplData['package'];
?>
<h3 class="dup-title margin-top-0">
    <i class="fas fa-undo-alt fa-sm"></i> <?php _e('Recovery Point - Available', 'duplicator-pro'); ?>
</h3>

<?php $tplMng->render('parts/recovery/package_info_mini'); ?>

<hr>

<div class="dup-dlg-recover">
    <div class="dup-dlg-recover-choose">
        <b><?php esc_html_e("Choose an Option", 'duplicator-pro'); ?></b>
    </div>

    <button type="button" class="button button-primary dpro-btn-set-recovery" data-package-id="<?php echo $package->ID; ?>">
        <span><i class="fas fa-undo-alt"></i> <?php esc_html_e("Set to Recovery Point", 'duplicator-pro'); ?></span>&nbsp;
    </button> &nbsp;
    <i class="fas fa-question-circle fa-sm dup-base-color"
        data-tooltip-title="<?php esc_attr_e("Activate Recovery", 'duplicator-pro'); ?>"
        data-tooltip="<?php esc_attr_e("This action will set this package as the active recovery point.", 'duplicator-pro'); ?>"
        aria-expanded="false">
    </i>

    <div class="dup-dlg-recover-or">
        - OR -
    </div>
   

    <button type="button" class="button button-primary dpro-btn-set-launch-recovery" data-package-id="<?php echo $package->ID; ?>">
        <span>
            <i class="fas fa-bolt"></i> <?php esc_html_e("Set &amp; Launch Recovery Point Install", 'duplicator-pro'); ?>
            <small>installer will open in new window</small>
        </span>&nbsp;
    </button>  &nbsp; 
    <i  class="fas fa-question-circle fa-sm dup-base-color"
        data-tooltip-title="<?php esc_attr_e("Launch Installer", 'duplicator-pro'); ?>"
        data-tooltip="<?php esc_attr_e(
            "This action will set this package as the active recovery point and launch the recovery installer wizard."
            . " The installer will "
            . "walk you through the restore process step by step.",
            'duplicator-pro'
        ); ?>"
        aria-expanded="false">
    </i>
</div>
<div class="dup-dlg-recover-link margin-top-2">
    <a href="<?php echo ControllersManager::getMenuLink(ControllersManager::TOOLS_SUBMENU_SLUG, "recovery") ?>">
        <?php esc_attr_e("Manage Recovery Point", 'duplicator-pro'); ?>
    </a>
</div>
