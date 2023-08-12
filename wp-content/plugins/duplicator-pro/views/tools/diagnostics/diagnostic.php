<?php
defined("ABSPATH") or die("");

use Duplicator\Controllers\ToolsPageController;
use Duplicator\Core\Controllers\ControllersManager;

$orphaned_filepaths = DUP_PRO_Server::getOrphanedPackageFiles();
$scan_run           = (isset($_POST['action']) && $_POST['action'] == 'duplicator_recursion') ? true : false;


$action_response  = null;
$diagnosticAction = false;

$_REQUEST['action'] = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'display';
$diagnosticAction   = $_REQUEST['action'];
switch ($_REQUEST['action']) {
    case 'duplicator_pro_tools':
        $action_response = DUP_PRO_U::__('Plugin settings reset.');
        break;
    case 'duplicator_pro_ui_view_state':
        $action_response = DUP_PRO_U::__('View state settings reset.');
        break;
    case 'duplicator_pro_package_active':
        $action_response = DUP_PRO_U::__('Active package settings reset.');
        break;
    case 'purge-orphans':
        $action_response = DUP_PRO_U::__('Cleaned up orphaned package files!');
        break;
    case 'tmp-cache':
        DUP_PRO_Package::tmp_cleanup(true);
        $action_response = DUP_PRO_U::__('Build cache removed.');
        break;
    default:
        $diagnosticAction = false;
        $action_response  = null;
}


?>
<form id="dup-settings-form" action="<?php echo ControllersManager::getCurrentLink(); ?>" method="post">
    <?php wp_nonce_field('duplicator_pro_settings_page'); ?>
    <input type="hidden" id="dup-settings-form-action" name="action" value="">
    <?php if (!empty($diagnosticAction)) : ?>
        <div id="message" class="notice notice-success is-dismissible dpro-diagnostic-action-<?php echo $diagnosticAction; ?>"><p><?php echo $action_response; ?></p>
                <?php
                if ($_REQUEST['action'] != 'display') :
                    if ($_REQUEST['action'] == 'purge-orphans') :
                        $html = "";

                        foreach ($orphaned_filepaths as $filepath) {
                            @unlink($filepath);
                            echo (file_exists($filepath)) ? "<div class='failed'><i class='fa fa-exclamation-triangle'></i> {$filepath}  </div>" : "<div class='success'> <i class='fa fa-check'></i> {$filepath} </div>";
                        }

                        echo $html;
                        $orphaned_filepaths = DUP_PRO_Server::getOrphanedPackageFiles();
                        ?>
                    <br/>
                    <i><?php DUP_PRO_U::esc_html_e('If any orphaned files didn\'t get removed then delete them manually') ?>. <br/><br/></i>
                    <?php endif; ?>
                <?php endif; ?>
        </div>
        <?php
    endif;
    include_once(DUPLICATOR____PATH . '/views/tools/diagnostics/inc.data.php');
    include_once(DUPLICATOR____PATH . '/views/tools/diagnostics/inc.settings.php');
    include_once(DUPLICATOR____PATH . '/views/tools/diagnostics/inc.validator.php');
    include_once(DUPLICATOR____PATH . '/views/tools/diagnostics/inc.phpinfo.php');
    ?>
</form>
<?php
$confirm1               = new DUP_PRO_UI_Dialog();
$confirm1->title        = DUP_PRO_U::__('Are you sure you want to delete?');
$confirm1->message      = DUP_PRO_U::__('Delete this option value.');
$confirm1->progressText = DUP_PRO_U::__('Removing, Please Wait...');
$confirm1->jsCallback   = 'DupPro.Settings.DeleteThisOption(this)';
$confirm1->initConfirm();

$confirm2               = new DUP_PRO_UI_Dialog();
$confirm2->title        = DUP_PRO_U::__('Do you want to Continue?');
$confirm2->message      = DUP_PRO_U::__('This will run the scan validation check. This may take several minutes.');
$confirm2->progressText = DUP_PRO_U::__('Please Wait...');
$confirm2->jsCallback   = 'DupPro.Tools.RecursionRun()';
$confirm2->initConfirm();


$confirm3               = new DUP_PRO_UI_Dialog();
$confirm3->title        = DUP_PRO_U::__('This process will remove all build cache files.');
$confirm3->message      = DUP_PRO_U::__('Be sure no packages are currently building or else they will be cancelled.');
$confirm3->progressText = $confirm1->progressText;
$confirm3->jsCallback   = 'DupPro.Tools.ClearBuildCacheRun()';
$confirm3->initConfirm();
?>
<script>
    jQuery(document).ready(function ($) {

        DupPro.Settings.DeleteOption = function (anchor) {
            var key = $(anchor).text(),
                    text = '<?php DUP_PRO_U::esc_html_e("Delete this option value"); ?> [' + key + '] ?';
<?php $confirm1->showConfirm(); ?>
            $("#<?php echo esc_js($confirm1->getID()); ?>-confirm").attr('data-key', key);
            $("#<?php echo esc_js($confirm1->getID()); ?>_message").html(text);

        };

        DupPro.Settings.DeleteThisOption = function (e) {
            var key = $(e).attr('data-key');
            jQuery('#dup-settings-form-action').val(key);
            jQuery('#dup-settings-form').submit();
        }

        DupPro.Tools.removeOrphans = function () {
            window.location = <?php echo json_encode(ToolsPageController::getPurgeOrphanActionUrl()); ?>;
        };

        DupPro.Tools.removeInstallerFiles = function () {
            window.location = <?php echo json_encode(ToolsPageController::getCleanFilesAcrtionUrl()); ?>;
            return false;
        };


        DupPro.Tools.ClearBuildCache = function () {
<?php $confirm3->showConfirm(); ?>
        };

        DupPro.Tools.ClearBuildCacheRun = function () {
            window.location = <?php echo json_encode(ToolsPageController::getRemoveCacheActionUrl()); ?>;
        };


        DupPro.Tools.Recursion = function ()
        {
<?php $confirm2->showConfirm(); ?>
        }

        DupPro.Tools.RecursionRun = function () {
            jQuery('#dup-settings-form-action').val('duplicator_recursion');
            jQuery('#dup-settings-form').submit();
        }

<?php
if ($scan_run) {
    echo "$('#duplicator-scan-results-1').html($('#duplicator-scan-results-2').html())";
}
?>

    });
</script>
