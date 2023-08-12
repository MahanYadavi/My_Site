<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

defined("ABSPATH") or die("");

use Duplicator\Addons\ProBase\License\License;
use Duplicator\Addons\ProBase\LicensingController;
use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Libs\Snap\SnapUtil;

DUP_PRO_U::hasCapability('manage_options');

$global  = DUP_PRO_Global_Entity::getInstance();
$sglobal = DUP_PRO_Secure_Global_Entity::getInstance();

$nonce_action                = 'duppro-settings-licensing-edit';
$error_response              = null;
$action_response             = null;
$license_activation_response = null;
$is_localhost                = strstr($_SERVER['HTTP_HOST'], 'localhost');

//SAVE RESULTS
if (isset($_POST['action'])) {
    $action = sanitize_text_field($_POST['action']);
    switch ($action) {
        case 'activate':
            DUP_PRO_U::verifyNonce($_POST['_wpnonce'], 'duplicator-pro-licence');

            /**
             * If license isn't visible input is always disabled
             */
            if ($global->license_key_visible === License::VISIBILITY_ALL) {
                $submitted_license_key = trim($_REQUEST['_license_key']);
            } else {
                $submitted_license_key = get_option(License::LICENSE_KEY_OPTION_NAME);
            }

            if (License::isValidOvrKey($submitted_license_key)) {
                License::setOvrKey($submitted_license_key);
            } else {
                if (preg_match('/^[a-f0-9]{32}$/i', $submitted_license_key)) {
                    update_option(License::LICENSE_KEY_OPTION_NAME, $submitted_license_key);
                    $license_activation_response = License::changeLicenseActivation(true);

                    switch ($license_activation_response) {
                        case License::ACTIVATION_RESPONSE_OK:
                            $action_response = DUP_PRO_U::__("License Activated");
                            break;
                        case License::ACTIVATION_RESPONSE_POST_ERROR:
                            $error_response = sprintf(
                                __(
                                    "Cannot communicate with snapcreek.com. " .
                                    "Please see <a target='_blank' href='%s'>this FAQ entry</a> for possible causes and resolutions.",
                                    'duplicator-pro'
                                ),
                                'https://snapcreek.com/duplicator/docs/faqs-tech/#faq-licensing-005-q'
                            );
                            break;
                        case License::ACTIVATION_RESPONSE_INVALID:
                        default:
                            $error_response = DUP_PRO_U::__('Error activating license.');
                            break;
                    }
                } else {
                    $error_response = DUP_PRO_U::__('Please enter a valid key. Key should be 32 characters long.');
                }
            }
            break;
        case 'deactivate':
        case 'clear_key':
            DUP_PRO_U::verifyNonce($_POST['_wpnonce'], 'duplicator-pro-licence');
            if (License::isValidOvrKey(License::getLicenseKey())) {
                // Reset license key otherwise will be artificially stuck on as valid
                update_option(License::LICENSE_KEY_OPTION_NAME, '');
            } else {
                $license_activation_response = License::changeLicenseActivation(false);

                switch ($license_activation_response) {
                    case License::ACTIVATION_RESPONSE_OK:
                        $action_response = DUP_PRO_U::__("License Deactivated");
                        break;

                    case License::ACTIVATION_RESPONSE_POST_ERROR:
                        $error_response = sprintf(
                            __(
                                "Cannot communicate with snapcreek.com. " .
                                "Please see <a target='_blank' href='%s'>this FAQ entry</a> for possible causes and resolutions.",
                                'duplicator-pro'
                            ),
                            'https://snapcreek.com/duplicator/docs/faqs-tech/#faq-licensing-005-q'
                        );
                        break;

                    case License::ACTIVATION_RESPONSE_INVALID:
                    default:
                        $error_response = DUP_PRO_U::__('Error deactivating license.');
                        break;
                }
            }

            if ($action == 'clear_key') {
                update_option(License::LICENSE_KEY_OPTION_NAME, '');

                $global->license_key_visible = License::VISIBILITY_ALL;
                $sglobal->lkp                = '';

                $global->save();
                $sglobal->save();
            }
            break;
        case 'change_visibility':
            DUP_PRO_U::verifyNonce($_POST['_wpnonce'], 'duplicator-pro-licence');
            $oldVisibility = (int) $global->license_key_visible;
            $newVisibility = filter_input(INPUT_POST, 'license_key_visible', FILTER_VALIDATE_INT);
            $newPassword   = SnapUtil::sanitizeInput(INPUT_POST, '_key_password', '');
            if ($oldVisibility === $newVisibility) {
                break;
            }

            $updateGlobal = false;

            switch ($newVisibility) {
                case License::VISIBILITY_ALL:
                    if ($sglobal->lkp !== $newPassword) {
                        $error_response = DUP_PRO_U::__("Wrong password entered. Please enter a right passowrd.");
                        break;
                    }
                    $newPassword  = ''; // reset password
                    $updateGlobal = true;
                    break;
                case License::VISIBILITY_NONE:
                case License::VISIBILITY_INFO:
                    if ($oldVisibility == License::VISIBILITY_ALL) {
                        $password_confirmation = SnapUtil::sanitizeInput(INPUT_POST, '_key_password_confirmation', '');

                        if (strlen($newPassword) === 0) {
                            $error_response = DUP_PRO_U::__('Password cannot be empty.');
                            break;
                        }

                        if ($newPassword !== $password_confirmation) {
                            $error_response = DUP_PRO_U::__("Passwords don't match.");
                            break;
                        }
                        $updateGlobal = true;
                    } else {
                        if ($sglobal->lkp !== $newPassword) {
                            $error_response = DUP_PRO_U::__("Wrong password entered. Please enter a right passowrd.");
                            break;
                        }
                    }
                    $updateGlobal = true;
                    break;
            }

            if ($updateGlobal) {
                $global->license_key_visible = $newVisibility;
                $sglobal->lkp                = $newPassword;
                $global->save();
                $sglobal->save();
            }
            break;
    }
}

