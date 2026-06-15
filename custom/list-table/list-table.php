<?php if (!defined('ABSPATH')) { exit; }

add_filter('pre_get_posts', 'bogopx_hide_translated_post_in_list_table');
add_filter('posts_join', 'bogopx_posts_join_for_default_locale_list', 20, 2);
add_filter('posts_where', 'bogopx_posts_where_for_default_locale_list', 20, 2);
// add_filter('the_posts', 'bogopx_prime_users_cache_for_list_table', 20, 2);
add_action('admin_init', 'bogopx_add_column_to_custom_post_type');

/////

/**
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
  }

  return $join;
}

/**
 * For the default locale list, we want to show only posts that have no locale or have the default locale.
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
    " AND (postmeta_bogopx_locale_filter.meta_id IS NULL OR postmeta_bogopx_locale_filter.meta_value = '' OR postmeta_bogopx_locale_filter.meta_value = %s)",
    $locale
  );

  return $where;
}

/**
 * Prime user objects for list-table rendering to avoid repeated wp_users lookups.
 *
 * @filter the_posts
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
 * Add "Locale" column in custom post types
 * 
 * @action admin_init
 */
function bogopx_add_column_to_custom_post_type() {
  $post_types = Bogo::get_localizable_post_types();

  foreach ($post_types as $pt) {
    if ($pt === 'post' || $pt === 'page') { continue; }

    add_filter("manage_{$pt}_posts_columns", function($columns) use ($pt) {
      return bogo_posts_columns($columns, $pt);
    });
    add_action("manage_{$pt}_posts_custom_column", 'bogo_manage_posts_custom_column', 10, 2);
  }
}

/**
 * Create button of flags to Edit/Create locale post
 * 
 * @usedin admin/includes/post.php
 */
function bogopx_create_admin_flag_buttons($post) {
  $post_id = $post->ID;
  static $accessible_locales = null;
  if ($accessible_locales === null) {
    $accessible_locales = bogo_get_user_accessible_locales();
  }
  $accessible_locales = array_diff($accessible_locales, [get_locale()]);

  $links = Bogo::get_locale_links($post_id);

  $flags = '';
  foreach ($accessible_locales as $locale) {
    $language = bogo_get_language($locale) ?: $locale;

    $link = isset($links[$locale])
      ? $links[$locale]
      : null;

    // if already has translation, create EDIT link
    if ($link) {
      $href = admin_url("post.php?post={$link['ID']}&action=edit");
      $post_status = $link['post_status'];

      $classes = "flag flag-{$locale} is-status-{$post_status}";
      $title = "Edit {$language} Translation";

      switch ($post_status) {
        case 'draft':
          $title = "[DRAFT] {$title}";
          break;
        case 'future':
          $title = "[SCHEDULED] {$title}";
          break;

        // @todo - after permanently deleted, it's still in cache so the button still show that it's trashed
        case 'trash':
          $title = "Deleted. Click here to completely remove or restore it.";
          $admin_url_args = 'edit.php?post_status=trash';
          if (isset($_GET['post_type'])) {
            $admin_url_args .= '&post_type=' . esc_attr($_GET['post_type']);
          }
          $href = admin_url($admin_url_args);
          break;
      }

      $flags .= "<a href='{$href}' class='{$classes}' title='{$title}' target='_blank'></a>";
    }
    // if no translation, create ADD link
    else {
      $classes = "flag flag-{$locale}";
      $title = "Add {$language} Translation";
      $flags .= "<a class='{$classes}' tabindex='0' title='{$title}' data-id='{$post_id}' data-locale='{$locale}'></a>";
    }
  }

  return "<div class='column-locale__inner'> {$flags} </div>";
}

/**
 * Create the origin post for the list table showing all locale post
 */
function bogopx_fill_origin_post_column($post_id, $locale) {
  if (Bogo::is_default_locale($locale)) { return '-'; }

  $link = Bogo::get_locale_link($post_id, BOGO_DEFAULT_LOCALE);

  $view_url = $link['url'];
  $edit_url = get_edit_post_link($link['ID']);
  $title = $link['post_title'];

  ob_start(); ?>

  <strong>
    <?= esc_html($title) ?>
  </strong>
  <div class="row-actions">
    <span>
      <a href="<?= esc_url($edit_url) ?>" target="_blank">
        <?= __('Edit') ?>
      </a>
      | 
    </span>
    <span>
      <a href="<?= esc_url($view_url) ?>" target="_blank">
        <?= __('View') ?>
      </a>
    </span>
  </div>

  <?php return ob_get_clean();
}

/**
 * Show one flag with its locale name. Skip if default locale
 */
function bogopx_fill_current_locale($post, $locale) {
  if (!$locale || Bogo::is_default_locale($locale)) {
    return '';
  }

  $language = bogo_get_language($locale) ?: $locale;
  return "<div class='bogo-current-locale'><i class='flag flag-{$locale}'></i> <span>{$language}</span></div>";
}