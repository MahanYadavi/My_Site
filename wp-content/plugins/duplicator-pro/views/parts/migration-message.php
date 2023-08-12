<?php
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Libs\Snap\SnapUtil;

$safeMsg         = DUP_PRO_Migration::getSaveModeWarning();
$cleanupReport   = DUP_PRO_Migration::getCleanupReport();
$cleanFileAction = (SnapUtil::filterInputRequest('action', FILTER_DEFAULT) === 'installer');
?>
<div class="dup-notice-success notice notice-success duplicator-pro-admin-notice dup-migration-pass-wrapper">
    <div class="dup-migration-pass-title">
        <i class="fa fa-check-circle"></i> <?php
        if (DUP_PRO_Migration::getMigrationData('restoreBackupMode')) {
            DUP_PRO_U::_e('This site has been successfully restored!');
        } else {
            DUP_PRO_U::_e('This site has been successfully migrated!');
        }
        ?>
    </div>
    <p>
        <?php printf(DUP_PRO_U::__('The following installation files are stored in the folder <b>%s</b>'), DUPLICATOR_PRO_SSDIR_PATH_INSTALLER); ?>
    </p>
    <ul class="dup-stored-minstallation-files">
        <?php foreach (DUP_PRO_Migration::getStoredMigrationLists() as $path => $label) { ?>
            <li>
                - <?php echo esc_html($label); ?>
            </li>
        <?php } ?>
    </ul>

    <?php
    if ($cleanFileAction) {
        require DUPLICATOR____PATH . '/views/parts/migration-clean-installation-files.php';
    } else {
        if (count($cleanupReport['instFile']) > 0) { ?>
            <p>
                <?php _e('Security actions:', 'duplicator-pro'); ?>
            </p>
            <ul class="dup-stored-minstallation-files">
                <?php
                foreach ($cleanupReport['instFile'] as $html) { ?>
                    <li>
                        <?php echo $html; ?>
                    </li>
                <?php } ?>
            </ul>
        <?php } ?>
        <p>
            <b><?php DUP_PRO_U::_e('Final step:'); ?></b><br>
            <span id="dpro-notice-action-remove-installer-files" class="link-style" onclick="DupPro.Tools.removeInstallerFiles();">
                <?php DUP_PRO_U::esc_html_e('Remove Installation Files Now!'); ?>
            </span>
        </p>
        <?php if (strlen($safeMsg) > 0) { ?>
            <div class="notice-safemode">
                <?php echo esc_html($safeMsg); ?>
            </div>
        <?php } ?>

        <p class="sub-note">
            <i><?php
                DUP_PRO_U::_e('Note: This message will be removed after all installer files are removed.'
                    . ' Installer files must be removed to maintain a secure site.'
                    . ' Click the link above to remove all installer files and complete the migration.');
                ?><br>
                <i class="fas fa-info-circle"></i>
                <?php
                DUP_PRO_U::_e('If an archive.zip/daf file was intentially added to the root directory to '
                    . 'perform an overwrite install of this site then you can ignore this message.')
                ?>
            </i>
        </p>
        <?php
    }

    echo apply_filters(DUP_PRO_Migration::HOOK_BOTTOM_MIGRATION_MESSAGE, '');
    ?>
</div>