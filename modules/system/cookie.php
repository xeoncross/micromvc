<?php
/**
 * Cookie
 *
 * Provides a encryption wrapper around standard cookie handling functions.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
class cookie
{

public static $saved = array();

/**
 * Decrypt cookie data
 *
 * @return mixed
 */
public static function get($name)
{
	if(!empty($_COOKIE[$name]))return unserialize(Cipher::decrypt(base64_decode($_COOKIE[$name]),config('cookie_salt')));
}


/**
 * Called before any output is sent to create an encrypted cookie with the given value.
 *
 * return boolean
 */
public static function set($name, $value, $expire = 0, $path = '/', $domain = '',$secure = FALSE, $httponly = FALSE, $overwrite = FALSE)
{
	if(!$overwrite&&isset(self::$saved[$name]))return;setcookie($name,($value?base64_encode(Cipher::encrypt(serialize($value),config('cookie_salt'))):''),$expire,$path,$domain,$secure,$httponly);return self::$saved[$name]=1;
}

}

// END