$license_status          = License::getLicenseStatus(true);
$license_type            = License::getType();
$license_text_disabled   = false;
$activate_button_text    = DUP_PRO_U::__('Activate');
$license_status_text_alt = '';

if ($license_status == License::STATUS_VALID) {
    $license_status_style  = 'color:#509B18';
    $activate_button_text  = DUP_PRO_U::__('Deactivate');
    $license_text_disabled = true;

    $license_key = License::getLicenseKey();

    if (License::isValidOvrKey($license_key)) {
        $standard_key        = License::getStandardKeyFromOvrKey($license_key);
        $license_status_text = DUP_PRO_U::__("Status: Active");
    } else {
        $license_status_text  = '<b>' . DUP_PRO_U::__('Status: ') . '</b>' . DUP_PRO_U::__('Active');
        $license_status_text .= '<br/>';
        $license_status_text .= '<b>' . DUP_PRO_U::__('Expiration: ') . '</b>';
        $license_status_text .= License::getExpirationDate(get_option('date_format'));
        $expDays              = License::getExpirationDays();
        if ($expDays == 0) {
            $expDays = __('expired', 'duplicator-pro');
        } elseif ($expDays == PHP_INT_MAX) {
            $expDays = __('no expiration', 'duplicator-pro');
        } else {
            $expDays = $expDays . ' ' . DUP_PRO_U::__('days left');
        }
        $license_status_text .= ' (<b>' . $expDays . '</b>)';
    }
} elseif (($license_status == License::STATUS_INACTIVE)) {
    //INACTIVE

    $license_status_style = 'color:#dd3d36;';
    $license_status_text  = DUP_PRO_U::__('Status: Inactive');
} elseif ($license_status == License::STATUS_SITE_INACTIVE) {
    //SITE-INACTIVE

    $license_status_style = 'color:#dd3d36;';
    $global               = DUP_PRO_Global_Entity::getInstance();

    if ($global->license_no_activations_left) {
        $license_status_text = __('Status: Inactive (out of site licenses).', 'duplicator-pro') . '<br>' . License::getNoActivationLeftMessage();
    } else {
        $license_status_text = __('Status: Inactive', 'duplicator-pro');
    }
} elseif ($license_status == License::STATUS_EXPIRED) {
    //EXPIRED

    $renewal_url          = 'https://snapcreek.com/checkout?edd_license_key=' . License::getLicenseKey();
    $license_status_style = 'color:#dd3d36;';
    $license_status_text  = sprintf(
        __(
            'Your Duplicator Pro license key has expired so you aren\'t getting important updates! ' .
            '<a target="_blank" href="%1$s">Renew your license now</a>',
            'duplicator-pro'
        ),
        $renewal_url
    );
} else {
    //DEFAULT

    $license_status_string    = License::getLicenseStatusString($license_status);
    $license_status_style     = 'color:#dd3d36;';
    $license_status_text      = '<b>' .  DUP_PRO_U::__('Status: ') . '</b>' . $license_status_string . '<br/>';
    $license_status_text_alt  = DUP_PRO_U::__('If license activation fails please wait a few minutes and retry.');
    $license_status_text_alt .= '<div class="dup-license-status-notes ">';
    $license_status_text_alt .= sprintf(
        DUP_PRO_U::__('- Failure to activate after several attempts please review %1$sfaq activation steps%2$s'),
        '<a target="_blank" href="https://snapcreek.com/duplicator/docs/faqs-tech/#faq-manage-005-q">',
        '</a>.<br/>'
    );
    $license_status_text_alt .= sprintf(
        __('- To upgrade or renew your license visit %1$ssnapcreek.com%2$s', 'duplicator-pro'),
        '<a target="_blank" href="https://snapcreek.com">',
        '</a>.<br/>'
    );
    $license_status_text_alt .= '- A valid key is needed for plugin updates but not for functionality.</div>';
}
?>

