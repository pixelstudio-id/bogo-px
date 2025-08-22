<?php

define('BOGO_DEFAULT_LOCALE', apply_filters('bogo_default_locale', bogo_get_default_locale()));

require_once __DIR__ . '/_helpers.php';
require_once __DIR__ . '/global-link-groups.php';
require_once __DIR__ . '/enqueue.php';
require_once __DIR__ . '/content.php';
require_once __DIR__ . '/list-table/list-table.php';
require_once __DIR__ . '/taxonomy/taxonomy.php';
require_once __DIR__ . '/nav-menu/nav-menu.php';
require_once __DIR__ . '/acf.php';
require_once __DIR__ . '/language-switcher/language-switcher.php';
require_once __DIR__ . '/user.php';
require_once __DIR__ . '/reusable-blocks/reusable-blocks.php';
require_once __DIR__ . '/the-seo-framework.php';

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

/**
 * Any function with the prefix _bogoHelper can be called with _Bogo::function_name()
 * 
 * This is just for cosmetic purpose
 */
class _Bogo {
  static function __callStatic($name, $args) {
    $func_name = "_bogoHelper_$name";

    if (is_callable($func_name)) {
      return call_user_func_array($func_name, $args);
    } else {
      trigger_error("The function _bogoHelper_{$name} does not exist.", E_USER_ERROR);
    }
  }
}

add_filter('bogo_duplicate_post', 'bogopx_escape_copied_content', 10, 3);

/**
 * Fixed the HTML tag within JSON comment when copying post being escaped
 * 
 * @filter bogo_duplicate_post
 */
function bogopx_escape_copied_content($postarr, $original_post, $locale) {
  $postarr['post_content'] = preg_replace(
    '/\\\\(u[0-9a-fA-F]{4}|[nrtbfv\\"\'\/])/',
    '\\\\$0',
    $postarr['post_content']
  );
  return $postarr;
}


add_filter('request', function($query) {
  if (!empty($query['name'])) {
    // $query['lang'] = 'de';
  }

  return $query;
});