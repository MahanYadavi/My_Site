<?php

/**
 * Duplicator package row in table packages list
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

 /**
  * Variables
  *
  * @var \Duplicator\Core\Views\TplMng  $tplMng
  * @var array<string, mixed> $tplData
  */

defined("ABSPATH") or die("");
$lang_wppathinfo = __('This site\'s root path is:', 'duplicator-pro') . '<br/><i>' . duplicator_pro_get_home_path() . '</i>';
?>

<div class="dup-dlg-links-subtxt">
    <?php esc_html_e("Learn how Duplicator works in just a few minutes...", 'duplicator-pro'); ?>
</div>

<div id="dup-ovr-hlp-tabs" class="dup-tabs-flat">
<div class="data-tabs">
     <a href="javascript:void(0)" class="tab active"><i class="fas fa-archive fa-fw"></i>  <?php _e('Create Backups', 'duplicator-pro'); ?></a>
     <a href="javascript:void(0)" class="tab"><i class="fas fa-bolt fa-fw"></i> <?php _e('Install Backups', 'duplicator-pro'); ?></a>
</div>

 <!-- =================
 TAB1: OVERVIEW HELP -->
 <div class="data-panels">
     <div class="panel">

         <div id="dup-link-spinner-1" class="dup-spinner">
             <div class="area-left">
                 <i class="fas fa-chevron-circle-left area-arrow"></i>
             </div>
             <!-- DATA -->
             <div class="area-data">

                <!-- =====================
                SPIN-1: INTRO  -->
                <div class="item active dup-spin-hlp">
                   <h3>
                       <i class="fab fa-wordpress-simple" style="font-weight: normal"></i>
                       <?php _e('Create Backups', 'duplicator-pro'); ?>
                   </h3>
                   <div class="sub-head">
                       <?php _e('Backups are the heart of Duplicator, and driven by these points!', 'duplicator-pro'); ?>
                   </div>

                   <ol>
                       <li>
                           <?php echo sprintf(
                               _x('In Duplicator Backups refer to %1$s Packages', 'Archive icon', 'duplicator-pro'),
                               '<i class="fas fa-archive fa-fw fa-sm"></i>'
                           ); ?>.
                       </li>
                       <li><?php _e('Packages can be manually created or scheduled', 'duplicator-pro'); ?>.</li>
                       <li><?php _e('Packages contain two files: <i>The Archive &amp; Installer</i>', 'duplicator-pro'); ?>.</li>
                   </ol>
                </div>

                <!-- =====================
                SPIN-2: PACKAGE OVERVIEW  -->
                <div class="item dup-spin-hlp">
                     <h3>
                        <i class="fas fa-archive fa-fw"></i>
                        <?php _e('Package Overview', 'duplicator-pro'); ?>
                     </h3>
                      <div class="sub-head">
                         <?php _e('A package is a customizable backup of your site with these two files:', 'duplicator-pro'); ?>
                     </div>

                     <div class="title">
                         <i class="far fa-file-archive fa-fw"></i> <?php _e('Archive File', 'duplicator-pro'); ?>
                         <i class="fas fa-question-circle"
                             data-tooltip-title="<?php _e("Archive File", 'duplicator-pro'); ?>"
                             data-tooltip="<?php _e('Archive files can be created in either .zip or .daf file formats. The Duplicator archive format (daf) '
                                 . 'is a custom format designed '
                                . 'for large sites on budget hosts', 'duplicator-pro'); ?>">
                         </i>
                     </div>
                     <?php _e(' The <i>archive.zip/daf</i> file contains your WordPress files and database', 'duplicator-pro'); ?>.
                      <br/><br/>

                     <div class="title">
                         <i class="fas fa-bolt fa-fw"></i> <?php _e('Installer File', 'duplicator-pro'); ?>
                         <i class="fas fa-question-circle"
                             data-tooltip-title="<?php _e("Archive Installer", 'duplicator-pro'); ?>"
                             data-tooltip="<?php _e('In case you lose this file an exact copy of this file is also stored inside the archive '
                                . 'named installer-backup.php', 'duplicator-pro'); ?>"></i>
                     </div>
                     <?php _e('The <i>installer.php</i> file helps to deploy the contents of the archive file', 'duplicator-pro'); ?>.
                 </div>

                 <!-- =====================
                 SPIN-3: ARCHIVE FILE -->
                 <div class="item dup-spin-hlp">
                     <h3><i class="far fa-file-archive fa-fw"></i> <?php _e('Package: Archive File', 'duplicator-pro'); ?></h3>
                      <div class="sub-head">
                         <?php _e('An archive.zip/daf file contains your WordPress site with the following assets:', 'duplicator-pro'); ?>
                     </div>

                     <div class="title">
                         <i class="fas fa-folder-open fa-fw"></i> <?php _e('Site Files', 'duplicator-pro'); ?>
                         <i class="fas fa-question-circle"
                             data-tooltip-title="<?php _e("WordPress Site Info", 'duplicator-pro'); ?>"
                             data-tooltip="<?php echo $lang_wppathinfo ?>"></i>
                     </div>
                     <?php _e(
                         'All site files including the WordPress core files, plugins, themes and files starting at the WordPress root folder.',
                         'duplicator-pro'
                     ); ?>
                     <br/><br/>

                     <div class="title">
                         <i class="fas fa-database fa-fw"></i> <?php _e('Database', 'duplicator-pro'); ?>
                     </div>
                     <?php _e('The database is stored in a single SQL file named database.sql.', 'duplicator-pro'); ?>
                     <br/><br/>
                     <small class="grey">
                           <?php _e('By default all files/database tables are included unless filters are set.', 'duplicator-pro'); ?>
                     </small>
                 </div>

                 <!-- =====================
                 SPIN-4: INSTALLER FILE -->
                 <div class="item dup-spin-hlp">
                     <h3><i class="fas fa-bolt fa-fw"></i> <?php _e('Package: Installer File', 'duplicator-pro'); ?></h3>
                     <div class="sub-head">
                         <?php _e('The installer.php is a PHP script that does the following:', 'duplicator-pro'); ?>
                     </div>

                     <div class="title">
                         <i class="fas fa-file-export fa-fw"></i> <?php _e('Extracts Archive', 'duplicator-pro'); ?>
                     </div>
                     <?php _e('Helps to restores your WordPress files at a location of your choice.', 'duplicator-pro'); ?>
                     <br/><br/>

                     <div class="title">
                         <i class="fas fa-database fa-fw"></i> <?php _e('Installs Database ', 'duplicator-pro'); ?>
                     </div>

                     <?php _e('Restores database and properly updates all URL/paths.', 'duplicator-pro'); ?>
                     <br/><br/>

                     <small class="grey">
                           <?php _e('The installer file is only used for classic &amp; overwrite standard install modes', 'duplicator-pro'); ?>.
                     </small>
                 </div>

                 <!-- =====================
                 SPIN-5: INSTALLER SECURE -->
                 <div class="item dup-spin-hlp">
                     <h3><i class="fas fa-bolt fa-fw"></i> <?php _e('Secure Installer', 'duplicator-pro'); ?></h3>
                     <div class="sub-head">
                         <?php _e('A secure installer keeps the location of your install process hidden.', 'duplicator-pro'); ?>
                     </div>

                     <ol>
                         <li>
                             <b><i class="fas fa-lock-open fa-fw"></i> <?php _e('Unsecured', 'duplicator-pro'); ?></b><br/>
                              <?php _e('An unsecured installer is named "installer.php".  This mode should only '
                                  . 'be used when outside users cannot access your server.', 'duplicator-pro'); ?>
                         </li>
                         <li>
                              <b><i class="fas fa-lock fa-fw"></i> <?php _e('Secured', 'duplicator-pro'); ?></b><br/>
                              <?php _e('A secure installer is named "[name]_[hash]_[date]_installer.php" and is only known by you. '
                                  . 'This keeps it safe from outside threats.', 'duplicator-pro'); ?>
                         </li>
                     </ol>
                     <br/><br/>

                      <div class="dup-ovr-continue">
                          <a href="javascript:void(0)" id="dup-ovr-next-exe">
                              <?php _e('Install Backups', 'duplicator-pro'); ?>
                              <i class="fas fa-chevron-circle-right"></i>
                          </a>
                      </div>
                 </div>
             </div>

             <div class="area-right">
                 <i class="fas fa-chevron-circle-right"></i>
             </div>

            <!-- Progress -->
             <div class="area-nav">
                 <span class="num"></span>
                 <progress class="progress"></progress>
             </div>
         </div>
     </div>


     <!-- ******************************************************
     TAB-2: INSTALLER RESOURCES -->
     <div class="panel" data-panel="2">
         <div id="dup-link-spinner-2" class="dup-spinner">

             <div class="area-left">
                 <i class="fas fa-chevron-circle-left area-arrow"></i>
             </div>

             <!-- Data -->
             <div class="area-data">
                 <!-- =====================
                 SPIN-1:  INTRO -->
                 <div class="item dup-spin-hlp active">
                     <h3>
                        <i class="fab fa-wordpress-simple" style="font-weight: normal"></i>
                        <?php _e('Install Backups', 'duplicator-pro'); ?>
                     </h3>
                     <div class="sub-head">
                         <?php _e('With Duplicator there are several ways to restore/install a backup', 'duplicator-pro'); ?>
                     </div>

                     <ol>
                         <li><?php _e('Install modes consist of two groups <i>Standard &amp; Custom</i>', 'duplicator-pro'); ?></li>
                         <li><?php _e('<b>Standard Modes include: </b>  <i>Import, Overwrite &amp; Classic</i>', 'duplicator-pro'); ?></li>
                         <li><?php _e('<b>Custom Modes include: </b>  <i>Recovery, Database, Two-Part</i>', 'duplicator-pro'); ?></li>
                     </ol>
                 </div>

                 <!-- =====================
                 SPIN-2:  STANDARD INSTALL MODES -->
                 <div class="item dup-spin-hlp">
                     <h3>
                         <i class="fas fa-bolt fa-fw"></i>
                         <?php _e('Standard Install Modes', 'duplicator-pro'); ?>
                     </h3>
                     <div class="sub-head">
                         <?php _e('Standard install modes are the most popular and support these 3 modes.', 'duplicator-pro'); ?>
                     </div>

                     <div class="title">
                        <i class="fas fa-arrow-alt-circle-down fa-fw"></i>&nbsp;
                        <a href="https://snapcreek.com/duplicator/docs/quick-start/#quick-045-q" target="_blank">
                            <b><?php _e('Import Install', 'duplicator-pro'); ?></b>
                        </a>
                     </div>
                     <?php _e('Drag-n-drop or link an archive file to any destination WordPress site', 'duplicator-pro'); ?>.
                     <br/><br/>

                     <div class="title">
                         <i class="far fa-window-close fa-fw"></i>&nbsp;
                         <a href="https://snapcreek.com/duplicator/docs/quick-start/#quick-043-q" target="_blank">
                             <b><?php _e('Overwrite Install', 'duplicator-pro'); ?></b>
                         </a>
                     </div>
                     <?php _e('Quickly overwrite an existing WordPress site in a few clicks', 'duplicator-pro'); ?>.
                     <br/><br/>

                     <div class="title">
                         <i class="far fa-save fa-fw"></i>&nbsp;
                         <a href="https://snapcreek.com/duplicator/docs/quick-start/#quick-040-q" target="_blank">
                             <b><?php _e('Classic Install', 'duplicator-pro'); ?></b>
                         </a>
                     </div>
                     <?php _e('Install to an empty server directory like a new WordPress install does', 'duplicator-pro'); ?>.<br/>
                 </div>


                 <!-- =====================
                 SPIN-3: STANDARD: IMPORT INSTALL -->
                 <div class="item dup-spin-hlp">
                     <h3>
                         <i class="fas fa-arrow-alt-circle-down"></i>
                         <?php _e('Standard: Import Install', 'duplicator-pro'); ?>
                     </h3>
                     <div class="sub-head">
                         <?php _e('Quick steps to import archive into an existing WordPress site and overwrite it.', 'duplicator-pro'); ?>
                     </div>

                     <div id="dup-ovr-hlp-vert-tabs-1" class="dup-tabs-vert">
                         <div class="data-tabs">
                             <div class="void"><i class="fab fa-wordpress-simple"></i>   <?php _e('Source Site', 'duplicator-pro'); ?></div>
                             <div class="tab active">1. <?php _e('Create Package', 'duplicator-pro'); ?></div>
                             <div class="tab">2. <?php _e('Choose Import', 'duplicator-pro'); ?></div>

                             <div class="void"><i class="fab fa-wordpress-simple"></i> <?php _e('Destination Site', 'duplicator-pro'); ?></div>
                             <div class="tab">3. <?php _e('Check WordPress', 'duplicator-pro'); ?></div>
                             <div class="tab">4. <?php _e('Import Archive', 'duplicator-pro'); ?></div>
                         </div>
                         <div class="data-panels dup-tabvert-hlp">
                             <div class="panel">
                                 <div class="title">
                                     <i class="fas fa-archive fa-fw"></i> <?php _e('Create a Package', 'duplicator-pro'); ?><br/>
                                     <small><?php _e('Pro ❯ Packages ❯ Create New', 'duplicator-pro'); ?></small>
                                 </div>
                                 <?php _e('On any WordPress site with Duplicator create a package', 'duplicator-pro'); ?>.
                             </div>

                             <div class="panel">
                                 <div class="title">
                                     <i class="fas fa-link fa-fw"></i> <?php _e('Choose An Import Method', 'duplicator-pro'); ?> <br/>
                                      <small><?php _e('Pro ❯ Packages ❯ Package Overview', 'duplicator-pro'); ?></small>
                                 </div>
                                 <b><?php _e('URL Import', 'duplicator-pro'); ?></b> <br/>
                                 <?php
                                     _e('Use', 'duplicator-pro');
                                     echo ' <i><i class="far fa-copy fa-xs"></i> ' . __('Copy Link', 'duplicator-pro') . '</i>&nbsp;';
                                     _e('to run import link install', 'duplicator-pro');
                                    ?>.
                                 <br/><br/>

                                 <b><?php _e('File Import', 'duplicator-pro'); ?></b> <br/>
                                 <?php
                                     _e('Use', 'duplicator-pro');
                                     echo ' <i><i class="fas fa-download fa-xs"></i> ' . __('Download', 'duplicator-pro') . '</i>&nbsp;';
                                     _e('to run import file install', 'duplicator-pro');
                                    ?>.
                             </div>

                             <div class="panel">
                                 <div class="title">
                                     <?php _e('Install WordPress', 'duplicator-pro'); ?>
                                 </div>
                                 <?php _e('Install WordPress if not already installed', 'duplicator-pro'); ?>.<br/>
                              <small>
                                  <?php _e('Most Hosting platforms have a one click WordPress install, this will be the quickest method to get '
                                      . 'WordPress on your host or have you host do it for you', 'duplicator-pro'); ?>&nbsp;.
                              </small>
                             </div>

                             <div class="panel">
                                 <div class="title">
                                     <?php _e('Import Archive ', 'duplicator-pro'); ?><br/>
                                     <small><?php _e('Pro ❯ Import ', 'duplicator-pro'); ?></small>
                                 </div>

                                  <b><?php _e('URL Import', 'duplicator-pro'); ?></b> <br/>
                                  <i><i class="far fa-copy fa-xs"></i> <?php _e('Paste Link', 'duplicator-pro'); ?></i> 
                                  <?php _e('from source site', 'duplicator-pro'); ?>.
                                  <br/><br/>

                                  <b><?php _e('File Import', 'duplicator-pro'); ?></b> <br/>
                                  <i><i class="fas fa-download fa-xs"></i> <?php _e('Drag-n-drop', 'duplicator-pro'); ?></i> 
                                  <?php _e('archive file from source site', 'duplicator-pro'); ?>.
                             </div>
                         </div>
                    </div>
                 </div>

                 <!-- =====================
                 SPIN-4:  OVERWRITE INSTALL -->
                 <div class="item dup-spin-hlp">
                     <h3>
                         <i class="far fa-window-close fa-fw"></i>
                         <?php _e('Standard: Overwrite Install', 'duplicator-pro'); ?>
                     </h3>
                    <div class="sub-head">
                         <?php _e('Quick steps to overwrite an existing WordPress site.', 'duplicator-pro'); ?>
                    </div>
                    <ol>
                        <li>
                            <b><?php _e('Create package', 'duplicator-pro'); ?>:</b> <?php _e(' Create package on source site', 'duplicator-pro'); ?>.
                        </li>
                        <li>
                            <b><?php _e('Transfer package', 'duplicator-pro'); ?>:</b>
                            <?php _e('Use FTP or cPanel to copy installer &amp; archive to destination WordPress site folder', 'duplicator-pro'); ?>.
                        </li>
                        <li>
                            <b><?php _e('Run Installer', 'duplicator-pro'); ?>:</b>
                            <?php _e('On destination server browse to URL of installer.php and follow install steps to overwrite site', 'duplicator-pro'); ?>.
                        </li>
                    </ol>
                     
                     <small>
                         <?php _e('Overwrite mode uses the existing wp-config.php to pre-fill database settings ', 'duplicator-pro'); ?>.
                     </small>
                 </div>

                <!-- =====================
                SPIN-5: CLASSIC INSTALL -->
                <div class="item dup-spin-hlp">
                    <h3>
                       <i class="far fa-save fa-fw"></i>
                       <?php _e('Standard: Classic Install', 'duplicator-pro'); ?>
                    </h3>
                    <div class="sub-head">
                        <?php _e('Quick steps to install a WordPress site.', 'duplicator-pro'); ?>
                    </div>
                    <ol>
                        <li>
                            <b><?php _e('Create package', 'duplicator-pro'); ?>:</b> <?php _e(' Create package on source site', 'duplicator-pro'); ?>.
                        </li>
                        <li>
                            <b><?php _e('Transfer package', 'duplicator-pro'); ?>:</b>
                            <?php _e(
                                'Use FTP, cPanel or host utilities to copy installer &amp; archive to <u>empty directory</u> on destination server.',
                                'duplicator-pro'
                            ); ?>
                        </li>
                        <li>
                            <b><?php _e('Run Installer', 'duplicator-pro'); ?>:</b>
                            <?php _e(
                                'On destination server browse to URL of installer.php and follow the install steps',
                                'duplicator-pro'
                            ); ?>.
                        </li>
                    </ol>
                    <small>
                         <?php _e('Classic install works simular to installing a brand new WordPress site', 'duplicator-pro'); ?>.
                    </small>
                </div>


                 <!-- =====================
                 SPIN-6: CUSTOM INSTALL MODES -->
                 <div class="item dup-spin-hlp">
                    <h3>
                        <i class="fas fa-bolt fa-fw"></i>
                        <?php _e('Custom Install Modes', 'duplicator-pro'); ?>
                    </h3>
                    <div class="sub-head">
                        <?php _e('There are 3 custom install modes for site re-deployment.', 'duplicator-pro'); ?>
                    </div>

                    <div class="title">
                        <i class="fa fa-undo fa-fw"></i>&nbsp;
                        <a href="https://snapcreek.com/duplicator/docs/quick-start/#quick-050-q" target="_blank">
                            <b><?php _e('Recovery Point', 'duplicator-pro'); ?></b>
                        </a>
                        
                    </div>
                    <?php _e('Restore the current site to a specific snapshot in time.', 'duplicator-pro'); ?>
                    <br/><br/>

                    <div class="title">
                        <i class="fas fa-database fa-fw"></i>&nbsp;
                        <a href="https://snapcreek.com/duplicator/docs/quick-start/#quick-055-q" target="_blank">
                            <b><?php _e('Database Install', 'duplicator-pro'); ?></b>
                        </a>
                       
                    </div>
                    <?php _e('Backup and restore only the database', 'duplicator-pro'); ?>.
                    <br/><br/>

                    <div class="title">
                        <i class="fa fa-random fa-fw"></i>&nbsp;
                        <a href="https://snapcreek.com/duplicator/docs/quick-start/#quick-060-q" target="_blank">
                            <b><?php _e('Two-Part', 'duplicator-pro'); ?></b>
                        </a>
                    </div>
                    <?php _e('Run the install process by manually moving some of the site files', 'duplicator-pro'); ?>.
                    <br/><br/>

                   <small>
                       <?php _e('Custom Install Modes are not shown in detail in this tutorial', 'duplicator-pro'); ?>.
                   </small>
                 </div>


                 <!-- =====================
                 SPIN-7: INSTALL RESOURCES -->
                 <div class="item dup-spin-hlp">
                     <h3>
                         <i class="fas fa-link fa-fw"></i>
                            <?php _e('Install Resources', 'duplicator-pro'); ?>
                     </h3>
                     <div class="sub-head">
                         <?php _e('Look for the Install Resources to aid with the install process.', 'duplicator-pro'); ?>
                     </div>

                     <div class="title">
                         <i class="far fa-file-archive fa-fw"></i>
                             <?php _e('Archive File', 'duplicator-pro'); ?>
                             <i class="fas fa-question-circle"
                                 data-tooltip-title="<?php _e("Archive File", 'duplicator-pro'); ?>"
                                 data-tooltip="<?php _e(
                                     'An import install only requires the archive file and can be from many different remote locations',
                                     'duplicator-pro'
                                 ); ?>">
                             </i>
                     </div>
                     <ol style="margin-top:2px">
                         <li>
                              <?php _e('Use <b><i class="far fa-copy fa-xs"></i> Copy Link</b> to run a remote import install.', 'duplicator-pro'); ?>
                         </li>
                         <li>
                             <?php _e(
                                 'Use <b><i class="fas fa-download fa-xs"></i> Download</b> to run a import/overwrite/classic install.',
                                 'duplicator-pro'
                             ); ?>
                         </li>
                     </ol>

                     <div class="title">
                         <i class="fas fa-bolt fa-fw"></i>
                         <?php _e('Archive Installer', 'duplicator-pro'); ?>
                         <i class="fas fa-question-circle"
                             data-tooltip-title="<?php _e("Archive Installer", 'duplicator-pro'); ?>"
                             data-tooltip="<?php _e(
                                 'Secure install names are complex, quickly copy the name to improve your workflow.',
                                 'duplicator-pro'
                             ); ?>">
                         </i>
                     </div>
                     <?php _e('The installer.php file can be used for overwrite/classic install modes.', 'duplicator-pro');?>
                 </div>

                <!-- =====================
                SPIN-8: MORE INFO -->
                <div class="item dup-spin-hlp">
                    <h3>
                        <i class="fas fa-info-circle"></i>
                        <?php _e('More information', 'duplicator-pro'); ?>
                    </h3>
                    <div class="sub-head">
                        <?php _e('For additional detailed information checkout our online resources', 'duplicator-pro'); ?>.
                    </div>
                    <ul>
                         <li>
                            <i class="far fa-file-alt fa-fw"></i>
                            <a href="https://snapcreek.com/duplicator/docs/quick-start/" class='dup-knowledge-base' target='_sc-home'>
                                <?php DUP_PRO_U::esc_html_e('Quick Start'); ?>
                            </a>
                        </li>
                        <li>
                            <i class='fa fa-book fa-fw'></i>
                            <a href='<?php echo DUPLICATOR_PRO_USER_GUIDE_URL; ?>' class='dup-full-guide' target='_sc-guide'>
                                <?php DUP_PRO_U::esc_html_e('Full User Guide'); ?>
                            </a>
                        </li>
                        <li>
                            <i class='far fa-file-code fa-fw'></i>
                            <a href='<?php echo DUPLICATOR_PRO_TECH_FAQ_URL; ?>' class='dup-faqs' target='_sc-faq'>
                                <?php DUP_PRO_U::esc_html_e('Technical FAQs'); ?>
                            </a>
                        </li>
                    </ul>
                </div>

             </div>

             <div class="area-right">
                 <i class="fas fa-chevron-circle-right"></i>
             </div>

            <!-- Progress -->
             <div class="area-nav">
                 <span class="num"></span>
                 <progress class="progress"></progress>
             </div>
         </div>

     </div>
 </div>
</div>

<script>
jQuery(document).ready(function($) {

    var spin1 = new Duplicator.UI.Ctrl.Spinner('dup-link-spinner-1');
    var spin2 = new Duplicator.UI.Ctrl.Spinner('dup-link-spinner-2');

    //INIT
    $("a#dup-ovr-next-exe").on("click", function() {
        $($('#dup-ovr-hlp-tabs div.data-tabs a.tab').get(1)).trigger("click");
        spin2.setPanel(0);
    });

    Duplicator.UI.Ctrl.tabsFlat('dup-ovr-hlp-tabs');
    Duplicator.UI.Ctrl.tabsVert('dup-ovr-hlp-vert-tabs-1');

//    //DEBUG Package Overview Area & Dialog
//    setTimeout(function(){
//        DupPro.Pack.openLinkDetails();
//        $($('div#dup-ovr-hlp-tabs a.tab').get(1)).trigger("click");
//        $('div#dup-link-spinner-2 div.area-right').trigger("click");
//    }, 1);
});
</script>


