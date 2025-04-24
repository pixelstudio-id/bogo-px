const { wp } = window;

function onReady() {
  const { addFilter } = wp.hooks;
  const { createHigherOrderComponent } = wp.compose;
  const { Fragment } = wp.element;
  const { BlockPreview, BlockIcon } = wp.blockEditor;
  const { getEntityRecords } = wp.data.select( 'core' );

  const { unregisterBlockType } = wp.blocks;

  const HIDDEN_REUSABLE_BLOCK_IDS = window.bogoReusableBlocks.localizedIds;

  // Filter the inserter to exclude specific reusable blocks
  const withFilteredInserterItems = createHigherOrderComponent( ( BlockListBlock ) => ( props ) => {
    const { name, attributes } = props;

    // Only filter core/block (reusable block wrapper)
    if (name === 'core/block') {
      if ( HIDDEN_REUSABLE_BLOCK_IDS.includes( attributes.ref ) ) {
        return null; // Hide this block
      }
    }

    return <BlockListBlock { ...props } />;
  }, 'withFilteredInserterItems' );

  addFilter(
    'editor.BlockListBlock',
    'custom/hide-reusable-blocks',
    withFilteredInserterItems
  );
}

wp.domReady(onReady);
