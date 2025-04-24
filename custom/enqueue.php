<?php

add_action('wp_enqueue_scripts', 'bogo_public_enqueue_scripts_custom', 10);
add_action('admin_enqueue_scripts', 'bogo_admin_enqueue_scripts_custom', 10);
add_action('enqueue_block_editor_assets', 'bogo_editor_enqueue_scripts_custom', 100);

/**
 * @action wp_enqueue_scripts
 */
function bogo_public_enqueue_scripts_custom() {
  wp_enqueue_script('bogopx-public', BOGO_DIST . '/bogopx-public.js', [], BOGO_VERSION, true);
  wp_enqueue_style('bogopx-public', BOGO_DIST . '/bogopx-public.css', [], BOGO_VERSION);
}

/**
 * @action admin_enqueue_scripts
 */
function bogo_admin_enqueue_scripts_custom() {
  wp_enqueue_script('bogopx-admin', BOGO_DIST . '/bogopx-admin.js', [], BOGO_VERSION, true);
  wp_enqueue_style('bogopx-admin', BOGO_DIST . '/bogopx-admin.css', [], BOGO_VERSION);

  wp_localize_script('bogopx-admin', 'bogoApiSettings', [
    'root' => esc_url_raw(rest_url('bogo/v1')),
    'namespace' => 'bogo/v1',
    'nonce' => wp_create_nonce('wp_rest'),
  ]);
}

/**
 * @action enqueue_block_editor_assets
 */
function bogo_editor_enqueue_scripts_custom() {
  if (!is_admin()) { return; }

  wp_enqueue_script('bogopx-editor', BOGO_DIST . '/bogopx-editor.js', [ 'wp-blocks', 'wp-dom' ] , BOGO_VERSION, true);
  wp_enqueue_style('bogopx-editor', BOGO_DIST . '/bogopx-editor.css', [ 'wp-edit-blocks' ], BOGO_VERSION);
 
  wp_localize_script('bogopx-editor', 'bogoApiSettings', [
    'root' => esc_url_raw(rest_url('bogo/v1')),
    'namespace' => 'bogo/v1',
    'nonce' => wp_create_nonce('wp_rest'),
  ]);
}