<form 
    id="dup-settings-form" 
    action="<?php echo ControllersManager::getCurrentLink(); ?>"
    method="post" 
    data-parsley-validate
>
    <?php // wp_nonce_field($nonce_action);
    ?>
    <input type="hidden" name="action" value="save" id="action">

    <?php if ($action_response != null) : ?>
        <div class="notice notice-success is-dismissible dpro-wpnotice-box">
            <p><?php echo $action_response; ?></p>
        </div>
    <?php endif; ?>

    <?php if ($error_response != null) : ?>
        <div class="notice notice-error is-dismissible dpro-wpnotice-box">
            <p><?php echo $error_response; ?></p>
        </div>
    <?php endif; ?>

    <h3 class="title"><?php DUP_PRO_U::esc_html_e("Activation") ?> </h3>
    <hr size="1" />
    <table class="form-table">
    <?php
    if ($global->license_key_visible !== License::VISIBILITY_NONE) : ?>
        <tr valign="top">
            <th scope="row"><?php DUP_PRO_U::esc_html_e("Dashboard") ?></th>
            <td>
                <?php
                echo sprintf(
                    DUP_PRO_U::__('%1$sManage Account Online%2$s'),
                    '<i class="fa fa-th-large fa-sm"></i> <a target="_blank" href="https://snapcreek.com/dashboard"> ',
                    '</a>'
                );
                ?>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php DUP_PRO_U::esc_html_e("License Type") ?></th>
            <td class="dup-license-type">
                <?php LicensingController::displayLicenseInfo(); ?>
            </td>            
        </tr>
    <?php endif; ?>
        <?php if ($global->license_key_visible === License::VISIBILITY_ALL) : ?>
        <tr valign="top">
            <th scope="row"><label><?php DUP_PRO_U::esc_html_e("License Key"); ?></label></th>
            <td class="dup-license-key-area">
                <input
                    type="<?php echo ($global->license_key_visible === License::VISIBILITY_ALL ? 'text' : 'password'); ?>"
                    class="dup-license-key-input"
                    name="_license_key"
                    id="_license_key"
                    <?php DUP_PRO_UI::echoDisabled($license_text_disabled || !$global->license_key_visible); ?>
                    value="<?php echo ($global->license_key_visible === License::VISIBILITY_ALL ? License::getLicenseKey() : '**********************'); ?>">
                <br>
                <p class="description">
                    <span style="<?php echo $license_status_style; ?>" >
                        <?php echo $license_status_text; ?>
                    </span>
                    <?php echo $license_status_text_alt; ?>
                </p>
                <?php $echostring = (($license_status != License::STATUS_VALID) ? 'true' : 'false'); ?>

                <div class="dup-license-key-btns">
                    <button
                        class="button"
                        onclick="DupPro.Licensing.ChangeActivationStatus(<?php echo $echostring; ?>);return false;">
                        <?php echo $activate_button_text; ?>
                    </button>
                    <button class="button" onclick="DupPro.Licensing.ClearActivationStatus();return false;">
                        <?php DUP_PRO_U::esc_html_e("Clear Key") ?>
                    </button>
                </div>
            </td>
        </tr>
        <?php endif;?>

    </table>

    <h3 class="title"><?php DUP_PRO_U::esc_html_e("Key Visibility") ?> </h3>
    <small>
        <?php
        DUP_PRO_U::esc_html_e(
            "This is an optional setting that prevents the 'License Key' from being copied. " .
            "Select the desired visibility mode, enter a password and hit the 'Change Visibility' button."
        );
        echo '<br/>';
        DUP_PRO_U::esc_html_e("Note: the password can be anything, it does not have to be the same as the WordPress user password.");
        ?>
    </small>
    <hr size="1" />
    <table class="form-table">
    <tr valign="top">
        <th scope="row"><label><?php DUP_PRO_U::esc_html_e("Visibility"); ?></label></th>
            <td>
                <label class="margin-right-1">
                    <input 
                        type="radio" 
                        name="license_key_visible" 
                        value="<?php echo License::VISIBILITY_ALL;?>" 
                        <?php checked($global->license_key_visible, License::VISIBILITY_ALL); ?>
                    >
                    <?php DUP_PRO_U::esc_html_e("License Visible"); ?>
                </label>
                <label class="margin-right-1">
                    <input 
                        type="radio" 
                        name="license_key_visible" 
                        value="<?php echo License::VISIBILITY_INFO;?>" 
                        <?php checked($global->license_key_visible, License::VISIBILITY_INFO); ?> 
                    >
                    <?php DUP_PRO_U::esc_html_e("Info Only"); ?>
                </label>
                <label>
                    <input 
                        type="radio" 
                        name="license_key_visible" 
                        value="<?php echo License::VISIBILITY_NONE;?>" 
                        <?php checked($global->license_key_visible, License::VISIBILITY_NONE); ?> 
                    >
                    <?php DUP_PRO_U::esc_html_e("License Invisible"); ?>
                </label>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label><?php DUP_PRO_U::esc_html_e("Password"); ?></label></th>
            <td>
                <input type="password" class="wide-input" name="_key_password" id="_key_password" size="50" />
            </td>
        </tr>
        <?php if ($global->license_key_visible == License::VISIBILITY_ALL) { ?>
        <tr valign="top">
            <th scope="row"><label><?php DUP_PRO_U::esc_html_e("Retype Password"); ?></label></th>
            <td>
                <input 
                    type="password" 
                    class="wide-input" 
                    name="_key_password_confirmation" 
                    id="_key_password_confirmation" 
                    data-parsley-equalto="#_key_password" 
                    size="50" 
                >
            </td>
        </tr>
        <?php } ?>
        <tr valign="top">
            <th scope="row"></th>
            <td>
                <?php
                wp_nonce_field('duplicator-pro-licence');
                ?>
                <button 
                    class="button" 
                    id="show_hide" 
                    onclick="DupPro.Licensing.ChangeKeyVisibility(); return false;"
                >
                    <?php echo DUP_PRO_U::__('Change Visibility'); ?>
                </button>
            </td>
        </tr>
    </table>
</form>

<script>
    jQuery(document).ready(function($) {
        DupPro.Licensing = new Object();

        // Ensure if they hit enter in one of the password boxes the correct action takes place
        $("#_key_password, #_key_password_confirmation").keyup(function(event) {

            if (event.keyCode == 13) {
                $("#show_hide").click();
            }
        });

        DupPro.Licensing.ChangeActivationStatus = function(activate) {
            if (activate) {
                $('#action').val('activate');
            } else {
                $('#action').val('deactivate');
            }
            $('#dup-settings-form').submit();
        }

        DupPro.Licensing.ClearActivationStatus = function() {
            $('#action').val('clear_key');
            $('#dup-settings-form').submit();
        }

        DupPro.Licensing.ChangeKeyVisibility = function(show) {
            $('#action').val('change_visibility');
            $('#dup-settings-form').submit();
        }

        DupPro.Licensing.ToggleUnlimited = function() {
            $('#unlmtd-lic-text').toggle();
        }
    });
</script>