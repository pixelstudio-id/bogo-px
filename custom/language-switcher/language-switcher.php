<?php

add_shortcode('bogo-dropdown', 'bogopx_dropdown_shortcode');

add_action('enqueue_block_editor_assets', 'bogopx_localize_dropdown_args', 110);


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
      $current_label = $atts['compact'] ? $link['label_short'] : $link['label'];
    }
  }
  
  $wrapper_classes = '';
  if ($links_count >= 5) {
    $wrapper_classes = 'has-columns-2';
  } elseif ($links_count >= 9) {
    $wrapper_classes = 'has-columns-3';
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
          title="<?= esc_attr($link['title']) ?>"
        >
          <?= esc_html($link['label']) ?>
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
function bogopx_localize_dropdown_args() {
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
}