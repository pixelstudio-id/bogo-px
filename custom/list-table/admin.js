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
  }
};

document.addEventListener('DOMContentLoaded', () => {
  localeColumn();
});
