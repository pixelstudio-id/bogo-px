<?php

add_filter('pre_get_posts', 'bogo_hide_translated_post_in_list_table');
add_filter('pre_get_posts', 'bogo_hide_translated_post_in_menu_editor');

/**
 * @filter pre_get_posts
 */
function bogo_hide_translated_post_in_list_table($query) {
  global $pagenow;
  if ($pagenow !== 'edit.php' || !$query->is_main_query()) { return $query; }

  // if has no 'lang' query, show only parent post
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
 *
 */
function bogo_hide_translated_post_in_menu_editor($query) {
  global $pagenow;
  if ($pagenow !== 'nav-menus.php') { return $query; }

  // TODO: can cause bug when installing extra plugins that add extra query before
  $junk_query_types = [
    'acf-taxonomy',
    'acf-post-type',
    'acf-ui-options-page',
    'acf-field-group',
    'acf-field',
    'wpcf7_contact_form',
    'wp_block',
    'wp_template',
    'wp_template_part',
    'wp_global_styles',
    'wp_navigation',
    'nav_menu_item',
    'nav_menu_item',
    'wp_global_styles',
  ];

  if (in_array($query->query['post_type'], $junk_query_types)) { return $query; }

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

  return $query;
}

/**
 * Create button of flags to Edit/Create locale post
 */
function bogo_create_admin_flag_buttons($post) {
  $post_id = $post->ID;
  $available_locales = bogo_available_locales();
  $available_locales = array_diff($available_locales, [get_locale()]);
  $available_translations = bogo_get_post_translations($post);

  $flags = '';
  foreach ($available_locales as $locale) {
    preg_match('/_(\w+)/', $locale, $matches);
    $country_id = isset($matches[1]) ? strtolower($matches[1]) : 'us';
    $language = bogo_get_language($locale) ?: $locale;

    $locale_post = isset($available_translations[$locale])
      ? $available_translations[$locale]
      : null;

    // if already has translation, create EDIT link
    if ($locale_post) {
      $href = esc_url(add_query_arg([
        'post' => $locale_post->ID,
        'action' => 'edit',
      ], 'post.php'));

      $post_status = $locale_post->post_status;
      $classes = "fi fi-{$country_id} is-status-{$post_status}";
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
      $classes = "fi fi-{$country_id}";
      $title = "Add {$language} Translation";
      $flags .= "<a class='{$classes}' title='{$title}' data-id='{$post_id}' data-locale='{$locale}'></a>";
    }
  }

  return "<div class='column-locale__inner'> {$flags} </div>";
}