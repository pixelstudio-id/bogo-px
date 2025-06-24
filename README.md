# Bogo PX

A modified version of [Bogo](https://wordpress.org/plugins/bogo/), a simple multilingual plugin for WordPress.  
Doesn’t clutter your database with extra tables like others.

## New Features

1. Flag buttons in Post List to edit/create translations. Only original posts are shown.

    ![](https://raw.github.com/hrsetyono/cdn/master/bogo/bogo-flags.png)

2. Translate Menu Item (fallbacks to translated title).

    ![](https://raw.github.com/hrsetyono/cdn/master/bogo/bogo-menu-item-localize.png)

3. Translate Category.

    ![](https://raw.github.com/hrsetyono/cdn/master/bogo/bogo-term-localize.png)

4. Editor header now has language switcher.

    ![](https://raw.github.com/hrsetyono/cdn/master/bogo/bogo-editor-switcher.png)

**OTHER FEATURES**

- **Auto Link Conversion** – Links update to the correct locale.
- **ACF Integration** – PostObject and Link field are localized.
- **User Restriction** – Limit users by language.
- **Reusable Blocks Translation** – Automatically replaced by locale version on frontend.

## How to Use

1. **Activate Plugin** → Go to Languages and select your languages.

2. **Manage Translations** in Posts/Pages via the "Locale" column:

   - Black & white flag: No translation – click to create
   - Transparent color: Draft – click to edit
   - Solid color: Published – click to edit

3. Add language switcher with:

    ```php
    <?= do_shortcode('[bogo-dropdown]'); ?>
    ```

4. Done! Dropdown appears only if a translation exists.

## Language Switcher

Use `[bogo-dropdown]` for switcher:

![](https://raw.github.com/hrsetyono/cdn/master/bogo/bogo-menu-translated.png))

Or `[bogo-dropdown style="toggle"]` for mobile-friendly version:

![](https://raw.github.com/hrsetyono/cdn/master/bogo/bogo-menu-toggle.png)

## Custom Post Type / Taxonomy

By default only support Post, Page, and Reusable Block. You can enable for custom post types:

    add_filter('bogo_localizable_post_types', fn($types) => [...$types, 'custom_pt']);

By default only support Post Category. You can enable for other taxonomies:

    add_filter('bogo_localizable_taxonomies', fn($tax) => [...$tax, 'post_tag', 'custom_tax']);

## Technical Changes

- Escaped HTML tags in duplicated post’s Gutenberg attributes.  
- `_original_post` now stores ID (not GUID).
- Removed Terms Translation page (replaced by custom field).

## Utility Functions

```php
bogo_localize_by_url($url, $force_locale)
```

Get the localized post from a base language URL.

$url (string) – Base language URL of the post.

$force_locale (string?) – Optional. Language code to force localization. Needed in API calls, as WP defaults to base language.

**RETURN**

`array` – If found, contains:

- `id` – Post ID.
- `locale` – Language code.
- `url` – Localized URL.
- `post` – WP_Post object.

`null` - If localized post not found or if URL doesn't contain "http".

```php
bogo_localize_by_id($id, $force_locale)
```

Similar to the above but using ID instead of URL.

- `$id` (string) - The ID of the post you want to get locale version of. Only ID from base language can be used.
- `$force_locale` (string?)

**RETURN**

Same as above.

```php
bogo_localize_post_by_id($id, $force_locale)
```

Same as `bogo_localize_by_id()` but it immediately returns the WP_Post object instead of the full data. Just a quick shortcut.

**RETURN**

`WP_Post` - If localized post exist

`null` - If localized post doesn't exist

## Using it in API

To get translated post in API calls, you need to set the global `locale` variable and initiate Bogo with `bogo_init_global_link_groups()`:

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

### Known Bugs

- 🔗 Switching base language mid-way breaks parent link in Post List.
- 🎌 Some flags might be incorrect due to shared languages between multiple country.
- 🔄 Category change on original doesn't sync to translations.
- 🔍 For Search bar to work properly, the `<form>` action has to be `<?= home_url() ?>`

### Future Plan

- Sync category & author from original to locale post.   
- Add direct view link for locale post in table.
- Added "Origin" column when filtering for a language.