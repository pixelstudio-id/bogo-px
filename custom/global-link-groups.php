<?php

add_action('init', 'bogo_init_global_link_groups');

add_action('post_updated', 'bogopx_update_links_cache_if_changes', 100, 3);
add_action('bogo_after_duplicate_post', 'bogopx_update_links_cache_after_duplicate_post', 10, 3);
// @todo - add WP Super Cache clear hook here too
add_action('wpsc_after_delete_cache_admin_bar', 'bogopx_delete_links_cache');

/**
 * @action post_updated
 */
function bogopx_update_links_cache_if_changes($post_id, $post_after, $post_before) {
  $current_locale = get_post_meta($post_id, '_locale', true) ?: BOGO_DEFAULT_LOCALE;
  if (Bogo::is_default_locale($current_locale)) { return; }

  $has_updated_link_data = $post_before->post_title !== $post_after->post_title
    || $post_before->post_name !== $post_after->post_name
    || $post_before->post_status !== $post_after->post_status;

  // @todo - instead of deleting, get the transient, loop to find the index, and update the data
  if ($has_updated_link_data) {
    $parent_id = get_post_meta($post_id, '_original_post', true);
    bogopx_update_links_cache($parent_id, $current_locale, [
      'ID' => $post_id,
      'url' => get_permalink($post_id),
      'post_title' => $post_after->post_title,
      'post_type' => $post_after->post_type,
      'post_name' => $post_after->post_name,
      'post_status' => $post_after->post_status,
    ]);
  }
}


/**
 * @action bogo_after_duplicate_post
 */
function bogopx_update_links_cache_after_duplicate_post($new_post_id, $original_post, $locale) {
  bogopx_update_links_cache($original_post->ID, $locale, [
    'ID' => $new_post_id,
    'url' => get_permalink($new_post_id),
    'post_title' => $original_post->post_title,
    'post_type' => $original_post->post_type,
    'post_name' => $original_post->post_name,
    'post_status' => 'draft',
  ]);
}

/**
 * Delete the cache when clicking "Delete Cache" button from WP Super Cache plugin
 * 
 * @action wpsc_after_delete_cache_admin_bar
 */
function bogopx_delete_links_cache() {
  delete_transient('bogo_locale_groups');
}

/**
 * @param int $parent_id
 * @param string $locale - the language code like xx_XX
 * @param array $new_post_arr - the post data that's changed, accepted values: ID, url, post_title, post_name, post_type, post_status
 */
function bogopx_update_links_cache($parent_id, $locale, $new_post_arr) {
  $transient_key = 'bogo_locale_groups';
  $groups = get_transient($transient_key) ?: [];
  if (!$groups) { return; }

  // initiate the index if not exists
  if (!isset($groups[$parent_id])) {
    $groups[$parent_id] = [
      $locale = [],
    ];
  }
  elseif (isset($groups[$parent_id]) && !isset($groups[$parent_id][$locale])) {
    $groups[$parent_id][$locale] = [];
  }

  $groups[$parent_id][$locale] = array_merge($groups[$parent_id][$locale], $new_post_arr);
  set_transient($transient_key, $groups, 0);
}


/**
 * Create global variable so it's easier to find localized posts within other filters and actions.
 * 
 * @action init
 */
function bogo_init_global_link_groups() {
  $transient_key = 'bogo_locale_groups';
  $groups = get_transient($transient_key) ?: [];

  if (!$groups) {
    $groups = _bogo_query_locale_groups();
    set_transient($transient_key, $groups, 0);
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
    $post_type = isset($group[BOGO_DEFAULT_LOCALE]) ? $group[BOGO_DEFAULT_LOCALE]['post_type'] : '';
    if (!$post_type) { continue; }

    if (!isset($groups_by_pt[$post_type])) {
      $groups_by_pt[$post_type] = [];
    }

    foreach ($group as $locale => $link) {
      if (!isset($groups_by_pt[$post_type][$locale])) {
        $groups_by_pt[$post_type][$locale] = [];
      }

      $groups_by_pt[$post_type][$locale][] = $link;
    }
  }

  global $BOGO_GROUPS_BY_POST_TYPE;
  $BOGO_GROUPS_BY_POST_TYPE = $groups_by_pt;
}



