<?php

add_filter('the_content', 'bogo_localize_links_in_content', 20);
add_filter('pre_get_posts', 'bogo_only_show_translated_posts', 20);


/**
 * @filter the_content
 */
function bogo_localize_links_in_content($content) {
  if (Bogo::is_default_locale()) { return $content; }

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


/**
 * Add meta_query to only include posts that are translated
 * 
 * @filter pre_get_posts
 */
function bogo_only_show_translated_posts($query) {
  if (is_admin()) { return $query; }

  global $posts;
  $post_type = isset($posts[0]) ? $posts[0]->post_type : 'page';
  if ($post_type === 'page') { return $query; }

  // abort if 'post' because there's a bug with the main blog query
  // @todo - find a way to filter main posts query
  // if ($post_type === 'post') { return $query; }

  // abort if post_type is not localizable
  if (!in_array($post_type, Bogo::get_localizable_post_types())) { return $query; }
  
  $locale = get_locale();
  $meta_query = [
    [
      'key' => '_locale',
      'value' => $locale,
      'compare' => '=',
    ],
  ];

  if (Bogo::is_default_locale()) {
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