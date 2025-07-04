<?php

/**
 * Returns true if the specified locale is the default locale.
 *
 * @param string $locale Locale code.
 */
function bogoHelper_is_default_locale($locale = null) {
	if ($locale) {
		return $locale === BOGO_DEFAULT_LOCALE;
	} else {
		return get_locale() === BOGO_DEFAULT_LOCALE;
	}
}

/**
 * Get all localizable post types
 * 
 * @return array
 */
function bogoHelper_get_localizable_post_types() {
  static $post_types = [];
  if (!$post_types) {
    $post_types = apply_filters('bogo_localizable_post_types', ['post', 'page', 'wp_block']);
    $post_types = array_diff($post_types, ['attachment', 'revision', 'nav_menu_item']);
  }

  return $post_types;
}

/**
 * Check if a post type is localizable
 * 
 * @param string $post_type
 * @return bool
 */
function bogoHelper_is_localizable_post_type($post_type) {
  $localizable_post_types = bogoHelper_get_localizable_post_types();
  return in_array($post_type, $localizable_post_types, true);
}

/**
 * Get the original post by one of its locale ID
 * 
 * @param int $post_id - ID of one of the translated post
 * @return array - Contains URL, ID, and WP_Post object of the original post
 */
function bogoHelper_get_original_post_by_locale_id($post_id) {
  global $BOGO_GROUPS_BY_ID;
  $original_id = null;

  foreach ($BOGO_GROUPS_BY_ID as $id => $group) {
    $locale_ids = array_column($group, 'id');
    
    if (in_array($post_id, $locale_ids)) {
      $original_id = $id;
      break;
    }
  }

  if ($original_id) {
    $original_post = $BOGO_GROUPS_BY_ID[$original_id][BOGO_DEFAULT_LOCALE];
    return $original_post;
  }
}

/**
 * Give all translated versions of that post
 * 
 * @todo - might be bugged because sometimes _original_post is empty on its parent post
 */
function bogoHelper_get_post_translations($post_id, $return_raw_post = true) {
  $original_post_id = (int) get_post_meta($post_id, '_original_post', true);
  global $BOGO_GROUPS_BY_ID;
  $group = $BOGO_GROUPS_BY_ID[$original_post_id] ?? [];

  if ($return_raw_post) {
    $group = array_map(function($p) {
      return $p['post'];
    }, $group);
  }

  return $group;
}

/**
 * Check if a post type has any translated post
 */
function bogoHelper_is_post_type_has_locale_post($post_type, $locale = BOGO_DEFAULT_LOCALE) {
  global $BOGO_GROUPS_BY_POST_TYPE;
  $groups = $BOGO_GROUPS_BY_POST_TYPE; // shortener
  return isset($groups[$post_type][$locale]) && is_array($groups[$post_type]) && count($groups[$post_type][$locale]) > 0;
}