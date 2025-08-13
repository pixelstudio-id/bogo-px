<?php

add_action('init', 'bogo_init_global_link_groups');

add_action('save_post', 'bogopx_reset_global_link_groups_on_save', 100, 3);
add_action('post_updated', 'bogopx_reset_global_link_groups_on_update', 100, 3);

/**
 * @action save_post
 */
function bogopx_reset_global_link_groups_on_save($post_id, $post, $update) {
  // Avoid running on autosave/revisions
  if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) { return; }
  if ($update) { return; }

  // @todo - when copying a post, the _locale is still not set, so this will always fail
  // $current_locale = get_post_meta($post_id, '_locale', true) ?: BOGO_DEFAULT_LOCALE;
  // if (Bogo::is_default_locale($current_locale)) { return; }

  delete_transient('bogo_locale_groups');
}


/**
 * @action post_updated
 */
function bogopx_reset_global_link_groups_on_update($post_id, $post_after, $post_before) {
  $current_locale = get_post_meta($post_id, '_locale', true) ?: BOGO_DEFAULT_LOCALE;
  if (Bogo::is_default_locale($current_locale)) { return; }

  $has_updated_link_data = $post_before->post_title !== $post_after->post_title
    || $post_before->post_name !== $post_after->post_name
    || $post_before->post_status !== $post_after->post_status;

  // @todo - instead of deleting, get the transient, loop to find the index, and update the data
  if ($has_updated_link_data) {
    delete_transient('bogo_locale_groups');
  }
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
    set_transient($transient_key, $groups, 86400); // 24 hour cache
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