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

/**
 * Decrypt and fetch cookie data
 *
 * @param string $key cookie name
 * @param array $c config settings
 * @return mixed
 */
public static function get($k,$c=NULL)
{
	$c=$c?:config('cookie');if(isset($_COOKIE[$k])&&($v=$_COOKIE[$k]))if($v=json_decode(Cipher::decrypt(base64_decode($v),$c['key'])))if($v[0]<$c['timeout'])return is_scalar($v[1])?$v[1]:(array)$v[1];
}


/**
 * Called before any output is sent to create an encrypted cookie with the given value.
 *
 * @param string $key cookie name
 * @param mixed $v the value to save
 * @param array $c config settings
 * return boolean
 */
public static function set($k,$v,$c=NULL)
{
	extract($c?:config('cookie'));empty($key)&&trigger_error(lang('cookie_no_key'));setcookie($k,($v?base64_encode(Cipher::encrypt(json_encode(array(time(),$v)),$key)):''),$expires,$path,$domain,$secure,$httponly);
}

}

// END