<?php
/**
 * INDEX BOOTSTRAP
 *
 * This is the starting point (index) for the system. Here we check cached files
 * and then precede to load the system and finally run the controller.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	Copyright (c) 2009 MicroMVC
 * @license		http://www.gnu.org/licenses/gpl-3.0.html
 * @link		http://micromvc.com
 * @version		1.0.1 <5/31/2009>
 ********************************** 80 Columns *********************************
 */

//Log current time so we can tell how long it takes to run this script
define('START_TIME', microtime(true));

//Log starting memory useage
define('START_MEMORY_USAGE', memory_get_usage());

//Set the unique name of the current page (for the cache)
$var = preg_replace("/([^a-z0-9_\-\.]+)/i", '_', $_SERVER["REQUEST_URI"]);
define('PAGE_NAME', ($var ? $var : 'index'));

//Define the OS file path separator
define('DS', DIRECTORY_SEPARATOR);


//Define the base file system path to MicroMVC
define('SYSTEM_PATH', realpath(dirname(__FILE__)). DS);

//Define the base file system path to MicroMVC
define('LIBRARY_PATH', SYSTEM_PATH. 'libraries'. DS);

//Define the base file system path to MicroMVC
define('FUNCTION_PATH', SYSTEM_PATH. 'functions'. DS);

//Define the base file system path to MicroMVC
define('MODULE_PATH', SYSTEM_PATH. 'modules'. DS);


//Include the common file to continue loading
require_once(FUNCTION_PATH. 'common.php');

//Discover whether this is an AJAX request or not
define('AJAX_REQUEST', is_ajax_request());

//Discover the current domain for the whole script
define('DOMAIN', current_domain());


//Define the file system path to the current site
define('SITE_PATH', SYSTEM_PATH. DOMAIN. DS);

//The file system path of the site's cache folder
define('CACHE_PATH', SITE_PATH. 'cache'. DS);

//The file system path of the site's upload folder
define('CONFIG_PATH', SITE_PATH. 'config'. DS);

//The file system path of the site's upload folder
define('MODEL_PATH', SITE_PATH. 'modles'. DS);

//The file system path of the site's upload folder
define('UPLOAD_PATH', SITE_PATH. 'uploads'. DS);

//The file system path of the site's upload folder
define('VIEW_PATH', SITE_PATH. 'views'. DS);


//Override the PHP error handler
set_error_handler('_error_handler');

//Require the config file for this site name
require(SITE_PATH. 'config/config.php');

//Require the config file for the hooks
require(SITE_PATH. 'config/hooks.php');

//Load the caching class
$cache = load_class('cache', LIBRARY_PATH);

//Load the hooks class
$hooks = load_class('hooks', LIBRARY_PATH, $hooks);

//Call first hook
$hooks->call('system_startup');

/**
 * Check for cached version -die if found
 */
if($output = $cache->fetch(md5(PAGE_NAME. AJAX_REQUEST), null, null)) {

	print $output;

	//If debuging is enabled
	if(DEBUG_MODE) {
		$time = round((microtime(true) - START_TIME), 5);
		$memory = round((memory_get_usage() - START_MEMORY_USAGE) / 1024);

		die('<!-- Rendered in '. $time. ' seconds using '. $memory. ' kb of memory -->');
	}

}


/**
 * strip the slashes that have been added to our POST/GET data!
 */
if (ini_get('magic_quotes_gpc')) {

	function array_clean(&$value) {
		$value = stripslashes($value);
	}
	//php 5+ only
	array_walk_recursive($_GET, 'array_clean');
	array_walk_recursive($_POST, 'array_clean');
	array_walk_recursive($_COOKIE, 'array_clean');
}


//Include the controller class file
load_class('controller', LIBRARY_PATH, NULL, FALSE);

//Include the base class file
load_class('base', LIBRARY_PATH, NULL, FALSE);

/**
 * Get the controller from the URI
 */
$routes = load_class('routes', LIBRARY_PATH);

//Set default controller/method if none is set in URL
$routes->set_defaults(
	$config['default_controller'],
	$config['default_method'],
	$config['permitted_uri_chars']
);

//Parse the URI
$routes->parse();

//Fetch the controller/method
$controller	= $routes->fetch(0);
$method		= $routes->fetch(1);


/**
 * START-UP THE SYSTEM!
 */

//Try to load the controller from the site directory first
if(file_exists(SITE_PATH. 'controllers'. DS. $controller. '.php')) {
	$path = SITE_PATH. 'controllers'. DS;

//Then try to see if there is a module
} elseif(file_exists(MODULE_PATH. $controller. DS. 'controllers'. DS. $controller. '.php')) {
	$path = MODULE_PATH. $controller. DS. 'controllers'. DS;

} else {
	//Show a 404 error and exit the script
	request_error();
}

//Load the controller and pass the $config
$controller = load_class($controller, $path, $config);

//Make sure someone isn't trying to access core or private functions
if($method != 'request_error' && $controller != 'core') {
	//Make sure this method exists (and is public)
	if(method_exists('core', $method) OR ! in_array($method, get_class_methods($controller))) {
		//Show a 404 error and exit the script
		request_error();
	}
}

//Call the startup hook
$controller->hooks->call('post_constructor');

// Call the requested method.
// Any URI segments present (besides the class/function)
// will be passed to the method for convenience
call_user_func_array(array(&$controller, $method), array_slice($routes->fetch(true), 2));

//Call the post-controller hook
$controller->hooks->call('post_method');

// And we're done!
$controller->render();

//Call the finish hook
$controller->hooks->call('system_shutdown');
