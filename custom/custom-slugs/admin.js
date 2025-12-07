import './admin.sass';
import { bogoFetch } from '@lib/PixelFetch';

function onReady() {
  const $slugFields = document.querySelectorAll('.bogopx-slug-field');
  if (!$slugFields) { return; }

  $slugFields.forEach(($field) => {
    const $button = $field.querySelector('.button');
    const $input = $field.querySelector('input[type="text"]');

    $button.addEventListener('click', async (e) => {
      e.preventDefault();
      $field.classList.add('is-loading');

      try {
        await bogoFetch.post('/custom-slugs', {
          locale: $input.dataset.locale,
          slug: $input.value,
        });
        $field.classList.add('is-success');
        setTimeout(() => {
          $field.classList.remove('is-success');
        }, 1000);
      } catch (error) {
        $field.insertAdjacentHTML('beforeend', `<small>${error.message}</small>`);
      }

      $field.classList.remove('is-loading');
    });
  });
}

document.addEventListener('DOMContentLoaded', onReady);
