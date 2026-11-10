<?php if (!defined('ABSPATH')) exit;

add_filter('wp_setup_nav_menu_item', 'bogo_setup_nav_menu_item', 10, 1);
add_filter('wp_get_nav_menu_items', 'bogo_get_nav_menu_items', 10, 3);

/**
 * Setup nav menu item with locale information.
 * 
 * @filter wp_setup_nav_menu_item
 */
function bogo_setup_nav_menu_item($menu_item) {
  if (isset($menu_item->bogo_locales)) {
    return $menu_item;
  }

  $menu_item->bogo_locales = array();

  if (isset($menu_item->post_type) and 'nav_menu_item' == $menu_item->post_type) {
    $menu_item->bogo_locales = get_post_meta($menu_item->ID, '_locale');
  }

  if ($menu_item->bogo_locales) {
    $menu_item->bogo_locales = bogo_filter_locales($menu_item->bogo_locales);
  } else {
    $menu_item->bogo_locales = bogo_available_locales();
  }

  return $menu_item;
}

/**
 * Filter nav menu items based on the current locale.
 * 
 * @filter wp_get_nav_menu_items
 */
function bogo_get_nav_menu_items($items, $menu, $args) {
  if (is_admin()) {
    return $items;
  }

  $locale = get_locale();
  foreach ($items as $key => $item) {
    if (!in_array($locale, $item->bogo_locales)) {
      unset($items[$key]);
    }
  }

  return $items;
}

