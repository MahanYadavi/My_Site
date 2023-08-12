<?php

  // Namespace
  namespace BMI\Plugin;

  // Use classes
  use BMI\Plugin\BMI_Logger as Logger;
  use BMI\Plugin\Backup_Migration_Plugin as BMP;
  use BMI\Plugin\Extracter\BMI_Extracter as Extracter;
  use BMI\Plugin\Progress\BMI_MigrationProgress as MigrationProgress;

  // Allow only PHP CLI to use this script
  if (php_sapi_name() !== 'cli') {
    echo 'This script it dedicated for PHP CLI';
    exit;
  }

  function isFunctionEnabled($func) {
    $disabled = explode(',', ini_get('disable_functions'));
    $isDisabled = in_array($func, $disabled);
    if (!$isDisabled && function_exists($func)) return true;
    else return false;
  }

  // Find WordPress Path
  function bmi_find_wordpress_base_path() {

    $dir = dirname(__FILE__);
    $previous = null;

    do {

      if (file_exists($dir . '/wp-config.php')) return $dir;
      if ($previous == $dir) break;
      $previous = $dir;

    } while ($dir = dirname($dir));

    return null;

  }

  // Tell WP to not use Themes and set Base Path
  define('BASE_PATH', bmi_find_wordpress_base_path() . '/');
  define('WP_USE_THEMES', false);
  define('BMI_USING_CLI_FUNCTIONALITY', true);
  if (isset($argv[1])) define('BMI_CLI_FUNCTION', $argv[1]);
  else {

    echo 'Please specify CLI function: bmi_restore [<backup_name>.zip], bmi_backup or bmi_quick_migration <backup URL>';
    exit();

  }

  if (isset($argv[2])) define('BMI_CLI_ARGUMENT', $argv[2]);
  if (isset($argv[3])) define('BMI_CLI_ARGUMENT_2', $argv[3]);

  // Pseudo Server variables
  $_SERVER['REQUEST_METHOD'] = 'CLI';

  // Increase max execution time
  if (isFunctionEnabled('headers_sent')) {
    if (!headers_sent()) {
      if (isFunctionEnabled('set_time_limit')) @set_time_limit(259200);
      if (isFunctionEnabled('ini_set')) {
        @ini_set('max_input_time', '259200');
        @ini_set('max_execution_time', '259200');
        @ini_set('session.gc_maxlifetime', '1200');
      }
    }
  }

  // Response
  ob_start();
  echo '100101011101' . "\n";

  @header('Connection: close');
  @header('Content-Length: ' . ob_get_length());

  ob_end_flush();
  flush();
  if (isFunctionEnabled('fastcgi_finish_request') && isFunctionEnabled('is_callable') && is_callable('fastcgi_finish_request')) {
    fastcgi_finish_request();
  }

  // Let the server know it's server-side script
  if (isFunctionEnabled('ignore_user_abort')) {
    @ignore_user_abort(true);
  }

  if (isFunctionEnabled('session_write_close')) {
    @session_write_close();
  }

  // Use WP Globals and load WordPress
  global $wp, $wp_query, $wp_the_query, $wp_rewrite, $wp_did_header;
  require_once BASE_PATH . 'wp-load.php';
