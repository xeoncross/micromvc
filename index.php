<?php
/**
 * INDEX
 *
 * This is the starting point (index) for the system. Here we check cached files
 * and then precede to load the system and finally run the controller.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */

// Not needed..?
//unset($GLOBALS, $_REQUEST);

//Log current time so we can tell how long it takes to run this script
define('START_TIME', microtime(true));

//Log starting memory useage
define('START_MEMORY_USAGE', memory_get_usage());

//Define the OS file path separator as *NIX style
define('DS', '/'); //DIRECTORY_SEPARATOR);

//Is this sever a windows machine?
define('WINDOWS', strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');

//Is this an AJAX request?
define('AJAX_REQUEST', (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
	AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'));

//Define the base file system path to MicroMVC
define('SYSTEM_PATH', realpath(dirname(__FILE__)). DS);

//Define the base file system path to modules
define('REQUIRED_PATH', SYSTEM_PATH. 'required'. DS);

//Define the base file system path to modules
define('MODULE_PATH', SYSTEM_PATH. 'modules'. DS);

// In order to know which domain directory to use we need to fetch the site domain (i.e. "www.site.com")
$domain = empty($_SERVER['SERVER_NAME']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];

// Match the name
preg_match('/^((([a-z0-9\-]{1,70}\.){1,6}[a-z]{2,4})|localhost)$/ui', $domain, $match);

//MUST HAVE A HOST!
if(empty($match[0]))
{
	header("HTTP/1.0 400 Bad Request");
	die('Sorry, the host site not set. Please check the URL entered.');
}

// Save the current domain name
define('DOMAIN', $match[0]);

// Default domain is the one given
$domain = DOMAIN;

// Include domain settings
require('domains.php');

// If an alias is found - then use that instead!
foreach($domains as $regex => $alias)
{
	if(preg_match('/^'. $regex. '$/i', DOMAIN))
	{
		$domain = $alias;
	}
}

// Site folder name
define('DOMAIN_FOLDER', $domain);

// Define the file system path to the site folder
define('SITE_PATH', SYSTEM_PATH. DOMAIN_FOLDER. DS);

// Get site mode
$mode = file_get_contents(SYSTEM_PATH. '.mode') or die('<b>.mode file is missing</b>');

// Define the current site mode (production, staging, development, etc...)
define('SITE_MODE', trim($mode));

// Remove values
unset($match, $domain, $domains, $alias, $mode);

// Make sure the site exists
if( ! file_exists(SITE_PATH))
{
	header("HTTP/1.0 400 Bad Request");
	die('Sorry, we could not find the site directory.');
}

/*
 * Setup system to handle multibyte unicode strings in UTF-8
 */

// Check whether PCRE has been compiled with UTF-8 support
if ( ! preg_match('/^.$/u', 'ñ'))
{
	trigger_error (
		'<a href="http://php.net/pcre">PCRE</a> has not been compiled with UTF-8 support. '.
		'See <a href="http://php.net/manual/reference.pcre.pattern.modifiers.php">PCRE Pattern Modifiers</a> '.
		'for more information. This application cannot be run without UTF-8 support.',
		E_USER_ERROR
	);
}


/**
* If string overloading is active, it will break many of the
* native implementations. mbstring.func_overload must be set
* to 0, 1 or 4 in php.ini (string overloading disabled).
* Also need to check we have the correct internal mbstring
* encoding
*/
if ( extension_loaded('mbstring'))
{

	if ( ini_get('mbstring.func_overload') & MB_OVERLOAD_STRING )
	{
        trigger_error('String functions are overloaded by mbstring', E_USER_ERROR);
    }

    // Set the default mb encoding
    mb_internal_encoding('UTF-8');
}
else
{
	// load the drop-in MB replacement library
	require(REQUIRED_PATH. 'mb_strings.php');
}

// Include core classes
require_once(REQUIRED_PATH. 'classes.php');

// Include the common system functions
require_once(REQUIRED_PATH. 'common.php');

// We must load the cache library by hand (since Load neededs it!)
require(SYSTEM_PATH. config::get('cache_library'));

// Set the class loader
spl_autoload_register(array('load', 'autoload'));

// Load modules
load::init(config::get('modules'));

// Set custom exception handling
set_exception_handler(array('controller', 'exception_handler'));

// Set custom error handler
set_error_handler(array('controller', 'error_handler'));

// Set the hook config
hook::$hooks = config::get('hooks');

// Parse the URI route
routes::parse();

// Call first hook
hook::call('system_startup');

/*
 * Guests to our site *should* not have a "logged_in" cookie set.
 * Therefore it is safe to show them cached pages instead of wasting
 * our valuable server resources re-rendering the whole page.
 */

// If cookie checking is disabled (or the cookie is not found)
if( ! config::get('caching_check_cookie') OR empty($_COOKIE[config::get('caching_check_cookie')]) )
{
	// If Caching is enabled - and a cached page is found
	if($output = cache::get('routes::get_uri()'. routes::get_uri(). AJAX_REQUEST))
	{
		// Get content type from start of the output
		list($content_type, $output) = explode('::', $output, 2);
		
		// If a content type is set - let the useragent know what type this is
		if($content_type)
		{
			header('Content-Type: '.$content_type.'; charset=utf-8');
		}
		
		// Allow a hook call - then print the output
		print hook::call('system_shutdown_cache', $output);

		// If debuging is enabled
		if( config::get('debug_mode') )
		{
			load::view('cache_debug', NULL, NULL);
		}

		die();
	}
}


// strip the slashes that have been added to our POST/GET data!
if (ini_get('magic_quotes_gpc'))
{
	function array_stripslashes(&$value)
	{
		$value = stripslashes($value);
	}

	array_walk_recursive($_GET, 'array_stripslashes');
	array_walk_recursive($_POST, 'array_stripslashes');
	array_walk_recursive($_COOKIE, 'array_stripslashes');
}


/**
 * Convert all global variables to proper UTF-8
 * while removing invalid character sequences.
 */
if( config('encoding') === 'utf-8' AND config('encode_globals') )
{
	$_GET    = string::array_to_utf8($_GET);
	$_POST   = string::array_to_utf8($_POST);
	$_COOKIE = string::array_to_utf8($_COOKIE);
	$_SERVER = string::array_to_utf8($_SERVER);
}


// Allow a hook call now that everything is loaded
hook::call('system_loaded');

// Build controller name
$controller = 'Controller_'. routes::fetch(0);

// If this controller is found
if( load::autoload($controller) )
{
	// Get the method name
	$method = routes::fetch(1);

	// Make sure this method exists, is not a method of controller, and it doesn't start with an underscore
	if(method_exists($controller, $method) AND ! method_exists('controller', $method) AND substr($method, 0, 1) !== '_')
	{
		// Save controller name
		define('CONTROLLER', $controller);

		// Everythings good, so load the controller
		$controller = load::singleton($controller);
	}

}

// If any of the above checks fail, then load the default class
if( ! is_object($controller))
{
	// Save controller name
	define('CONTROLLER', 'Controller');

	// Load the controller class
	$controller = load::singleton('Controller');

	// Call the 404 method instead
	$method = 'show_404';
}

// Call the startup hook
hook::call('system_pre_method');

// Call the requested method.
// Any URI segments present (besides the class/method)
// will be passed to the method for convenience
call_user_func_array(array($controller, $method), array_slice(routes::fetch(true), 2));

// Call the post-controller hook
hook::call('system_post_method');

// And we're done!
$controller->render();

