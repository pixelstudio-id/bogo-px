import './editor.sass';

const { wp } = window;

function onReady() {
  const dropdown = window.bogoLanguageDropdown.select_html;

  setTimeout(addLanguageDropdown, 100);

  function addLanguageDropdown() {
    const $header = document.querySelector('.edit-post-header__toolbar');
    if ($header) {
      $header.insertAdjacentHTML('beforeend', dropdown);
    }
  }
}

wp.domReady(onReady);
