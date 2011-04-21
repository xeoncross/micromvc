<?php
/**
 * Cookie
 *
 * Provides a encryption wrapper around standard cookie handling functions . 
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc . com/license
 ********************************** 80 Columns *********************************
 */
class cookie
{

/**
 * Decrypt and fetch cookie data
 *
 * @param string $key cookie name
 * @param array $config settings
 * @return mixed
 */
public static function get($key, $config = NULL)
{
	// Use default config settings if needed
	$config = $config ?: config('cookie');
	
	if(isset($_COOKIE[$key]))
	{
		// Decrypt cookie using cookie key
		if($v = json_decode(Cipher::decrypt(base64_decode($_COOKIE[$key]), $config['key'])))
		{
			// Has the cookie expired?
			if($v[0] < $config['timeout'])
			{
				return is_scalar($v[1])?$v[1]:(array)$v[1];
			}
		}
	}
}


/**
 * Called before any output is sent to create an encrypted cookie with the given value. 
 *
 * @param string $key cookie name
 * @param mixed $value to save
 * @param array $config settings
 * return boolean
 */
public static function set($name, $value, $config = NULL)
{
	// Use default config settings if needed
	extract($config ?: config('cookie'));
	
	// You must supply a key!
	empty($name) AND trigger_error(lang('cookie_no_key'));
	
	// If the cookie is being removed we want it left blank
	$value = $value ? base64_encode(Cipher::encrypt(json_encode(array(time(), $value)), $key)) : '';
	
	// Save cookie to user agent
	setcookie($name, $value, $expires, $path, $domain, $secure, $httponly);
}

}

// END
