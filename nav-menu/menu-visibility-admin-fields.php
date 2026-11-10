<?php if (!defined('ABSPATH')) exit;

add_action('wp_nav_menu_item_custom_fields', 'bogo_add_fields_in_menu_item', 1, 2);
add_action('wp_update_nav_menu', 'bogo_update_nav_menu_translations', 10, 2);

add_filter('pre_get_posts', 'bogo_hide_translated_post_in_menu_editor');


/**
 * Add custom field containing the translated title and description
 * 
 * @action wp_update_nav_menu
 */
function bogo_update_nav_menu_translations($menu_id, $menu_data = []) {
  $fields = $_POST['bogo_fields'] ?? null;
  if ($fields) {
    foreach ($fields as $id => $field) {
      update_post_meta($id, 'bogo_fields', $field);
    }
  }

  // delete all locale cache variants after saving menu
  foreach ((array) bogo_available_locales() as $locale) {
    $cache_key = "bogo_menu_items_fields_{$menu_id}_{$locale}";
    delete_transient($cache_key);
  }

  // cleanup old cache key format for backward compatibility
  delete_transient("bogo_menu_items_fields_{$menu_id}");
}


/**
 * Hide translated posts when choosing Menu Item to add
 * 
 * @todo - this might mess with other query that wants to find translated post in Nav Menu page
 * 
 * @filter pre_get_posts
 */
function bogo_hide_translated_post_in_menu_editor($query) {
  global $pagenow;
  if ($pagenow !== 'nav-menus.php') { return $query; }

  $excluded_pt = ['acf-taxonomy', 'acf-post-type', 'acf-ui-options-page', 'wp_template_part', 'wpcf7_contact_form', 'wp_global_styles', 'nav_menu_item'];
  if (in_array($query->query['post_type'], $excluded_pt)) { return $query; }

  // ignore this filter if specifically ask for translated post
  $has_metakey_locale = isset($query->query['meta_key']) && in_array($query->query['meta_key'], ['_original_post', '_locale']);
  $has_metaquery_locale = false;

  if (isset($query->query['meta_query'])) {
    $keys = array_column($query->query['meta_query'], 'key');
    $has_metaquery_locale = array_intersect($keys, ['_original_post', '_locale']);
  };

  if ($has_metakey_locale || $has_metaquery_locale) { return $query; }

  // TODO: can cause bug when installing extra plugins that add extra query before
  $junk_query_types = [
    'acf-taxonomy',
    'acf-post-type',
    'acf-ui-options-page',
    'acf-field-group',
    'acf-field',
    'wpcf7_contact_form',
    'wp_block',
    'wp_template',
    'wp_template_part',
    'wp_global_styles',
    'wp_navigation',
    'nav_menu_item',
    'nav_menu_item',
    'wp_global_styles',
  ];

  if (in_array($query->query['post_type'], $junk_query_types)) { return $query; }

  $query->set('meta_query', [
    'relation' => 'OR',
    [
      'key' => '_locale',
      'compare' => 'NOT EXISTS',
    ],
    [
      'key' => '_locale',
      'value' => get_locale(),
      'compare' => '=',
    ],
  ]);

  return $query;
}

/**
 * @action wp_nav_menu_item_custom_fields
 */
function bogo_add_fields_in_menu_item($id, $menu_item) {
  if ($menu_item->type === 'taxonomy') {
    _bogo_echo_menu_item_taxonomy_notice($menu_item);
    return;
  }

  // abort if menu title is shortcode
  if (preg_match('/^\[.+\]$/', trim($menu_item->post_title))) {
    return;
  }

  $active_locales = $menu_item->bogo_locales;
  $all_locales = bogo_available_languages();
  unset($all_locales[BOGO_DEFAULT_LOCALE]);

  $fields = get_post_meta($id, 'bogo_fields', true) ?: [];

  // fallback if still using the old data format
  if (!$fields) { 
    $titles = json_decode(get_post_meta($id, 'bogo_titles', true), true) ?: [];
    $descs = json_decode(get_post_meta($id, 'bogo_descriptions', true), true) ?: [];

    foreach ($titles as $locale => $title) {
      $fields[$locale] = [
        't' => $title,
        'd' => $descs[$locale] ?? '',
      ];
    }
  }

  $links = [];
  if ($menu_item->type === 'post_type') {
    $links = Bogo::get_locale_links($menu_item->object_id);
  }

  $html_fields = [];
  foreach ($all_locales as $locale => $label) {
    $menu_title = empty($menu_item->post_title) ? $menu_item->title : $menu_item->post_title;
 
    $placeholder = isset($links[$locale]) && !empty($links[$locale]['post_title']) ? $links[$locale]['post_title'] : $menu_title;
    $classes = [];
    $classes[] = isset($links[$locale]) ? 'has-fixed-placeholder' : '';

    $custom_title = $fields[$locale]['t'] ?? '';
    $custom_desc = $fields[$locale]['d'] ?? '';
    $classes[] = isset($links[$locale]) || !empty($custom_title) ? '' : 'is-empty';
  
    $styles = [];
    $styles[] = in_array($locale, $active_locales) ? '' : 'display: none';

    $html_fields[$locale] = [
      'title' => $custom_title,
      'description' => $custom_desc,
      'placeholder' => $placeholder,
      'classes' => implode(' ', $classes),
      'styles' => implode('; ', $styles),
      'label' => $label,
      'field_name_title' => "bogo_fields[{$id}][{$locale}][t]",
      'field_name_description' => "bogo_fields[{$id}][{$locale}][d]",
    ];
  }

  ?>
    <div class="bogo-fields">
    <?php foreach ($html_fields as $locale => $att): ?>

      <div
        class="bogo-field <?= esc_attr($att['classes']) ?>"
        data-locale="<?= esc_attr($locale) ?>"
        style="<?= esc_attr($att['styles']) ?>"
      >
        <label>
          <i class="flag flag-<?= esc_attr($locale) ?>"></i>
          <span>
            <?= esc_html($att['label']) ?>
          </span>
          <input
            type="text"
            placeholder="<?= esc_attr($att['placeholder']) ?>"
            data-name="<?= esc_attr($att['field_name_title']) ?>"
            value="<?= esc_attr($att['title']) ?>"
          >
        </label>
        <textarea
          placeholder="<?= esc_attr($att['label']) ?> Description"
          rows="3"
          data-name="<?= esc_attr($att['field_name_description']) ?>"
        ><?= esc_textarea($att['description']) ?></textarea>
      </div>

    <?php endforeach; ?>
    </div>
  <?php
}

/**
 * Show a notice with link to edit the taxonomy
 */
function _bogo_echo_menu_item_taxonomy_notice($menu_item) {
  $href = get_edit_term_link($menu_item->object_id, $menu_item->object);
  ?>
  <p class="bogo-menu-notice">
    <a href="<?= esc_url($href) ?>" target="_blank">
      <i class="dashicons-before dashicons-translation"></i>
      <span>
        Edit translation here
      </span>
    </a>
  </p>
  <?php
}
