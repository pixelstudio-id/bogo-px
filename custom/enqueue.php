<?php

add_action('wp_enqueue_scripts', 'bogo_public_enqueue_scripts_custom', 10);
add_action('admin_enqueue_scripts', 'bogo_admin_enqueue_scripts_custom', 10);

/**
 * @action wp_enqueue_scripts
 */
function bogo_public_enqueue_scripts_custom() {
	wp_enqueue_script('bogo-public-custom', BOGO_DIST . '/public.js', [], BOGO_VERSION, true);
	wp_enqueue_style('bogo-public-custom', BOGO_DIST . '/public.css', [], BOGO_VERSION);
}

/**
 * @action admin_enqueue_scripts
 */
function bogo_admin_enqueue_scripts_custom() {
	wp_enqueue_script('bogo-admin-custom', BOGO_DIST . '/admin.js', [], BOGO_VERSION, true);
	wp_enqueue_style('bogo-admin-custom', BOGO_DIST . '/admin.css', [], BOGO_VERSION);

	wp_enqueue_style('bogo-flags', BOGO_DIST . '/flags.css', [], '1.0.0');
}