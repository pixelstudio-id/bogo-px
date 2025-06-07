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

  $links = bogo_language_switcher_links([ 'echo' => false ]);
  $is_compact = $atts['compact'] === '1';

  $wrapper_classes = '';
  $current_lang_name = '';
  $locale_count = 0;

  foreach ($links as $i => $link) {
    $link['label'] = $link['native_name'] ?: $link['title'];
    $link['classes'] = '';
    $link['title_attr'] = '';
 
    if ($link['locale'] === get_locale()) {
      $current_lang_name = $is_compact ? strtoupper(substr(get_locale(), 0, 2)) : $link['label'];
      $link['classes'] = 'is-current';
    }
    else if (!empty($link['href'])) {
      $locale_count += 1;
      $link['title_attr'] = "View {$link['title']} translation";
    }

    $links[$i] = $link;
  }

  // if no translation, return nothing
  if ($locale_count <= 0) {
    return '';
  }

  // if has 6 or more items, split to 2 columns
  if ($locale_count >= 5) {
    $wrapper_classes = 'has-columns-2';
  }

  ob_start(); ?>

  <div class="bogo-dropdown is-style-<?= $atts['style'] ?>">
    <span class="bogo-dropdown__button" tabindex="0">
      <?= esc_html($current_lang_name) ?>
    </span>
    <ul class="bogo-dropdown__links <?= $wrapper_classes ?>">
    <?php foreach ($links as $link): if (!empty($link['href'])): ?>  
      <li class="<?= esc_attr($link['classes']) ?>">
        <a
          hreflang="<?= esc_attr($link['lang']) ?>"
          href="<?= esc_url($link['href']) ?>"
          title="<?= esc_attr($link['title_attr']) ?>"
        >
          <?= esc_html($link['label']) ?>
        </a>
      </li>
    <?php endif; endforeach ?>
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