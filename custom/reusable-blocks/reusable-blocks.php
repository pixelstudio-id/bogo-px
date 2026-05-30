<?php
// /wp-admin/edit.php?post_type=wp_block

add_action('enqueue_block_editor_assets', 'bogopx_localize_reusable_blocks', 110);
add_action('admin_head', 'bogopx_add_style_localized_blocks', 110);

add_filter('render_block_core/block', 'bogopx_render_localized_reusable_block', 10, 2);
add_action('save_post_wp_block', 'bogo_clear_localized_block_ids_cache', 10);


/**
 * Hide localized Reusable blocks in the block editor.
 * 
 * @action enqueue_block_editor_assets
 */
function bogopx_localize_reusable_blocks() {
  global $post;
  $current_locale = get_post_meta($post->ID, '_locale', true) ?: BOGO_DEFAULT_LOCALE;
  
  // abort if default locale
  if (Bogo::is_default_locale($current_locale)) {
    return;
  }

  global $BOGO_GROUPS_BY_POST_TYPE;
  $blocks = $BOGO_GROUPS_BY_POST_TYPE['wp_block'] ?? [];
  $blocks_default_locale = $blocks[BOGO_DEFAULT_LOCALE] ?? [];

  $block_ids = isset($blocks[BOGO_DEFAULT_LOCALE]) ? wp_list_pluck($blocks[BOGO_DEFAULT_LOCALE], 'ID') : [];

  global $BOGO_GROUPS_BY_ID;
  $localized_blocks = [];
  foreach ($block_ids as $block_id) {
    $group = $BOGO_GROUPS_BY_ID[$block_id] ?? [];
    $localized_blocks[$block_id] = array_column($group, 'ID', 'locale');
  }

  $locale_name = bogo_get_language($current_locale);
  $locale_name_no_parentheses = trim(preg_replace('/\(.+\)/', '', $locale_name));
  
  wp_localize_script('bogopx-editor', 'bogoReusableBlocks', [
    'localizedBlocks' => $localized_blocks,
    'indexURL' => admin_url('edit.php?post_type=wp_block'),
    'editURL' => admin_url('post.php?post=$$$&action=edit'),
    'locale' => $current_locale,
    'localeName' => $locale_name_no_parentheses,
  ]);
}


/**
 * Add <style> tag to hide the localized blocks to appear in Inserter.
 * @todo - eventhough hidden, the sidebar inserter still consume space, so it looks weird
 * 
 * @action admin_head
 */
function bogopx_add_style_localized_blocks() {
  // only enable in gutenberg editor
  $screen = get_current_screen();
  if ($screen->base !== 'post' || $screen->post_type !== 'wp_block') { return; }

  $cache_key = 'bogo_localized_reusable_block_ids';
  $localized_ids = get_transient($cache_key, []);

  if (empty($localized_ids)) {
    $localized_ids = get_posts([
      'post_type' => 'wp_block',
      'posts_per_page' => -1,
      'fields' => 'ids',
      'meta_query' => [
        'relation' => 'OR',
        [
          'key' => '_locale',
          'value' => BOGO_DEFAULT_LOCALE,
          'compare' => '!=',
        ],
        [
          'key' => '_locale',
          'value' => '',
          'compare' => '=',
        ],
      ],
    ]);

    set_transient($cache_key, $localized_ids, DAY_IN_SECONDS);
  }

  $classes = [];
  foreach ($localized_ids as $id) {
    $classes[] = ".block-editor-inserter__panel-content .block-editor-block-types-list__item[class*=\"{$id}\"]";
    $classes[] = ".components-autocomplete__result[id*=\"{$id}\"]";
  }
  $classes = html_entity_decode(implode(', ', $classes));
  // ?>

  <style id="bogopx-hide-localized-blocks">
    <?= $classes ?> {
      display: none !important;
    }
  </style>

  <?php //
}

/**
 * Delete the ids transient when updating or creating a `wp_block` post type
 */
function bogo_clear_localized_block_ids_cache($post_id) {
  delete_transient('bogo_localized_reusable_block_ids');
}


/**
 * @filter render_block_core/block
 */
function bogopx_render_localized_reusable_block($block_content, $block) {
  // abort if default locale
  if (Bogo::is_default_locale()) { return $block_content; }

  $locale_block = Bogo::get_locale_post($block['attrs']['ref']);

  if ($locale_block) {
    $new_content = apply_filters('the_content', $locale_block->post_content);
    return $new_content;
  }

  return $block_content;
}