<?php

  // Namespace
  namespace BMI\Plugin\Progress;

  // Use
  use BMI\Plugin\BMI_Logger AS Logger;

  // Exit on direct access
  if (!defined('ABSPATH')) exit;

  /**
   * Main File Scanner Logic
   */
  class BMI_ZipProgress {

    public function __construct() {

      if (!file_exists(BMI_BACKUPS)) mkdir(BMI_BACKUPS, 755, true);

      $this->latest = BMI_BACKUPS . '/latest.log';
      $this->latest_progress = BMI_BACKUPS . '/latest_progress.log';

    }

    public function start($muted = false) {

      $this->muted = $muted;

    }

    public function progress($progress = '0') {

      $this->progress = fopen($this->latest_progress, 'w') or die(__("Unable to open file!", 'backup-backup'));
      fwrite($this->progress, $progress);
      fclose($this->progress);

    }

    public function log($log = '', $level = 'INFO') {

      if (!$this->muted) {
        $this->file = @fopen($this->latest, 'a');
        if ($this->file) {
          $log_string = '[' . strtoupper($level) . '] [' . date('Y-m-d H:i:s') . '] ' . $log . "\n";
          @fwrite($this->file, $log_string);
          if (defined('BMI_USING_CLI_FUNCTIONALITY') && BMI_USING_CLI_FUNCTIONALITY === true) {
            echo $log_string;
          }
        }
        @fclose($this->file);
      } else {
        error_log('[' . strtoupper($level) . '] [' . date('Y-m-d H:i:s') . '] ' . $log);
      }

    }

    public function end() {

      return true;

    }

  }
