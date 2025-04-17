import './public.sass';

const bogoDropdown = () => {
  const $toggles = document.querySelectorAll('.bogo-dropdown.is-style-toggle .bogo-dropdown__button');
  $toggles.forEach(($t) => {
    $t.addEventListener('click', this.onClick);
  });

  /**
   *
   */
  function onClick(e) {
    const $wrapper = e.currentTarget.closest('.bogo-dropdown');
    $wrapper.classList.toggle('is-toggled');
  }
};

document.addEventListener('DOMContentLoaded', () => {
  bogoDropdown();
});
