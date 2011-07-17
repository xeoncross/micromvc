<?php
/**
 * URL
 *
 * Read and parse information about the current URL URI.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2011 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
namespace Core;

class URL
{

	/**
	 * Return the current URL
	 *
	 * @param boolean $path
	 * @return string
	 */
    public static function get($path = FALSE)
    {
		return self::scheme() . '://' . self::domain() . self::port() . config('site_url') . ($path ? self::path() : '');
    }


	/**
	 * Return the current protocal scheme
	 *
	 * @return string
	 */
    public static function scheme()
    {
		return strtolower(getenv('HTTPS')) == 'on' ? 'https' : 'http';
    }


	/**
	 * Return the server domain hostname
	 *
	 * @return string
	 */
    public static function domain()
    {
		return getenv('HTTP_HOST') ? getenv('HTTP_HOST') : getenv('SERVER_NAME');
    }


	/**
	 * Return the domain port number only if unstandard (i.e. :8080)
	 *
	 * @param string $prefix for port
	 * @return string
	 */
    public static function port($prefix = ':')
    {
		//$scheme = self::scheme();
		$port = getenv('SERVER_PORT');

		// Because testing environments may be faking HTTPs on port 80 lets not do this
		//if(($scheme == 'http' AND $port === '80') OR ($scheme == 'https' AND $port === '443'))

		if($port === '80' OR $port === '443')
		{
			return;
		}

		return $prefix . $port;
    }


	/**
	 * Return the URL path segment
	 *
	 * @return string
	 */
    public static function path($key = NULL, $default = NULL)
    {
		static $path = NULL;

		if($path === NULL)
		{
			$path = explode('?', getenv('REQUEST_URI'));
			$path = mb_substr($path[0], strlen(config('site_url')));
			$path = explode('/', trim($path, '/'));
		}

		if($path)
		{
			return($key !== NULL ? (isset($path[$key]) ? $path[$key] : $default) : implode('/', $path));
		}

		return $default;
    }


	/**
	 * Return the URL query string
	 *
	 * @param string $prefix for query string
	 * @return string
	 */
    public static function query_string($prefix = '?')
    {
		return getenv('QUERY_STRING') ? $prefix . getenv('QUERY_STRING') : '';
    }
}

// END
