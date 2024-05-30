import './public.sass';

const bogoDropdown = {
  init() {
    const $toggles = document.querySelectorAll('.bogo-dropdown.is-style-toggle .bogo-dropdown__button');

    $toggles.forEach(($t) => {
      $t.addEventListener('click', this.onClick);
    });
  },

  onClick(e) {
    const $wrapper = e.currentTarget.closest('.bogo-dropdown');
    $wrapper.classList.toggle('is-toggled');
  },
};

function onReady() {
  bogoDropdown.init();
}

document.addEventListener('DOMContentLoaded', onReady);
