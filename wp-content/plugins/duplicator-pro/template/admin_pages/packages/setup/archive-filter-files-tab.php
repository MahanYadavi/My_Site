<?php

/**
 * @package Duplicator
 */

use Duplicator\Libs\Snap\SnapIO;

defined("ABSPATH") or die("");

/**
 * Variables
 *
 * @var \Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */
?>
<div class="filter-files-tab-content">
    <?php
    $uploads      = wp_upload_dir();
    $upload_dir   = SnapIO::safePath($uploads['basedir']);
    $content_path = defined('WP_CONTENT_DIR') ? SnapIO::safePath(WP_CONTENT_DIR) : '';
    ?>

    <div class="dup-package-hdr-1">
        <?php DUP_PRO_U::esc_html_e("Filters") ?>
    </div>

    <div class="dup-form-item">
        <span class="title"><?php DUP_PRO_U::esc_html_e("Database Only") ?>:</span>
        <span class="input">
            <input type="checkbox" id="export-onlydb" name="export-onlydb" onclick="DupPro.Pack.ExportOnlyDB()"/>
                <label for="export-onlydb"><?php DUP_PRO_U::esc_html_e('Archive only the database') ?></label>
        </span>
    </div>

    <div class="dup-form-item" id="dup-file-filter-label">
        <span class="title"><?php DUP_PRO_U::esc_html_e("Enable Filters") ?>:</span>
        <span class="input">
            <input type="checkbox" id="filter-on" name="filter-on" onclick="DupPro.Pack.ToggleFileFilters()" />
            <label for="filter-on">
                <?php DUP_PRO_U::esc_html_e("Allow folders, files &amp; file extensions to be excluded") ?>
            </label>
            <i class="fas fa-question-circle fa-sm"
                data-tooltip-title="<?php DUP_PRO_U::esc_attr_e("File Filters"); ?>"
                data-tooltip="<?php DUP_PRO_U::esc_attr_e(
                    'File filters allow you to ignore directories/files and file extensions.  When creating a package only include the data you '
                    . 'want and need.  This helps to improve the overall archive build time and keep your backups simple and clean.'
                ); ?>">
            </i>
        </span>
    </div>

    <div class="dup-form-item" id="dup-name-filter-label">
        <span class="title"><?php DUP_PRO_U::esc_html_e("Name Filters") ?>:</span>
        <span class="input">
            <input type="checkbox" id="filter-names" name="filter-names" />
            <label for="filter-names">
                <?php DUP_PRO_U::esc_html_e("Automatically filter files with invalid encoded names") ?>
            </label>
            <i class="fas fa-question-circle fa-sm"
                data-tooltip-title="<?php DUP_PRO_U::esc_attr_e("Auto Name Filter"); ?>"
                data-tooltip="<?php DUP_PRO_U::esc_attr_e('Automatically filter all files with invalid encoded names. '
                . 'This option is not commonly needed except on some servers that do not handle encoded characters. '
                . 'Only enable this option if you are having troubles with building/installing packages with encoded characters in file paths. '
                . 'Enabling this option will filter encoded paths.'); ?>">
            </i>
        </span>
    </div>

    <div id="dup-exportdb-items-off">
        <div id="dup-file-filter-items">

            <!-- DIRECTORIES -->
            <div>
                <label for="filter-dirs" title="<?php DUP_PRO_U::esc_attr_e("Separate all filters by semicolon"); ?>">
                    <b><?php DUP_PRO_U::esc_html_e("Folders") ?>:</b>
                    <sup class="dup-badge-01" title="<?php DUP_PRO_U::esc_attr_e("Number of diectory filters") ?>" id="filter-dirs-count">(0)</sup>
                </label>
                <div class='dup-quick-links'>
                    <a 
                        href="javascript:void(0)" 
                        onclick="DupPro.Pack.AddExcludePath('<?php echo esc_js(duplicator_pro_get_home_path()); ?>')">
                        [<?php DUP_PRO_U::esc_html_e("root path") ?>]
                    </a>
                    <?php if (! empty($content_path)) :?>
                        <a 
                            href="javascript:void(0)" 
                            onclick="DupPro.Pack.AddExcludePath('<?php echo SnapIO::safePath(WP_CONTENT_DIR); ?>')">
                            [<?php DUP_PRO_U::esc_html_e("wp-content") ?>]
                        </a>
                    <?php endif; ?>
                    <a href="javascript:void(0)" onclick="DupPro.Pack.AddExcludePath('<?php echo rtrim($upload_dir, '/'); ?>')">
                        [<?php DUP_PRO_U::esc_html_e("wp-uploads") ?>]
                    </a>
                    <a href="javascript:void(0)" onclick="DupPro.Pack.AddExcludePath('<?php echo SnapIO::safePath(WP_CONTENT_DIR); ?>/cache')">
                        [<?php DUP_PRO_U::esc_html_e("cache") ?>]
                    </a>
                    <a href="javascript:void(0)" onclick="jQuery('#filter-dirs').val(''); DupPro.Pack.CountFilters();">
                        <?php DUP_PRO_U::esc_html_e("(clear)") ?>
                    </a>
                </div>
                <textarea name="filter-dirs" id="filter-dirs" placeholder="/full_path/exclude_path1;/full_path/exclude_path2;"></textarea>
            </div><br/>

            <!-- EXTENSIONS -->
            <div>
                <label class="no-select" title="<?php DUP_PRO_U::esc_attr_e("Separate all filters by semicolon"); ?>">
                    <b><?php DUP_PRO_U::esc_html_e("File Extensions") ?>:</b>
                </label>
                <div class='dup-quick-links'>
                    <a href="javascript:void(0)" onclick="DupPro.Pack.AddExcludeExts('avi;mov;mp4;mpeg;mpg;swf;wmv;aac;m3u;mp3;mpa;wav;wma')">
                        [<?php DUP_PRO_U::esc_html_e("media") ?>]
                    </a>
                    <a href="javascript:void(0)" onclick="DupPro.Pack.AddExcludeExts('zip;rar;tar;gz;bz2;7z')">
                        [<?php DUP_PRO_U::esc_html_e("archive") ?>]
                    </a>
                    <a href="javascript:void(0)" onclick="jQuery('#filter-exts').val('')">
                        <?php DUP_PRO_U::esc_html_e("(clear)") ?>
                    </a>
                </div>
                <textarea name="filter-exts" id="filter-exts" placeholder="ext1;ext2;ext3;"></textarea>
            </div><br/>

            <!-- FILES -->
            <div>
                <label class="no-select" title="<?php DUP_PRO_U::esc_attr_e("Separate all filters by semicolon"); ?>">
                    <b><?php DUP_PRO_U::esc_html_e("Files") ?>:</b>
                    <sup class="dup-badge-01" title="<?php DUP_PRO_U::esc_attr_e("Number of file filters") ?>" id="filter-files-count">(0)</sup>
                </label>
                <div class='dup-quick-links'>
                    <a href="javascript:void(0)" onclick="DupPro.Pack.AddExcludeFilePath('<?php echo esc_js(duplicator_pro_get_home_path()); ?>')">
                        <?php DUP_PRO_U::esc_html_e("(file path)") ?>
                    </a>
                    <a href="javascript:void(0)" onclick="jQuery('#filter-files').val(''); DupPro.Pack.CountFilters();">
                        <?php DUP_PRO_U::esc_html_e("(clear)") ?>
                    </a>
                </div>
                <textarea name="filter-files" id="filter-files" placeholder="/full_path/exclude_file_1.ext;/full_path/exclude_file2.ext"></textarea>
            </div>

            <div class="dup-tabs-opts-help">
                <?php DUP_PRO_U::esc_html_e("The directories, extensions and files above will be be exclude from the archive file if enable is checked."); ?>
                <br/>
                <?php
                DUP_PRO_U::esc_html_e("Use full path for directories or specific files.");
                echo " <b>";
                DUP_PRO_U::esc_html_e("Use filenames without paths to filter same-named files across multiple directories.");
                echo "</b>";
                ?> <br/>
                <?php DUP_PRO_U::esc_html_e("Use semicolons to separate all items. Use # to comment a line."); ?>
            </div>
        </div>
    </div>

    <!-- DB ONLY ENABLED -->
    <div id="dup-exportdb-items-checked">
        <?php
            echo wp_kses(
                DUP_PRO_U::__(
                    "<b>Overview:</b><br> This advanced option excludes all files from the archive.  Only the database and a copy of the installer.php "
                    . "will be included in the archive.zip file. The option can be used for backing up and moving only the database."
                ),
                array(
                    'b' => array(),
                    'br' => array(),
                )
            );
            echo '<br/><br/>';

            echo wp_kses(
                DUP_PRO_U::__(
                    "<b><i class='fa fa-exclamation-circle'></i> Notice:</b><br/> "
                    . "Installing only the database over an existing site may have unintended consequences.  "
                    . "Be sure to know the state of your system before installing the database without the associated files.  "
                ),
                array(
                    'b' => array(),
                    'i' => array('class'),
                    'br' => array()
                )
            );

            DUP_PRO_U::esc_html_e(
                "For example, if you have WordPress 5.6 on this site and you copy this site's database to a host that has WordPress 5.8 files "
                . "then the source code of the files  will not be in sync with the database causing possible errors. "
                . "This can also be true of plugins and themes.  "
                . "When moving only the database be sure to know the database will be compatible with "
                . "ALL source code files. Please use this advanced feature with caution!"
            );

            echo '<br/><br/>';

            echo wp_kses(
                DUP_PRO_U::__("<b>Install Time:</b><br> When installing a database only package please visit the "),
                array(
                    'b' => array(),
                    'br' => array(),
                )
            );
            ?>
            <a href="https://snapcreek.com/duplicator/docs/quick-start/#quick-050-q" target="_blank">
                <?php DUP_PRO_U::esc_html_e('database only quick start'); ?>
            </a>
        <br/><br/>
    </div>
</div>
