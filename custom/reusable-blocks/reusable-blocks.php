<?php
// /wp-admin/edit.php?post_type=wp_block

add_action('enqueue_block_editor_assets', 'bogopx_enqueue_hide_reusable_blocks', 110);
add_action('admin_head', 'bogopx_add_style_hide_localized_blocks', 110);

add_action('admin_menu', 'bogopx_add_reusable_blocks_menu', 10);
add_filter('render_block_core/block', 'bogopx_render_localized_reusable_block', 10, 2);

/**
 * @filter render_block_core/block
 */
function bogopx_render_localized_reusable_block($block_content, $block) {
  $locale_blocks = Bogo::get_post_translations($block['attrs']['ref']);
  $locale_block = $locale_blocks[get_locale()] ?? null;

  if ($locale_block) {
    $new_content = apply_filters('the_content', $locale_block->post_content);
    return $new_content;
  }

  return $block_content;
}


/**
 * @action admin_head
 */
function bogopx_add_style_hide_localized_blocks() {
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
 * Hide localized Reusable blocks in the block editor.
 * 
 * @action enqueue_block_editor_assets
 */
function bogopx_enqueue_hide_reusable_blocks() {
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
  
  wp_localize_script('bogopx-editor', 'bogoReusableBlocks', [
    'localizedIds' => $localized_ids,
  ]);
}

/**
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
