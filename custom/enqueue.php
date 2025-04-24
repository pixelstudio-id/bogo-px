<?php

add_action('wp_enqueue_scripts', 'bogo_public_enqueue_scripts_custom', 10);
add_action('admin_enqueue_scripts', 'bogo_admin_enqueue_scripts_custom', 10);
add_action('enqueue_block_editor_assets', 'bogo_editor_enqueue_scripts_custom', 100);

/**
 * @action wp_enqueue_scripts
 */
function bogo_public_enqueue_scripts_custom() {
  wp_enqueue_script('bogopx-public', BOGO_DIST . '/bogopx-public.js', [], BOGO_VERSION, true);
  wp_enqueue_style('bogopx-public', BOGO_DIST . '/bogopx-public.css', [], BOGO_VERSION);
}

/**
 * @action admin_enqueue_scripts
 */
function bogo_admin_enqueue_scripts_custom() {
  wp_enqueue_script('bogopx-admin', BOGO_DIST . '/bogopx-admin.js', [], BOGO_VERSION, true);
  wp_enqueue_style('bogopx-admin', BOGO_DIST . '/bogopx-admin.css', [], BOGO_VERSION);

  wp_localize_script('bogopx-admin', 'bogoApiSettings', [
    'root' => esc_url_raw(rest_url('bogo/v1')),
    'namespace' => 'bogo/v1',
    'nonce' => wp_create_nonce('wp_rest'),
  ]);
}

/**
 * @action enqueue_block_editor_assets
 */
function bogo_editor_enqueue_scripts_custom() {
  if (!is_admin()) { return; }

  wp_enqueue_script('bogopx-editor', BOGO_DIST . '/bogopx-editor.js', [ 'wp-blocks', 'wp-dom' ] , BOGO_VERSION, true);
  wp_enqueue_style('bogopx-editor', BOGO_DIST . '/bogopx-editor.css', [ 'wp-edit-blocks' ], BOGO_VERSION);
 
  // get current locale for dropdown
  global $post;
  $current_locale = get_post_meta($post->ID, '_locale', true) ?: BOGO_DEFAULT_LOCALE;

  $accessible_locales = bogo_get_user_accessible_locales();
  $accessible_posts = array_merge(
    [ $current_locale => $post ],
    Bogo::get_post_translations($post->ID),
  );

  $current_option = null;
  $options = [];
  $options_create = [];

  foreach ($accessible_locales as $locale) {
    $locale_name = bogo_get_language_native_name($locale);
    $locale_name = trim(preg_replace('/\(.+\)/', '', $locale_name));

    $p = $accessible_posts[$locale] ?? null;

    if ($p && $locale === $current_locale) {
      $current_option = [
        'label' => $locale_name,
        'locale' => $locale,
        'status' => $p->post_status,
      ];
    } elseif ($p) {
      $options[] = [
        'url' => get_edit_post_link($p->ID),
        'label' => $locale_name,
        'locale' => $locale,
        'status' => $p->post_status,
      ];
    } else {
      $options_create[] = [
        'url' => "/posts/{$post->ID}/translations/{$locale}",
        'label' => $locale_name,
        'locale' => $locale,
      ];
    }
  }

  wp_localize_script('bogopx-editor', 'bogoLanguageDropdown', [
    'currentOption' => $current_option,
    'options' => $options,
    'optionsCreate' => $options_create,
    'length' => count($options) + count($options_create),
  ]);

  wp_localize_script('bogopx-editor', 'bogoApiSettings', [
    'root' => esc_url_raw(rest_url('bogo/v1')),
    'namespace' => 'bogo/v1',
    'nonce' => wp_create_nonce('wp_rest'),
  ]);
}