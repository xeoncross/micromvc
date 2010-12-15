<?php
/**
 * Index
 *
 * This file defines the MVC processing logic for the system
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */

// System Start Time
define('START_TIME', microtime(true));

// System Start Memory
define('START_MEMORY_USAGE', memory_get_usage());

// Extension of all PHP files
define('EXT', '.php');

// Absolute path to the system folder
define('SP', realpath(dirname(__FILE__)). '/');

// Are we using windows?
define('WINDOWS', strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');

// Include bootstrap
require('bootstrap.php');

//Is this an AJAX request?
define('AJAX_REQUEST',strtolower(server('HTTP_X_REQUESTED_WITH'))==='xmlhttprequest');

// What is the current domain?
define('DOMAIN', (server('HTTPS')=='on'?'https://':'http://').h(server('SERVER_NAME')?server('HTTP_HOST'):server('SERVER_NAME')));

// Custom init script?
if(config('init')) require('init.php');

// Get the current URL (defaulting to the index)
$url = ((url() ? explode('/', url()) : array()) + explode('/', config('index')));

// Get the controller and page
list($controller, $method) = array_slice($url, 0, 2);
$params = array_slice($url, 2);

// Routes allow custom URL
foreach(config('routes') as $regex => $path)
{
	if(preg_match("/^$regex/", url()))
	{
		list($controller, $method) = explode('/',$path);
		$params = $url;
		break;
	}
}

// Find the controller
foreach(config('modules') as $m)
{
	if(is_file(SP."modules/$m/controller/$controller".EXT))
	{
		require(SP."modules/$m/controller/$controller".EXT);
		break;
	}
}

// Add the controller prefix
$controller = 'controller_'.$controller;

// If the controller was not found - issue a 404
if( ! class_exists($controller, FALSE))
{
	$controller = 'controller';
	$method = 'show_404';
}

// Load the controller
$controller = new $controller;

// Set the error handler
set_error_handler(array($controller, '_error_handler'));

// Catch E_FATAL errors too!
register_shutdown_function(array($controller, '_fatal_error_handler'));

// Set the exception handler
set_exception_handler(array($controller, '_exception_handler'));

// One last check to make sure we can run this method
if( ! in_array($method,get_class_methods($controller)))
//if( ! method_exists($controller, $method) OR substr($method, 0, 1) === '_')
{
	$method = 'show_404';
}

// Call the page
call_user_func_array(array($controller, $method), $params);

// Render the page
$controller->_render();

// End