<?php if (!defined('ABSPATH')) exit;

add_action('in_widget_form', 'bogo_in_widget_form', 10, 3);
add_filter('widget_update_callback', 'bogo_widget_update_callback', 10, 4);
add_filter('widget_display_callback', 'bogo_widget_display_callback', 10, 3);

/**
 * Add language selection options to widget form
 * 
 * @action in_widget_form
 */
function bogo_in_widget_form($widget, $return, $instance) {
	$available_languages = bogo_available_languages([
		'orderby' => 'value', 
	]);

	if (empty($available_languages)) {
		return;
	}

	$return = null;

	$selected_languages = isset($instance['bogo_locales'])
		? (array) $instance['bogo_locales']
		: array_keys($available_languages);

  ?>
  <fieldset class="bogo-locale-options">
    <legend><?= esc_html(__('Displayed on pages in', 'bogo')) ?></legend>
    <?php foreach ($available_languages as $locale => $language):
      $checked = in_array($locale, $selected_languages);
      $id_attr = $widget->get_field_id('bogo_locales') . '-' . $locale;
    ?>
    <label class="bogo-locale-option<?= $checked ? ' checked' : ''; ?>" for="<?= $id_attr ?>">
      <input type="checkbox" id="<?= $id_attr ?>" name="<?= $widget->get_field_name('bogo_locales') ?>[]" value="<?= esc_attr($locale) ?>"<?= $checked ? ' checked="checked"' : ''; ?> />
      <?= esc_html($language) ?>
    </label>
    <?php endforeach; ?>
  </fieldset>
  <?php
}

/**
 * Save language selection options for the widget
 * 
 * @filter widget_update_callback
 */
function bogo_widget_update_callback($instance, $new_instance, $old_instance, $widget) {
	if (isset($new_instance['bogo_locales']) and is_array($new_instance['bogo_locales'])) {
		$instance['bogo_locales'] = $new_instance['bogo_locales'];
	} else {
		$instance['bogo_locales'] = [];
	}

	return $instance;
}

/**
 * Hide / show widgets on specific locale
 * 
 * @filter widget_display_callback
 */
function bogo_widget_display_callback($instance, $widget, $args) {
	if (isset($instance['bogo_locales'])) {
		$locale = get_locale();	

		if (!in_array($locale, (array) $instance['bogo_locales'])) {
			$instance = false;
		}
	}

	return $instance;
}