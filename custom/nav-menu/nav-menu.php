<?php

add_filter('pre_get_posts', 'bogo_hide_translated_post_in_menu_editor');
add_action('wp_nav_menu_item_custom_fields', 'bogo_add_fields_in_menu_item', 1, 2);
add_action('wp_update_nav_menu', 'bogo_save_translated_custom_link', 10, 2);

add_filter('wp_get_nav_menu_items', 'bogo_localize_nav_menu_items', 15, 3);

/**
 * @filter wp_get_nav_menu_items
 */
function bogo_localize_nav_menu_items($items, $menu, $args) {
  if (is_admin()) { return $items; } // abort if admin
  if (Bogo::is_default_locale()) { return $items; } // abort if base locale

  foreach ($items as &$item) {
    $titles = json_decode(get_post_meta($item->db_id, 'bogo_titles', true), true);
    
    // if custom, it's always replaced by the Bogo Field
    if ($item->type === '' || $item->type === 'post_type_archive') {
      $titles = json_decode(get_post_meta($item->db_id, 'bogo_titles', true), true);
      $item->title = empty($titles[get_locale()]) ? $item->title : $titles[get_locale()];
    }
    // if post_type, check if empty, use the native title
    elseif ($item->type === 'post_type') {
      $locale_obj = bogo_localize_by_id($item->object_id);
      $default_title = $item->title;

      if ($locale_obj) {
        $default_title = $locale_obj['post']->post_title;
        $item->url = $locale_obj['url'];
      }

      $item->title = empty($titles[get_locale()]) ? $default_title : $titles[get_locale()];
    }
  }

  return $items;
}

/**
 * Hide translated posts when choosing Menu Item to add
 * 
 * TODO: this might mess with other query that wants to find translated post in Nav Menu page
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
  
  $values = json_decode(get_post_meta($id, 'bogo_titles', true), true);

  $posts = [];
  if ($menu_item->type === 'post_type') {
    $posts = Bogo::get_post_translations($menu_item->object_id);
  }

  $fields = [];
  foreach ($all_locales as $locale => $label) {
    $placeholder = isset($posts[$locale]) ? $posts[$locale]->post_title : $menu_item->post_title;
    $classes = [];
    $classes[] = isset($posts[$locale]) ? 'has-fixed-placeholder' : '';
    $classes[] = isset($posts[$locale]) || !empty($value) ? '' : 'is-empty';
  
    $styles = [];
    $styles[] = in_array($locale, $active_locales) ? '' : 'display: none';

    $fields[$locale] = [
      'value' => $values[$locale] ?? '',
      'placeholder' => $placeholder,
      'classes' => implode(' ', $classes),
      'styles' => implode('; ', $styles),
      'label' => $label,
      'field_name' => "bogo_titles[{$id}][{$locale}]",
    ];
  }

  ?>
    <div class="bogo-menu-titles">
    <?php foreach ($fields as $locale => $att): ?>

      <label class="bogo-field <?= esc_attr($att['classes']) ?>" style="<?= esc_attr($att['styles']) ?>">
        <i class="flag flag-<?= esc_attr($locale) ?>"></i>
        <span>
          <?= esc_html($att['label']) ?>
        </span>
        <input
          type="text"
          placeholder="<?= esc_attr($att['placeholder']) ?>"
          name="<?= esc_attr($att['field_name']) ?>"
          value="<?= esc_attr($att['value']) ?>"
        >
      </label>

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



/**
 * Add custom field containing the translated title
 * 
 * @action wp_update_nav_menu
 */
function bogo_save_translated_custom_link($menu_id, $menu_data = []) {
  $titles = $_POST['bogo_titles'] ?? null;
  if (!$titles) { return; }

  foreach ($titles as $id => $title) {
    update_post_meta($id, 'bogo_titles', json_encode($title, JSON_UNESCAPED_UNICODE));
  }
}