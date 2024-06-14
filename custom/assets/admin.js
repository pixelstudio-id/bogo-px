import MyFetch from './_MyFetch';
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
    const myFetch = new MyFetch(window.bogo.apiSettings.root, {
      'X-WP-Nonce': window.bogo.apiSettings.nonce,
    });

    try {
      $button.classList.add('is-loading');
      $button.setAttribute('href', '#'); // add href so it's not clickable again

      const result = await myFetch.post(`/posts/${id}/translations/${locale}`, {});
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

const navLocale = {
  init() {
    const $checkboxes = document.querySelectorAll('.menu-item-custom .bogo-locale-options input[type="checkbox"]');
    $checkboxes.forEach(($cb) => {
      $cb.addEventListener('change', this.onCheckboxChange);
    });

    const $titles = document.querySelectorAll('.menu-item-custom [name*="_bogo_title"]');
    $titles.forEach(($t) => {
      $t.addEventListener('change', this.onTitleChange);
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
   * Add or remove the 'is-empty' class
   */
  onTitleChange(e) {
    const { value } = e.currentTarget;
    const $wrapper = e.currentTarget.closest('label');

    $wrapper.classList.toggle('is-empty', !value);
  },
};

function onReady() {
  localeColumn.init();
  navLocale.init();
}

document.addEventListener('DOMContentLoaded', onReady);
