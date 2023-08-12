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
  class BMI_MigrationProgress {

    public function __construct($continue = false) {

      if (!file_exists(BMI_BACKUPS)) mkdir(BMI_BACKUPS, 755, true);

      $this->latest = BMI_BACKUPS . '/latest_migration.log';
      $this->progress = BMI_BACKUPS . '/latest_migration_progress.log';

      if (file_exists($this->latest) && $continue === false) {
        unlink($this->latest);
      }

    }

    public function start($muted = false) {

      $this->muted = $muted;

    }

    public function mute() {

      $this->muted = true;

    }

    public function unmute() {

      $this->muted = false;

    }

    public function progress($progress = '0') {

      file_put_contents($this->progress, $progress);

    }

    public function log($log = '', $level = 'INFO') {

      $this->file = fopen($this->latest, 'a');

      if (!$this->muted) {
        $log_string = '[' . strtoupper($level) . '] [' . date('Y-m-d H:i:s') . '] ' . $log . "\n";
        fwrite($this->file, $log_string);
        if (defined('BMI_USING_CLI_FUNCTIONALITY') && BMI_USING_CLI_FUNCTIONALITY === true) {
          echo $log_string;
        }
      }

      fclose($this->file);
    }

    public function end() {

      return true;

    }

  }
