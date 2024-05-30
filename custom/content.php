<?php

add_filter('the_content', 'bogo_localize_links_in_content', 20);

/**
 * @filter the_content
 */
function bogo_localize_links_in_content($content) {
  // abort if base locale
  if (bogo_is_base_locale()) { return $content; }

  $content = preg_replace_callback('/(<a.+href=")(.+)(".*>.+<\/a>)/Ui', function($matches) {
    $url = $matches[2];
    $locale_link = bogo_get_locale_link_by_url($url);

    if ($locale_link) {
      $url = $locale_link['url'];
    }

    return $matches[1] . $url . $matches[3];
  }, $content);

  return $content;
}