<?php if (!defined('ABSPATH')) exit;

add_filter('wp_get_nav_menu_items', 'bogo_localize_nav_menu_items', 15, 3);

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
    $item_id = $item->type === 'taxonomy' ? "term_{$item->object_id}" : $item->db_id;
    $fields = $all_fields[$item_id] ?? [];
    $custom_title = !empty($fields['t']) ? $fields['t'] : '';
    $custom_desc = !empty($fields['d']) ? $fields['d'] : '';

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

    if (!empty($custom_desc) && !empty($item->description)) {
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
 * @return array - An array of menu item ID as key and current locale field as value
 */
function _bogo_get_menu_items_fields($menu_id, $items) {
  $locale = get_locale();
  $cache_key = "bogo_menu_items_fields_{$menu_id}_{$locale}";
  $all_fields = get_transient($cache_key) ?: [];
  if (!empty($all_fields)) { return $all_fields; }

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
      $locale_field = isset($fields[$locale]) ? (array) $fields[$locale] : [];
      $all_fields["term_{$item->object_id}"] = [
        't' => $locale_field['n'] ?? '',
        'd' => $locale_field['d'] ?? '',
      ];
    } else {
      $fields = get_post_meta($item->db_id, 'bogo_fields', true) ?: [];
      $locale_field = isset($fields[$locale]) ? (array) $fields[$locale] : [];
      $all_fields[$item->db_id] = [
        't' => $locale_field['t'] ?? '',
        'd' => $locale_field['d'] ?? '',
      ];
      
      // fallback if still using the old data format
      if (empty($all_fields[$item->db_id]['t']) && empty($all_fields[$item->db_id]['d'])) {
        $titles = json_decode(get_post_meta($item->db_id, 'bogo_titles', true), true) ?: [];
        $descs = json_decode(get_post_meta($item->db_id, 'bogo_descriptions', true), true) ?: [];
        $all_fields[$item->db_id] = [
          't' => $titles[$locale] ?? '',
          'd' => $descs[$locale] ?? '',
        ];
      }
    }
  }

  set_transient($cache_key, $all_fields, DAY_IN_SECONDS * 30);
  return $all_fields;
}

