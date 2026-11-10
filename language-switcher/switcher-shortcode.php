<?php if (!defined('ABSPATH')) exit;

add_shortcode('bogo-switcher', 'bogopx_switcher_shortcode');
add_shortcode('bogo-dropdown', 'bogopx_switcher_shortcode');

/**
 * @shortcode [bogo-switcher style="popup" compact="0"]
 */
function bogopx_switcher_shortcode($atts, $content) {
  $atts = shortcode_atts([
    'style' => 'popup', // 'toggle'
    'compact' => '0', // '0' or '1'
  ], $atts);

  $atts['compact'] = $atts['compact'] === '1';
  $links = BogoSwitcher::get_detailed_links($atts);

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
