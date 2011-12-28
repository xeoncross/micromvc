<?php

require('../Bootstrap.php');

/*
 * URL-to-controller routing
 */

//header('Content-Type: text/plain');
//header('Content-Type: text/html; charset=utf-8');
//require('../Bootstrap.php');

$path = PATH;
$params = array();
$controller = NULL;
$routes = config('Route')->routes;

// Default homepage route
if(PATH === '')
{
	$controller = $routes[''];
}
// If this is not a valid, safe path (more complex params belong in GET/POST)
else
{
	foreach($routes as $route => $resource)
	{
		if( ! $route) continue; // Skip homepage route

		// Is this a regex?
		if($route{0} === '/')
		{
			if(preg_match($route, $path, $matches))
			{
				$complete = array_shift($matches);

				// The following code tries to solve:
				// (Regex) "/^path/(\w+)/" + (Path) "path/word/other" = (Params) array(word, other)

				// Skip the regex match and continue from there
				$params = explode('/', trim(mb_substr($path, mb_strlen($complete)), '/'));

				if($params[0])
				{
					// Add captured group back into params
					foreach($matches as $match)
					{
						array_unshift($params, $match);
					}
				}
				else
				{
					$params = $matches;
				}

				$controller = $resource;
			}
		}
		else
		{
			if(mb_substr($path, 0, mb_strlen($route)) === $route)
			{
				$params = explode('/', trim(mb_substr($path, mb_strlen($route)), '/'));
				$controller = $resource;
			}
		}

		if($controller) break;
	}

	// Controller not found
	if( ! $controller)
	{
		$controller = $routes['404'];
	}
}

// Remove to free memory
unset($routes);
config('Route', TRUE);

try
{
	// Get the controller method
	list($controller, $method) = explode('::', $controller) + array('', 'index');

	// Load the controller
	$controller = new $controller;

	// Allow REST-specific methods (is it safe to use REQUEST_METHOD like this?)
	if(method_exists($controller, getenv('REQUEST_METHOD') . $method))
	{
		$method = getenv('REQUEST_METHOD') . $method;
	}

	// Run before
	$controller->before($method);

	// Let the controller take it from here!
	$result = call_user_func_array(array($controller, $method), $params);

	// Run after
	$controller->after($method, $result);
}
catch (Exception $e)
{
	\Micro\Error::exception($e);
}
// κόψη/bar/ዕንቁላል/Зарегистрируйте4сь
//var_dump(get_defined_vars());
