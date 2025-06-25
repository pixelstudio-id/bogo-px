import './admin.sass';

/**
 *
 */
const menuLocale = () => {
  if (!document.body.classList.contains('nav-menus-php')) { return; }

  const $checkboxes = document.querySelectorAll('.menu-item .bogo-locale-options input[type="checkbox"]');
  $checkboxes.forEach(($cb) => {
    $cb.addEventListener('change', onCheckboxChange);
  });

  const $mainTitles = document.querySelectorAll('.edit-menu-item-title');
  $mainTitles.forEach(($t) => {
    $t.addEventListener('input', onMainTitleInput);
  });

  const $fields = document.querySelectorAll('.bogo-field input');
  $fields.forEach(($field) => {
    $field.addEventListener('change', onChangeInput);
  });

  /**
   * Hide or show the translation field
   */
  function onCheckboxChange(e) {
    const { checked } = e.currentTarget;
    const value = e.currentTarget.getAttribute('value');
    const $input = e.currentTarget.closest('.menu-item-settings').querySelector(`label [name*="[${value}]"]`);

    // abort if no input
    if (!$input) { return; }

    const $wrapper = $input.closest('.bogo-menu-field');

    if (checked) {
      $wrapper.style.display = '';
    } else {
      $wrapper.style.display = 'none';
    }
  }

  /**
   * Edit the main title in each menu item
   */
  function onMainTitleInput(e) {
    const $input = e.currentTarget;
    const $wrapper = $input.closest('.menu-item');
    const $bogoInputs = $wrapper.querySelectorAll('.bogo-menu-titles label:not(.has-fixed-placeholder) input');

    $bogoInputs.forEach(($i) => {
      $i.setAttribute('placeholder', $input.value);
    });
  }

  /**
   * When filling the field
   */
  function onChangeInput(e) {
    const { value } = e.currentTarget;
    const $wrapper = e.currentTarget.closest('label');

    $wrapper.classList.toggle('is-empty', !value);
  }
};

document.addEventListener('DOMContentLoaded', () => {
  menuLocale();
});
