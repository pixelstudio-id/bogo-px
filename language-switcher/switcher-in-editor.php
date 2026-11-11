<?php if (!defined('ABSPATH')) exit;

add_action('enqueue_block_editor_assets', 'bogopx_localize_args_for_lang_dropdown', 110);

/**
 * @action enqueue_block_editor_assets
 */
function bogopx_localize_args_for_lang_dropdown() {
  // get current locale for dropdown
  global $post;
  $current_locale = get_post_meta($post->ID, '_locale', true) ?: BOGO_DEFAULT_LOCALE;

  $accessible_locales = bogo_get_user_accessible_locales();
  $accessible_links = array_merge(
    [ $current_locale => [
      'id' => $post->ID,
      'title' => $post->post_title,
      'post_status' => $post->post_status,
    ] ],
    Bogo::get_locale_links($post->ID),
  );

  $current_option = null;
  $options = [];
  $options_create = [];

  foreach ($accessible_locales as $locale) {
    $name = bogo_get_language($locale);
    // $name = bogo_get_language_native_name($locale);
    $name = trim(preg_replace('/\(.+\)/', '', $name));

    $link = $accessible_links[$locale] ?? null;

    if ($link && $locale === $current_locale) {
      $current_option = [
        'label' => $name,
        'locale' => $locale,
        'status' => $link['post_status'],
      ];
    } elseif ($link) {
      $options[] = [
        'url' => admin_url("post.php?post={$link['ID']}&action=edit"),
        'label' => $name,
        'locale' => $locale,
        'status' => $link['post_status'],
      ];
    } else {
      $options_create[] = [
        'url' => "/posts/{$post->ID}/translations/{$locale}",
        'label' => $name,
        'locale' => $locale,
      ];
    }
  }

  wp_localize_script('bogo-px-editor', 'bogoLanguageDropdown', [
    'currentOption' => $current_option,
    'options' => $options,
    'optionsCreate' => $options_create,
    'length' => count($options) + count($options_create),
  ]);
}