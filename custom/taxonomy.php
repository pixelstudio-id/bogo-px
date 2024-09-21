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

  $names = get_term_meta($term->term_id, 'bogo_names', true);
  $descs = get_term_meta($term->term_id, 'bogo_descriptions', true);

  // if names is JSON string (from deprecated code), convert it first
  if (is_string($names)) {
    $names = json_decode($names, true);
  }

  // ?>

  <tr class="form-field bogo-term-names">
		<th><label>Localized Names and Descriptions</label></th>
		<td>
      <?php foreach ($locales as $locale => $label):
        $name = $names[$locale] ?? '';
        $desc = $descs[$locale] ?? '';
        $classes = $name ? '' : 'is-empty';
      ?>
      <div>
        <label class="bogo-field <?= $classes ?>">
          <i class="flag flag-<?= $locale ?>"></i>
          <span><?= $label ?></span>
          <input
            type="text"
            placeholder="<?= $term->name ?>"
            name="bogo_names[<?= $locale ?>]"
            value="<?= $name ?>"
          >
        </label>
        <textarea
          placeholder="<?= $label ?> Description"
          rows="5"
          name="bogo_descriptions[<?= $locale ?>]"
        ><?= $desc ?></textarea>
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
  $names = $_POST['bogo_names'] ?? null;
  if ($names) {
    update_term_meta($term_id, 'bogo_names', $names);
  }

  $descs = $_POST['bogo_descriptions'] ?? null;
  if ($descs) {
    update_term_meta($term_id, 'bogo_descriptions', $descs);
  }
}

/**
 * @filter get_term
 */
function bogo_get_term_translate($term, $taxonomy) {
  $locale = get_locale();
  if (Bogo::is_default_locale($locale)) { return $term; }
  if (!in_array($taxonomy, Bogo::get_localizable_taxonomies())) { return $term; }

  $names = get_term_meta($term->term_id, 'bogo_names', true);
  if ($names) {
    // if names is JSON string (from deprecated code), convert it first
    if (is_string($names)) {
      $names = json_decode($names, true);
    }

    $term->name = empty($names[$locale]) ? $term->name : $names[$locale];
  }

  $descs = get_term_meta($term->term_id, 'bogo_descriptions', true);
  if ($descs) {
    $term->description = empty($descs[$locale]) ? $term->description : $descs[$locale];
  }

  return $term;
}