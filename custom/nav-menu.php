<?php

add_filter('pre_get_posts', 'bogo_hide_translated_post_in_menu_editor');
add_filter('wp_get_nav_menu_items', 'bogo_localize_nav_menu_items', 15, 3);

add_action('wp_nav_menu_item_custom_fields', 'bogo_add_locale_label_in_custom_link', 1, 2);
add_action('wp_update_nav_menu', 'bogo_save_translated_custom_link', 10, 2);

/**
 * @filter wp_get_nav_menu_items
 */
function bogo_localize_nav_menu_items($items, $menu, $args) {
  foreach ($items as $item) {
    if ($item->type === 'custom') {
      $titles = json_decode(get_post_meta($item->db_id, '_bogo_title', true), true);
      $item->title = empty($titles[get_locale()]) ? $item->title : $titles[get_locale()];
    }
    elseif ($item->type === 'post_type') {
      $locale_link = bogo_get_locale_link_by_id($item->object_id);
      if ($locale_link) {
        $item->title = $locale_link['post']->post_title;
        $item->url = $locale_link['url'];
      }
    }
  }

  return $items;
}

/**
 * @filter pre_get_posts
 */
function bogo_hide_translated_post_in_menu_editor($query) {
  global $pagenow;
  if ($pagenow !== 'nav-menus.php') { return $query; }

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
function bogo_add_locale_label_in_custom_link($id, $menu_item) {
  if ($menu_item->object !== 'custom') { return; }

  $active_locales = $menu_item->bogo_locales;
  $all_locales = bogo_available_languages();
  unset($all_locales[BOGO_BASE_LOCALE]);

  $titles = json_decode(get_post_meta($id, '_bogo_title', true), true);

  ?>
    <div class="field-bogo-titles">
    <?php foreach ($all_locales as $locale => $label):
      $value = $titles[$locale] ?? '';
      $style_atts = in_array($locale, $active_locales) ? '' : 'style="display: none"';
      $class_atts = $value ? '' : 'class="is-empty"';
    ?>
      <label <?= $style_atts ?> <?= $class_atts ?>>
        <i class="flag flag-<?= $locale ?>"></i>
        <input
          type="text"
          placeholder="<?= $label ?>"
          name="_bogo_title[<?= $id ?>][<?= $locale ?>]"
          value="<?= $value ?>"
        >
      </label>
    <?php endforeach; ?>
    </div>
  <?php
}

/**
 * Add custom field containing the translated title
 * 
 * @action wp_update_nav_menu
 */
function bogo_save_translated_custom_link($menu_id, $menu_data = []) {
  $titles = $_POST['_bogo_title'] ?? null;
  if (!$titles) { return; }

  foreach ($titles as $id => $title) {
    update_post_meta($id, '_bogo_title', json_encode($title));
  }
} 