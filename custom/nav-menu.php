<?php

add_filter('pre_get_posts', 'bogo_hide_translated_post_in_menu_editor');
add_filter('wp_get_nav_menu_items', 'bogo_localize_nav_menu_items', 15, 3);

add_action('wp_nav_menu_item_custom_fields', 'bogo_add_locale_label_in_custom_link', 1, 4);

/**
 * @filter wp_get_nav_menu_items
 */
function bogo_localize_nav_menu_items($items, $menu, $args) {
  foreach ($items as $item) {
    $locale_link = bogo_get_locale_link_by_id($item->object_id);

    if ($locale_link) {
      $item->title = $locale_link['post']->post_title;
      $item->url = $locale_link['url'];
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
function bogo_add_locale_label_in_custom_link($id, $menu_item, $depth, $args) {
  if ($menu_item->object !== 'custom') { return; }

  $langs = bogo_available_languages();
  unset($langs[BOGO_BASE_LOCALE]);

  ?>
    <fieldset class="bogo-locale-labels">
    <?php foreach ($langs as $key => $label): ?>
      <label>
        <i class="fi fi-<?= $key ?>">
        <input type="text" placeholder="<?= $label ?>" name="bogo-locale-<?= $key ?>">
      </label>
    <?php endforeach; ?>
    </fieldset>
  <?php
}