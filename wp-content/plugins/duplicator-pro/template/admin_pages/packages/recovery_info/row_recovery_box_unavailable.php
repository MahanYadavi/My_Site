<?php

/**
 * Duplicator package row in table packages list
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

use Duplicator\Package\Recovery\RecoveryStatus;

defined("ABSPATH") or die("");

/**
 * Variables
 *
 * @var \Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 * @var \DUP_PRO_Package $package
 */
$package = $tplData['package'];

$tooltipContent = esc_attr__(
    'A package recovery point status is not required to be enabled and in some cases is desirable. ' .
    'For example you may want to backup only your database.  In this case you can still ' .
    'run a database only install, however the ability to use the recovery point installer will be unavailable.',
    'duplicator-pro'
);
?>

<div class="dup-recover-dlg-title">
    <b><i class="fas fa-undo-alt fa-xs fa-fw"></i><?php _e('Status', 'duplicator-pro'); ?>:</b>
    <?php _e('Disabled', 'duplicator-pro'); ?>
    <sup>
        <i class="fas fa-question-circle fa-xs"
           data-tooltip-title="<?php _e('Recovery', 'duplicator-pro'); ?>"
           data-tooltip="<?php echo $tooltipContent; ?>">
        </i>
    </sup>
</div>


<div class="dup-recover-dlg-subinfo">
    <?php $tplMng->render('parts/recovery/package_info_mini'); ?>
</div>


<?php
    $recoverStatus = new RecoveryStatus($package);
    $tplMng->render('parts/recovery/exclude_data_box', array('recoverStatus' => $recoverStatus));
