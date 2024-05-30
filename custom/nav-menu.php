<?php

add_filter('wp_get_nav_menu_items', 'bogo_localize_nav_menu_items', 15, 3);

/**
 * @filter wp_get_nav_menu_items
 */
function bogo_localize_nav_menu_items($items, $menu, $args) {
  $post_ids = array_map(function ($item) {
    return $item->object_id;
  }, $items);

  $locale_posts = get_posts([
    'post_type' => 'any',
    'meta_query' => [
      'relation' => 'AND',
      [
        'key' => '_locale',
        'value' => get_locale(),
        'compare' => '=',
      ],
      [
        'key' => '_original_post',
        'value' => join(', ', $post_ids),
        'compare' => 'IN',
      ],
    ],
  ]);

  foreach ($locale_posts as $p) {
    $_original_post = get_post_meta($p->ID, '_original_post', true);
    $found_index = array_search($_original_post, array_column($items, 'object_id'));
    
    // replace the title and URL
    if ($found_index !== false) {
      $items[$found_index]->title = $p->post_title;
      $items[$found_index]->url = get_permalink($p);
    }
  }

  // TODO: hide untranslated post?

  return $items;
}