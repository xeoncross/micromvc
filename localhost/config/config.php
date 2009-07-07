<?php

//Theme to load
$config['theme'] = 'default';
//Default controller to call
$config['default_controller'] = 'welcome';
//Default method to run
$config['default_method'] = 'index';
//Characters to allow in the URI string ($_GET data)
$config['permitted_uri_chars'] = 'a-z 0-9~%.:_\-';

//Enable error reporting?
//error_reporting(E_ALL|E_STRICT);

/*
 * Set the server timezone
 * see: http://us3.php.net/manual/en/timezones.php
 */
date_default_timezone_set("America/Chicago");


/**
 * Enable or Disable caching for this site
 *
 * Set to FALSE to disable caching
 * Set to a number (in seconds) to enable:
 * i.e. 60 * 2 = (2 minutes)
 */
define('CACHING', FALSE);

//Should Debug info be shown with errors and such? (true or false)
define('DEBUG_MODE', TRUE);

/*
 * URL ADDRESS PATHS
 */

// Absolute URL path to the site root. Leave blank unless this site is in a subfolder.
define('SITE_URL', '/MicroMVC/');

// Absolute URL path to the themes directory
define('THEME_URL', SITE_URL. DOMAIN. '/views/');

// Absolute URL path to the upload directory
define('UPLOAD_URL', SITE_URL. 'uploads/');

