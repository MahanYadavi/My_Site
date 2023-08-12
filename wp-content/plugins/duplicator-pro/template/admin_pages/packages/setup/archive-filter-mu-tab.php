<?php

/**
 * @package Duplicator
 */

use Duplicator\Addons\ProBase\License\License;

defined("ABSPATH") or die("");

/**
 * Variables
 *
 * @var \Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */

if (!is_multisite()) {
    return;
}
?>
<div class="filter-mu-tab-content <?php echo (License::can(License::CAPABILITY_MULTISITE_PLUS) ? '' : 'disabled');?>" >
    <div style="max-width:900px">
        <?php if (!License::can(License::CAPABILITY_MULTISITE_PLUS)) { ?>
            <div class="dpro-panel-optional-txt alert-disabled" style="text-align: center">
            <b><?php esc_html_e("Notice:", 'duplicator-pro'); ?></b>
                <?php
                    printf(
                        esc_html__(
                            'This option isn\'t available at the %1$s license level.',
                            'duplicator-pro'
                        ),
                        License::getLicenseToString()
                    );
                ?>
                <br>
                <?php
                    printf(
                        _x(
                            'To enable this option %1$supgrade%2$s the License.',
                            '%1$s and %2$s represents the opening and closing HTML tags for an anchor or link',
                            'duplicator-pro'
                        ),
                        '<a href="' . esc_url(License::getUpsellURL()) . '" target="_blank">',
                        '</a>'
                    );
                ?>
            </div>
        <?php } ?>
        <?php
        echo '<b>' . DUP_PRO_U::esc_html__("Overview:") . '</b><br/>';
        DUP_PRO_U::esc_html_e(
            "When you want to move a full multisite network or convert a subsite to a standalone site just "
            . "create a standard package like you would with a single site. "
            . "Then browse to the installer and choose either 'Restore entire multisite network'  or 'Convert subsite into a standalone site'.  "
            . "These options will be present on Step 1 of the installer when restoring a Multisite package."
        );
        ?>
    </div>
    <table class="mu-opts">
        <tr>
            <td>
                <b><?php DUP_PRO_U::esc_html_e("Included Sub-Sites"); ?>:</b><br/>
                <select name="mu-include[]" id="mu-include" multiple="true" class="mu-selector">
                    <?php
                    $subsites = License::can(License::CAPABILITY_MULTISITE_PLUS) ? DUP_PRO_MU::getSubsites() : array();
                    foreach ($subsites as $site) {
                        echo "<option value='" . esc_attr($site->id) . "'>" . esc_html($site->domain . $site->path) . "</option>";
                    }
                    ?>
                </select>
            </td>
            <td>
                <button type="button" id="mu-exclude-btn" class="mu-push-btn"><i class="fa fa-chevron-right"></i></button>
                <br/>
                <button type="button" id="mu-include-btn" class="mu-push-btn"><i class="fa fa-chevron-left"></i></button>
            </td>
            <td>
                <b><?php DUP_PRO_U::esc_html_e("Excluded Sub-Sites"); ?>:</b><br/>
                <select name="mu-exclude[]" id="mu-exclude" multiple="true" class="mu-selector"></select>
            </td>
        </tr>
    </table>

    <div class="dpro-panel-optional-txt" style="text-align: left">
        <?php
            echo wp_kses(
                DUP_PRO_U::__(
                    "<u><b>Important:</b></u> Full network restoration is an installer option only if you include <b>all</b> subsites."
                    . " If any subsites are filtered then you may only restore individual subsites as standalones sites at install-time."
                ),
                array(
                    'b' => array(),
                    'u' => array(),
                )
            );
            ?>
        <br/>
        <br/>
        <?php DUP_PRO_U::esc_html_e(
            "This section allows you to control which sub-sites of a multisite network you want to include within your package. " .
            "The 'Included Sub-Sites' will also be available to choose from at install time."
        ); ?> <br/>
        <?php DUP_PRO_U::esc_html_e(
            "By default all packages are included. "
            . "The ability to exclude sub-sites are intended to help shrink your package if needed."
        ); ?>
    </div>
</div>
