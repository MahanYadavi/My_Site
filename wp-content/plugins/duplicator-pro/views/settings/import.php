<?php
/* @var $global DUP_PRO_Global_Entity */

defined("ABSPATH") or die("");

use Duplicator\Controllers\ImportPageController;
use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Snap\SnapUtil;

DUP_PRO_U::hasCapability('manage_options');

$nonce_action    = 'duppro-settings-import-edit';
$action_error    = false;
$action_updated  = null;
$action_response = DUP_PRO_U::__("Import Settings Saved");

$global = DUP_PRO_Global_Entity::getInstance();

//SAVE RESULTS
if (isset($_POST['action']) && $_POST['action'] == 'save_import_settings') {
    DUP_PRO_U::verifyNonce($_POST['_wpnonce'], $nonce_action);
    $global->import_chunk_size  = filter_input(
        INPUT_POST,
        'import_chunk_size',
        FILTER_VALIDATE_INT,
        array(
            'options' => array('default' => DUPLICATOR_PRO_DEFAULT_CHUNK_UPLOAD_SIZE)
        )
    );
    $global->import_custom_path = filter_input(
        INPUT_POST,
        'import_custom_path',
        FILTER_CALLBACK,
        array(
            'options' => array(SnapUtil::class, 'sanitizeNSCharsNewlineTrim')
        )
    );
    $newRecoveryCustomPath      = filter_input(
        INPUT_POST,
        'recovery_custom_path',
        FILTER_CALLBACK,
        array(
            'options' => array(SnapUtil::class, 'sanitizeNSCharsNewlineTrim')
        )
    );

    if (
        strlen($global->import_custom_path) > 0 &&
        (
            !is_dir($global->import_custom_path) ||
            !is_readable($global->import_custom_path)
        )
    ) {
        $action_response            = DUP_PRO_U::__(
            "The custom path isn't a valid directory. Check that it exists or that access to it is not restricted by PHP's open_basedir setting."
        );
        $global->import_custom_path = '';
        $action_error               = true;
    }

    $failMessage = '';
    if ($global->setRecoveryCustomPath($newRecoveryCustomPath, $failMessage) == false) {
        $action_error    = true;
        $action_response = $failMessage;
    }

    $action_updated = $global->save();
}
?>
<form id="dup-settings-form" action="<?php echo ControllersManager::getCurrentLink(); ?>" method="post" data-parsley-validate>
    <?php wp_nonce_field($nonce_action); ?>
    <input type="hidden" name="action" value="save_import_settings">

    <?php if ($action_error) { ?>
        <div class="notice notice-error is-dismissible dpro-wpnotice-box"><p><?php echo $action_response; ?></p></div>
    <?php } elseif ($action_updated) { ?>
        <div class="notice notice-success is-dismissible dpro-wpnotice-box"><p><?php echo $action_response; ?></p></div>
    <?php } ?> 

    <h3 id="duplicator-pro-import-settings" class="title"><?php DUP_PRO_U::esc_html_e("Import Settings"); ?></h3>
    <hr size="1" />
    <table class="form-table margin-top-1">
        <tr>
            <th scope="row">
                <label for="input_import_chunk_size" ><?php DUP_PRO_U::esc_html_e("Upload Chunk Size"); ?></label>
            </th>
            <td >
                <select name="import_chunk_size" id="input_import_chunk_size" class="postform">
                    <?php foreach (ImportPageController::getChunkSizes() as $size => $label) { ?>
                        <option value="<?php echo $size; ?>" <?php selected($global->import_chunk_size, $size); ?>><?php echo esc_html($label); ?></option>
                    <?php } ?>
                </select>
                <p class="description">
                    <?php
                        _e("If you have issue uploading a package start with a lower size.  The connection size is from slowest to fastest.", 'duplicator-pro');
                    ?><br/>
                    <small>
                        <?php
                            _e("Note: This setting only applies to the 'Import File' option.", 'duplicator-pro');
                        ?>
                    </small>
                </p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="import_custom_path" ><?php DUP_PRO_U::esc_html_e("Import custom path"); ?></label>
            </th>
            <td >
                <input 
                    class="large" 
                    type="text" 
                    name="import_custom_path" 
                    id="input_import_custom_path" 
                    value="<?php echo esc_attr($global->import_custom_path); ?>" 
                    placeholder=""
                >
                <p class="description">
                    <?php
                    esc_html_e(
                        "Setting a custom path does not change the folder where packages are uploaded but adds a folder to check for packages list.",
                        'duplicator-pro'
                    );
                    ?>
                    <br>
                    <?php
                    esc_html_e(
                        "This can be useful when you want to manually upload packages to another location which can also be a local storage of current or other site.",
                        'duplicator-pro'
                    );
                    ?>
                </p>
            </td>
        </tr>
    </table>

    <h3 class="title"><?php DUP_PRO_U::esc_html_e('Recovery') ?> </h3>
    <hr size="1" />

    <table class="form-table margin-top-1">
        <tr>
            <th scope="row">
                <label for="input_recovery_custom_path" ><?php DUP_PRO_U::esc_html_e("Recovery custom path"); ?></label>
            </th>
            <td>
                <input 
                    class="large" 
                    type="text" 
                    name="recovery_custom_path" 
                    id="input_recovery_custom_path" 
                    value="<?php echo esc_attr($global->getRecoveryCustomPath()); ?>" 
                    placeholder=""
                >
                <p class="description">
                    <?php
                    esc_html_e(
                        "Setting a custom path changes the location the recovery points are generated.",
                        'duplicator-pro'
                    );
                    ?>
                </p>
            </td>
        </tr>
    </table>

    <p class="submit dpro-save-submit">
        <input 
            type="submit" 
            name="submit" 
            id="submit" 
            class="button-primary" 
            value="<?php DUP_PRO_U::esc_attr_e('Save Import Settings') ?>" 
            style="display: inline-block;" 
        >
    </p>
</form>