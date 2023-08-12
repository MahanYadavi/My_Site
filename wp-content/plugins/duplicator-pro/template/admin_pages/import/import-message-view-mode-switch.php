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

?><div class="notice notice-success is-dismissible">
    <p>
        <?php DUP_PRO_U::_e('The mode has <b>switched to advanced</b> because more packages have been detected in the import folder.'); ?>
    </p>
</div>
