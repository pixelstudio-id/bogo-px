<?php

add_filter('wp_get_nav_menu_items', 'bogo_localize_nav_menu_items', 15, 3);

/**
 * @filter wp_get_nav_menu_items
 */
function bogo_localize_nav_menu_items($items, $menu, $args) {
  foreach ($items as $item) {
    $locale_link = bogo_get_locale_link_by_id($item->object_id);

    if ($locale_link) {
      $item->title = $locale_link['post']->post_title;
      $item->url = $locale_link['url'];
    }
  }

  return $items;
}