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
<div id="dup-pro-import-vews-and-opt-wrapper" >
    <ul class="dup-pro-toggle-sub-menu">
        <li>
            <span class="dup-pro-toggle" >
                <span class="button" >
                    <i class="fas fa-bars fa-lg"></i><span class="screen-reader-text">Options</span>
                </span>
            </span>
            <ul>
                <li class="title"><?php DUP_PRO_U::esc_html_e('VIEWS'); ?></li>
                <li>
                    <span class="link-style no-decoration dup-pro-import-view-list <?php echo $tplData['viewMode'] == 'list' ? 'active' : ''; ?>">
                        <?php DUP_PRO_U::_e('Advanced mode'); ?>
                        <span class="description">View multiple packages</span>
                    </span>
                </li>
                <li>
                    <span class="link-style no-decoration dup-pro-import-view-single <?php echo $tplData['viewMode'] == 'single' ? 'active' : ''; ?>">
                        Basic mode
                        <span class="description"><?php DUP_PRO_U::esc_html_e('View last uploaded package'); ?></span>
                    </span>
                </li>
                <li class="title separator"><?php DUP_PRO_U::esc_html_e('TOOLS'); ?></li>
                <li>
                    <a class="no-decoration" href="?page=duplicator-pro-settings&tab=import" target="_blank">
                        <?php DUP_PRO_U::esc_html_e('Import Settings'); ?>
                    </a>&nbsp;
                    <i class="fas fa-external-link-alt fa-small" ></i>
                </li>
                <li>
                    <span class="link-style no-decoration dup-pro-open-help-link">
                        <?php DUP_PRO_U::esc_html_e('Quick Start'); ?>
                    </span>
                </li>
            </ul>
        </li>
    </ul>
</div>
