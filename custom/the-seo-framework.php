<?php

add_filter('the_seo_framework_title_from_custom_field', 'bogopx_override_seo_framework_home_title', 10, 2);
add_filter('the_seo_framework_meta_render_data', 'bogopx_override_seo_framework_home_description', 10);
// add_filter('the_seo_framework_descr iption_output', 'bogopx_override_seo_framework_home_description', 10);

add_filter('the_seo_framework_meta_render_data', 'bogopx_fix_canonical_for_translated_homepage', 10);



/**
 * In homepage of localized post, the Title and Description uses the global Homepage setting. This will override it.
 * 
 * @filter the_seo_framework_title_from_custom_field
 */
function bogopx_override_seo_framework_home_title ($title, $args) {
  // If Bogo installed, not default locale, and static front page
  if (class_exists('Bogo') && !Bogo::is_default_locale() && is_front_page() && !is_home()) {
    global $post;
    if (!$post) { return $title; }

    $meta_title = get_post_meta($post->ID, '_genesis_title', true);
    if ($meta_title) {
      return $meta_title;
    }
  }

  return $title;
}

/**
 * @filter the_seo_framework_description_output
 */
function bogopx_override_seo_framework_home_description ($tags) {
  // If Bogo installed, not default locale, and static front page
  if (class_exists('Bogo') && !Bogo::is_default_locale() && is_front_page() && !is_home()) {
    global $post;
    if (!$post) { return $tags; }

    $meta_desc = get_post_meta($post->ID, '_genesis_description', true);
    if ($meta_desc && isset($tags['description']['attributes'])) {
      $tags['description']['attributes']['content'] = $meta_desc;
    }
  }

  return $tags;
}

/**
 * Fix canonical URL being always the English URL in homepage
 * 
 * @filter the_seo_framework_meta_render_data
 */
function bogopx_fix_canonical_for_translated_homepage($data) {
  // abort if default locale
  if (Bogo::is_default_locale()) { return $data; }

  // abort if not homepage
  if (!is_front_page()) { return $data; }

  $data['canonical']['attributes']['href'] = home_url();
  return $data;
}
