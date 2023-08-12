<?php

/**
 * Duplicator package row in table packages list
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Core\Views\TplMng;
use Duplicator\Package\Recovery\RecoveryStatus;

defined("ABSPATH") or die("");

/**
 * Variables
 *
 * @var ControllersManager $ctrlMng
 * @var TplMng $tplMng
 * @var array<string, mixed> $tplData
 * @var RecoveryStatus $recoverStatus
 */

$recoverStatus = $tplData['recoverStatus'];
$filteredData  = $recoverStatus->getFilteredData();

$activeType  = $recoverStatus->getType();
$activeLabel = strtolower($recoverStatus->getTypeLabel());

$isLocalStorageSafe  = $recoverStatus->isLocalStorageEnabled();
$isWordPressCoreSafe = $recoverStatus->isWordPressCoreComplete();
$isDatabaseSafe      = $recoverStatus->isDatabaseComplete();

//echo '<pre>';var_export($isWordPressCoreSafe); echo '</pre>';
//echo '<pre>';var_export($recoverStatus->activeTemplate); echo '</pre>';

/**
 * @var wpdb $wpdb
*/
global $wpdb;
?>
<div class="dup-recover-dlg-notice-box">

    <!-- =================
    OVERVIEW -->
    <div class="title-area">
        <div class="title">
            <?php _e("OVERVIEW", 'duplicator-pro'); ?>:
        </div>
    </div>

    <div class="ovr-info">
        <b><?php
            echo sprintf(
                '%s %s %s',
                __('This', 'duplicator-pro'),
                $activeLabel,
                __('is not available as a rapid recovery point', 'duplicator-pro')
            );
            ?>.
        </b>&nbsp;
        <a href="javascript:void(0)" onclick="jQuery('.dup-recover-dlg-notice-box .ovr-info-sub').toggle();" class="whatsthis">
             <?php _e("more info...", 'duplicator-pro'); ?>
        </a>

        <div class="ovr-info-sub">
            <?php
                 echo sprintf(
                     '%s <a href="https://snapcreek.com/duplicator/docs/quick-start/#install_site" target="_blank">%s</a> %s',
                     __(
                         "The recovery point is a streamlined recovery system used to rapidly restore your site from a disaster.  A functioning WordPress "
                         . "backend is not required, only the URL provided with the recovery point is needed to restore the package. Packages that are not "
                         . "recovery point eligible will need to use the  ",
                         'duplicator-pro'
                     ),
                     __('standard install modes', 'duplicator-pro'),
                     __('for re-deployment which include Classic, Overwrite and Import modes.', 'duplicator-pro')
                 );

                 echo sprintf(
                     '<br/><br/> <i>%s <i class="far fa-check-circle fa-fw pass" style="font-size:13px"></i> %s</i>',
                     __('A recovery point should meet the requirements listed below with a ', 'duplicator-pro'),
                     __('checkmark. Clicking each link provides additional details.  ', 'duplicator-pro')
                 );
                    ?>
        </div>
    </div>

    <div class="title-area">
        <div class="title">
            <?php _e("REQUIREMENTS", 'duplicator-pro'); ?>:
        </div>
    </div>

    <!--  ===============
    LOCAL SERVER STORAGE -->
    <div class="req-data">
        <?php
        if ($activeType == $recoverStatus::TYPE_TEMPLATE) {
            echo '<i class="far fa-question-circle fa-fw pass"></i>';
        } else {
            echo $isLocalStorageSafe
                ? '<i class="far fa-check-circle fa-fw pass"></i>'
                : '<i class="far fa-times-circle fa-fw fail"></i>';
        }
        ?>
        <a class="req-title" href="javascript:void(0)" onclick="jQuery(this).parent().children('div.req-info').toggle();">
            <?php _e("Local Server Storage", 'duplicator-pro'); ?>
        </a>
        
        <div class="req-info">

            <i class="fas fa-server  fa-fw fa-lg"></i>
            <?php _e("Recovery points require one of the following 'Local Server' storage types:", 'duplicator-pro'); ?>
            <ul class="req-info-list">
                <li>
                    <?php
                        $url = '?page=duplicator-pro-storage&tab=storage&inner_page=edit-default';
                        echo sprintf(
                            "<i class='far fa-hdd fa-fw'></i><sup><i class='fas fa-undo-alt fa-xs'></i></sup>&nbsp; "
                            . "<b><a href='${url}' target='_blank'>%s</a></b> %s",
                            __('[Local Default]', 'duplicator-pro'),
                            __('This is the default built-in local storage type.', 'duplicator-pro')
                        );
                        ?>
                </li>
                <li>
                    <?php
                        echo sprintf(
                            "<i class='fas fa-hdd'></i><sup><i class='fas fa-undo-alt fa-xs'></i></sup>&nbsp; <b>%s</b> %s",
                            __('[Local Non-Default]', 'duplicator-pro'),
                            __('This is a custom directory on this server.', 'duplicator-pro')
                        );
                        ?>
                </li>
            </ul>

            <div class="req-status">
                <b><?php _e("STATUS", 'duplicator-pro'); ?>:</b><br/>
                <?php
                if ($activeType == $recoverStatus::TYPE_TEMPLATE) {
                    _e("Templates do not control storage locations, only schedules and new package creation control this process. "
                    . "No changes can be made to affect this test.", 'duplicator-pro');
                } elseif ($isLocalStorageSafe) {
                    echo '<span class="darkgreen">';
                    echo __("At least one local server storage is associated with this ", 'duplicator-pro') . $activeLabel . '.';
                    echo '</span>';
                } else {
                    echo '<span class="maroon">';
                    echo __("No local server storage found for this ", 'duplicator-pro') . $activeLabel . '.';
                    echo '</span>';
                }
                ?>
            </div>
       </div>
    </div>

    <!--  ===============
    WORDPRESS CORE -->
    <div class="req-data">
        <?php
           echo $isWordPressCoreSafe
                ? '<i class="far fa-check-circle fa-fw pass"></i>'
                : '<i class="far fa-times-circle fa-fw fail"></i>';
        ?>
        <a class="req-title" href="javascript:void(0)" onclick="jQuery(this).parent().children('div.req-info').toggle();">
            <?php _e("WordPress Core Folders", 'duplicator-pro'); ?>
        </a>
        <div class="req-info">
             <i class="fab fa-wordpress-simple fa-fw fa-lg"></i>
             <?php _e(
                 "A recovery point needs all WordPress core folders included in the package (wp-admin, wp-content &amp; wp-includes).",
                 'duplicator-pro'
             ); ?>

             <div class="req-status">
                <b><?php _e("STATUS", 'duplicator-pro'); ?>:</b><br/>
                <?php if (($filteredData['dbonly'])) : ?>
                    <span class="maroon">
                        <?php _e("Package is setup as a database only configuration, the core WordPress folders have been excluded "
                            . "automatically.", 'duplicator-pro'); ?>
                    </span>
                <?php elseif (count($filteredData['filterDirs']) > 0) : ?>
                    <span class="maroon">
                        <?php _e("WordPress core folders being filtered.", 'duplicator-pro'); ?>
                        <?php foreach ($filteredData['filterDirs'] as $path) { ?>
                            <small class="req-paths-data"><?php echo esc_html($path); ?></small>
                        <?php } ?>
                    </span>
                <?php else : ?>
                    <span class="darkgreen">
                        <?php _e("No WordPress core folder filters set", 'duplicator-pro'); ?>.
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!--  ===============
    DATABASE TABLES -->
    <div class="req-data">
       <?php
          echo $isDatabaseSafe
               ? '<i class="far fa-check-circle fa-fw pass"></i>'
               : '<i class="far fa-times-circle fa-fw fail"></i>';
        ?>
       <a class="req-title" href="javascript:void(0)" onclick="jQuery(this).parent().children('div.req-info').toggle();">
           <?php _e("Primary Database Tables", 'duplicator-pro'); ?>
       </a>
       <div class="req-info">
           <i class="fas fa-database fa-fw fa-lg"></i>
            <?php
                echo sprintf(
                    "%s <i>'%s'</i> %s %s %s",
                    __('All database tables with the prefix', 'duplicator-pro'),
                    $wpdb->prefix,
                    __('must be included in the', 'duplicator-pro'),
                    $activeLabel,
                    __('for this to be an eligible recovery point.', 'duplicator-pro')
                );
                ?>
            <div class="req-status">
                <b><?php _e("STATUS", 'duplicator-pro'); ?>:</b><br/>
                <?php if (count($filteredData['filterTables']) > 0) : ?>
                    <?php _e("Filtered table list", 'duplicator-pro'); ?>:
                    <?php
                    foreach ($filteredData['filterTables'] as $table) {
                        if (strpos($table, $wpdb->prefix) !== false) {
                               echo "<small class='req-paths-data maroon'>{$table}</small>";
                        } else {
                                echo "<small class='req-paths-data darkgreen'>{$table}</small>";
                        }
                    }
                    ?>
                <?php else : ?>
                    <span class="darkgreen">
                        <?php _e("No table filters set on this package.", 'duplicator-pro'); ?>
                    </span>
                <?php endif; ?>
            </div>
       </div>
    </div><br/>


    <div class="title-area">
        <div class="title">
            <?php _e("NOTES", 'duplicator-pro'); ?>:
        </div>
    </div>

    <div class="req-notes">
        <a href="javascript:void(0)" onclick="jQuery(this).parent().find('.req-notes-sub').slideDown(); jQuery(this).hide();">
            <?php _e("Show Details...", 'duplicator-pro'); ?>
        </a>
        <div class="req-notes-sub">
        <?php
        switch ($recoverStatus->getType()) {
            case $recoverStatus::TYPE_PACKAGE:
                printf(
                    _x(
                        'To create a recovery-point enabled package change the conditions of the package build or template to meet the requirements listed '
                        . 'above. Then use either the %1$sRecovery Point%2$s tool or the Recovery Point button to set which package you would like as '
                        . 'the active recovery-point.',
                        '%1$s and %2$s represents the opening and closing HTML tags for an anchor or link',
                        'duplicator-pro'
                    ),
                    '<a href="' . esc_url(DUP_PRO_CTRL_recovery::getRecoverPageLink()) . '" target="_blank" >',
                    '</a>'
                );
                break;

            case $recoverStatus::TYPE_SCHEDULE:
                 _e(
                     'To change the recovery status visit the template link above and make sure that it passes the recovery status test.  If the local '
                     . 'storage test does not pass check the schedule storage types and make sure the local server storage type is selected. These steps '
                     . 'are optional and only required if you want to enable this schedule as an active recovery point.',
                     'duplicator-pro'
                 );
                break;

            case $recoverStatus::TYPE_TEMPLATE:
                _e('To change a template recovery point status to enabled, edit the template and make sure that it passes the recovery '
                    . 'status test.', 'duplicator-pro');
                break;
        }
        ?>
        </div>
    </div>
</div>
