<?php

add_filter('the_content', 'bogopx_localize_links_in_content', 20);
add_filter('pre_get_posts', 'bogopx_prevent_base_post_overriden_with_locale_post', 15);
add_filter('pre_get_posts', 'bogopx_fix_posts_from_all_locale_displayed', 20);

add_filter('home_url', 'bogopx_add_trailing_slash_to_home_url_in_search', 10, 4);

/**
 * Replace all links in content with localized version, if any
 * 
 * @filter the_content
 */
function bogopx_localize_links_in_content($content) {
  if (Bogo::is_default_locale()) { return $content; }

  $content = preg_replace_callback('/(<a.+href=")(.+)(".*>.+<\/a>)/Ui', function($matches) {
    $url = $matches[2];
    $locale_obj = bogo_localize_by_url($url);

    if ($locale_obj) {
      $url = $locale_obj['url'];
    }

    return $matches[1] . $url . $matches[3];
  }, $content);

  return $content;
}

/**
 * Prevent the content of locale post overriding the base post if it has the same slug
 * 
 * @filter pre_get_posts
 */
function bogopx_fix_posts_from_all_locale_displayed($query) {
  if (is_admin()) { return $query; }

  $is_single_query = $query->is_main_query() && $query->is_single();
  if (!$is_single_query) { return $query; }
  
  $is_single_base_locale = empty($query->query['page']) && Bogo::is_default_locale();
  if (!$is_single_base_locale) { return $query; }

  $meta_query = [
    'relation' => 'OR',
    [
      'key' => '_locale',
      'compare' => 'NOT EXISTS',
    ],
    [
      'key' => '_locale',
      'value' => '',
      'compare' => '=',
    ],
    [
      'key' => '_locale',
      'value' => get_locale(),
      'compare' => '=',
    ],
  ];

  $query->set('meta_query', $meta_query);
  return $query;
}

/**
 * Add meta_query to only include base language posts
 * 
 * @filter pre_get_posts
 */
function bogopx_fix_posts_from_all_lang_displayed($query) {
  if (is_admin()) { return $query; }

  $post_type = $query->get('post_type') ?: 'post';
  if ($post_type === 'page' || !Bogo::is_localizable_post_type($post_type)) {
    return $query;
  }

  if (!Bogo::is_default_locale()) {
     return $query;
  }

  
  // global $posts;
  // if (!$posts) { return $query; }
  
  // @todo - can be bugged if there's only 1 post in the loop
  // if (is_array($posts) && count($posts) <= 1) {
  //   return $query;
  // }

  // abort if 'post' because there's a bug with the main blog query
  // @todo - find a way to filter main posts query
  // if ($post_type === 'post') { return $query; }

  // abort if post_type is not localizable
  // if (!Bogo::is_localizable_post_type($post_type)) { return $query; }
  
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
    $meta_query[] = [
      'key' => '_locale',
      'value' => '',
      'compare' => '=',
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
 * Search bar needs to have trailing slash in the <form> for it to detect the locale
 * 
 * @filter home_url
 */
function bogopx_add_trailing_slash_to_home_url_in_search($url) {
  if (!is_search()) { return $url; }

  return trailingslashit($url);
}
