import './editor.sass';

const { wp } = window;

function onReady() {
  const { current_option, options } = window.bogoLanguageDropdown;

  const currentHTML = `<div class="bogo-dropdown__button" tabindex="0">
    <i class="fi fi-${current_option.flag_id}"></i>
    <span>${current_option.label}</span>
  </div>`;

  let optionsHTML = '';
  options.forEach((option) => {
    const statusHTML = option.status === 'draft'
      ? '<b>DRAFT</b>'
      : '';

    optionsHTML += `<li>
      <a href="${option.url}">
        <i class="fi fi-${option.flag_id}"></i>
        <span>
          ${option.label}
          ${statusHTML}
        </span>
      </a>
    </li>`;
  });

  const selectHTML = `<div class="bogo-dropdown">
    ${currentHTML}
    <ul class="bogo-dropdown__links">
      ${optionsHTML}
    </ul>
  </div>`;

  setTimeout(addLanguageDropdown, 100);
  function addLanguageDropdown() {
    const $header = document.querySelector('.edit-post-header__toolbar');
    if ($header) {
      $header.insertAdjacentHTML('beforeend', selectHTML);
    }
  }
}

wp.domReady(onReady);
