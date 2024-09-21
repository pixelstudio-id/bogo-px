<?php

define('BOGO_DEFAULT_LOCALE', apply_filters('bogo_default_locale', bogo_get_default_locale()));

require_once __DIR__ . '/_helpers.php';
require_once __DIR__ . '/global-link-groups.php';
require_once __DIR__ . '/enqueue.php';
require_once __DIR__ . '/content.php';
require_once __DIR__ . '/list-table.php';
require_once __DIR__ . '/taxonomy.php';
require_once __DIR__ . '/nav-menu.php';
require_once __DIR__ . '/acf.php';
require_once __DIR__ . '/shortcode.php';
require_once __DIR__ . '/user.php';

/**
 * Any function with the prefix bogoHelper can be called with Bogo::function_name()
 * 
 * This is just for cosmetic purpose
 */
class Bogo {
  static function __callStatic($name, $args) {
    $func_name = "bogoHelper_$name";

    if (is_callable($func_name)) {
      return call_user_func_array($func_name, $args);
    } else {
      trigger_error("The function bogoHelper_{$name} does not exist.", E_USER_ERROR);
    }
  }
}