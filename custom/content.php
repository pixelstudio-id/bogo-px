<?php

add_filter('the_content', 'bogopx_localize_links_in_content', 20);
// add_filter('pre_get_posts', 'bogopx_prevent_base_post_overriden_with_locale_post', 15);
add_filter('pre_get_posts', 'bogopx_fix_posts_from_all_locale_displayed', 20);

add_action('template_redirect', 'bogopx_set_404_to_empty_locale');

/**
 * @action template_redirect
 * 
 * @warn - might show 404 to search or blog if no post is found
 */
function bogopx_set_404_to_empty_locale() {
  global $post;

  if (!$post) {
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    nocache_headers();
    $template_404 = get_404_template();
    if ($template_404) {
      include($template_404);
    } else {
      echo '<h2>404 Not Found</h2>';
    }
    exit;
  }
}

/**
 * Replace all links in content with localized version, if any
 * 
 * @filter the_content 20
 */
function bogopx_localize_links_in_content($content) {
  if (Bogo::is_default_locale() || is_admin() || is_feed()) { return $content; }
  if (stripos($content, '<a') === false || stripos($content, 'href=') === false) { return $content; }

  $processor = new WP_HTML_Tag_Processor($content);
  $localized_url_cache = [];

  while ($processor->next_tag('a')) {
    $url = $processor->get_attribute('href');
    if (!$url) { continue; }

    if (!isset($localized_url_cache[$url])) {
      $locale_link = bogo_localize_by_url($url);
      $localized_url_cache[$url] = $locale_link ? $locale_link['url'] : $url;
    }

    $new_url = $localized_url_cache[$url];
    if ($new_url !== $url) {
      $processor->set_attribute('href', $new_url);
    }
  }

  return $processor->get_updated_html();
}

/**
 * Prevent the content of locale post overriding the base post if it has the same slug
 * 
 * @filter pre_get_posts
 */
function bogopx_prevent_base_post_overriden_with_locale_post($query) {
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
function bogopx_fix_posts_from_all_locale_displayed($query) {
  if (is_admin()) { return $query; }

  $post_type = $query->get('post_type') ?: 'post';
  if ($post_type === 'page' || !Bogo::is_localizable_post_type($post_type)) { return $query; }

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
  if ($old_meta_query && $old_meta_query !== $meta_query) {
    $old_meta_query['relation'] = 'AND';
    $old_meta_query[] = count($meta_query) === 1 ? $meta_query[0] : $meta_query;
    $query->set('meta_query', $old_meta_query);
  }
  else {
    $query->set('meta_query', $meta_query);
  }
  
  return $query;
}
