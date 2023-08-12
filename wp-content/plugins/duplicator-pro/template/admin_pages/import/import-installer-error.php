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

?>
<div class="wrap">
    <h1>
        <?php _e("Install package error", 'duplicator-pro'); ?>
    </h1>
    <p>
        <?php DUP_PRO_U::esc_html_e("Error on package prepare, please go back and try again."); ?>
    </p>
</div>
