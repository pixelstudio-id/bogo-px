<?php if (!defined('ABSPATH')) exit;

// deprecated - use the [bogo-switcher] shortcode instead

add_shortcode('bogo', 'bogo_shortcode_callback');

/**
 * [bogo]
 */
function bogo_shortcode_callback($atts, $content) {
	$atts = shortcode_atts([
		'view' => 'list',
	], $atts);
  return BogoSwitcher::render_list('echo=0');
}

/**
 * Get the HTML markup to list available language options
 */
function bogo_language_switcher($args = '') {
  $args = wp_parse_args( $args, array(
      'echo' => false,
    ) );

    $links = BogoSwitcher::get_links( $args );
    $total = count( $links );
    $count = 0;

    $output = '';

    foreach ( $links as $link ) {
      $count += 1;
      $class = array();
      $class[] = bogo_language_tag( $link['locale'] );
      $class[] = bogo_lang_slug( $link['locale'] );

      if ( get_locale() === $link['locale'] ) {
        $class[] = 'current';
      }

      if ( 1 == $count ) {
        $class[] = 'first';
      }

      if ( $total == $count ) {
        $class[] = 'last';
      }

      $class = implode( ' ', array_unique( $class ) );

      $label = $link['native_name'] ? $link['native_name'] : $link['title'];
      $title = $link['title'];

      if ( empty( $link['href'] ) ) {
        $li = esc_html( $label );
      } else {
        $atts = array(
          'rel' => 'alternate',
          'hreflang' => $link['lang'],
          'href' => esc_url( $link['href'] ),
          'title' => $title,
        );

        if ( get_locale() === $link['locale'] ) {
          $atts += array(
            'class' => 'current',
            'aria-current' => 'page',
          );
        }

        $li = sprintf(
          '<a %1$s>%2$s</a>',
          bogo_format_atts( $atts ),
          esc_html( $label )
        );
      }

      $li = sprintf(
        '<span class="bogo-language-name">%s</span>',
        $li
      );

      if ( apply_filters( 'bogo_use_flags', true ) ) {
        $country_code = bogo_get_country_code( $link['locale'] );

        $flag = sprintf(
          '<span class="bogoflags bogoflags-%s"></span>',
          $country_code ? strtolower( $country_code ) : 'zz'
        );

        $li = sprintf( '<li class="%1$s">%3$s %2$s</li>', $class, $li, $flag );
      } else {
        $li = sprintf( '<li class="%1$s">%2$s</li>', $class, $li );
      }

      $output .= $li . "\n";
    }

    $output = sprintf(
      '<ul class="bogo-language-switcher list-view">%s</ul>',
      $output
    );

    $output = apply_filters( 'bogo_language_switcher', $output, $args );

    if ( $args['echo'] ) {
      echo $output;
    } else {
      return $output;
    }
  }
}