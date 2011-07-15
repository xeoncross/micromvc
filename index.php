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
define('AJAX_REQUEST', strtolower(server('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest');

// Custom init script?
if(config('init')) require('init.php');

// Register events
foreach(config('events') as $event => $class)
{
	event($event, '', $class);
}

$route = new Route();

// Parse the routes to find the correct controller
list($params, $route, $controller) = $route->parse(url::path(), config('routes'));

// Any else before we start?
event('pre_controller', $controller);

// Load and run action
$controller = new $controller($route);

if($params)
{
	call_user_func_array(array($controller, 'action'), $params);
}
else
{
	$controller->action();
}

// Render output
$controller->render();

// One last chance to do something
event('post_controller', $controller);

// End
