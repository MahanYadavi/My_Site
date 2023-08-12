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

    public function __construct($backup_name, $files = 0, $bytes = 0, $cron = false, $reset = true) {

      if (!file_exists(BMI_BACKUPS)) mkdir(BMI_BACKUPS, 755, true);

      $this->name = $backup_name;
      $this->date = date('Y-m-d H:i:s');
      $this->millis = microtime(true);
      $this->cron = $cron;
      $this->logfilename = substr($backup_name, 0, -4) . '.log';
      $this->latest = BMI_BACKUPS . '/latest.log';
      $this->latest_progress = BMI_BACKUPS . '/latest_progress.log';
      $this->files = $files;
      $this->bytes = $bytes;
      $this->total_queries = 1;

      if ($reset == true) {
        if (file_exists($this->latest)) @unlink($this->latest);
        if (file_exists($this->latest_progress)) @unlink($this->latest_progress);
        file_put_contents($this->latest_progress, '0/100');
      }

    }

    public function createManifest($dbBackupEngine = 'v4') {

      global $table_prefix;

      $manifest = array(
        'name' => $this->name,
        'date' => $this->date,
        'files' => $this->files,
        'bytes' => $this->bytes,
        'cron' => $this->cron,
        'total_queries' => $this->total_queries,
        'manifest' => date('Y-m-d H:i:s'),
        'millis_start' => $this->millis,
        'millis_end' => microtime(true),
        'version' => BMI_VERSION,
        'domain' => parse_url(home_url())['host'],
        'dbdomain' => get_option('siteurl'),
        'uid' => get_current_user_id(),
        'source_query_output' => BMI_DB_MAX_ROWS_PER_QUERY,
        'db_backup_engine' => $dbBackupEngine,
        'config' => array(
          'ABSPATH' => ABSPATH,
          'DB_NAME' => DB_NAME,
          'DB_USER' => DB_USER,
          'DB_PASSWORD' => DB_PASSWORD,
          'DB_HOST' => DB_HOST,
          'DB_CHARSET' => (defined('DB_CHARSET') ? DB_CHARSET : ''),
          'DB_COLLATE' => (defined('DB_COLLATE') ? DB_COLLATE : ''),
          'AUTH_KEY' => (defined('AUTH_KEY') ? AUTH_KEY : ''),
          'SECURE_AUTH_KEY' => (defined('SECURE_AUTH_KEY') ? SECURE_AUTH_KEY : ''),
          'LOGGED_IN_KEY' => (defined('LOGGED_IN_KEY') ? LOGGED_IN_KEY : ''),
          'NONCE_KEY' => (defined('NONCE_KEY') ? NONCE_KEY : ''),
          'AUTH_SALT' => (defined('AUTH_SALT') ? AUTH_SALT : ''),
          'SECURE_AUTH_SALT' => (defined('SECURE_AUTH_SALT') ? SECURE_AUTH_SALT : ''),
          'LOGGED_IN_SALT' => (defined('LOGGED_IN_SALT') ? LOGGED_IN_SALT : ''),
          'NONCE_SALT' => (defined('NONCE_SALT') ? NONCE_SALT : ''),
          'WP_DEBUG_LOG' => WP_DEBUG_LOG,
          'WP_CONTENT_URL' => WP_CONTENT_URL,
          'WP_CONTENT_DIR' => trailingslashit(WP_CONTENT_DIR),
          'table_prefix' => $table_prefix
        )
      );

      return json_encode($manifest);

    }

    public function start($muted = false) {

      $this->muted = $muted;

    }

    public function log($log = '', $level = 'INFO') {

      if (!$this->muted) {
        $this->file = fopen($this->latest, 'a');
        $log_string = '[' . strtoupper($level) . '] [' . date('Y-m-d H:i:s') . '] ' . $log . "\n";
        fwrite($this->file, $log_string);
        fclose($this->file);
        if (defined('BMI_USING_CLI_FUNCTIONALITY') && BMI_USING_CLI_FUNCTIONALITY === true) {
          echo $log_string;
        }
      }

    }

    public function progress($progress = '0') {

      $this->progress = fopen($this->latest_progress, 'w') or die(__("Unable to open file!", 'backup-backup'));
      fwrite($this->progress, $progress);
      fclose($this->progress);

    }

    public function end() {

      // fclose($this->file);

    }

  }
