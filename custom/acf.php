<?php

if (class_exists('ACF')) {
  add_filter('acf/fields/post_object/query', 'bogo_acf_fields_hide_localized_posts', 10, 3);
  add_filter('acf/fields/page_link/query', 'bogo_acf_fields_hide_localized_posts', 10, 3);

  add_filter('acf/format_value/type=post_object', 'bogo_acf_format_post_to_locale_post', 10, 3);
  add_filter('acf/format_value/type=link', 'bogo_acf_format_link_to_locale_link', 10);
}

add_filter('wp_link_query_args', 'bogo_hide_translated_post_in_link_modal');

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

/**
 * @filter acf/format_value/type=post_object
 */
function bogo_acf_format_post_to_locale_post($value, $post_id, $field) {
  $locale_post = bogo_get_locale_post_by_id($value);

  if ($locale_post) {
    $value = $locale_post;
  }

  return $value;
}

/**
 * @filter acf/format_value/type=link
 */
function bogo_acf_format_link_to_locale_link($value) {
  $locale_link = bogo_get_locale_link_by_url($value['url']);

  if ($locale_link) {
    $value['title'] = $locale_link['post']->post_title;
    $value['url'] = $locale_link['url'];
  }

  return $value;
}

/**
 * Hide the locale posts in the native Link modal, mostly used by ACF
 * 
 * @filter wp_link_query_args
 */
function bogo_hide_translated_post_in_link_modal($query) {
  $query['meta_query'] = [
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

  return $query;
}