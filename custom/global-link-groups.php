<?php

add_action('init', 'bogo_init_global_link_groups');

/**
 * Cache the link pairings in global variable to make conversion easier
 * 
 * @action init
 */
function bogo_init_global_link_groups() {
  // abort if current language is base language
  if (is_admin() || bogo_is_default_locale()) { return; }

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

    $locale = get_post_meta($p->ID, '_locale', true) ?: BOGO_DEFAULT_LOCALE;

    $groups[$original_post_id][$locale] = [
      'id' => $p->ID,
      'locale' => $locale,
      'url' => get_permalink($p),
      'post' => $p,
    ];
  }

  // group by URL
  $groups_by_url = [];
  foreach ($groups as $group) {
    $og_url = $group[BOGO_DEFAULT_LOCALE]['url'];
    $groups_by_url[$og_url] = $group;
  }

  global $BOGO_GROUPS_BY_ID;
  global $BOGO_GROUPS_BY_URL;

  $BOGO_GROUPS_BY_ID = $groups;
  $BOGO_GROUPS_BY_URL = $groups_by_url;
}

/**
 * Get the translated version of a post by URL
 */
function bogo_get_locale_link_by_url($url) {
  $parsed_url = parse_url($url);

  // abort if doesn't have scheme (maybe a #id link)
  if (!isset($parsed_url['scheme'])) { return null; }

  $base_url = "{$parsed_url['scheme']}://{$parsed_url['host']}";

  if (isset($parsed_url['path'])) {
    $base_url .= $parsed_url['path'];
  }

  global $BOGO_GROUPS_BY_URL;
  $link = $BOGO_GROUPS_BY_URL[$base_url][get_locale()] ?? null;

  if (!$link) { return null; }
  
  if (isset($parsed_url['query'])) {
    $link['url'] .= "?{$parsed_url['query']}";
  }

  return $link ?: null;
}

/**
 * Get the translated version of a post by ID
 */
function bogo_get_locale_link_by_id($id) {
  global $BOGO_GROUPS_BY_ID;
  $link = $BOGO_GROUPS_BY_ID[$id][get_locale()] ?? null;

  return $link ?: null;
}

/**
 * 
 */
function bogo_get_locale_post_by_id($id) {
  $link = bogo_get_locale_link_by_id($id);

  if ($link) {
    return $link['post'];
  }

  return null;
}