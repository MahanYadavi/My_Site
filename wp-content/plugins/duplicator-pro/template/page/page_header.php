<?php

/**
 * Duplicator page header
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

require_once(DUPLICATOR____PATH . '/assets/js/javascript.php');
?>
<div class="wrap">
    <?php
    $tplMng->render('page/page_main_title');
