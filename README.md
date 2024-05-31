# Bogo PX

This is modified version of [Bogo](https://wordpress.org/plugins/bogo/), a straight-forward multilingual plugin for WordPress.

This does not pollute your database with tons of extra tables like other multilingual plugins.

## New Features

1. Flag buttons in Post List table as a shortcut to edit/create translation. Translated posts no longer displayed in the table.

    ![](https://raw.github.com/hrsetyono/cdn/master/bogo/bogo-flags.png)

1. Automatically change the page title in Menu with the translated version. Also a new shortcode `[bogo-dropdown]` for language selector.

    ![](https://raw.github.com/hrsetyono/cdn/master/bogo/bogo-menu-translated.png))

1. Shortcode variation `[bogo-dropdown style="toggle"]` that fits better for mobile.

    ![](https://raw.github.com/hrsetyono/cdn/master/bogo/bogo-menu-toggle.png)

1. Added language switcher in the editor's header.

    ![](https://raw.github.com/hrsetyono/cdn/master/bogo/bogo-editor-switcher.png)

**OTHER FEATURES**

1. **Automatic Link Conversion** - Links in content and menu automatically converted to the locale version.

1. **ACF Integration** - PostObject and Link field automatically converted to the locale version.

## How to Use

1. If your base language is not `en_US`, you need to add filter to change it:

    ```php
    add_filter('bogo_base_language', function() { return 'id_ID'; })
    ```

1. After activating the plugin, go to Languages and select the available language.

1. You will now see a new column called "Locale" in Posts and Pages table with transparent flags in it.

    - Black & White flag means no translation of that language. Click it to copy existing content and add that translation.
    - Colored flag with "D" means it's on Draft. Click it to edit.
    - Colored flag means it's published. Click it to edit.

1. Add `[bogo-dropdown]` shortcode somewhere in your theme to allow user to switch language.

    ```php
    <?= do_shortcode('[bogo-dropdown]'); ?>
    ```

1. (Optional) If you want to hide/add Menu Item depending on the language, each Menu Item now has checkboxes on it's visibility.

1. Done!

## Technical Changes

- Added code to escape HTML tag in Gutenberg's attribute after duplicated a post.
- Changed the `_original_post` meta to store ID instead of GUID for easier querying.

## Known Bugs

- If you switched the base language mid-way, the Post List table won't show the proper parent post.
- Language with "formal" or "informal" version can't work.
- You might encounter wrong or empty flag as the SVG files we use doesn't follow international naming.