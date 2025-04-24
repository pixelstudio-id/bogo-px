<?php

add_action('init', 'bogo_init_global_link_groups');

/**
 * Cache the link pairings in global variable to make conversion easier
 * 
 * @action init
 */
function bogo_init_global_link_groups() {
  // @todo - is this too slow to always run?

  $posts = get_posts([
    'post_type' => Bogo::get_localizable_post_types(),
    'meta_query' => [[
      'key' => '_original_post',
      'compare' => 'EXISTS',
    ]],
    'post_status' => is_user_logged_in() ? ['publish', 'draft'] : ['publish'],
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
  
  global $BOGO_GROUPS_BY_ID;
  $BOGO_GROUPS_BY_ID = $groups;

  // group by URL
  $groups_by_url = [];
  foreach ($groups as $group) {
    $og_url = isset($group[BOGO_DEFAULT_LOCALE]) ? $group[BOGO_DEFAULT_LOCALE]['url'] : '';
    $groups_by_url[$og_url] = $group;
  }

  global $BOGO_GROUPS_BY_URL;
  $BOGO_GROUPS_BY_URL = $groups_by_url;

  // group by post type and lang
  $groups_by_pt = [];
  foreach ($groups as $original_id => $group) {
    $post_type = isset($group[BOGO_DEFAULT_LOCALE]) ? $group[BOGO_DEFAULT_LOCALE]['post']->post_type : '';
    if (!$post_type) { continue; }

    if (!isset($groups_by_pt[$post_type])) {
      $groups_by_pt[$post_type] = [];
    }

    // group by lang
    foreach ($group as $locale => $item) {
      if (!isset($groups_by_pt[$post_type][$locale])) {
        $groups_by_pt[$post_type][$locale] = [];
      }

      $groups_by_pt[$post_type][$locale][] = $item['post'];
    }
  }

  global $BOGO_GROUPS_BY_POST_TYPE;
  $BOGO_GROUPS_BY_POST_TYPE = $groups_by_pt;
}

/**
 * Get the translated version of a post by URL
 */
function bogo_localize_by_url($url) {
  $parsed_url = parse_url($url);

  // abort if doesn't have scheme (maybe a #id link)
  if (!isset($parsed_url['scheme'])) { return null; }

  // abort if doesn't have host (maybe a tel or mailto link)
  if (!isset($parsed_url['host'])) { return null; }

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
function bogo_localize_by_id($id, $force_locale = null) {
  global $BOGO_GROUPS_BY_ID;
  $id = is_array($id) ? $id[0] : $id;

  $locale = get_locale();
  if ($force_locale) {
    switch_to_locale($force_locale);
    $locale = determine_locale();
  }

  if (isset($BOGO_GROUPS_BY_ID[$id])) {
    $link = $BOGO_GROUPS_BY_ID[$id][$locale] ?? null;
    return $link;
  }

  return null;
}

/**
 * Shortcut to directly get the 'post' object from bogo_localize_by_id()
 */
function bogo_localize_post_by_id($id, $force_locale = null) {
  $link = bogo_localize_by_id($id, $force_locale);

  if ($link) {
    return $link['post'];
  }

  return null;
}