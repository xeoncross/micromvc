<?php
/**
 * Route
 *
 * Parse the given URL path and find the matching route rule.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2011 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
namespace Core;

class Route
{
	/**
	 * Parse the given URL path and return the correct controller and parameters.
	 *
	 * @param string $path segment of URL
	 * @param array $routes to test against
	 * @return array
	 */
	public function parse($path, array $routes = NULL)
	{
		// If this is not a valid, safe path (more complex params belong in GET/POST)
		if($path AND ! preg_match('/^[\w\-~\/\.]{1,400}$/', $path))
		{
			$path = '404';
		}

		// Default homepage route
		if($path === '')
		{
			return array(array(), '', $routes['']);
		}

		foreach($routes as $route => $controller)
		{
			if( ! $route) continue; // Skip homepage route

			// Is this a regex?
			if($route{0} === '/')
			{
				if(preg_match($route, $path, $matches))
				{
					// Skip the regex match and continue from there
					$params = explode('/', trim(mb_substr($path, mb_strlen($matches[0])), '/'));

					// Add captured group back into params
					$complete = array_shift($matches);
					foreach($matches as $match)
					{
						array_unshift($params, $match);
					}

					return array($params, $complete, $controller);
				}
			}
			else
			{
				if(mb_substr($path, 0, strlen($route)) === $route)
				{
					$params = explode('/', trim(mb_substr($path, strlen($route)), '/'));
					return array($params, $route, $controller);
				}
			}
		}

		// Controller not found
		return array(array($path), $path, $routes['404']);
	}
}

// END
