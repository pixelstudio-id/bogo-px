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

### Other Features

- Automatically copy the post/page when creating draft for new translation.
- ACF Post Object field automatically converted to the translated version if exist.

## Technical Changes

- Added code to escape HTML tag in Gutenberg's attribute after duplicated a post.
- Changed the `_original_post` meta to store ID instead of GUID for easier querying.

## Known Bugs

- If you switched the base language mid-way, the Post List table won't show the proper parent post.
- Language with "formal" or "informal" version can't work.