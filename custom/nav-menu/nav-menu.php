<?php
add_filter('wp_get_nav_menu_items', 'bogo_localize_nav_menu_items', 15, 3);

add_filter('pre_get_posts', 'bogo_hide_translated_post_in_menu_editor');
add_action('wp_nav_menu_item_custom_fields', 'bogo_add_fields_in_menu_item', 1, 2);
add_action('wp_update_nav_menu', 'bogo_save_translated_menu_item', 10, 2);


/**
 * @filter wp_get_nav_menu_items
 */
function bogo_localize_nav_menu_items($items, $menu, $args) {
  // abort if in admin or base locale
  if (is_admin() || Bogo::is_default_locale()) { return $items; }

  $menu_id = $menu->term_id;
  $all_fields = _bogo_get_menu_items_fields($menu_id, $items);

  // Loop through each menu item and replace the title, url, and description
  foreach ($items as &$item) {
    $locale = get_locale();
    $item_id = $item->type === 'taxonomy' ? "term_{$item->object_id}" : $item->db_id;
    $fields = $all_fields[$item_id] ?? [];
    $custom_title = isset($fields[$locale]) && !empty($fields[$locale]['t']) ? $fields[$locale]['t'] : '';
    $custom_desc = isset($fields[$locale]) && !empty($fields[$locale]['d']) ? $fields[$locale]['d'] : '';

    // if custom, it's always replaced by the Bogo Field
    if ($item->type === '' || $item->type === 'custom' || $item->type === 'post_type_archive' || $item->type === 'taxonomy') {
      $item->title = $custom_title ?: $item->title;
    }
    // if post_type, check if empty, use the native title
    elseif ($item->type === 'post_type') {
      $locale_link = Bogo::get_locale_link($item->object_id);
      $default_title = $item->title;

      if ($locale_link) {
        $default_title = $locale_link['post_title'];
        $item->url = $locale_link['url'];
      }

      $item->title = $custom_title ?: $default_title;
    }

    if (!empty($custom_desc)) {
      $item->post_content = $custom_desc;
      $item->description = $custom_desc;
    }
  }

  return $items;
}

/**
 * Get the custom fields of each menu item and cache it
 * 
 * @param int $menu_id - The menu term ID
 * @param array $items - The menu items to get the fields for
 * 
 * @return array - An array of menu item ID as key and its fields as value
 */
function _bogo_get_menu_items_fields($menu_id, $items) {
  $cache_key = "bogo_menu_items_fields_{$menu_id}";
  $all_fields = get_transient($cache_key) ?: [];
  if (!empty($all_fields)) {
    return $all_fields;
  }

  // Prepare the taxonomy meta cache for more efficient query later
  $taxonomy_ids = [];
  foreach ($items as $item) {
    if ($item->type === 'taxonomy') {
      $taxonomy_ids[] = (int) $item->object_id;
    }
  }
  if ($taxonomy_ids) {
    update_termmeta_cache($taxonomy_ids);
  }

  foreach ($items as $item) {
    // if taxonomy, use different syntax and normalize "n" to "t"
    if ($item->type === 'taxonomy') {
      $fields = get_term_meta($item->object_id, 'bogo_fields', true) ?: [];
      $all_fields["term_{$item->object_id}"] = array_map(function($f) {
        return [
          't' => $f['n'] ?? '',
          'd' => $f['d'] ?? '',
        ];
      }, $fields);
    } else {
      $all_fields[$item->db_id] = get_post_meta($item->db_id, 'bogo_fields', true) ?: [];
      
      // fallback if still using the old data format
      if (empty($all_fields[$item->db_id])) {
        $titles = json_decode(get_post_meta($item->db_id, 'bogo_titles', true), true) ?: [];
        $descs = json_decode(get_post_meta($item->db_id, 'bogo_descriptions', true), true) ?: [];

        $fields = [];
        foreach ($titles as $locale => $title) {
          $fields[$locale] = [
            't' => $title,
            'd' => $descs[$locale] ?? '',
          ];
        }
        $all_fields[$item->db_id] = $fields;
      }
    }
  }

  set_transient($cache_key, $all_fields, DAY_IN_SECONDS);
  return $all_fields;
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



/**
 * Add custom field containing the translated title and description
 * 
 * @action wp_update_nav_menu
 */
function bogo_save_translated_menu_item($menu_id, $menu_data = []) {
  $fields = $_POST['bogo_fields'] ?? null;
  if ($fields) {
    foreach ($fields as $id => $field) {
      update_post_meta($id, 'bogo_fields', $field);
    }
  }

  // delete the cache after saving menu
  $cache_key = "bogo_menu_items_fields_{$menu_id}";
  delete_transient($cache_key);
}