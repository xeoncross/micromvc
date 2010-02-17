<?php defined('SYSTEM_PATH') or die('No direct access');
/**
 * Routes
 *
 * Holds and parses information from the URI
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
class routes
{

	public static $uri_string			= '';
	public static $uri_segments			= array();

	public static function parse()
	{
		//Find and store URI string of page
		self::get_uri();

		//Process URI into an array of segments
		self::parse_uri();

		//Check for any routes
		self::parse_routes();
	}


	/**
	 * Create a URI string from $_SERVER values
	 */
	public static function get_uri()
	{
		//The SERVER values to look for the path info in
		$server = array('PATH_INFO', 'REQUEST_URI', 'ORIG_PATH_INFO', 'REDIRECT_URL');

		foreach($server as $item)
		{
			//Try the REQUEST_URI
			if( ! empty($_SERVER[$item]) AND $uri = trim($_SERVER[$item]))
			{
				// Remove the query string (if given)
				$uri = parse_url($uri, PHP_URL_PATH);

				// Reduce multiple slashes to a single slash
				// Could result in a fatal regex backtrace
				$uri = preg_replace('#//+#', '/', $uri);

				// Force the path to start with a forward slash
				if(substr($uri, 0, 1) !== '/')
				{
					$uri = '/'. $uri;
				}

				// Remove the site URL from the URL
				if (strpos($uri, SITE_URL) === 0)
				{
					$uri = substr($uri, strlen(SITE_URL));
				}

				// Remove the index.php file from the URL
				if (strpos($uri, 'index.php') === 0)
				{
					$uri = substr($uri, 9);
				}

				// Remove any remaining starting or ending slashes
				$uri = trim($uri, '\\/');

				//If anything is left
				if($uri)
				{
					//If there are bad characters in the URI
					if(preg_match('#[^a-z0-9'. preg_quote(config::get('permitted_uri_chars')). ']+#ui', $uri))
					{
						// End the script with an error
						throw new Exception(lang('invalid_uri_characters'));
					}

					//Set the URI String
					return self::$uri_string = $uri;
				}
			}
		}
	}


	/**
	 * Clean and separate the URI string into an array
	 */
	public static function parse_uri()
	{
		//Split the URI into an array
		self::$uri_segments = explode('/', self::$uri_string);

		//If a controller was NOT set in the URL
		if(empty(self::$uri_segments[0]))
		{
			self::$uri_segments[0] = config::get('default_controller');
		}

		//If a Method was NOT set in the URL
		if(empty(self::$uri_segments[1]))
		{
			self::$uri_segments[1] = config::get('default_method');
		}

	}


	/**
	 * Parse the controller routes (if any)
	 */
	public static function parse_routes()
	{
		$routes = config::get('routes');

		//Exit if no valid routes to worry about...
		if( empty($routes) OR !is_array($routes) OR empty(self::$uri_string))
		{
			return;
		}

		//Check each route
		foreach($routes as $route => $data)
		{

			// Convert wild-cards to ungready RegEx
			$route = str_replace(
				array(':any', ':let', ':num'), 
				array('.+?', '[a-z]+', '[0-9]+'), 
				$route
			);

			//See if it matches the URI string
			if(preg_match('#^'. $route. '#i', self::$uri_string))
			{
				// Block access to this URI?
				if( ! $data)
				{
					// Change the URI to the show_404() page
					return self::$uri_segments = array('controller', 'show_404');
				}

				//Remove controller name
				unset(self::$uri_segments[0]);

				//Add new controller & method to the beginning of the segments
				array_unshift(self::$uri_segments, $data[0], $data[1]);

				return;
			}
		}
	}


	/**
	 * Returns the URI as a string, array, or array element
	 */
	public static function fetch($type = NULL)
	{
		if($type === NULL)
		{
			return self::$uri_string;
		}
		elseif (is_int($type))
		{
			//Only return it if it exists
			if(isset(self::$uri_segments[$type]))
			{
				return self::$uri_segments[$type];
			}
		}
		else
		{
			return self::$uri_segments;
		}
	}

}

