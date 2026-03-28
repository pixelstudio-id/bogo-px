<?php if (!defined('ABSPATH')) { exit; }

add_filter('pre_get_posts', 'bogopx_hide_translated_post_in_list_table');
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
        'value' => '',
        'compare' => '=',
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
  $accessible_locales = bogo_get_user_accessible_locales();
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
      $href = get_edit_post_link($link['ID']);
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