<?php if (!defined('ABSPATH')) exit;

class BogoSwitcher {
  /**
   * Get the language switcher links.
   */
  static function get_links($args = '') {
    global $wp_query;

    $args = wp_parse_args( $args, array() );

    $locale = get_locale();

    $available_languages = bogo_available_languages();

    $translations = array();
    $is_singular = false;

    if (is_singular() || !empty($wp_query->is_posts_page)) {
      $translations = Bogo::get_locale_links(get_queried_object_id());
      $is_singular = true;
    }

    $switcher_links = array();

    foreach ( $available_languages as $code => $name ) {
      // @changed - skip if the locale is draft
      if (isset($translations[$code]) && $translations[$code]['post_status'] === 'draft') {
        continue;
      }
      
      $native_name = bogo_get_language_native_name( $code );

      if ( bogo_locale_is_alone( $code ) ) {
        $native_name = bogo_get_short_name( $native_name );
      }

      $link = array(
        'locale' => $code,
        'lang' => bogo_language_tag( $code ),
        'title' => $name,
        'native_name' => trim( $native_name ),
        'href' => '',
      );

      if (is_home()) {
        $posts_page = get_option('page_for_posts');
        if ($posts_page) {
          $has_any_post = Bogo::is_post_type_has_locale_post('post', $code);
          if ($has_any_post) {
            $link['href'] = isset($translations[$code]) ? $translations[$code]['url'] : '';
          }
        } else {
          $link['href'] = bogo_url(null, $code);
        }
      }
      elseif ($is_singular) {
        $link['href'] = $translations[$code]['url'] ?? '';
      }
      elseif (is_archive()) {
        global $posts;
        if (Bogo::is_default_locale($code)) {
          $link['href'] = bogo_url(null, $code);
        }
        elseif ($posts) {
          $post_type = $posts[0]->post_type;
          $has_any_post = Bogo::is_post_type_has_locale_post($post_type, $code);
          $link['href'] = $has_any_post ? bogo_url(null, $code) : '';
        }
      }
      else {
        $link['href'] = isset($translations[$code]) && !empty($translations[$code]['url'])
        ? $translations[$code]['url']
        : '';
        // @changed - if using the method below, it will show all locales on 404, search, etc.
        // $link['href'] = bogo_url(null, $code);
      }

      $switcher_links[] = $link;
    }

    return apply_filters( 'bogo_language_switcher_links', $switcher_links, $args );
  }

  /**
   * Get language switcher links with additional metadata.
   */
  static function get_detailed_links($atts) {
    $atts = wp_parse_args($atts, [
      'compact' => false,
    ]);

    $raw_links = self::get_links([ 'echo' => false ]);
    $links = [];

    foreach ($raw_links as $link) {
      if (empty($link['href'])) {
        continue;
      }

      $link['tooltip'] = sprintf(__('View %s translation', 'bogo'), $link['title']);
      $link['is_current'] = $link['locale'] === get_locale();
    
      if ($atts['compact']) {
        $slug = substr($link['locale'], 0, 2);

        $custom_slugs = get_option('bogopx_custom_slugs', []);
        if (isset($custom_slugs[$link['locale']])) {
          $slug = $custom_slugs[$link['locale']];
        }

        $slug = apply_filters('bogo_lang_slug', strtolower($slug));
        $link['lang_code'] = strtoupper($slug);
      }

      // if is_current, put the link at the beginning
      if ($link['is_current']) {
        array_unshift($links, $link);
      } else {
        $links[] = $link;
      }
    }

    return $links;
  }
}

/**
 * For compatibility with older version
 */
function bogo_language_switcher_links($args = '') {
  return BogoSwitcher::get_links($args);
}


/**
 * For compatibility with older version
 */
function bogoHelper_get_switcher_links($atts = []) {
  return BogoSwitcher::get_detailed_links($atts);
}