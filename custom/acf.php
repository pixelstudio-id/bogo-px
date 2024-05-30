<?php

if (class_exists('ACF')) {
  add_filter('acf/fields/post_object/query', 'bogo_acf_fields_hide_localized_posts', 10, 3);
  add_filter('acf/fields/page_link/query', 'bogo_acf_fields_hide_localized_posts', 10, 3);
}

/**
 * @filter acf/fields/post_object/query
 */
function bogo_acf_fields_hide_localized_posts($args, $field, $post_id) {
  $args['meta_query'] = [
    'relation' => 'OR',
    [
      'key' => '_locale',
      'compare' => 'NOT EXISTS',
    ],
    [
      'key' => '_locale',
      'value' => get_locale(),
      'compare' => '=',
    ],
  ];
  return $args;
}