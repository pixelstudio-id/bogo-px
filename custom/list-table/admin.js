import { bogoFetch } from '@lib/PixelFetch';
import './admin.sass';

const localeColumn = () => {
  const $buttons = document.querySelectorAll('.column-locale__inner a');
  if (!$buttons) { return; }

  $buttons.forEach(($b) => {
    $b.addEventListener('click', onClick);
  });

  /**
   *
   */
  async function onClick(e) {
    const $button = e.currentTarget;
    if ($button.getAttribute('href')) { return; }

    e.preventDefault();
    const { id, locale } = $button.dataset;
    let editLink = '';

    try {
      $button.classList.add('is-loading');
      $button.setAttribute('href', '#'); // add href so it's not clickable again

      const result = await bogoFetch.post(`/posts/${id}/translations/${locale}`, {});

      const localePost = result[locale];
      if (localePost) {
        editLink = localePost.edit_link;
      }
    } catch (err) {
      console.log(err);

      // if already exists, the error contain the existing edit link
      if (err.code === 'bogo_translation_exists') {
        editLink = err.data.edit_link;
      }
    }

    if (editLink) {
      window.open(editLink, '_blank');
      $button.classList.add('is-status-draft');
      $button.setAttribute('href', editLink);
      $button.setAttribute('target', '_blank');
    }

    $button.classList.remove('is-loading');
  }
};

document.addEventListener('DOMContentLoaded', () => {
  localeColumn();
});
