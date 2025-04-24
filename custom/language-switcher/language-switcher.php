<?php

add_shortcode('bogo-dropdown', 'bogopx_dropdown_shortcode');
add_action('enqueue_block_editor_assets', 'bogopx_localize_dropdown_args', 110);


/**
 * @shortcode [bogo-dropdown]
 */
function bogopx_dropdown_shortcode($atts, $content) {
  $atts = shortcode_atts([
    'style' => 'popup', // or 'toggle'
  ], $atts);

  $links = bogo_language_switcher_links([ 'echo' => false ]);

  $current_lang_name = '';
  $links_html = '';
  $locale_count = 0;

  foreach ($links as $link) {
    // $classes = bogo_language_tag($link['locale']) . ' ' . bogo_lang_slug($link['locale']);
    $label = $link['native_name'] ?: $link['title']; 

    if (empty($link['href'])) {
      // skip empty link
    }
    // if current link
    elseif ($link['locale'] === get_locale()) {
      $current_lang_name = $label;
      $links_html .= "<li class='is-current'>
        <a
          hreflang='{$link['lang']}'
          href='{$link['href']}'
        >
          {$label}
        </a>
      </li>";
    }
    else {
      $locale_count += 1;
      $links_html .= "<li>
        <a
          hreflang='{$link['lang']}'
          title='View {$link['title']} translation'
          href='{$link['href']}'
        >
          {$label}
        </a>
      </li>";
    }
  }

  // if no translation, return nothing
  if ($locale_count <= 0) {
    return '';
  }

  // if has 6 or more items, split to 2 columns
  $links_classes = '';
  if ($locale_count > 5) {
    $links_classes = 'has-columns-2';
  }

  return "<div class='bogo-dropdown is-style-{$atts['style']}'>
    <span class='bogo-dropdown__button' tabindex='0'>
      {$current_lang_name}
    </span>
    <ul class='bogo-dropdown__links {$links_classes}'>
      {$links_html}
    </ul>
  </div>";
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