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
 *
 */
function bogoHelper_get_localizable_post_types() {
  static $post_types = [];
  if (!$post_types) {
    $post_types = apply_filters('bogo_localizable_post_types', ['post', 'page']);
    $post_types = array_diff($post_types, ['attachment', 'revision', 'nav_menu_item']);
  }

  return $post_types;
}

/**
 * Give all translated versions of that post
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