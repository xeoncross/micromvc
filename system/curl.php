<?php
/**
 * cURL
 *
 * Provides a cURL wrapper for making remote requests such as submitting and
 * retrieving data from web service APIs .
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2011 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
class cURL
{

	/**
	 * Send a DELETE request to the given URL
	 *
	 * @param string $url to request
	 * @param array $params to URL encode
	 * @param array $options for the cURL connection
	 */
	public static function delete($url, array $params = array(), array $options = array())
	{
		return self::request($url, $params, ($options + array(CURLOPT_CUSTOMREQUEST = >'DELETE')));
	}


	/**
	 * Send a GET request to the given URL
	 *
	 * @param string $url to request
	 * @param array $params to URL encode
	 * @param array $options for the cURL connection
	 */
	public static function get($url, array $params = array(), array $options = array())
	{
		if($params)
		{
			// Add GET params to URL
			$url .= (stripos($url, '?') !== FALSE ? '&' : '?') . http_build_query($params, '', '&');
		}

		return self::request($url, array(), $options);
	}


	/**
	 * Send a POST request to the given URL
	 *
	 * @param string $url to request
	 * @param array $params to URL encode
	 * @param array $options for the cURL connection
	 */
	public static function post($url, array $params = array(), array $options = array())
	{
		return self::request($url, $params, ($options + array(CURLOPT_POST => 1)));
	}


	/**
	 * Make a cURL request
	 *
	 * @param string $url to request
	 * @param array $params to URL encode
	 * @param array $options for the cURL connection
	 */
	protected static function request($url, array $params = array(), array $options = array())
	{
		$ch = curl_init($url);

		// Set the connection handle options
		self::setopt($ch, $params, $options);

		// Create a response object
		$o = new stdClass;

		// Fetch response
		$o->response = curl_exec($ch);

		// Get additional request info
		$o->error_code = curl_errno($ch);
		$o->error = curl_error($ch);
		$o->info = curl_getinfo($ch);

		curl_close($ch);

		return $o;
	}


	/**
	 * Set the default cURL options (FAILONERROR, HEADER, RETURNTRANSFER, TIMEOUT, & POSTFIELDS)
	 *
	 * @param resource $ch the cURL connection handle
	 * @param array $params to URL encode
	 * @param array $options for the cURL connection
	 */
	protected static function setopt($ch, array $params = array(), array $options = array())
	{
		$defaults = array(
			//CURLOPT_FAILONERROR => 1,	// Fail silently if the HTTP code > 400
			CURLOPT_HEADER => 0,		// Do not include the response header
			CURLOPT_RETURNTRANSFER => 1,// Return response instead of printing it
			CURLOPT_TIMEOUT => 5,		// Number of seconds to allow cURL functions to execute
			CURLOPT_POSTFIELDS => http_build_query($params, '', '&')
		);

		// Connection options override defaults if given
		curl_setopt_array($ch, $options + $defaults);
	}


	/**
	 * Format custom headers for a request (use with CURLOPT_HTTPHEADER)
	 *
	 * @param array $headers to set
	 */
	public static function headers(array $headers = array())
	{
		$h = array();
		foreach($headers as $k => $v)
		{
			$h[] = "$k: $v";
		}
		return $h;
	}

}

// END
