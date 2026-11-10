<?php if (!defined('ABSPATH')) exit;

add_filter('pre_get_posts', 'bogopx_hide_translated_post_in_list_table');
add_filter('posts_join', 'bogopx_posts_join_for_default_locale_list', 20, 2);
add_filter('posts_where', 'bogopx_posts_where_for_default_locale_list', 20, 2);
// add_filter('the_posts', 'bogopx_prime_users_cache_for_list_table', 20, 2);

/**
 * Hide translated posts in the list table for the default locale.
 * 
 * @filter pre_get_posts
 */
function bogopx_hide_translated_post_in_list_table($query) {
  global $pagenow;
  if ($pagenow !== 'edit.php' || !$query->is_main_query()) { return $query; }

  $is_trash_view = isset($_GET['post_status']) && $_GET['post_status'] === 'trash';
  if ($is_trash_view) { return $query; }

  $post_type = $query->get('post_type') ?: 'post';
  if (is_array($post_type)) {
    $post_type = reset($post_type);
  }
  if (!bogo_is_localizable_post_type($post_type)) { return $query; }

  // If no explicit lang filter is selected, default to current locale.
  // For default locale we use a custom lightweight SQL condition to avoid
  // expensive WP_Meta_Query OR + NOT EXISTS join expansion.
  $lang = get_query_var('lang');
  if (!$lang) {
    $current_locale = get_locale();

    if (Bogo::is_default_locale($current_locale)) {
      $query->set('bogopx_default_locale_list_filter', true);
      $query->set('bogo_suppress_locale_query', true);
    } else {
      $query->set('lang', $current_locale);
    }
  }

  return $query;
}

/**
 * For the default locale list, we want to show only posts that have no locale or have the default locale.
 * 
 * @filter posts_join
 */
function bogopx_posts_join_for_default_locale_list($join, $query) {
  global $wpdb;

  if (!$query->get('bogopx_default_locale_list_filter')) {
    return $join;
  }

  if (false === strpos($join, 'postmeta_bogopx_locale_filter')) {
    $join .= " LEFT JOIN {$wpdb->postmeta} AS postmeta_bogopx_locale_filter"
      . " ON ({$wpdb->posts}.ID = postmeta_bogopx_locale_filter.post_id"
      . " AND postmeta_bogopx_locale_filter.meta_key = '_locale')";
    
    // Ensure necessary index exists for performance
    _bogo_ensure_postmeta_index_for_locale_filter();
  }

  return $join;
}

/**
 * For the default locale list, we want to show only posts that have no locale or have the default locale.
 * Optimized: Use post_id IS NULL instead of meta_id IS NULL, and use IN() for better index usage
 * 
 * @filter posts_where
 */
function bogopx_posts_where_for_default_locale_list($where, $query) {
  global $wpdb;

  if (!$query->get('bogopx_default_locale_list_filter')) {
    return $where;
  }

  $locale = get_locale();
  $where .= $wpdb->prepare(
    " AND (postmeta_bogopx_locale_filter.post_id IS NULL OR postmeta_bogopx_locale_filter.meta_value IN ('', %s))",
    $locale
  );

  return $where;
}


/**
 * Prime user objects for list-table rendering to avoid repeated wp_users lookups.
 *
 * @filter the_posts
 * @deprecated - Use the built-in WordPress object caching instead.
 */
function bogopx_prime_users_cache_for_list_table($posts, $query) {
  global $pagenow;

  if (!is_admin() || $pagenow !== 'edit.php' || !$query->is_main_query()) {
    return $posts;
  }

  $user_ids = [];
  $current_user_id = get_current_user_id();
  if ($current_user_id) {
    $user_ids[] = (int) $current_user_id;
  }

  foreach ((array) $posts as $post) {
    if (!empty($post->post_author)) {
      $user_ids[] = (int) $post->post_author;
    }
  }

  $user_ids = array_values(array_unique(array_filter($user_ids)));
  if (!empty($user_ids) && function_exists('cache_users')) {
    cache_users($user_ids);
  }

  return $posts;
}


/**
 * Ensure postmeta table has the necessary index for locale filtering queries
 * This prevents slow queries by allowing MySQL to efficiently filter on post_id and meta_key
 * 
 * @internal
 */
function _bogo_ensure_postmeta_index_for_locale_filter() {
  global $wpdb;
  static $index_checked = false;

  if ($index_checked) {
    return;
  }

  $index_checked = true;

  // Check if index already exists
  $indexes = $wpdb->get_results(
    "SHOW INDEXES FROM {$wpdb->postmeta} WHERE Key_name = 'idx_post_id_meta_key_bogo'"
  );

  if (!empty($indexes)) {
    return;
  }

  // Create the index if it doesn't exist (limit meta_key to 100 chars to stay within index size limits)
  $wpdb->query(
    "ALTER TABLE {$wpdb->postmeta} ADD INDEX idx_post_id_meta_key_bogo (post_id, meta_key(100))"
  );
}