<?php
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

$safeMsg = DUP_PRO_Migration::getSaveModeWarning();
$nonce   = wp_create_nonce('duplicator_pro_cleanup_page');
$url     = self_admin_url('admin.php?page=duplicator-pro-tools&tab=diagnostics&_wpnonce=' . $nonce);
?>
<div class="dup-notice-success notice notice-success duplicator-pro-admin-notice dup-migration-pass-wrapper" >
    <p>
        <b><?php
        if (DUP_PRO_Migration::getMigrationData('restoreBackupMode')) {
            DUP_PRO_U::_e('Restore Backup Almost Complete!');
        } else {
            DUP_PRO_U::_e('Migration Almost Complete!');
        }
        ?></b>
    </p>
    <p>
        <?php
        DUP_PRO_U::esc_html_e('Reserved Duplicator Pro installation files have been detected in the root directory.  '
            . 'Please delete these installation files to avoid security issues.');
        ?>
        <br/>
        <?php DUP_PRO_U::esc_html_e('Go to: Tools > General > Information  > Stored Data > and click the "Remove Installation Files" button'); ?><br>
        <a id="dpro-notice-action-general-site-page" href="<?php echo $url; ?>">
            <?php DUP_PRO_U::esc_html_e('Take me there now!'); ?>
        </a>
    </p>
    <?php if (strlen($safeMsg) > 0) { ?>
        <div class="notice-safemode">
            <?php echo esc_html($safeMsg); ?>
        </div>
    <?php } ?>
    <p class="sub-note">
        <i><?php
            DUP_PRO_U::_e('If an archive.zip/daf file was intentially added to the root '
                . 'directory to perform an overwrite install of this site then you can ignore this message.');
            ?>
        </i>
    </p>

    <?php echo apply_filters(DUP_PRO_Migration::HOOK_BOTTOM_MIGRATION_MESSAGE, ''); ?>
</div>