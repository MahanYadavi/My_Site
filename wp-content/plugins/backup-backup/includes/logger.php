<?php

  // Namespace
  namespace BMI\Plugin;

  // Exit on direct access
  if (!defined('ABSPATH')) exit;

  /**
   * Main Plugin Logger
   */
  class BMI_Logger {

    public static function append($type, $log) {

      if (file_exists(BMI_CONFIG_DIR . DIRECTORY_SEPARATOR . 'complete_logs.log')) {
        $date = '[' . date('Y-m-d H:i:s') . '] ';
        $file = fopen(BMI_CONFIG_DIR . DIRECTORY_SEPARATOR . 'complete_logs.log', 'a');
                fwrite($file, $date . $type . ' ' . $log . "\n");
                fclose($file);
      }

    }

    public static function log($log) {

      BMI_Logger::append('[LOG]', $log);

    }

    public static function error($log) {

      BMI_Logger::append('[ERROR]', $log);

    }

    public static function debug($log) {

      if (BMI_DEBUG === TRUE) {
        BMI_Logger::append('[DEBUG]', $log);
      }

    }

  }
