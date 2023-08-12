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
?>
<div class="dup-pro-toolbar-recovery-info margin-bottom-1">
    <?php
    if (DUP_PRO_Package_Recover::getRecoverPackageId() === false) {
        $tplMng->render('admin_pages/packages/recovery_info/no_recovery_set');
    } else {
        DUP_PRO_CTRL_recovery::renderRecoveryWidged(
            array(
            'selector'   => false,
            'subtitle'   => '',
            'copyLink'   => true,
            'copyButton' => true,
            'launch'     => true,
            'download'   => true,
            'info'       => true
            )
        );
    }
    ?>
</div>