/**
 * Query all locale posts and group them by original post ID
 * 
 * @return array
 */
function _bogo_query_locale_groups() {
  $groups = [];

  $posts = get_posts([
    'post_type' => Bogo::get_localizable_post_types(),
    'meta_query' => [[
      'key' => '_original_post',
      'compare' => 'EXISTS',
    ]],
    'post_status' => ['publish', 'draft'],
    'posts_per_page' => -1
  ]);

  // get homepage ID the raw way because get_option() returns null on locale page
  $home_id = get_transient('bogo_page_on_front');
  if ($home_id === false) {
    global $wpdb;
    $home_id = $wpdb->get_var("SELECT option_value FROM {$wpdb->options} WHERE option_name = 'page_on_front'");
    set_transient('bogo_page_on_front', $home_id, 86400 * 30); // 1 month cache
  }
  $home_id = (int) $home_id;

  foreach ($posts as $p) {
    $original_post_id = get_post_meta($p->ID, '_original_post', true);

    if (!isset($groups[$original_post_id])) {
      $groups[$original_post_id] = [];
    }

    $locale = get_post_meta($p->ID, '_locale', true) ?: BOGO_DEFAULT_LOCALE;
    $url = '';

    if ($home_id === $p->ID) {
      $url = trailingslashit(home_url());
    } else if ($home_id == $original_post_id) { // @warn - intentionally "==" because $original_post_id is a string
      $url = trailingslashit(home_url()) . bogo_lang_slug($locale);
    } else {
      $url = get_permalink($p->ID);
    }

    $groups[$original_post_id][$locale] = [
      'ID' => $p->ID,
      'locale' => $locale,
      'url' => $url,
      'post_title' => $p->post_title,
      'post_name' => $p->post_name,
      'post_type' => $p->post_type,
      'post_status' => $p->post_status,
    ];
  }

  return $groups;
}

/**
 * Get the translated version of a post by URL
 */
function bogo_localize_by_url($url) {
  $url_with_trailing_slash = preg_replace('/^([^?#]*[^\/?])([?#]|$)/', '$1/$2', $url);
  $parsed_url = parse_url($url_with_trailing_slash);

  // abort if anchor link (just #id)
  if (!isset($parsed_url['path'])) { return null; }

  // abort if special link like mailto: or tel: (no host but has scheme)
  if (!isset($parsed_url['host']) && isset($parsed_url['scheme'])) { return null; }

  // append domain name to dynamic URL (no scheme and host)
  if (!isset($parsed_url['scheme']) && !isset($parsed_url['host'])) {
    $parsed_url['scheme'] = ( ! empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off' ) ? 'https' : 'http';
    $parsed_url['host'] = $_SERVER['HTTP_HOST'];
  }

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

  if (isset($parsed_url['fragment'])) {
    $link['url'] .= "#{$parsed_url['fragment']}";
  }

  return $link ?: null;
}

/**
 * Get the translated links of a post by ID
 * 
 * @deprecated - replaced by Bogo::get_locale_link($id, $locale)
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
  // else, find if the given ID is a localized ID by looping through each group
  else {
    foreach ($BOGO_GROUPS_BY_ID as $group) {
      $ids_in_group = array_column($group, 'ID');
      if (in_array($id, $ids_in_group)) {
        $link = $group[$locale] ?? null;
        return $link;
      }
    }
  }

  return null;
}

/**
 * Query for the WP_Post object of the localized post using ID
 * 
 * @return WP_Post|null
 * 
 * @deprecated - use Bogo::get_locale_post($id) instead
 */
function bogo_localize_post_by_id($id, $force_locale = null) {
  $link = bogo_localize_by_id($id, $force_locale);

  if ($link && isset($link['id'])) {
    $p = get_post($link['id']);
    return $p;
  }

  return null;
}