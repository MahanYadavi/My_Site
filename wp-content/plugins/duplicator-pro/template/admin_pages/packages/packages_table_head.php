<?php

/**
 * Duplicator package row in table packages list
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

use Duplicator\Addons\ProBase\License\License;

defined("ABSPATH") or die("");

/**
 * Variables
 *
 * @var \Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var \Duplicator\Core\Views\TplMng $tplMng
 * @var array<string, mixed> $tplData
 */

$global = DUP_PRO_Global_Entity::getInstance();

if ($global->installer_name_mode == DUP_PRO_Global_Entity::INSTALLER_NAME_MODE_SIMPLE) {
    $packageExeNameModeMsg = DUP_PRO_U::__(
        "When clicking the Installer download button, the 'Save as' dialog is currently defaulting the name to 'installer.php'. "
        . "To improve the security and get more information, "
        . "go to: Settings > Packages Tab > Installer > Name option or click on the gear icon at the top of this page."
    );
} else {
    $packageExeNameModeMsg = DUP_PRO_U::__(
        "When clicking the Installer download button, the 'Save as' dialog is defaulting the name to '[name]_[hash]_[date]_installer.php'. "
        . "This is the secure and recommended option.  For more information, "
        . "go to: Settings > Packages Tab > Installer > Name or click on the gear icon at the top of this page.<br/><br/>"
        . "To quickly copy the hashed installer name, to your clipboard use the copy icon link or click the installer name and manually copy the selected text."
    );
}

global $packagesViewData;
?>
<h2 class="screen-reader-text">Packages list</h2>
<thead>
    <tr>
        <th style="width:10px;">
            <input 
                type="checkbox" 
                id="dup-chk-all" 
                title="<?php DUP_PRO_U::esc_attr_e("Select all packages") ?>" 
                style="margin-left:15px" onclick="DupPro.Pack.SetDeleteAll()" />
        </th>
        <th style="padding-right:35px; width:80px;">
            <?php DUP_PRO_U::esc_html_e("Type") ?>
        </th>
        <th style="padding-right:25px; width:100px;">
            <?php DUP_PRO_U::esc_html_e("Created") ?>
        </th>
        <th style="padding-right:25px; width:70px;">
            <?php DUP_PRO_U::esc_html_e("Size") ?>
        </th>
        <th>
            <?php DUP_PRO_U::esc_html_e("Name") ?>
        </th>
        <th style="width:75px;"></th>
        <th style="width:25px;"></th>
        <?php if ($tplData['totalElements'] > 0) : ?>
            <th id="dup-header-chkall">
                <a href="javascript:void(0)" class="button button-link"><i class="fas fa-chevron-left"></i></a>
            </th>
        <?php else : ?>
            <th></th>
        <?php endif; ?>
    </tr>
</thead>
