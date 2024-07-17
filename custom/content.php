<?php

add_filter('the_content', 'bogo_localize_links_in_content', 20);
add_filter('pre_get_posts', 'bogo_only_show_translated_posts', 20);

/**
 * Add meta_query to only include posts that are translated
 * 
 * @filter pre_get_posts
 */
function bogo_only_show_translated_posts($query) {
  if (is_admin()) { return $query; }

  // abort if 'post' because there's a bug with the main blog query
  $post_type = $query->get('post_type');
  if ($post_type === 'post' || $post_type === 'page') { return $query; }

  // abort if post_type is not localizable
  if (!in_array($post_type, bogo_localizable_post_types())) { return $query; }
  
  $locale = get_locale();
  $meta_query = [
    [
      'key' => '_locale',
      'value' => $locale,
      'compare' => '=',
    ],
  ];

  if ($locale === BOGO_DEFAULT_LOCALE) {
    $meta_query['relation'] = 'OR';
    $meta_query[] = [
      'key' => '_locale',
      'compare' => 'NOT EXISTS',
    ];
  }

  // If has old meta query, combine them
  $old_meta_query = $query->get('meta_query');
  if ($old_meta_query) {
    $old_meta_query['relation'] = 'AND';
    $old_meta_query[] = count($meta_query) === 1 ? $meta_query[0] : $meta_query;
    $query->set('meta_query', $old_meta_query);
  }
  else {
    $query->set('meta_query', $meta_query);
  }
  
  return $query;
}

/**
 * @filter the_content
 */
function bogo_localize_links_in_content($content) {
  if (bogo_is_default_locale()) { return $content; }

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