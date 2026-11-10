<?php if (!defined('ABSPATH')) exit;

add_action('restrict_manage_posts', 'bogo_add_language_filter_in_post_table', 10, 2);

/**
 * Add a language filter dropdown to the posts list table.
 * 
 * @action restrict_manage_posts
 */
function bogo_add_language_filter_in_post_table($post_type, $which) {
  if (!bogo_is_localizable_post_type($post_type)) {
    return;
  }

  $available_languages = bogo_available_languages();
  $current_locale = empty( $_GET['lang'] ) ? '' : $_GET['lang'];

  echo '<select name="lang">';

  $selected = ( '' == $current_locale ) ? ' selected="selected"' : '';

  echo '<option value=""' . $selected . '>'
    . esc_html( __( 'Show all locales', 'bogo' ) ) . '</option>';

  foreach ( $available_languages as $locale => $lang ) {
    $selected = ( $locale == $current_locale ) ? ' selected="selected"' : '';

    echo '<option value="' . esc_attr( $locale ) . '"' . $selected . '>'
      . esc_html( $lang ) . '</option>';
  }

  echo '</select>' . "\n";
}