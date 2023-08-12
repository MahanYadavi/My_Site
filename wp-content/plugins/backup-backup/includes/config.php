<?php

  // Namespace
  namespace BMI\Plugin\Dashboard;

  // Exit on direct access
  if (!defined('ABSPATH')) exit;

  if (!function_exists('bmi_get_config')) {
    function bmi_get_config($setting, $configpath = false) {

      if ($configpath == false) $configpath = BMI_CONFIG_PATH;

      // Load default and additional
      $defaults = json_decode(file_get_contents(BMI_CONFIG_DEFAULT));

      // Result default
      if (isset($defaults->{$setting}))
        $result = $defaults->{$setting};
      else $result = array();

      // Load user config
      if (file_exists($configpath) && BMI_CONFIG_STATUS) {

        // Get file contents
        $bmi_config_contents = file_get_contents($configpath);
        $bmi_config_json = json_decode($bmi_config_contents);

        // If config is correct set it
        if (json_last_error() == JSON_ERROR_NONE) {

          // Setting exist?
          if (isset($bmi_config_json->{$setting})) {

            // Get result
            $result = $bmi_config_json->{$setting};

          }

        }

      }

      // Replace exceptions
      if ($setting == 'STORAGE::LOCAL::PATH' && $result == 'default') {
        $result = BMI_BACKUPS_DEFAULT;
      }

      // Replace backshashes
      if ($setting == 'STORAGE::LOCAL::PATH') {
        $result = str_replace('\\\\', DIRECTORY_SEPARATOR, $result);
        $result = str_replace('\\', DIRECTORY_SEPARATOR, $result);
        $result = str_replace('/', DIRECTORY_SEPARATOR, $result);
      }

      // Return setting
      return $result;

    }
  }

  if (!function_exists('bmi_set_config')) {
    function bmi_set_config($setting, $value) {

      // Load default and additional
      if (file_exists(BMI_CONFIG_PATH)) {

        // Get file contents
        $bmi_config_contents = file_get_contents(BMI_CONFIG_PATH);
        $bmi_config_json = json_decode($bmi_config_contents);

        // Result default
        $default = bmi_get_config($setting);

        // If config is correct set it
        if (!(json_last_error() == JSON_ERROR_NONE)) {

          // Setting refill base
          $bmi_config_json = json_decode(json_encode(array()));;

        }

        // Allow empty
        $allow_empty = ['OTHER:CLI:PATH'];

        // Check if setting is not empty
        if (isset($value) && (!is_string($value) || (in_array($setting, $allow_empty) || strlen(trim($value)) > 0))) {

          // Set new setting
          @$bmi_config_json->{$setting} = $value;

        } else return false;

        // Write edited settings
        file_put_contents(BMI_CONFIG_PATH, json_encode($bmi_config_json));
        return true;

      }

      return false;

    }
  }

  if (!function_exists('bmi_try_checked')) {
    function bmi_try_checked($setting, $reversed = false) {

      if (!$reversed) {

        if (bmi_get_config($setting) == 'true' || bmi_get_config($setting) === true) {
          echo ' checked';
        } else return false;

      } else {

        if (bmi_get_config($setting) == 'true' || bmi_get_config($setting) === true) {
          return false;
        } else {
          echo ' checked';
        }

      }

    }
  }

  if (!function_exists('bmi_try_value')) {
    function bmi_try_value($setting) {

      $res = bmi_get_config($setting);
      if ($res !== false) {
        echo ' value="' . sanitize_text_field($res) . '"';
      } else echo '';

    }
  }

  $bmi_initial_config_filepath = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'backup-migration' . DIRECTORY_SEPARATOR . 'config.json';
  $bmi_initial_config_dirpath = dirname($bmi_initial_config_filepath);
  $bmi_database_config_dirpath = get_option('BMI::STORAGE::LOCAL::PATH', false);

  if ($bmi_database_config_dirpath != false && dirname($bmi_database_config_dirpath) != $bmi_initial_config_dirpath) {
    $bmi_initial_config_filepath = $bmi_database_config_dirpath . DIRECTORY_SEPARATOR . 'config.json';
    $bmi_initial_config_dirpath = dirname($bmi_initial_config_filepath);
  }

  // Get config and parse it
  if (file_exists($bmi_initial_config_filepath)) {

    // Get file contents
    $bmi_config_contents = file_get_contents($bmi_initial_config_filepath);
    $bmi_config_json = json_decode($bmi_config_contents);

    // If config is correct set it
    if (json_last_error() == JSON_ERROR_NONE) {

      if (!defined('BMI_CONFIG_STATUS')) define('BMI_CONFIG_STATUS', true);
      $localStoragePath = bmi_get_config('STORAGE::LOCAL::PATH', $bmi_initial_config_filepath);
      if (!defined('BMI_BACKUPS')) define('BMI_BACKUPS', $localStoragePath . DIRECTORY_SEPARATOR . 'backups');

      if ($bmi_database_config_dirpath == false || $bmi_database_config_dirpath != $localStoragePath) {
        @copy($bmi_initial_config_filepath, $localStoragePath . DIRECTORY_SEPARATOR . 'config.json');
        @unlink($bmi_initial_config_filepath);

        $prev_path = dirname($bmi_initial_config_filepath);
        $prev_path_backups = dirname($bmi_initial_config_filepath) . DIRECTORY_SEPARATOR . 'backups';

        if (file_exists($prev_path_backups) && is_dir($prev_path_backups)) {
          $scanned_directory_backups = array_diff(scandir($prev_path_backups), ['..', '.']);
          foreach ($scanned_directory_backups as $i => $file) {
            if (file_exists($prev_path . DIRECTORY_SEPARATOR . $file) && !is_dir($prev_path . DIRECTORY_SEPARATOR . $file)) {
              rename($prev_path . DIRECTORY_SEPARATOR . $file, $localStoragePath . DIRECTORY_SEPARATOR . $file);
            }
          }
          @rmdir($prev_path_backups);
        }

        if (file_exists($prev_path) && is_dir($prev_path)) {
          $scanned_directory = array_diff(scandir($prev_path), ['..', '.']);
          foreach ($scanned_directory as $i => $file) {
            if (file_exists($prev_path . DIRECTORY_SEPARATOR . $file) && !is_dir($prev_path . DIRECTORY_SEPARATOR . $file)) {
              rename($prev_path . DIRECTORY_SEPARATOR . $file, $localStoragePath . DIRECTORY_SEPARATOR . $file);
            }
          }
          @rmdir($prev_path);
        }

        update_option('BMI::STORAGE::LOCAL::PATH', $localStoragePath);
      }

      if (!defined('BMI_CONFIG_PATH')) define('BMI_CONFIG_PATH', $localStoragePath . DIRECTORY_SEPARATOR . 'config.json');
      if (!defined('BMI_CONFIG_DIR')) define('BMI_CONFIG_DIR', $localStoragePath);

    } else {

      if (!defined('BMI_CONFIG_STATUS')) define('BMI_CONFIG_STATUS', false);

    }

  } else {

    if (!file_exists(dirname($bmi_initial_config_filepath)) && !is_dir(dirname($bmi_initial_config_filepath))) {
      @mkdir(dirname($bmi_initial_config_filepath), 0755, true);
    }

    @copy(BMI_CONFIG_DEFAULT, $bmi_initial_config_filepath);
    if (!defined('BMI_CONFIG_STATUS')) define('BMI_CONFIG_STATUS', true);
    if (!defined('BMI_CONFIG_PATH')) define('BMI_CONFIG_PATH', $bmi_initial_config_filepath);
    if (!defined('BMI_CONFIG_DIR')) define('BMI_CONFIG_DIR', dirname($bmi_initial_config_filepath));

  }
