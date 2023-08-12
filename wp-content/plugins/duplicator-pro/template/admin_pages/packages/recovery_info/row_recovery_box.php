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
 * @var \DUP_PRO_Package $package
 */

$package        = $tplData['package'];
$isRecoveable   = DUP_PRO_Package_Recover::isPackageIdRecoveable($package->ID);
$isRecoverPoint = (DUP_PRO_Package_Recover::getRecoverPackageId() === $package->ID);

if ($isRecoveable) {
    $tplMng->render('admin_pages/packages/recovery_info/row_recovery_box_available');
} else {
    $tplMng->render('admin_pages/packages/recovery_info/row_recovery_box_unavailable');
}
