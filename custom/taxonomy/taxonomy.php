<?php

add_action('admin_init', 'bogo_init_add_fields_to_taxonomies');
remove_filter('get_term', 'bogo_get_term_filter', 10, 2);
add_filter('get_term', 'bogo_get_term_translate', 10, 2);

/**
 * Get taxonomies that can be localized
 */
function bogoHelper_get_localizable_taxonomies() {
  static $taxonomies = [];

  if (!$taxonomies) {
    $taxonomies = apply_filters('bogo_localizable_taxonomies', ['category']);
  }

  return $taxonomies;
}

/**
 * @action admin_init
 */
function bogo_init_add_fields_to_taxonomies() {
  $taxonomies = Bogo::get_localizable_taxonomies();

  foreach ($taxonomies as $tax) {
    add_action("{$tax}_edit_form_fields", 'bogo_add_fields_to_taxonomies', 20, 2);
    add_action("saved_{$tax}", 'bogo_after_save_taxonomy');
  }
}

/**
 * @action {$taxonomy}_edit_form_fields
 */
function bogo_add_fields_to_taxonomies($term, $taxonomy) {
  $locales = bogo_available_languages();
  unset($locales[BOGO_DEFAULT_LOCALE]);

  $fields = get_term_meta($term->term_id, 'bogo_fields', true);

  // if still using the old data format
  if (!$fields) {
    $names = get_term_meta($term->term_id, 'bogo_names', true) ?: [];
    $descs = get_term_meta($term->term_id, 'bogo_descriptions', true) ?: [];

    $fields = [];
    foreach ($names as $locale => $name) {
      $fields[$locale] = [
        'n' => $name,
        'd' => $descs[$locale] ?? '',
      ];
    }
  }

  foreach ($locales as $locale => $label) {
    $name = isset($fields[$locale]) && !empty($fields[$locale]['n']) ? $fields[$locale]['n'] : '';
    $desc = isset($fields[$locale]) && !empty($fields[$locale]['d']) ? $fields[$locale]['d'] : '';

    $locales[$locale] = [
      'label' => $label,
      'name' => $name,
      'description' => $desc,
      'classes' => $name ? '' : 'is-empty',
      'field_name_n' => "bogo_fields[{$locale}][n]",
      'field_name_d' => "bogo_fields[{$locale}][d]",
    ];
  }

  // ?>

  <tr class="form-field bogo-term-names">
		<th><label>Localized Names and Descriptions</label></th>
		<td>
      <?php foreach ($locales as $locale => $att): ?>
      <div class="bogo-field <?= esc_attr($att['classes']) ?>">
        <label>
          <i class="flag flag-<?= esc_attr($locale) ?>"></i>
          <span>
            <?= esc_html($att['label']) ?>
          </span>
          <input
            type="text"
            placeholder="<?= esc_attr($term->name) ?>"
            name="<?= esc_attr($att['field_name_n']) ?>"
            value="<?= esc_attr($att['name']) ?>"
          >
        </label>
        <textarea
          placeholder="<?= esc_attr($att['label']) ?> Description"
          rows="5"
          name="<?= esc_attr($att['field_name_d']) ?>"
        ><?= esc_textarea($att['description']) ?></textarea>
      </div>
      <?php endforeach; ?>
		</td>
	</tr>
  <?php
}

/**
 * @action saved_{$taxonomy}
 */
function bogo_after_save_taxonomy($term_id) {
  $fields = $_POST['bogo_fields'] ?? null;
  if ($fields) {
    update_term_meta($term_id, 'bogo_fields', $fields);
  }
}

/**
 * @filter get_term
 */
function bogo_get_term_translate($term, $taxonomy) {
  $locale = get_locale();
  if (Bogo::is_default_locale($locale)) { return $term; }
  if (!in_array($taxonomy, Bogo::get_localizable_taxonomies())) { return $term; }

  $fields = get_term_meta($term->term_id, 'bogo_fields', true) ?: [];

  // If still using the old data format
  if (!$fields) {
    $names = get_term_meta($term->term_id, 'bogo_names', true) ?: [];
    $names = is_string($names) ? json_decode($names, true) : $names; // if names is JSON string (from deprecated code)

    $descs = get_term_meta($term->term_id, 'bogo_descriptions', true) ?: [];

    foreach ($names as $locale => $name) {
      $fields[$locale] = [
        'n' => $name,
        'd' => $descs[$locale] ?? '',
      ];
    }
  }

  $term->name = isset($fields[$locale]) && !empty($fields[$locale]['n']) ? $fields[$locale]['n'] : $term->name;
  $term->description = isset($fields[$locale]) && !empty($fields[$locale]['d']) ? $fields[$locale]['d'] : $term->description;

  return $term;
}