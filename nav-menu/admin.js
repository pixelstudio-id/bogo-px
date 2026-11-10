import './admin.sass';

/**
 *
 */
const menuLocale = () => {
  if (!document.body.classList.contains('nav-menus-php')) { return; }

  const $bogoCheckboxes = document.querySelectorAll('.menu-item .bogo-locale-options input[type="checkbox"]');
  $bogoCheckboxes.forEach(($cb) => {
    $cb.addEventListener('change', onCheckboxChange);
  });

  const $mainTitles = document.querySelectorAll('.edit-menu-item-title');
  $mainTitles.forEach(($t) => {
    $t.addEventListener('input', onMainTitleInput);
  });

  const $bogoInputs = document.querySelectorAll('.bogo-field input, .bogo-field textarea');
  $bogoInputs.forEach(($i) => {
    $i.addEventListener('input', onInputField);
  });

  // Screen options for description
  const $cbDescriptionSO = document.querySelector('.metabox-prefs input[type="checkbox"][name="description-hide"]');
  if ($cbDescriptionSO) {
    $cbDescriptionSO.addEventListener('change', onChangeDescriptionSO);
    onChangeDescriptionSO({ currentTarget: $cbDescriptionSO });
  }

  /**
   * Hide or show the translation field
   */
  function onCheckboxChange(e) {
    const { checked } = e.currentTarget;
    const value = e.currentTarget.getAttribute('value');
    const $wrapper = e.currentTarget.closest('.menu-item-settings').querySelector(`.bogo-field[data-locale="${value}"]`);

    // abort if no field
    if (!$wrapper) { return; }

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
    const $mainTitle = e.currentTarget;
    const $outer = $mainTitle.closest('.menu-item');
    const $inputs = $outer.querySelectorAll('.bogo-field:not(.has-fixed-placeholder) input');

    $inputs.forEach(($i) => {
      $i.setAttribute('placeholder', $mainTitle.value);
    });
  }

  /**
   * Listener for input fields as we type or paste
   */
  function onInputField(e) {
    const { value } = e.currentTarget;

    // check if empty or not
    const $field = e.currentTarget.closest('.bogo-field');
    $field.classList.toggle('is-empty', !value);

    // set the `name` attribute so it can be submitted
    const $wrapper = $field.closest('.bogo-fields');
    if (!$wrapper.classList.contains('is-dirty')) {
      const $inputs = $wrapper.querySelectorAll('input, textarea');
      $inputs.forEach(($i) => {
        const name = $i.getAttribute('data-name');
        $i.setAttribute('name', name);
      });
    }
    $wrapper.classList.add('is-dirty');
  }

  /**
   * Add class to hide or show the description field
   */
  function onChangeDescriptionSO(e) {
    const $cb = e.currentTarget;
    if ($cb.checked) {
      document.body.classList.remove('bogo-show-description');
    } else {
      document.body.classList.add('bogo-show-description');
    }
  }
};

document.addEventListener('DOMContentLoaded', () => {
  menuLocale();
});
