<?php

add_filter( 'map_meta_cap', 'bogo_map_meta_cap', 10, 4 );

function bogo_map_meta_cap( $caps, $cap, $user_id, $args ) {
	$meta_caps = array(
		'bogo_manage_language_packs' => 'install_languages',
		'bogo_edit_terms_translation' => 'manage_categories',
		'bogo_access_all_locales' => 'manage_options',
		'bogo_access_locale' => 'read',
	);

	$meta_caps = apply_filters( 'bogo_map_meta_cap', $meta_caps );
	$all_locales_cap = $meta_caps['bogo_access_all_locales'] ?? 'manage_options';

	$caps = array_diff( $caps, array_keys( $meta_caps ) );

	if ( isset( $meta_caps[$cap] ) ) {
		$caps[] = $meta_caps[$cap];
	}

	static $accessible_locales = array();
	static $access_all_locales = array();
	static $post_locales = array();
	$requires_accessible_locales = in_array(
		$cap,
		array( 'bogo_access_locale', 'edit_post', 'delete_post' ),
		true
	);

	if ( $requires_accessible_locales
	and ! isset( $access_all_locales[$user_id] ) ) {
		$access_all_locales[$user_id] = user_can(
			$user_id,
			$all_locales_cap
		);
	}

	if ( $requires_accessible_locales
	and ! $access_all_locales[$user_id]
	and ! isset( $accessible_locales[$user_id] ) ) {
		$accessible_locales[$user_id] = bogo_get_user_accessible_locales(
			$user_id
		);
	}

	if ( 'bogo_access_locale' === $cap
	and ! $access_all_locales[$user_id] ) {
		$locale = $args[0];

		if ( ! in_array( $locale, $accessible_locales[$user_id] ) ) {
			$caps[] = 'do_not_allow';
		}
	}

	if ( in_array( $cap, array( 'edit_post', 'delete_post' ), true )
	and ! $access_all_locales[$user_id] ) {
		$post_id = absint( $args[0] ?? 0 );

		if ( ! $post_id ) {
			return $caps;
		}

		$others_cap_pattern = 'edit_post' === $cap
			? 'edit_others_'
			: 'delete_others_';
		$editing_others = false;

		foreach ( (array) $caps as $required_cap ) {
			if ( false !== strpos( (string) $required_cap, $others_cap_pattern ) ) {
				$editing_others = true;
				break;
			}
		}

		if ( ! $editing_others ) {
			return $caps;
		}

		if ( ! isset( $post_locales[$post_id] ) ) {
			$post_locales[$post_id] = bogo_get_post_locale( $post_id );
		}

		$locale = $post_locales[$post_id];

		if ( ! in_array( $locale, $accessible_locales[$user_id] ) ) {
			$caps[] = 'do_not_allow';
		}
	}

	return $caps;
}
