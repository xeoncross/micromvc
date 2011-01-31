<?php
/**
 * Session
 *
 * Stores session data in encrypted cookies to save database/memcached load. 
 * Flash uploaders and session masquerading should make use of the open() method 
 * to allow hyjacking of sessions. Sessions stored in cookies must be under 4KB.
 *
 * Also make sure to call the token methods if using forms to help prevent CSFR.
 * <input value="<?php print Session::token(); ?>" name="token" />
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
class Session
{

/**
 * Configure the session settings, check for problems, and then start the session.
 *
 * @param array $config an optional configuration array
 * @return boolean
 */
public static function start($name = 'session')
{
	if(!empty($_SESSION))return FALSE;$_SESSION=cookie::get($name);return TRUE;
}


/**
 * Called at end-of-page to save the current session data to the session cookie
 *
 * return boolean
 */
public static function save($name = 'session')
{
	return cookie::set($name,$_SESSION);
}


/**
 * Destroy the current users session
 */
public static function destroy($name = 'session')
{
	cookie::set($name,'');unset($_COOKIE[$name],$_SESSION);
}


/**
 * Create new session token or validate the token passed
 *
 * @param string $token value to validate
 * @return string|boolean
 */
public static function token($token = NULL)
{
	if(!empty($_SESSION))return(func_num_args()?(!empty($_SESSION['token'])&&$token===$_SESSION['token']):($_SESSION['token']=token()));
}

}

// END