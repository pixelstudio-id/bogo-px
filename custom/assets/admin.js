import { bogoFetch } from './_MyFetch';
import './admin.sass';

const localeColumn = {
  init() {
    const $buttons = document.querySelectorAll('.column-locale__inner a');
    if (!$buttons) { return; }

    $buttons.forEach(($b) => {
      $b.addEventListener('click', this.onClick);
    });
  },

  async onClick(e) {
    const $button = e.currentTarget;
    if ($button.getAttribute('href')) { return; }

    e.preventDefault();
    const { id, locale } = $button.dataset;

    try {
      $button.classList.add('is-loading');
      $button.setAttribute('href', '#'); // add href so it's not clickable again

      const result = await bogoFetch.post(`/posts/${id}/translations/${locale}`, {});
      $button.classList.remove('is-loading');

      const localePost = result[locale];
      if (localePost) {
        $button.classList.add('is-status-draft');
        $button.setAttribute('href', localePost.edit_link);
        $button.setAttribute('target', '_blank');

        window.open(localePost.edit_link, '_blank');
      }
    } catch (err) {
      console.log(err);
    }
  },
};

/**
 * Bogo fields that exist in Menu and Category editor
 */
const bogoField = {
  init() {
    const $fields = document.querySelectorAll('.bogo-field input');
    if (!$fields) { return; }

    $fields.forEach(($field) => {
      $field.addEventListener('change', this.onChange);
    });
  },

  onChange(e) {
    const { value } = e.currentTarget;
    const $wrapper = e.currentTarget.closest('label');

    $wrapper.classList.toggle('is-empty', !value);
  },
};

/**
 *
 */
const menuLocale = {
  init() {
    if (!document.body.classList.contains('nav-menus-php')) { return; }

    const $checkboxes = document.querySelectorAll('.menu-item .bogo-locale-options input[type="checkbox"]');
    $checkboxes.forEach(($cb) => {
      $cb.addEventListener('change', this.onCheckboxChange);
    });

    const $mainTitles = document.querySelectorAll('.edit-menu-item-title');
    $mainTitles.forEach(($t) => {
      $t.addEventListener('input', this.onMainTitleInput);
    });
  },

  /**
   * Hide or show the translation field
   */
  onCheckboxChange(e) {
    const { checked } = e.currentTarget;
    const value = e.currentTarget.getAttribute('value');
    const $input = e.currentTarget.closest('.menu-item-settings').querySelector(`label [name*="[${value}]"]`);

    // abort if no input
    if (!$input) { return; }

    const $inputWrapper = $input.closest('label');

    if (checked) {
      $inputWrapper.style.display = '';
    } else {
      $inputWrapper.style.display = 'none';
    }
  },

  /**
   * Edit the main title in each menu item
   */
  onMainTitleInput(e) {
    const $input = e.currentTarget;
    const $wrapper = $input.closest('.menu-item');
    const $bogoInputs = $wrapper.querySelectorAll('.bogo-menu-titles label:not(.has-fixed-placeholder) input');

    $bogoInputs.forEach(($i) => {
      $i.setAttribute('placeholder', $input.value);
    });
  },
};

/**
 *
 */
const termLocale = {
  init() {
    if (!document.body.classList.contains('term-php')) { return; }

    const $name = document.querySelector('input#name');
    if ($name) {
      $name.addEventListener('input', this.onNameInput);
    }
  },

  /**
   * Update the placeholder after changing the main name
   */
  onNameInput(e) {
    const $input = e.currentTarget;
    const $bogoInputs = document.querySelectorAll('.bogo-term-names .bogo-field input');

    $bogoInputs.forEach(($i) => {
      $i.setAttribute('placeholder', $input.value);
    });
  },
};

function onReady() {
  bogoField.init();
  localeColumn.init();
  menuLocale.init();
  termLocale.init();
}

document.addEventListener('DOMContentLoaded', onReady);
