<?php

/**
 * @package Duplicator
 */

use Duplicator\Installer\Core\Descriptors\ArchiveConfig;
use Duplicator\Package\SettingsUtils;

defined("ABSPATH") or die("");

/**
 * Variables
 *
 * @var \Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */

$secureOn   = (isset($tplData['secureOn']) ? $tplData['secureOn'] : ArchiveConfig::SECURE_MODE_NONE);
$securePass = (isset($tplData['securePass']) ? $tplData['securePass'] : '');

$unavaliableMessage = '';
$encryptAvaliable   = SettingsUtils::isArchiveEncryptionAvaiable($unavaliableMessage);

?>
<div class="archive-setup-tab" >
    <div class="dup-package-hdr-1">
        <?php esc_html_e('Security', 'duplicator-pro'); ?>
    </div>

    <div class="dup-form-item">
        <span class="title">
            <?php esc_html_e('Mode', 'duplicator-pro') ?>:
        </span>
        <div class="input">
            <span class="secure-on-input-wrapper">
                <label class="margin-right-1" >
                    <input 
                        type="radio" 
                        name="secure-on" 
                        id="secure-on-none" 
                        onclick="DupPro.EnableInstallerPassword()" 
                        required
                        value="<?php echo ArchiveConfig::SECURE_MODE_NONE; ?>"
                        <?php checked($secureOn, ArchiveConfig::SECURE_MODE_NONE); ?>
                        data-parsley-multiple="secure-on-mltiple-error"
                        data-parsley-errors-container="#secure-on-parsely-error"
                    >
                    <?php esc_html_e('None', 'duplicator-pro') ?>
                </label>
                <label class="margin-right-1" >
                    <input 
                        type="radio" 
                        name="secure-on" 
                        id="secure-on-inst-pwd" 
                        value="<?php echo ArchiveConfig::SECURE_MODE_INST_PWD; ?>"
                        <?php checked($secureOn, ArchiveConfig::SECURE_MODE_INST_PWD); ?>
                        onclick="DupPro.EnableInstallerPassword()" 
                        data-parsley-multiple="secure-on-mltiple-error"
                    >
                    <?php esc_html_e('Installer password', 'duplicator-pro') ?>
                </label>
                <label <?php echo ($encryptAvaliable ? '' : 'class="silver"'); ?>>
                    <input 
                        type="radio" 
                        name="secure-on" 
                        id="secure-on-arc-encrypt" 
                        value="<?php echo ArchiveConfig::SECURE_MODE_ARC_ENCRYPT; ?>"
                        <?php echo ($encryptAvaliable ? checked($secureOn, ArchiveConfig::SECURE_MODE_ARC_ENCRYPT, false) : ''); ?>
                        onclick="DupPro.EnableInstallerPassword()" 
                        <?php disabled(!$encryptAvaliable); ?>
                        data-parsley-multiple="secure-on-mltiple-error"
                    >
                    <?php esc_html_e('Archive encryption', 'duplicator-pro') ?>
                </label>
                <i class="fas fa-question-circle fa-sm"
                    data-tooltip-title="<?php esc_attr_e('Security', 'duplicator-pro'); ?>"
                    data-tooltip="<?php $tplMng->renderEscAttr('admin_pages/packages/setup/security-tooltip-content'); ?>">
                </i>
            </span>
            <div id="secure-on-parsely-error"></div>
        </div>
    </div>
    <div class="dup-form-item">
        <span class="title">
            <?php esc_html_e('Password', 'duplicator-pro') ?>:
        </span>
        <div class="input">
            <span class="dup-password-toggle"> 
                <input 
                    id="secure-pass" 
                    type="password" 
                    name="secure-pass" 
                    required="required"
                    size="50"
                    maxlength="150"
                    value="<?php echo esc_attr($securePass); ?>" 
                >
                <button type="button" >
                    <i class="fas fa-eye fa-sm"></i>
                </button>
            </span>
            <div class="input dup-tabs-opts-help-secure-pass">
                <?php
                    esc_html_e(
                        'Caution: Passwords are case-sensitive and if lost cannot be recovered.  Please keep passwords in a safe place!',
                        'duplicator-pro'
                    );
                    echo '<br/>';
                    esc_html_e(
                        'If this password is lost then a new archive file will need to be created.',
                        'duplicator-pro'
                    );
                    ?>
            </div>
        </div>
    </div>

    <?php if (!$encryptAvaliable) { ?>
        <div class="dup-form-item">
            <span class="title">
                &nbsp;
            </span>
            <span class="input dup-tabs-opts-notice">
                <i>
                <i class="fas fa-exclamation-triangle fa-xs"></i>
                <?php
                    echo esc_html__("The security mode 'Archive encryption' option above is currently disabled on this server.", 'duplicator-pro') . '<br>'
                        . $unavaliableMessage;
                ?>
                </i>
            </span>
        </div>
    <?php } ?>

</div>

<script>
    jQuery(function($) {
        DupPro.EnableInstallerPassword = function () {
            let $button = $('#secure-btn');
            let secureOnVal = $('.secure-on-input-wrapper input:checked').val();

            if (secureOnVal == <?php echo json_encode(ArchiveConfig::SECURE_MODE_NONE); ?>) {
                $('#secure-pass').removeAttr('required');
                $('#secure-pass').attr('readonly', true);
                $('#dpro-install-secure-lock').hide();
                $button.prop('disabled', true);
            } else {
                $('#secure-pass').attr('readonly', false);
                $('#secure-pass').attr('required', 'true').focus();
                $('#dpro-install-secure-lock').show();
                $button.prop('disabled', false); 
            }
        };

        $('#secure-on-none').parsley().on('field:error', function() {
            $('.archive-setup-tab button').trigger('click');
            $('html,body').animate({scrollTop: $(".archive-setup-tab").offset().top - 30},'slow');
        });
    });
</script>
