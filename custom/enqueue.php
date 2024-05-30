<?php

add_action('wp_enqueue_scripts', 'bogo_public_enqueue_scripts_custom', 10);
add_action('admin_enqueue_scripts', 'bogo_admin_enqueue_scripts_custom', 10);
add_action('enqueue_block_editor_assets', 'bogo_editor_enqueue_scripts_custom', 100);

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

function bogo_editor_enqueue_scripts_custom() {
	if (!is_admin()) { return; }

  wp_enqueue_script('bogo-editor-custom', BOGO_DIST . '/editor.js', [ 'wp-blocks', 'wp-dom' ] , BOGO_VERSION, true);
  wp_enqueue_style('bogo-editor-custom', BOGO_DIST . '/editor.css', [ 'wp-edit-blocks' ], BOGO_VERSION);

	// create language dropdown
	global $post;
	$current_locale = get_post_meta($post->ID, '_locale', true);
	$locale_posts = bogo_get_post_translations($post);
	$locale_posts[$current_locale] = $post;
	$options_html = '';

	foreach ($locale_posts as $locale => $p) {
		$value = esc_url(add_query_arg([
			'post' => $p->ID,
			'action' => 'edit',
		], 'post.php'));

		$label = bogo_get_language($locale);
		$atts = $current_locale === $locale ? 'selected' : '';
		$options_html .= "<option value='{$value}' {$atts}>{$label}</option>";
	}

	$onchange = 'onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);"';
	$select_html = "<select {$onchange}> {$options_html} </select>";
	wp_localize_script('bogo-editor-custom', 'bogoLanguageDropdown', [
		'select_html' => $select_html,
	]);
}