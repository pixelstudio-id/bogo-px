<?php

add_action('rest_api_init', 'bogopx_register_custom_slugs_api');

/**
 * @action rest_api_init
 */
function bogopx_register_custom_slugs_api() {
  register_rest_route('bogo/v1', '/custom-slugs', [
    'methods' => 'POST',
    'callback' => 'bogopx_api_update_custom_slugs',
    'permission_callback' => function() {
      return current_user_can('bogo_manage_language_packs');
      // return current_user_can('manage_options');
    },
  ]);
}

/**
 * @route POST /custom-slugs
 */
function bogopx_api_update_custom_slugs($request) {
  $params = $request->get_params();
  $params = wp_parse_args($params, [
    'slug' => '',
    'locale' => '',
  ]);

  if (!$params || !isset($params['slug']) || !isset($params['locale'])) {
    return new WP_Error('invalid_slugs', 'Invalid slugs data');
  }

  $custom_slugs = get_option('bogopx_custom_slugs', []);
  $slug = strtolower(sanitize_title($params['slug']));

  if ($slug) {
    $custom_slugs[$params['locale']] = $slug;
  } else {
    unset($custom_slugs[$params['locale']]);
  }

  return update_option('bogopx_custom_slugs', $custom_slugs);
}

/**
 * Call this in /admin/language-packs.php column definition to render the custom slug field
 */
function bogopx_render_custom_slug_column($item) {
  if (!$item->active) { return; }
  if (bogo_is_default_locale($item->locale)) { return; }

  $slug = strtolower(substr($item->locale, 0, 2));
  $custom_slugs = get_option('bogopx_custom_slugs', []);
  $custom_slug = array_key_exists($item->locale, $custom_slugs)
    ? $custom_slugs[$item->locale]
    : '';

  ob_start();
  // ?>

  <div class="bogopx-slug-field">
    <input
      type="text"
      name="slugs[]"
      data-locale="<?= esc_attr($item->locale) ?>"
      placeholder="<?= esc_attr($slug) ?>"
      value="<?= esc_attr($custom_slug) ?>"
    >
    <a class="button button-primary" href="#">
      Save
    </a>
  </div>

  <?php //
  return ob_get_clean();
}