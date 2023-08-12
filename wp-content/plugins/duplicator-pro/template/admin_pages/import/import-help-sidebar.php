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
<div class="dpro-screen-hlp-info"><b><?php DUP_PRO_U::esc_html_e('Resources'); ?>:</b> 
    <ul>
        <?php echo DUP_PRO_UI_Screen::getHelpSidebarBaseItems(); ?>
        <li>
            <i class='fas fa-cog'></i> <a href='admin.php?page=duplicator-pro-settings&tab=import'>
                <?php DUP_PRO_U::esc_html_e('Import Settings'); ?>
            </a>
        </li>
        <li>
            <i class='fas fa-mouse-pointer'></i> 
                <a href='https://snapcreek.com/blog/how-migrate-wordpress-site-drag-drop-duplicator-pro/' target='_sc-ddguide'>
                <?php DUP_PRO_U::esc_html_e('Drag and Drop Guide'); ?>
            </a>
        </li>                
    </ul>
</div>
