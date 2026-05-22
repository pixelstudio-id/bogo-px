<?php

add_shortcode('bogo-dropdown', 'bogopx_dropdown_shortcode');

add_action('enqueue_block_editor_assets', 'bogopx_localize_args_for_lang_dropdown', 110);


/**
 * @shortcode [bogo-dropdown]
 */
function bogopx_dropdown_shortcode($atts, $content) {
  $atts = shortcode_atts([
    'style' => 'popup', // 'toggle'
    'compact' => '0', // '0' or '1'
  ], $atts);

  $atts['compact'] = $atts['compact'] === '1';
  $links = Bogo::get_switcher_links($atts);

  // abort if no links
  $links_count = count($links);
  if ($links_count <= 0) { return ''; }

  $current_label = '';
  foreach ($links as $i => $link) {
    if ($link['is_current']) {
      $current_label = $atts['compact'] ? $link['lang_code'] : $link['native_name'];
    }
  }

  $wrapper_classes = '';
  if ($links_count >= 9) {
    $wrapper_classes = 'has-columns-3';
  } elseif ($links_count >= 5) {
    $wrapper_classes = 'has-columns-2';
  }

  ob_start(); ?>

  <div class="bogo-dropdown is-style-<?= $atts['style'] ?>">
    <span class="bogo-dropdown__button" tabindex="0">
      <?= esc_html($current_label) ?>
    </span>
    <ul class="bogo-dropdown__links <?= $wrapper_classes ?>">
    <?php foreach ($links as $link): ?>  
      <li class="<?= $link['is_current'] ? 'is-current' : '' ?>">
        <a
          hreflang="<?= esc_attr($link['lang']) ?>"
          href="<?= esc_url($link['href']) ?>"
          title="<?= esc_attr($link['tooltip']) ?>"
        >
          <?= esc_html($link['native_name']) ?>
        </a>
      </li>
    <?php endforeach ?>
    </ul>
  </div>

  <?php return ob_get_clean();
}

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

  wp_localize_script('bogopx-editor', 'bogoLanguageDropdown', [
    'currentOption' => $current_option,
    'options' => $options,
    'optionsCreate' => $options_create,
    'length' => count($options) + count($options_create),
  ]);
}