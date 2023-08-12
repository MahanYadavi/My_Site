<?php

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Utils\CachesPurge\CachesPurge;

?>
<div class="dpro-diagnostic-action-installer">
    <p>
        <b><?php echo DUP_PRO_U::__('Installation cleanup ran!'); ?></b>
    </p>
    <?php
    $fileRemoved = DUP_PRO_Migration::cleanMigrationFiles();
    $removeError = false;
    if (count($fileRemoved) === 0) {
        ?>
        <p>
            <b><?php DUP_PRO_U::_e('No Duplicator files were found on this WordPress Site.'); ?></b>
        </p> <?php
    } else {
        foreach ($fileRemoved as $path => $success) {
            if ($success) {
                ?><div class="success">
                    <i class="fa fa-check"></i> <?php DUP_PRO_U::_e("Removed"); ?> - <?php echo esc_html($path); ?>
                </div><?php
            } else {
                ?><div class="failed">
                    <i class='fa fa-exclamation-triangle'></i> <?php DUP_PRO_U::_e("Found"); ?> - <?php echo esc_html($path); ?>
                </div><?php
                $removeError = true;
            }
        }
    }
    foreach (DUP_PRO_Migration::purgeCaches() as $message) {
        ?><div class="success">
            <i class="fa fa-check"></i> <?php echo $message; ?>
        </div>
        <?php
    }

    if ($removeError) {
        ?>
        <p>
        <?php DUP_PRO_U::_e('Some of the installer files did not get removed, '); ?>
            <span class="link-style" onclick="DupPro.Tools.removeInstallerFiles();">
        <?php DUP_PRO_U::_e('please retry the installer cleanup process'); ?>
            </span><br>
        <?php DUP_PRO_U::_e(' If this process continues please see the previous FAQ link.'); ?>
        </p>
        <?php
    } else {
        delete_option(DUP_PRO_UI_Notice::OPTION_KEY_MIGRATION_SUCCESS_NOTICE);
    }
    ?>
    <div style="font-style: italic; max-width:900px; padding:10px 0 25px 0;">
        <p>
            <b><i class="fa fa-shield-alt"></i> <?php DUP_PRO_U::esc_html_e('Security Notes'); ?>:</b>
            <?php
            DUP_PRO_U::_e(' If the installer files do not successfully get removed with this action, '
                . 'then they WILL need to be removed manually through your hosts control panel '
                . 'or FTP.  Please remove all installer files to avoid any security issues on this site.');
            ?><br>
            <?php
            DUP_PRO_U::_e('For more details please visit '
                . 'the FAQ link <a href="https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-295-q" target="_blank">'
                . 'Which files need to be removed after an install?'
                . '</a>');
            ?>
        </p>
        <p>
            <b><i class="fa fa-thumbs-up"></i> <?php DUP_PRO_U::esc_html_e('Help Support Duplicator'); ?>:</b>
            <?php
            DUP_PRO_U::_e('The Duplicator team has worked many years to make moving a WordPress site a much easier process. ');
            echo '<br/>';
            DUP_PRO_U::_e('Show your support with a '
                . '<a href="https://wordpress.org/support/plugin/duplicator/reviews/?filter=5" '
                . 'target="_blank">5 star review</a>! We would be thrilled if you could!');
            ?>
        </p>
    </div>
</div>