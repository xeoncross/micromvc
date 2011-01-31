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
list($module, $controller) = array_slice($url, 0, 2);

// Routes allow custom URL
foreach(config('routes') as $regex => $path)
{
	if(preg_match("/^$regex/", url()))
	{
		list($module, $controller) = explode('/',$path);
		$params = $url;
		break;
	}
}

// Register events
foreach(config('events') as $event => $class)
{
	event($event, '', $class);
}

// Missing controller - or hidden module
if(in_array($module, config('disabled_modules')) OR !is_file(SP. $module.'/controller/'.$controller.'.php'))
{
	$module = 'error';
	$controller = '404';
}

/*
 * PHP's default error handling should only be overriden while in debug 
 * mode since all the extra information is not needed in production nor 
 * should be shown to users!
 */
if(config('debug_mode'))
{
	// Set the error handler
	set_error_handler(array('error', 'handler'));
	
	// Catch E_FATAL errors too!
	register_shutdown_function(function(){if($e=error_get_last())Error::exception(new ErrorException($e['message'],$e['type'],0,$e['file'],$e['line']));});
	
	// Set the exception handler
	set_exception_handler(array('error', 'exception'));
}

event('pre_controller');

$controller = new Controller(SP.$module.'/controller/'.$controller.EXT);

event('post_controller', $controller);

//print dump(get_defined_vars());

// End
