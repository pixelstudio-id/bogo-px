import './admin.sass';

/**
 *
 */
const termLocale = () => {
  if (!document.body.classList.contains('term-php')) { return; }

  const $name = document.querySelector('input#name');
  if ($name) {
    $name.addEventListener('input', onNameInput);
  }

  const $fields = document.querySelectorAll('.bogo-field input');
  $fields.forEach(($field) => {
    $field.addEventListener('change', onChangeLocaleField);
  });

  /**
   * Update the placeholder after changing the main name
   */
  function onNameInput(e) {
    const $input = e.currentTarget;
    const $bogoInputs = document.querySelectorAll('.bogo-term-names .bogo-field input');

    $bogoInputs.forEach(($i) => {
      $i.setAttribute('placeholder', $input.value);
    });
  }

  /**
   * Toggle "is-empty" class
   */
  function onChangeLocaleField(e) {
    const { value } = e.currentTarget;
    const $wrapper = e.currentTarget.closest('label');

    $wrapper.classList.toggle('is-empty', !value);
  }
};

document.addEventListener('DOMContentLoaded', () => {
  termLocale();
});
