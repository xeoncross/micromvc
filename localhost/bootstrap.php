<?php defined('SYSTEM_PATH') or die('No direct access');
/**
 * Bootstrap
 *
 * This file is where you can configure your project settings
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */

// Set the class loader
spl_autoload_register(array('load', 'autoload'));

// Set the local used
setlocale(LC_ALL, 'en_US.utf-8');

/**
 * Gzip compress the page for faster transfers!
 * If you are unable to enable gzip in apache then uncomment
 * this line to enable it in PHP.
 */
//gzip_compression();


// Overload php.ini error reporting?
//error_reporting(E_ALL|E_STRICT);

/*
 * Set the server timezone
 * see: http://us3.php.net/manual/en/timezones.php
 */
date_default_timezone_set("America/Chicago");


/*
 * URL address paths
 */

// Absolute URL path to the site root.
define('SITE_URL', '/micromvc/');

// Absolute URL path to the themes directory
define('VIEW_URL', SITE_URL. DOMAIN. '/views/');

// Absolute URL path to the upload directory
define('UPLOAD_URL', SITE_URL. DOMAIN. '/uploads/');

// Absolute URL path to the shared javascript directory
define('JAVASCRIPT_URL', SITE_URL. 'js/');

// Absolute URL path to the modules directory
define('MODULE_URL', SITE_URL. 'modules/');

/*
 * File system paths
 */

//The file system path of the site's uploads folder
define('UPLOAD_PATH', SITE_PATH. 'uploads'. DS);

//The file system path of the site's cache folder
define('CACHE_PATH', SITE_PATH. 'cache'. DS);

//Define the base file system path to logs
define('LOG_PATH', SITE_PATH. 'logs'. DS);


// Must load the cache library by hand to save "load" from looking for it.
require(SYSTEM_PATH. 'modules/core/classes/cache.php');

/*
 * Modules
 *
 * To enable a module please enter it's name here. The order they are
 * listed is the order they will be scanned for files.
 */
$modules = array(
	//'database',
	//'memcache',
	//'recaptcha',
	//'disqus',
	'core',
);

// Load modules
load::init($modules);

// Remove module data
unset($modules);

// Enable exception handling
set_exception_handler(array('controller', 'exception_handler'));

// Convert all PHP errors to exceptions
set_error_handler(array('controller', 'error_handler'));
