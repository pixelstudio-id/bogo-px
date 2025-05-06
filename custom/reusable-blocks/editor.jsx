import './editor.sass';

const { wp } = window;
const { createHigherOrderComponent } = wp.compose;

const addTranslationNotice = createHigherOrderComponent((BlockEdit) => {  
  return (props) => {
    if (props.name !== 'core/block') {
      return (
        <BlockEdit { ...props } />
      );
    }

    if (!window.bogoReusableBlocks) {
      return (
        <BlockEdit { ...props } />
      );
    }

    const { ref } = props.attributes;
    const { localizedBlocks, locale, localeName, indexURL, editURL } = window.bogoReusableBlocks;
    const block = localizedBlocks[ref] || null;

    const message = block
      ? `This block has ${localeName} translation`
      : `No ${localeName} translation found`;
    const buttonText = block
      ? 'EDIT »'
      : 'CREATE »';
    const buttonLink = block
      ? editURL.replace('$$$', block[locale])
      : indexURL;

    return (
      <>
        <BlockEdit key="edit" { ...props } />
        <label class="bogopx-reusable-label">
          <i class={`flag flag-${locale}`}></i>
          <span>{message}</span>
          <a href={buttonLink} target="_blank">
            {buttonText}
          </a>
        </label>
      </>
    );
  };
}, 'addTranslationNotice');

wp.hooks.addFilter('editor.BlockEdit', 'bogo-px/add-translation-notice', addTranslationNotice);
