<?php

/**
 * Duplicator beta feathures
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

defined("ABSPATH") || exit;

/**
 * Variables
 *
 * @var \Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */
?>
<h3 class="title">
    <?php _e("Beta Features", 'duplicator-pro'); ?>
</h3>
<pr>
    To use the below features you must first enable them.
    These features are at <span style="font-style:italic">beta</span> status
    - they have worked well in internal testing but are not considered <span style="font-style:italic">release ready</span> at this time.
    Because of this, ensure you create a site backup or Recovery Point before using.
</pr>
<hr size="1">
<form id="dup-settings-form" action="<?php echo Duplicator\Core\Controllers\ControllersManager::getCurrentLink(); ?>" method="post">
    <table class="form-table" role="presentation">
        <?php
        /* Leave this as an example for the future
        $tplMng->render(
            'parts/settings_fields/checkbox',
            array(
                'fieldLabel'         => __('MU Subsite Import', 'duplicator-pro'),
                'fieldName'          => Duplicator\Controllers\SettingsPageController::FIELD_MU_BETA_NAME,
                'fieldChecked'       => DUP_PRO_Global_Entity::getInstance()->betaMUimport,
                'fieldCheckboxLabel' => __('Enable', 'duplicator-pro'),
                'fieldDescription'   => __(
                    'Allow importing of subsites into existing multisite.' .
                        '<br/><span style="font-style: italic">Only available to Business and Gold licenses</span>',
                    'duplicator-pro'
                )
            )
        );
        */
        ?>
    </table>
    <?php
    $tplData['actions']['save']->getActionNonceFileds();
    submit_button(__('Save Beta Settings', 'duplicator-pro'));
    ?>
</form>
