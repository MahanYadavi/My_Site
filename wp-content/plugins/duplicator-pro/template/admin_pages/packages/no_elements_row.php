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
<tr class="dpro-nopackages">
    <td colspan="8" class="dup-list-nopackages">
        <br />
        <i class="fa fa-archive fa-sm"></i>
        <?php DUP_PRO_U::esc_html_e("No Packages Found"); ?><br />
        <i><?php DUP_PRO_U::esc_html_e("Click 'Create New' to Archive Site"); ?></i>
        <div class="dup-quick-start">
            <b><?php DUP_PRO_U::esc_html_e("New to Duplicator?"); ?></b><br />
            <span class="dup-open-details link-style" onclick="DupPro.Pack.openLinkDetails()">
                <?php DUP_PRO_U::esc_html_e("Learn Duplicator in a few minutes!"); ?>
            </span><br/>
            <a class="dup-quick-start-link" href="https://snapcreek.com/duplicator/docs/quick-start/" target="_blank">
                <?php DUP_PRO_U::esc_html_e("Visit the 'Quick Start' guide!"); ?>
            </a>
        </div>
        <div style="height:75px">&nbsp;</div>
    </td>
</tr>
