<?php

  // Namespace
  namespace BMI\Plugin\Dashboard;

  // Exit on direct access
  if (!defined('ABSPATH')) exit;

?>

<table>
  <tr class="br_tr_template">
    <td>
      <label class="br_label" for="">
        <input class="br_checkbox" id="" type="checkbox">
        <span class="br_date">---</span>
      </label>
    </td>
    <td class="br_name tooltip-html" tooltip="example.com" data-top="5">---</td>
    <td class="br_size">---</td>
    <td class="br_stroage center">
      <div class="cf br_wrapper_storage">
        <div class="left">

          <svg class="list-storage-img strg-local tooltip" tooltip="<?php _e('Local Storage', 'backup-backup') ?>" data-top="5">
            <use xlink:href="<?php echo $this->get_asset('images', 'local-server-2.svg#img') ?>"></use>
          </svg>

          <svg class="list-storage-img strg-gdrive tooltip" tooltip="<?php _e('Google Drive', 'backup-backup') ?>" data-top="5">
            <use xlink:href="<?php echo $this->get_asset('images', 'google-drive-mono.svg#img') ?>"></use>
          </svg>

        </div>
        <div class="right">

          <svg class="list-storage-img strg-suc tooltip" tooltip="<?php _e('Backup is stored on: Google Drive & Local Storage.', 'backup-backup') ?>" data-top="5">
            <use xlink:href="<?php echo $this->get_asset('images', 'list-success.svg#img') ?>"></use>
          </svg>

          <svg class="list-storage-img strg-warn img-red tooltip" tooltip="<?php _e('There was an error during upload to: Google Drive.', 'backup-backup') ?>" data-top="5" style="display: none;">
            <use xlink:href="<?php echo $this->get_asset('images', 'list-warning.svg#img') ?>"></use>
          </svg>

          <svg class="list-storage-img strg-ong ongoing tooltip" tooltip="<?php _e('Upload to Google Drive in progress:', 'backup-backup') ?>" data-top="5" style="display: none;">
            <use xlink:href="<?php echo $this->get_asset('images', 'list-ongoing.svg#img') ?>"></use>
          </svg>

          <svg class="list-storage-img strg-wait tooltip" tooltip="<?php _e('Backup is queued for upload.', 'backup-backup') ?>" data-top="5" style="display: none;">
            <use xlink:href="<?php echo $this->get_asset('images', 'list-waiting.svg#img') ?>"></use>
          </svg>

        </div>
      </div>
    </td>
    <td class="center">
      <div class="brow_lock">
        <img class="tooltip bc-unlocked-btn hoverable" tooltip="<?php _e('Lock backup files', 'backup-backup') ?>" src="<?php echo $this->get_asset('images', 'unlocked-min.svg'); ?>" alt="image">
        <img class="tooltip bc-locked-btn hoverable" tooltip="<?php _e('Unlock backup files', 'backup-backup') ?>" src="<?php echo $this->get_asset('images', 'lock-min.svg'); ?>" alt="image">
      </div>
    </td>
    <td>
      <div class="brow_subactions">
        <a href="#" class="bc-download-btn hoverable nodec untab" tabindex="-1" download>
          <img class="tooltip" tooltip="<?php _e('Download the backup file. Click on it downloads it', 'backup-backup') ?>" src="<?php echo $this->get_asset('images', 'download-min.png'); ?>" alt="image">
        </a>
        <img class="tooltip bc-url-btn hoverable untab" tabindex="-1" tooltip="<?php _e('Copy link to backup file for super-quick migration', 'backup-backup') ?>" src="<?php echo $this->get_asset('images', 'link-min.png'); ?>" alt="image">
        <a href="#" class="bc-logs-btn hoverable nodec untab" tabindex="-1" download>
          <img class="tooltip" tooltip="<?php _e('Download log file which was created at time of backup', 'backup-backup') ?>" src="<?php echo $this->get_asset('images', 'log-min.svg'); ?>" alt="image">
        </a>
      </div>
    </td>
    <td>
      <div class="restore-btn hoverable tooltip" tooltip="<?php _e('Restore this backup on this site', 'backup-backup') ?>">
        <img src="<?php echo $this->get_asset('images', 'restore-min.svg'); ?>" width="12px" alt="image">
        <?php _e('Restore', 'backup-backup') ?>
      </div>
    </td>
    <td><img class="tooltip bc-remove-btn hoverable" tooltip="<?php _e('Delete this backup', 'backup-backup') ?>" src="<?php echo $this->get_asset('images', 'red-close-min.svg'); ?>" alt="image"></td>
  </tr>
</table>
