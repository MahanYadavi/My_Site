<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

defined("ABSPATH") or die("");

/**
 * Variables
 *
 * @var \Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */

?>
<div class="dup-pro-import-upload-message" >
    <p class="import-upload-reset-message-error">
        <i class="fa fa-exclamation-triangle"></i> <b><?php DUP_PRO_U::esc_html_e('UPLOAD FILE PROBLEM'); ?></b>
    </p>
    <p>
        <?php DUP_PRO_U::_e('Error message:'); ?>&nbsp;
        <b><span class="import-upload-error-message"><!-- here is set the message received from the server --></span></b>
    </p>
    <div><?php DUP_PRO_U::_e('Possible solutions:'); ?></div>
    <ul class="dup-pro-simple-style-list" >
        <li>
            <?php _e('If you are using Server to Server transfer function make sure the URL is a valid URL', 'duplicator-pro'); ?>
        </li>
        <li>
            <?php
                printf(
                    __('If you are using the upload function try to change the chunk size in <a href="%s">settings</a> and try again', 'duplicator-pro'),
                    'admin.php?page=duplicator-pro-settings&tab=import'
                );
                ?>
        </li>
        <li>
            <?php
                printf(
                    __('Upload the file via FTP/file manager to the "%s" folder and reload the page.', 'duplicator-pro'),
                    esc_html(DUPLICATOR_PRO_PATH_IMPORTS)
                );
                ?>
        </li>
    </ul>
    <p>
        <b>
        <?php
        printf(
            _x(
                'For more information see %1$s[this FAQ item]%2$s',
                '%1$s and %2$s represents the opening and closing HTML tags for an anchor or link',
                'duplicator-pro'
            ),
            '<a href="https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-035-q" target="_blank">',
            '</a>'
        );
        ?>
        </b>
    </p>
</div>
