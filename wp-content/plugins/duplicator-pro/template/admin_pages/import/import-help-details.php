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

<div style="padding:10px 10px 10px 0">
    <!-- OVERVIEW -->
    <b><?php DUP_PRO_U::esc_html_e("Overview"); ?>:</b><br/>
    <?php
        DUP_PRO_U::esc_html_e(
            "The import migration tool allows a Duplicator Pro archive to be installed over this site. " .
            "This process is slightly different than using the " . "standalone installer but the end results will be the same. " .
            "The archive file will be exacted, the database installed and this current WordPress site will be overwritten. " .
            "Follow the steps in the Quick Start section to import and install your Duplicator Pro archive file."
        );
        ?>
    <br/><br/>

    <!-- MODES -->
    <b><?php DUP_PRO_U::esc_html_e("Modes"); ?>:</b><br/>
    <?php
        DUP_PRO_U::esc_html_e(
            'Only one archive can be uploaded in "Basic Mode". ' .
            'To upload multiple archive switch to "Advanced Mode" via the menu on the right.'
        );
        ?>
    <br/><br/>

    <!-- STEPS -->
    <b><?php DUP_PRO_U::esc_html_e("Steps"); ?>:</b><br/>
    <?php DUP_PRO_U::esc_html_e('The import process consists of two steps and then the process of running the installer.'); ?>
    <ul>
        <li>
            <b><?php DUP_PRO_U::esc_html_e("Step 1"); ?>:</b>
            <?php DUP_PRO_U::esc_html_e('This step simply upload the Duplicator Pro archive.zip/daf file to this server.'); ?>
        </li>
        <li>
            <b><?php DUP_PRO_U::esc_html_e("Step 2"); ?>:</b>
            <?php DUP_PRO_U::esc_html_e('This step checks to see if a "Recover Point" will be used in the event the site needs to be restored.'); ?>
        </li>
    </ul>
</div>
