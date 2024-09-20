<?php
// @todo - adding the Locale column is quite heavy. Find a way to optimize it

add_filter('pre_get_posts', 'bogo_hide_translated_post_in_list_table');
add_action('admin_init', 'bogo_add_column_to_custom_post_type');


/////

/**
 * @filter pre_get_posts
 */
function bogo_hide_translated_post_in_list_table($query) {
  global $pagenow;
  if ($pagenow !== 'edit.php' || !$query->is_main_query()) { return $query; }

  // if has no 'lang' query, show only parent post
  $lang = get_query_var('lang');
  if (!$lang) {
    $query->set('meta_query', [
      'relation' => 'OR',
      [
        'key' => '_locale',
        'compare' => 'NOT EXISTS',
      ],
      [
        'key' => '_locale',
        'value' => get_locale(),
        'compare' => '=',
      ],
    ]);
  }

  return $query;
}

/**
 * Add "Locale" column in custom post types
 * 
 * @action admin_init
 */
function bogo_add_column_to_custom_post_type() {
  $post_types = bogo_localizable_post_types();
  foreach ($post_types as $pt) {
    if ($pt === 'post' || $pt === 'page') { continue; }

    add_filter("manage_{$pt}_posts_columns", function($columns) use ($pt) {
      return bogo_posts_columns($columns, $pt);
    }, 9999);
  }
}

/**
 * Create button of flags to Edit/Create locale post
 * 
 * @usedin admin/includes/post.php
 */
function bogo_create_admin_flag_buttons($post) {
  $post_id = $post->ID;
  $accessible_locales = bogo_get_user_accessible_locales();
  $accessible_locales = array_diff($accessible_locales, [get_locale()]);

  $accessible_posts = bogo_get_post_translations($post);

  $flags = '';
  foreach ($accessible_locales as $locale) {
    $language = bogo_get_language($locale) ?: $locale;

    $locale_post = isset($accessible_posts[$locale])
      ? $accessible_posts[$locale]
      : null;

    // if already has translation, create EDIT link
    if ($locale_post) {
      $href = get_edit_post_link($locale_post->ID);

      $post_status = $locale_post->post_status;
      $classes = "flag flag-{$locale} is-status-{$post_status}";
      $title = "Edit {$language} Translation";

      switch ($post_status) {
        case 'draft':
          $title = "[DRAFT] {$title}";
          break;
        case 'future':
          $title = "[SCHEDULED] {$title}";
      }

      $flags .= "<a href='{$href}' class='{$classes}' title='{$title}' target='_blank'></a>";
    }
    // if no translation, create ADD link
    else {
      $classes = "flag flag-{$locale}";
      $title = "Add {$language} Translation";
      $flags .= "<a class='{$classes}' title='{$title}' data-id='{$post_id}' data-locale='{$locale}'></a>";
    }
  }

  return "<div class='column-locale__inner'> {$flags} </div>";
}