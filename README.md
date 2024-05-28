# Bogo PX

This is modified version of [Bogo](https://wordpress.org/plugins/bogo/), a straight-forward multilingual plugin for WordPress.


## Changes

1. Added code to escape HTML tag in Gutenberg's attribute after duplicated a post.
2. Added a (+) button in Post List table as shortcut
3. Removed translated post from Post List table and combine them under the original post.

## Known Bugs

1. If you switched the base language mid-way, the Post List table won't show the proper parent post.
2. Language with "formal" or "informal" version can't work
3. When using browsersync to mask the localhost domain to like "localhost:3000", the Nav Menu replacer can't work since it checks for GUID.