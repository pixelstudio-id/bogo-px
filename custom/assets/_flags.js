import MyFetch from './_MyFetch';

const flags = {
  init() {
    const $buttons = document.querySelectorAll('.column-locale__inner a');
    if (!$buttons) { return; }

    $buttons.forEach(($b) => {
      $b.addEventListener('click', this.onClick);
    });
  },

  async onClick(e) {
    const $button = e.currentTarget;
    if ($button.getAttribute('href') !== '#') { return; }

    e.preventDefault();
    const { id, locale } = $button.dataset;
    const myFetch = new MyFetch(window.bogo.apiSettings.root, {
      'X-WP-Nonce': window.bogo.apiSettings.nonce,
    });

    try {
      $button.classList.add('is-loading');
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

export default flags;
