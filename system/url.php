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
		return strtolower(server('HTTPS')) == 'off' ? 'http' : 'https';
    }


	/**
	 * Return the server domain hostname
	 *
	 * @return string
	 */
    public static function domain()
    {
		return server('HTTP_HOST') ? server('HTTP_HOST') : server('SERVER_NAME');
    }


	/**
	 * Return the domain port number only if unstandard (i.e. :8080)
	 *
	 * @param string $prefix for port
	 * @return string
	 */
    public static function port($prefix = ':')
    {
		$scheme = self::scheme();
		$port = server('SERVER_PORT');

		if(($scheme == 'http' AND $port === '80') OR ($scheme == 'https' AND $port === '443'))
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
			$path = explode('?', server('REQUEST_URI'));
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
    public static function query($prefix = '?')
    {
		return server('QUERY_STRING') ? $prefix . server('QUERY_STRING') : '';
    }
}

// END
