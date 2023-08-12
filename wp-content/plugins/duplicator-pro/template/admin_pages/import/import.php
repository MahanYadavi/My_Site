<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

defined("ABSPATH") or die("");

use Duplicator\Controllers\ImportPageController;

/**
 * Variables
 *
 * @var \Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */

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
?> 

<div class="dup-pro-tab-content-wrapper" >
    <div id="dup-pro-import-phase-one" >
        <?php $tplMng->render('admin_pages/import/import-step1'); ?>
    </div>
    <div id="dup-pro-import-phase-two" class="no-display" >
        <?php $tplMng->render('admin_pages/import/import-step2'); ?>
    </div>
</div>
<?php
require_once DUPLICATOR____PATH . '/views/tools/recovery/widget/recovery-widget-scripts.php';

$tplMng->render('admin_pages/import/import-scripts');
