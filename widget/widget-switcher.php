<?php if (!defined('ABSPATH')) exit;

// @deprecated - no longer used

add_action('widgets_init', 'bogo_widgets_init', 10, 0);

/**
 * @action widgets_init
 */
function bogo_widgets_init() {
	register_widget('Bogo_Widget_Language_Switcher');
}

/**
 * Language switcher widget class
 */
class Bogo_Widget_Language_Switcher extends WP_Widget {
	function __construct() {
		$widget_ops = [
			'description' => __('Language switcher widget by Bogo plugin', 'bogo'),
		];

		$control_ops = [];
		WP_Widget::__construct('bogo_language_switcher',
			__('Language Switcher', 'bogo'),
			$widget_ops, $control_ops
		);
	}

	function widget($args, $instance) {
		$title = apply_filters( 'widget_title',
			empty($instance['title'])
				? __('Language Switcher', 'bogo')
				: $instance['title'],
			$instance, $this->id_base
		);

		echo $args['before_widget'];

		if ($title) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		echo BogoSwitcher::render_list();

		echo $args['after_widget'];
	}

	function form($instance) {
		$instance = wp_parse_args((array) $instance, ['title' => '']);
		$title = strip_tags($instance['title']);

		echo '<p><label for="' . $this->get_field_id('title') . '">' . esc_html(__('Title:', 'bogo')) . '</label> <input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . esc_attr($title) . '" /></p>';
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;

		$new_instance = wp_parse_args(
			(array) $new_instance,
			['title' => '']
		);

		$instance['title'] = strip_tags($new_instance['title']);

		return $instance;
	}
}

