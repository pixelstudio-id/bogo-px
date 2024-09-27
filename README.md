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

1. **User Restriction** - Limit users from editing certain language.

## How to Use

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
add_filter('bogo_localizable_post_types', function($post_types) {
  $post_types[] = 'custom_pt';
  return $post_types;
});
```

For taxonomy translation, it adds custom fields to the Term's setting page. By default it's active on Post Category. To add it on Post Tags or custom taxonomy, add this code:

```php
add_filter('bogo_localizable_taxonomies', function($taxonomies) {
  $taxonomies[] = 'post_tag';
  $taxonomies[] = 'custom_tax';
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
- If parent post changed category, the other language isn't changed.

### Future Plan

- Add translatable Description for Menu.
- Allow locale post listing in trash to restore/permanently delete.
- Change the category & author of locale post when the original post is changed too.
- Add direct link to view the locale post within table.

### Using it in API

If you want to get translated page during API call, you need to change the locale and initiate the BOGO's global object:

```php

/**
 * @route GET /page/:id?lang=xx
 */
function api_callback_get_page($params) {
  $id = $params['id'];
  $lang = $params['lang'] ?? '';

  if ($lang) {
    add_filter('locale', function($locale) use ($lang) {
      return $lang;
    });
    bogo_init_global_link_groups()
  }

  $page = get_post($id); // now this will return the translated version, if any
  return $page
}


```