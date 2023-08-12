<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

defined("ABSPATH") or die("");

use Duplicator\Libs\Snap\SnapWP;

/**
 * Variables
 *
 * @var \Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */

$postTypeCount = SnapWP::getPostTypesCount();
if (
    ($recoverPackage = DUP_PRO_Package_Recover::getRecoverPackage()) == false ||
    $recoverPackage->isOutToDate()
) {
    $badgeClass =  'badge-warn';
    $badgeLabel = 'Notice';
} else {
    $badgeClass =  'badge-pass';
    $badgeLabel = 'Good';
}
?>
<div class="dup-pro-import-header" >
    <h2 class="title">
        <i class="fas fa-arrow-alt-circle-down"></i> <?php printf(DUP_PRO_U::esc_html__("Step %s of 2: Confirmation"), '<span class="red">2</span>'); ?>
    </h2>
    <hr />
</div>
<div class="dup-pro-recovery-details-max-width-wrapper" >
    <div class="dup-pro-import-box closable opened" >
        <div class="box-title" >
            <?php DUP_PRO_U::_e('Recovery Point'); ?>
            <div class="badge <?php echo $badgeClass; ?> margin-right-1">
                <?php echo $badgeLabel; ?>
            </div>
        </div>
        <div class="box-content">
            <div  id="dup-pro-recovery-details-select-entry" class="dup-pro-recovery-info-set" >
                <?php
                DUP_PRO_CTRL_recovery::renderRecoveryWidged(array(
                    'selector'   => true,
                    'subtitle'   => '',
                    'copyLink'   => true,
                    'copyButton' => true,
                    'launch'     => false,
                    'download'   => true,
                    'info'       => true
                ));
                ?>
            </div>
            <hr>

            <div class="dup-pro-recovery-not-required">
                <i class="far fa-arrow-alt-circle-right"></i>
                <?php
                DUP_PRO_U::_e(
                    'The Recovery Point is not mandatory to perform an import. ' .
                    'However, it can assist in restoring this site if there is a problem during install. ' .
                    'If you have no need to recover this site then you can continue without creating the Recovery Point.'
                );
                ?>
            </div>

        </div>
    </div><br/>

    <div class="dup-pro-import-box closable opened" >
        <div class="box-title" >
            <?php DUP_PRO_U::_e('System Overview'); ?>
        </div>
        <div class="box-content">
            <div id="dup-pro-recovery-details-overview" >
                <div>
                    <?php DUP_PRO_U::esc_html_e("This site currently contains"); ?>:
                </div>

                <table class="margin-left-2" >
                    <?php foreach ($postTypeCount as $label => $count) { ?>
                        <tr>
                            <td><?php echo esc_html($label); ?></td>
                            <td class="text-right"><?php echo $count; ?></td>
                        </tr>
                    <?php } ?>
                </table>
                <p>
                    <?php DUP_PRO_U::esc_html_e("This process will:") ?>
                </p>
                <ul>
                    <li>
                        <i class="far fa-check-circle"></i>&nbsp;
                        <?php DUP_PRO_U::esc_html_e("Launch the interactive installer wizard to install this new package."); ?>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="dup-pro-import-confirm-buttons">
        <input 
            id="dup-pro-import-launch-installer-cancel" 
            type="button" class="button button-large recovery-reset" 
            value="<?php _e('Cancel', 'duplicator-pro'); ?>"
        >&nbsp;
        <button 
            id="dup-pro-import-launch-installer-confirm" 
            type="button" 
            class="button button-primary button-large" 
            onclick="DupPro.ImportManager.confirmLaunchInstaller();"
        >
            <i class="fa fa-bolt fa-sm"></i> <?php _e('Launch Installer', 'duplicator-pro'); ?>
        </button>
    </div>
</div>
