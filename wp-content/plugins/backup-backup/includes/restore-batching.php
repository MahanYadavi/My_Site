<?php

  // Namespace
  namespace BMI\Plugin;

  // Exit on GET access
  if (!(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' && isset($_POST['bmi_restore_secret']))) {
    echo 'Access denied!';
    exit;
  }

  // Double check because why not
  if (empty($_POST)) exit;
  if (!isset($_POST['bmi_restore_secret'])) exit;

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

  // Validate the secret
  if (gettype($_POST['bmi_restore_secret']) == 'string' && strlen($_POST['bmi_restore_secret']) == '64') {

    // For now we don't trust the user
    $bmi_continue_module = false;

    // Check the secret
    $bmi_secret_storage = __DIR__ . '/htaccess/.restore_secret';
    if (file_exists($bmi_secret_storage)) {
      $bmi_saved_secret = file_get_contents($bmi_secret_storage);
      if ($bmi_saved_secret === $_POST['bmi_restore_secret']) {
        $bmi_continue_module = true;
      } else exit;
    } else exit;

    // Set definition for handler
    define('BMI_RESTORE_SECRET', $_POST['bmi_restore_secret']);
    define('BMI_POST_CONTINUE_RESTORE', true);

    // Tell WP to not use Themes and set Base Path
    define('BASE_PATH', bmi_find_wordpress_base_path() . '/');
    define('WP_USE_THEMES', false);

    // Third time just for sure
    if ($bmi_continue_module === true) {

      // Use WP Globals and load WordPress
      global $wp, $wp_query, $wp_the_query, $wp_rewrite, $wp_did_header;
      require_once BASE_PATH . 'wp-load.php';

    }

  }
