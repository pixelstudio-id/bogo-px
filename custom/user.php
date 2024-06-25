<?php

add_action('admin_head', 'bogo_add_style_in_user_profile');

/**
 * 
 */
function bogo_add_style_in_user_profile() {
  global $pagenow;
  if ($pagenow !== 'user-edit.php') { return; }

  ?>
  <style>
    .bogo-locale-option[for="bogo_accessible_locale-<?= BOGO_DEFAULT_LOCALE ?>"] {
      display: none !important;
    }
  </style>
  <?php
}