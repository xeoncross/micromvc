<?php

// System Start Time
define('START_TIME', microtime(true));

// System Start Memory
define('START_MEMORY_USAGE', memory_get_usage());

// Extension of all PHP files
define('EXT', '.php');

// Directory separator (Unix-Style works on all OS)
//define('DS', '/');

// Absolute path to the system folder
define('SP', realpath(__DIR__). '/');

// Is this an AJAX request?
define('AJAX_REQUEST', strtolower(getenv('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest');

// The current TLD address, scheme, and port
define('DOMAIN', (strtolower(getenv('HTTPS')) == 'on' ? 'https' : 'http') . '://'
	. getenv('HTTP_HOST') . (($p = getenv('SERVER_PORT')) != 80 AND $p != 443 ? ":$p" : ''));

// Get the current URL path (only)
define('PATH', rawurldecode(trim(parse_url(getenv('REQUEST_URI'), PHP_URL_PATH), '/')));

// Load the common functions (used next)
require(SP . 'MicroMVC.php');

/*
 * Default Locale Settings
 */

// Get locale from user agent
if(isset($_COOKIE['lang']))
{
	$preference = (string) $_COOKIE['lang'];
}
else
{
	$preference = Locale::acceptFromHttp(getenv('HTTP_ACCEPT_LANGUAGE'));
}

// Match preferred language to those available, defaulting to generic English
$locale = Locale::lookup(config()->languages, $preference, false, 'en');

// Default Locale
Locale::setDefault($locale);
setlocale(LC_ALL, $locale . '.utf-8');
//putenv("LC_ALL", $locale);

// Default timezone of server
date_default_timezone_set('UTC');

// iconv encoding
iconv_set_encoding("internal_encoding", "UTF-8");

// multibyte encoding
mb_internal_encoding('UTF-8');

// Disable SimpleXML/DOM error output
libxml_use_internal_errors(true);
