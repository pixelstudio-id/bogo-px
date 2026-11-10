<?php

add_action('wp_enqueue_scripts', 'bogopx_public_enqueue_scripts_custom', 10);
add_action('admin_enqueue_scripts', 'bogopx_admin_enqueue_scripts_custom', 10);
add_action('enqueue_block_editor_assets', 'bogopx_editor_enqueue_scripts_custom', 100);

/**
 * @action wp_enqueue_scripts
 */
function bogopx_public_enqueue_scripts_custom() {
  wp_enqueue_script('bogo-px-public', BOGO_DIST . '/bogo-px-public.js', [], BOGO_VERSION, true);
  wp_enqueue_style('bogo-px-public', BOGO_DIST . '/bogo-px-public.css', [], BOGO_VERSION);
}

/**
 * @action admin_enqueue_scripts
 */
function bogopx_admin_enqueue_scripts_custom() {
  wp_enqueue_script('bogo-px-admin', BOGO_DIST . '/bogo-px-admin.js', [], BOGO_VERSION, true);
  wp_enqueue_style('bogo-px-admin', BOGO_DIST . '/bogo-px-admin.css', [], BOGO_VERSION);

  wp_localize_script('bogo-px-admin', 'bogoApiSettings', [
    'root' => esc_url_raw(rest_url('bogo/v1')),
    'namespace' => 'bogo/v1',
    'nonce' => wp_create_nonce('wp_rest'),
  ]);
}

/**
 * @action enqueue_block_editor_assets
 */
function bogopx_editor_enqueue_scripts_custom() {
  if (!is_admin()) { return; }

  wp_enqueue_script('bogo-px-editor', BOGO_DIST . '/bogo-px-editor.js', [ 'wp-blocks', 'wp-dom' ] , BOGO_VERSION, true);
  wp_enqueue_style('bogo-px-editor', BOGO_DIST . '/bogo-px-editor.css', [ 'wp-edit-blocks' ], BOGO_VERSION);
 
  wp_localize_script('bogo-px-editor', 'bogoApiSettings', [
    'root' => esc_url_raw(rest_url('bogo/v1')),
    'namespace' => 'bogo/v1',
    'nonce' => wp_create_nonce('wp_rest'),
  ]);
}