<?php

add_action('admin_enqueue_scripts', 'bogo_admin_enqueue_scripts_custom', 10);

/**
 * @action admin_enqueue_scripts
 */
function bogo_admin_enqueue_scripts_custom() {
	wp_enqueue_script('bogo-admin-custom', BOGO_DIST . '/admin.js', [], BOGO_VERSION, true);
	wp_enqueue_style('bogo-admin-custom', BOGO_DIST . '/admin.css', [], BOGO_VERSION);

	wp_enqueue_style('bogo-flags', BOGO_DIST . '/flags.css', [], '1.0.0');
}