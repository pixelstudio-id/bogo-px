<?php

add_filter('wp_get_nav_menu_items', 'bogo_localize_nav_menu_items', 15, 3);

/**
 * @filter wp_get_nav_menu_items
 */
function bogo_localize_nav_menu_items($items, $menu, $args) {
  $post_ids = array_map(function ($item) {
    return $item->object_id;
  }, $items);

  $guids = array_map(function ($item) {
    return $item->guid;
  }, $items);
  var_dump($items);

  $locale_posts = get_posts([
    'post_type' => 'any',
    'meta_query' => [
      [
        'key' => '_locale',
        'value' => get_locale(),
        'compare' => '=',
      ],
      [
        'key' => '_original_post',
        'value' => join(', ', $guids),
        'compare' => 'IN',
      ],
    ],
  ]);

  var_dump($locale_posts);
  return $items;
}