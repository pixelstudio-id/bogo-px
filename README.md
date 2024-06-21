# Bogo PX

This is modified version of [Bogo](https://wordpress.org/plugins/bogo/), a straight-forward multilingual plugin for WordPress.

This does not pollute your database with tons of extra tables like other multilingual plugins.

## New Features

1. Flag buttons in Post List table as a shortcut to edit/create translation. Translated posts no longer displayed in the table.

    ![](https://raw.github.com/hrsetyono/cdn/master/bogo/bogo-flags.png)

1. Translate Menu Item. If empty, it will use the translated Title of that post/page.

    ![](https://raw.github.com/hrsetyono/cdn/master/bogo/bogo-menu-item-localize.png)

1. Translate Category.

    ![](https://raw.github.com/hrsetyono/cdn/master/bogo/bogo-term-localize.png)

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

1. Done! Note that if a page doesn't have any translated version, the dropdown won't appear.

## Language Switcher

Use the shortcode `[bogo-dropdown]` to output a language switcher:

![](https://raw.github.com/hrsetyono/cdn/master/bogo/bogo-menu-translated.png))


Use the variation `[bogo-dropdown style="toggle"]` that fits better for mobile.

![](https://raw.github.com/hrsetyono/cdn/master/bogo/bogo-menu-toggle.png)

## Custom Post Type / Taxonomy

By default this plugin only add locale option on Pages and Posts. To add it on a custom post type, add this code:

```php
// replace 'YOURCPT' with the custom post type name
add_filter('bogo_localizable_post_types', function($post_types) {
  $post_types[] = 'YOURCPT';
  return $post_types;
});
```

For taxonomy translation, it adds custom fields to the Term's setting page. By default it's active on Category and Tags. To add it on a custom taxonomy, add this code:

```php
// replace 'YOURTAX' with the custom taxonomy
add_filter('bogo_localizable_taxonomies', function($taxonomies) {
  $taxonomies[] = 'YOURTAX';
  return $taxonomies;
});
```

### Technical Changes

- Added code to escape HTML tag in Gutenberg's attribute after duplicated a post.
- Changed the `_original_post` meta to store ID instead of GUID for easier querying.
- Removed the Terms Translation page because it's changed to using custom field.

### Known Bugs

- If you switched the base language mid-way, the Post List table won't show the proper parent post.
- Some languages are spoken in multiple countries, therefore the flags might be wrong.

### Future Plan

- Add interface to add translation within Gutenberg.
- Add translatable Description for Menu and Term.
- Add conditional check whether the user has access to edit that language or not. This role thing is already part of BOGO, just need to add conditional and hide the language option.