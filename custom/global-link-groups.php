<?php

define('BOGO_BASE_LOCALE', apply_filters('bogo_base_locale', 'en_US'));

add_action('init', 'bogo_init_global_link_groups');

/**
 * Cache the link pairings in global variable to make conversion easier
 * 
 * @action init
 */
function bogo_init_global_link_groups() {
  // abort if current language is base language
  if (is_admin() || bogo_is_base_locale()) { return; }

  $posts = get_posts([
    'post_type' => 'any',
    'meta_query' => [
      [
        'key' => '_original_post',
        'compare' => 'EXISTS',
      ],
    ],
    'posts_per_page' => -1
  ]);

  $groups = [];
  foreach ($posts as $p) {
    $original_post_id = get_post_meta($p->ID, '_original_post', true);

    if (!isset($groups[$original_post_id])) {
      $groups[$original_post_id] = [];
    }

    $groups[$original_post_id][] = [
      'id' => $p->ID,
      'locale' => get_post_meta($p->ID, '_locale', true) ?: BOGO_BASE_LOCALE,
      'url' => get_permalink($p),
      'post' => $p,
    ];
  }

  global $BOGO_LINK_GROUPS;
  $BOGO_LINK_GROUPS = $groups;
}

/**
 * Check whether current locale is the same as base locale
 */
function bogo_is_base_locale() {
  return get_locale() === BOGO_BASE_LOCALE;
}

/**
 * More verbose function of _bogo_get_locale_link()
 */
function bogo_get_locale_link_by_url($url) {
  $link = _bogo_get_locale_link('url', $url);
  return $link ?: null;
}

/**
 * More verbose function of _bogo_get_locale_link()
 */
function bogo_get_locale_link_by_id($id) {
  $link = _bogo_get_locale_link('id', $id);
  return $link ?: null;
}

/**
 * 
 */
function bogo_get_locale_post_by_id($id) {
  $link = _bogo_get_locale_link('id', $id);

  if ($link) {
    return $link['post'];
  }

  return null;
}

/**
 * Get locale link by searching through its $key
 */
function _bogo_get_locale_link($key, $value) {
  global $BOGO_LINK_GROUPS;
  if (!$BOGO_LINK_GROUPS) { return null; }

  foreach ($BOGO_LINK_GROUPS as $group) {
    $is_in_this_group = array_search($value, array_column($group, $key));

    if ($is_in_this_group !== false) {
      $locale_index = array_search(get_locale(), array_column($group, 'locale'));
      return $group[$locale_index];
    }
  }

  return null;
}