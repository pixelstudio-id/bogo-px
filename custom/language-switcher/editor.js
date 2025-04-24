import { bogoFetch } from '@lib/PixelFetch';
import './editor.sass';

const { wp } = window;

function onReady() {
  const { currentOption, options, optionsCreate } = window.bogoLanguageDropdown;

  const currentHTML = `<div class="bogo-dropdown__button" tabindex="0">
    <i class="flag flag-${currentOption.locale}"></i>
    <span>${currentOption.label}</span>
  </div>`;

  let optionsHTML = '';
  if (options.length >= 1) {
    optionsHTML += '<li><h6>AVAILABLE LANGUAGES</h6></li>';
    options.forEach((option) => {
      const statusHTML = option.status === 'draft'
        ? '<b>DRAFT</b>'
        : '';
      optionsHTML += `<li>
        <a href="${option.url}">
          <i class="flag flag-${option.locale}"></i>
          <span>
            ${option.label}
            ${statusHTML}
          </span>
        </a>
      </li>`;
    });
  }

  let optionsNewHTML = '';
  if (optionsCreate.length >= 1) {
    optionsNewHTML += '<li><h6>ADD TRANSLATION</h6></li>';
    optionsCreate.forEach((option) => {
      optionsNewHTML += `<li>
        <a data-route="${option.url}" data-locale="${option.locale}">
          <i class="flag flag-${option.locale}"></i>
          <span>
            ${option.label}
            <b>DRAFT</b>
          </span>
        </a>
      </li>`;
    });
  }

  let dropdownSize = '';
  if (window.bogoLanguageDropdown.length > 10) {
    dropdownSize = 'huge';
  } else if (window.bogoLanguageDropdown.length > 5) {
    dropdownSize = 'large';
  }

  const selectHTML = `<div class="bogo-dropdown">
    ${currentHTML}
    <div class="bogo-options is-${dropdownSize}">
      <ul class="bogo-options__available">
        ${optionsHTML}
      </ul>
      <ul class="bogo-options__new">
        ${optionsNewHTML}
      </ul>
  </div>`;

  setTimeout(addLanguageDropdown, 500);

  /**
   * Output the HTML at the top bar of Gutenberg
   */
  function addLanguageDropdown() {
    const $header = document.querySelector('.edit-post-header__toolbar');
    if ($header) {
      $header.insertAdjacentHTML('beforeend', selectHTML);
    }

    const $createLinks = $header.querySelectorAll('.bogo-options__new a');
    $createLinks.forEach(($link) => {
      $link.addEventListener('click', _onCreateNew);
    });
  }

  /**
   * Create new translation and redirect to its edit page
   */
  async function _onCreateNew(e) {
    const $link = e.currentTarget;
    const $wrapper = $link.closest('li');
    const { route, locale } = $link.dataset;

    $wrapper.classList.add('is-loading');
    let editLink = '';

    try {
      const result = await bogoFetch.post(route);
      const localePost = result[locale];

      editLink = localePost.edit_link;
    } catch (err) {
      console.log(err);

      // if already exists, the error contain the existing edit link
      if (err.code === 'bogo_translation_exists') {
        editLink = err.data.edit_link;
      }
    }

    $wrapper.classList.remove('is-loading');
    if (editLink) {
      $link.setAttribute('href', editLink);

      // move it to available <ul>
      const $targetToMove = $wrapper.closest('.bogo-options').querySelector('.bogo-options__available');
      if ($targetToMove) {
        $targetToMove.insertAdjacentHTML('beforeend', $wrapper.outerHTML);
        $wrapper.remove();
      }
      window.open(editLink, '_blank');
    }
  }
}

wp.domReady(onReady);
