<?php

add_shortcode('bogo-dropdown', 'bogo_dropdown_shortcode');

/**
 * @shortcode [bogo-dropdown]
 */
function bogo_dropdown_shortcode($atts, $content) {
  $atts = shortcode_atts([
    'style' => 'popup', // or 'toggle'
  ], $atts);

  $links = bogo_language_switcher_links([ 'echo' => false ]);
  $current_lang_name = '';
  $links_html = '';

  foreach ($links as $link) {
    // $classes = bogo_language_tag($link['locale']) . ' ' . bogo_lang_slug($link['locale']);
    $label = $link['native_name'] ?: $link['title']; 

    // if no link
    if (empty($link['href'])) {
      $links_html .= "<li class='has-no-link'>
        <a title='No {$link['title']} translation for this page'>
          {$label}
        </a>
      </li>";
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

  return "<div class='bogo-dropdown is-style-{$atts['style']}'>
    <span class='bogo-dropdown__button' tabindex='0'>
      {$current_lang_name}
    </span>
    <ul class='bogo-dropdown__links'>
      {$links_html}
    </ul>
  </div>";
}
