<?php
// @note - All `bogoHelper_do_something()` method can be shortened into `Bogo::do_something()`

/**
 * 
 */
function bogoHelper_get_switcher_links($atts = []) {
  $atts = wp_parse_args($atts, [
    'compact' => false,
  ]);

  $links = bogo_language_switcher_links([ 'echo' => false ]);

  foreach ($links as $i => $link) {
    if (empty($link['href'])) {
      unset($links[$i]);
      continue;
    }

    $link['name'] = $link['title'];
    $link['label'] = $link['native_name'] ?: $link['name'];
    $link['title'] = sprintf(__('View %s translation', 'bogo'), $link['name']);
    $link['is_current'] = $link['locale'] === get_locale();
  
    if ($atts['compact']) {
      $link['label_short'] = strtoupper(substr($link['locale'], 0, 2));
    }

    $links[$i] = $link;
  }

  $links = array_values($links); // reindex the array
  return $links;
}

/**
 * Get list of installed locales in this site
 */
function bogoHelper_get_available_locales() {
  return bogo_available_locales();
}

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
 * Get the group containing all translated links of a post
 */
function _bogoHelper_find_locale_group($post_id) {
  if ($post_id instanceof WP_Post) {
    $post_id = $post_id->ID;
  }

  global $BOGO_GROUPS_BY_ID;
  if (!$BOGO_GROUPS_BY_ID) { return null; }

  $found_group = $BOGO_GROUPS_BY_ID[$post_id] ?? null;

  // if default locale, but not admin, just return as is, no need to find group
  if (Bogo::is_default_locale() && !is_admin()) {
    return $found_group;
  }

  // if group is not found, try finding the original post ID
  if (!$found_group) {
    foreach ($BOGO_GROUPS_BY_ID as $group) {
      $ids_in_group = array_column($group, 'ID');
      if (in_array($post_id, $ids_in_group)) {
        $found_group = $group;
        break;
      }
    }
  }

  return $found_group;

  // find all the ID in case the given ID is a translated one
  // @todo - disabled because it's quite heavy to loop all groups everytime this function is called
  $is_admin_or_not_default_locale = is_admin() || !Bogo::is_default_locale();
  if (!$found_group && $is_admin_or_not_default_locale) {
    foreach ($BOGO_GROUPS_BY_ID as $group) {
      $ids_in_group = array_column($group, 'ID');
      if (in_array($post_id, $ids_in_group)) {
        return $group;
      }
    }
  }

  return $found_group;
}

/**
 * Get all the translated links of a post
 * 
 * @param int $post_id
 * @param bool $return_post_object - If true, return WP_Post object instead of array
 * 
 * @return array - Contains ID, url, post_title, locale, post_status, post_type.
 */
function bogoHelper_get_locale_links($post_id, $return_post_object = false) {
  $group = _bogoHelper_find_locale_group($post_id);
  if (!$group) { return []; }

  if ($return_post_object) {
    $post_ids = array_column($group, 'ID');
    $posts = get_posts([
      'post_type' => 'any',
      'post__in' => $post_ids,
      'orderby' => 'post__in',
      'posts_per_page' => -1,
    ]);
    return $posts;
  }

  return $group;
}

/**
 * Alias of get_locale_links() with 2nd parameter set to true
 */
function bogoHelper_get_locale_posts($post_id) {
  return bogoHelper_get_locale_links($post_id, true);
}

/**
 * Get a certain locale link of a Post
 * 
 * @param int $post_id
 * @param string? $locale - If null, will use the current locale
 * @param bool? $return_post_object - If true, return WP_Post object instead of array
 * 
 * @return array|WP_Post - null if not found
 */
function bogoHelper_get_locale_link($post_id, $locale = null, $return_post_object = false) {
  $group = _bogoHelper_find_locale_group($post_id);
  if (!$group) { return null; }

  $locale = $locale ?: get_locale();

  // if ($locale) {
  //   switch_to_locale($locale);
  //   $locale = determine_locale();
  // } else {
  //   $locale = get_locale();
  // }

  $link = $group[$locale] ?? null;

  if ($return_post_object && $link) {
    return get_post($link['ID']);
  }

  return $link;
}

/**
 * Alias of get_locale_link() with 3rd parameter set to true
 */
function bogoHelper_get_locale_post($post_id, $locale = null) {
  return bogoHelper_get_locale_link($post_id, $locale, true);
}

/**
 * Check if a post type has any translated post
 */
function bogoHelper_is_post_type_has_locale_post($post_type, $locale = BOGO_DEFAULT_LOCALE) {
  global $BOGO_GROUPS_BY_POST_TYPE;
  $groups = $BOGO_GROUPS_BY_POST_TYPE; // shortener
  return isset($groups[$post_type][$locale]) && is_array($groups[$post_type]) && count($groups[$post_type][$locale]) > 0;
}


/**
 * Give all translated versions of that post
 * 
 * @deprecated - replaced by bogoHelper_get_locale_links()
 */
function bogoHelper_get_post_translations($post_id, $return_post_object = true) {
  if (!Bogo::is_default_locale()) {
    $post_id = (int) get_post_meta($post_id, '_original_post', true);
  }

  global $BOGO_GROUPS_BY_ID;
  $group = $BOGO_GROUPS_BY_ID[$post_id] ?? [];

  if ($return_post_object) {
    $post_ids = array_column($group, 'ID');
    $posts = get_posts([
      'post_type' => 'any',
      'post__in' => $post_ids,
      'orderby' => 'post__in',
      'posts_per_page' => -1,
    ]);
    return $posts;
  }

  return $group;
}