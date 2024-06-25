<?php

define('BOGO_DEFAULT_LOCALE', apply_filters('bogo_default_locale', bogo_get_default_locale()));

require_once __DIR__ . '/global-link-groups.php';
require_once __DIR__ . '/enqueue.php';
require_once __DIR__ . '/content.php';
require_once __DIR__ . '/list-table.php';
require_once __DIR__ . '/taxonomy.php';
require_once __DIR__ . '/nav-menu.php';
require_once __DIR__ . '/acf.php';
require_once __DIR__ . '/shortcode.php';
require_once __DIR__ . '/user.php';