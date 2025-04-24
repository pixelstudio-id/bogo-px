<?php
// /wp-admin/edit.php?post_type=wp_block

add_action('enqueue_block_editor_assets', 'bogopx_localize_reusable_blocks', 110);
add_action('admin_head', 'bogopx_add_style_localized_blocks', 110);

add_filter('render_block_core/block', 'bogopx_render_localized_reusable_block', 10, 2);
add_action('admin_menu', 'bogopx_add_reusable_blocks_menu', 10);


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

  if ($blocks_default_locale) {
    $block_ids = array_map(function($block) {
      return $block->ID;
    }, $blocks_default_locale);
  }

  global $BOGO_GROUPS_BY_ID;
  $localized_blocks = [];
  foreach ($block_ids as $block_id) {
    $localized_blocks[$block_id] = array_column($BOGO_GROUPS_BY_ID[$block_id], 'id', 'locale');
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
  $localized_ids = get_posts([
    'post_type' => 'wp_block',
    'posts_per_page' => -1,
    'fields' => 'ids',
    'meta_query' => [
      'relation' => 'OR',
      [
        'key' => '_locale',
        'value' => get_locale(),
        'compare' => '!=',
      ],
      [
        'key' => '_locale',
        'value' => '',
        'compare' => '=',
      ],
    ],
  ]);

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
 * @filter render_block_core/block
 */
function bogopx_render_localized_reusable_block($block_content, $block) {
  // abort if default locale
  if (Bogo::is_default_locale()) { return $block_content; }

  $locale_blocks = Bogo::get_post_translations($block['attrs']['ref']);
  $locale_block = $locale_blocks[get_locale()] ?? null;

  if ($locale_block) {
    $new_content = apply_filters('the_content', $locale_block->post_content);
    return $new_content;
  }

  return $block_content;
}

/**
 * Add a shortcut to Reusable Blocks in the admin menu
 * 
 * @action admin_menu
 */
function bogopx_add_reusable_blocks_menu() {
  add_submenu_page(
    'edit.php?post_type=page',
    __('Reusable Blocks', 'bogo'),
    __('Reusable Blocks', 'bogo'),
    'edit_posts',
    'edit.php?post_type=wp_block'
  );
}
