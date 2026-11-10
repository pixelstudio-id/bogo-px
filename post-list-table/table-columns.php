<?php if (!defined('ABSPATH')) { exit; }

add_action('admin_init', 'bogo_register_post_table_hooks');

/////

/**
 * Register hooks to add and fill the Locale columns
 * 
 * @action admin_init
 */
function bogo_register_post_table_hooks() {
  $post_types = Bogo::get_localizable_post_types();

  foreach ($post_types as $pt) {
    $hook_add_column = "manage_{$pt}_posts_columns";
    $hook_fill_column = "manage_{$pt}_posts_custom_column";

    switch ($pt) {
      case 'post':
        $hook_add_column = 'manage_posts_columns';
        break;
      case 'page':
        $hook_add_column = 'manage_pages_columns';
        $hook_fill_column = 'manage_pages_custom_column';
        break;
    }

    add_filter($hook_add_column, function($columns) use ($pt) {
      return bogo_add_locale_column($columns, $pt);
    });
    add_action($hook_fill_column, 'bogo_fill_locale_column', 10, 2);
  }
}

/**
 * Add the Locale column to the post list table for localizable post types.
 * 
 * @filter manage_posts_columns
 */
function bogo_add_locale_column($posts_columns, $post_type) {
  if (!bogo_is_localizable_post_type($post_type)) { return $posts_columns; }

  // @todo - using $_GET conditional is bugged because on QuickEdit, the $_GET is always empty. Column shouldn't be conditional.
  $is_lang_filtered = isset($_GET['lang']) && ! Bogo::is_default_locale($_GET['lang']);
  $is_trash_view = isset($_GET['post_status']) && $_GET['post_status'] === 'trash';

  $locale_title = !$is_lang_filtered || $is_trash_view ? __('Locale', 'bogo') : __('Origin', 'bogo');

  // place it on 4th column
  if (!isset($posts_columns['locale'])) {
    $posts_columns = array_merge(
      array_slice( $posts_columns, 0, 3 ),
      ['locale' => $locale_title],
      array_slice( $posts_columns, 3 )
    );
  }

  return $posts_columns;
}

/**
 * Fill the Locale column for each post with flags
 * 
 * @action manage_posts_custom_column
 */
function bogo_fill_locale_column($column_name, $post_id) {
  if ($column_name !== 'locale') { return; }

  $post = get_post($post_id);
  $is_trash_view = isset($_GET['post_status']) && $_GET['post_status'] === 'trash';
  $locale = Bogo::get_locale($post_id);

  if ($is_trash_view) {
    echo _bogo_render_current_locale($post, $locale);
  }
  elseif (Bogo::is_default_locale($locale)) {
    echo _bogo_render_locale_column($post);
  }
  else {
    echo _bogo_render_origin_post_column($post_id, $locale);
  }
}

/**
 * Create button of flags to Edit/Create locale post
 */
function _bogo_render_locale_column($post) {
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

  // calculate flag per row (max 2 rows)
  $flags_per_row = count($accessible_locales) > 10
    ? round(count($accessible_locales) / 2)
    : count($accessible_locales);

  return "<div class='column-locale__inner' style='--flagsPerRow: {$flags_per_row}'> {$flags} </div>";
}

/**
 * Create the origin post for the list table showing all locale post
 */
function _bogo_render_origin_post_column($post_id, $locale) {
  if (Bogo::is_default_locale($locale)) { return '-'; }

  $link = Bogo::get_locale_link($post_id, BOGO_DEFAULT_LOCALE);

  if (!$link) {
    return '-';
  }

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
function _bogo_render_current_locale($post, $locale) {
  if (!$locale || Bogo::is_default_locale($locale)) {
    return '';
  }

  $language = bogo_get_language($locale) ?: $locale;
  return "<div class='bogo-current-locale'><i class='flag flag-{$locale}'></i> <span>{$language}</span></div>";
}
