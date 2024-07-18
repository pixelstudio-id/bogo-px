<?php

add_action('admin_init', 'bogo_init_add_fields_to_taxonomies');
remove_filter('get_term', 'bogo_get_term_filter', 10, 2);
add_filter('get_term', 'bogo_get_term_translate', 10, 2);


/**
 * @action admin_init
 */
function bogo_init_add_fields_to_taxonomies() {
  $taxonomies = apply_filters('bogo_localizable_taxonomies', ['category', 'post_tag']);

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

  $values = json_decode(get_term_meta($term->term_id, 'bogo_names', true), true);
  ?>
  <tr class="form-field bogo-term-names">
		<th><label>Localized Names</label></th>
		<td>
      <?php foreach ($locales as $locale => $label):
        $value = $values[$locale] ?? '';
        $classes = $value ? '' : 'is-empty';
      ?>
        <label class="bogo-field <?= $classes ?>">
          <i class="flag flag-<?= $locale ?>"></i>
          <span><?= $label ?></span>
          <input
            type="text"
            placeholder="<?= $term->name ?>"
            name="bogo_names[<?= $locale ?>]"
            value="<?= $value ?>"
          >
        </label>
      <?php endforeach; ?>
		</td>
	</tr>
  <?php
}

/**
 * @action saved_{$taxonomy}
 */
function bogo_after_save_taxonomy($term_id) {
  $name = $_POST['bogo_names'] ?? null;
  if (!$name) { return; }

  update_term_meta($term_id, 'bogo_names', json_encode($name, JSON_UNESCAPED_UNICODE));
}

/**
 * @filter get_term
 */
function bogo_get_term_translate($term, $taxonomy) {
  $locale = get_locale();
  if ($locale === BOGO_DEFAULT_LOCALE) { return $term; }

  $names = json_decode(get_term_meta($term->term_id, 'bogo_names', true), true);
  if (!$names) { return $term; }

  $term->name = empty($names[$locale]) ? $term->name : $names[$locale];
  return $term;
}