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

function onReady() {
  localeColumn.init();
}

document.addEventListener('DOMContentLoaded', onReady);
