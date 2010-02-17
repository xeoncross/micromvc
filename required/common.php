<?php defined('SYSTEM_PATH') or die('No direct access');
/**
 * Common Functions
 *
 * This file contains a growing list of common functions for used throughout
 * the MicroMVC system.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */


/**
 * Returns quick, pre-formatted resource information at any given 
 * point in the script.
 *
 * @param string $msg optional message
 * @return string
 */
function usage_info($message = '')
{
	return dump(
		round(microtime(TRUE)-START_TIME, 6)." secs \n"
		. number_format(memory_get_usage()-START_MEMORY_USAGE)
		." bytes (current) \n"
		. number_format(memory_get_peak_usage())
		." bytes (peak) \n"
		. number_format(memory_get_peak_usage(TRUE))
		." bytes (currently allocated) \n"
		. ( $t = backtrace(1) ? substr($t[0]['file'], strlen(SYSTEM_PATH))
		. ' on line '. $t[0]['line'] : '')
		. "\n". $message
	);
}


/**
 * Return an HTML safe dump of the given variable(s) surrounded by <pre> tags.
 * You can pass any number of variables (of any type) to this function.
 *
 * @param mixed
 * @return string
 */
function dump()
{

	$string = '';
	foreach(func_get_args() as $data)
	{
		// Represent NULL's
		$data = $data === NULL ? 'NULL' : $data;

		// Convert objects and arrays into strings
		if( ! is_scalar($data))
		{
			$data = print_r($data, TRUE);
		}

		//HTML encode the string to make it safe
		$string .= '<pre class="dump">'. h($data). "</pre>\n";
	}

	return $string;
}


/**
 * Record memory usage and timestamp and then return difference next run (and restart)
 * 
 * @return array
 */
function benchmark()
{
	static $start_time;
	static $memory_usage;

	$time = round((microtime(true) - $start_time) * 1000, 5). 'ms';

	//Caculate result
	$result = array($time, (memory_get_usage() - $memory_usage));

	//Set new times
	$start_time = microtime(true);
	$memory_usage = memory_get_usage();

	//return
	return $result;
}


/**
 * Checks that a directory exists and is writable. If the directory does
 * not exist, the function will try to create it and/or change the
 * CHMOD settings on it.
 *
 * @param string $dir the directory you want to check
 * @param string $chmod the CHMOD octal value
 * @return bool
 */
function directory_usable($dir, $chmod = 0777)
{

	//If it doesn't exist - create it!
	if( ! is_dir($dir) AND ! mkdir($dir, $chmod, TRUE))
	{
		return FALSE;
	}

	//Make it writable
	if( ! is_writable($dir) AND ! chmod($dir, $chmod))
	{
		return FALSE;
	}

	return TRUE;
}


/**
 * Convert special characters to HTML entities in the encoding
 * specified in the config file making the string safe to
 * display in the browser.
 *
 * @param string $str the string to encode
 * @return string
 */
function h($str = '')
{
	return htmlspecialchars($str, ENT_QUOTES, config::get('encoding'));
}


/**
 * Gzip compress page output
 * Original function came from wordpress.org
 */
function gzip_compression()
{

	//If no encoding was given - then it must not be able to accept gzip pages
	if(empty($_SERVER['HTTP_ACCEPT_ENCODING']))
		return false;

	//If zlib is not ALREADY compressing the page - and ob_gzhandler is set
	if ((ini_get('zlib.output_compression') === 'On'
		OR ini_get('zlib.output_compression_level') > 0)
		OR ini_get('output_handler') === 'ob_gzhandler')
		return false;

	//Else if zlib is loaded start the compression.
	if (extension_loaded('zlib') AND (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== FALSE))
	{
		ob_start('ob_gzhandler');
	}

}


/**
 * Returns a MySQL/SQLite/PostgreSQL safe date string
 *
 * @param int $timestamp the optional timestamp to use
 * @return string
 */
function sql_date($timestamp = NULL)
{
	return date('Y-m-d H:i:s', $timestamp ? $timestamp : time());
}


/**
 * Build a backtrace of the current function/method.
 * This is useful for debuging
 *
 * @param int $level the max levels to trace
 * @return array
 */
function backtrace($level = 5)
{
	// Get a backtrace
	$trace = debug_backtrace();

	// Remove this function
	array_shift($trace);

	// Get a trace up to $level deep
	$trace = array_slice($trace, 0, $level);

	// Remove object data and type
	foreach($trace as & $data)
	{
		if(isset($data['object']))
		{
			unset($data['object']);
		}

		unset($data['type']);
	}

	return $trace;
}


/**
 * Return a singleton instance of the current controller.
 * Because many libraries are loaded before the Controller
 * class has fully loaded, we must use the controllers
 * singleton instead of the load::singleton() to avoid a
 * recursive death.
 *
 * @return object
 */
function get_instance()
{
	return Controller::get_instance();
}


/**
 * Write to a log file
 *
 * @param string $message the log message
 * @return bool
 */
function log_message($message = '')
{

	// Build file path
	$filepath = LOG_PATH. date('Y-m-d'). '.log';

	// If file cannot be opened
	if ( ! $fp = @fopen($filepath, 'a'))
	{
		return FALSE;
	}

	// Lock the file and write
	flock($fp, LOCK_EX);
	fwrite($fp, date("[d-M-Y H:i:s]"). " $message\n");
	flock($fp, LOCK_UN);
	fclose($fp);

	return TRUE;
}


/**
 * Header redirection for both location and refresh types.
 *
 * @param string $uri the URI string
 * @param string $method either location or redirect
 */
function redirect($uri = '', $method = 'location', $http_response_code = 302)
{

	//Correct path slashes
	$uri = trim($uri, '\\/'). '/';

	//Added SITE_URL if not added
	if(substr($uri, 0, strlen(SITE_URL)) != SITE_URL)
	{
		$uri = SITE_URL. $uri;
	}

	//Do we need to add the site URL prefix?
	$uri = strpos($uri, '://') !== FALSE ? $uri : 'http://'. DOMAIN. $uri;

	if($method == 'refresh')
	{
		header("Refresh:0;url=". $uri);
	}
	else
	{
		header("Location: ". $uri, TRUE, $http_response_code);
	}
	exit;
}


/**
 * Return the site url prefixed to the uri path given
 *
 * @param string $uri the URI path
 * @return string
 */
function site_url($uri = NULL)
{
	return 'http://'.DOMAIN.SITE_URL.($uri ? trim($uri, '/') : '');
}


/**
 * Return the current page's URL
 *
 * @return string
 */
function current_url()
{
	return site_url(routes::get_uri());
}


/**
 * Return the URL to a module's folder
 *
 * @param string $module the module name
 * @return string
 */
function module_url($module)
{
	return 'http://'.DOMAIN.MODULE_URL.trim($module, '/').'/';
}


/**
 * Safely fetch a $_POST key's value, defaulting to the value
 * provided if the key is not found.
 *
 * @param string $key the post key
 * @param mixed $value the default value if key is not found
 * @return mixed
 */
function post($key, $value = NULL)
{
	return array_key_exists($key, $_POST) ? $_POST[$key] : $value;
}


/**
 * Validate and Type Cast the given variable into an integer.
 * On fail, return default value instead.
 *
 * @param mixed $int the value to convert
 * @param int $default the default value to assign
 * @param int $min the lowest value allowed
 * @return int
 */
function to_int($int, $default = 0, $min = NULL)
{
	return (is_scalar($int) && is_numeric($int) && ($int >= $min) ? (int) $int : $default);
}


/**
 * Validate and Type Cast the given variable into a float.
 * On fail, return default value instead.
 *
 * @param mixed $float the value to convert
 * @param float $default the default value to assign
 * @param float $min the lowest value allowed
 * @return float
 */
function to_float($float, $default = 0.0, $min = NULL)
{
	return (is_scalar($float) && is_numeric($float) && ($float >= $min) ? (float) $float : $default);
}


/**
 * Validate and Type Cast the given variable into a string.
 * On fail, return default value instead.
 *
 * @param mixed $string the value to convert
 * @param string $default the default value to assign
 * @return string
 */
function to_string($string, $default = '')
{
	//return is_string( $string ) ? $string : $default;
	return (is_scalar($string) ? (string) $string : $default);
}


/**
 * Validate and Type Cast the given variable into an array.
 * On fail, return default value instead.
 *
 * @param mixed $array the value to convert
 * @param array $default the default value to assign
 * @return array
 */
function to_array($array, $default = array())
{
	return is_array( $array ) ? $array : $default;
}


/**
 * Fetch the $_SESSION value (or default if not found)
 * 
 * @param string $key the name of the session key
 * @param mixed	$value the default if not found
 * @return mixed
 */
function session($key, $value = NULL)
{
	return isset($_SESSION[$key]) ? $_SESSION[$key] : $value;
}


/**
 * Fetch a line from the language file (loading it if needed).
 * If a value is given then the matching line will be over-written.
 *
 * @param string $line the line key
 * @param string $group the language group
 * @param string $value the optional new value
 * @return string
 */
function lang($line = NULL, $group = 'system', $value = NULL)
{

	//If we are setting the value
	if( func_num_args() == 3 )
	{
		return lang::set($line, $group, $value);
	}

	//Return the value
	return lang::get($line, $group);

}


/**
 * Fetch a value (or group of values) from the config (loading
 * the config file if needed). If the third option is given then
 * the matching key or group will be re-written.
 *
 * @param string $key the config value key
 * @param string $group the config group
 * @param mixed $value the optional new value
 * @return mixed
 */
function config($key = NULL, $group = 'config', $value = NULL)
{

	//If we are setting the value
	if( func_num_args() == 3 )
	{
		return config::set($key, $group, $value);
	}

	//Return the value
	return config::get($key, $group);

}


/**
 * Returns the HTTP response status for a given code.
 * If no code provided, optionally return an array
 * of all status.
 *
 * @param int $code the header status code
 * @param bool $return on fail, should return the full array?
 * @return mixed
 */
function http_response_status($code = null, $return = FALSE)
{
	$statuses =  array(
	100 => 'Continue',
	101 => 'Switching Protocols',
	102 => 'Processing',

	200 => 'OK',
	201 => 'Created',
	202 => 'Accepted',
	203 => 'Non-Authoritative Information',
	204 => 'No Content',
	205 => 'Reset Content',
	206 => 'Partial Content',
	207 => 'Multi-Status',
	226 => 'IM Used',

	300 => 'Multiple Choices',
	301 => 'Moved Permanently',
	302 => 'Found',
	303 => 'See Other',
	304 => 'Not Modified',
	305 => 'Use Proxy',
	306 => 'Reserved',
	307 => 'Temporary Redirect',

	400 => 'Bad Request',
	401 => 'Unauthorized',
	402 => 'Payment Required',
	403 => 'Forbidden',
	404 => 'Not Found',
	405 => 'Method Not Allowed',
	406 => 'Not Acceptable',
	407 => 'Proxy Authentication Required',
	408 => 'Request Timeout',
	409 => 'Conflict',
	410 => 'Gone',
	411 => 'Length Required',
	412 => 'Precondition Failed',
	413 => 'Request Entity Too Large',
	414 => 'Request-URI Too Long',
	415 => 'Unsupported Media Type',
	416 => 'Requested Range Not Satisfiable',
	417 => 'Expectation Failed',
	422 => 'Unprocessable Entity',
	423 => 'Locked',
	424 => 'Failed Dependency',
	426 => 'Upgrade Required',
	449 => 'Retry With',

	500 => 'Internal Server Error',
	501 => 'Not Implemented',
	502 => 'Bad Gateway',
	503 => 'Service Unavailable',
	504 => 'Gateway Timeout',
	505 => 'HTTP Version Not Supported',
	506 => 'Variant Also Negotiates',
	507 => 'Insufficient Storage',
	509 => 'Bandwidth Limit Exceeded',
	510 => 'Not Extended'
	);

	//If the code is found
	if(isset($statuses[$code]))
	{
		return $statuses[$code];
	}

	//Fail or return the array?
	return $return ? $statuses : FALSE;
}


/**
 * Convert a number in bytes to the highest human form posible
 * 
 * @param int $bytes the value in bytes
 * @param int $round decimal precision
 * @return string
 */
function byte_to_human($bytes, $round = 2)
{
	$sizes = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
	for ($i=0; $bytes > 1024 && $i < count($sizes) - 1; $i++) $bytes /= 1024;
	return round($bytes,$round).$sizes[$i];
}


/**
 * Try to fetch the current users IP address
 * 
 * @return string
 */
function ip_address()
{

	static $ip = FALSE;

	if( $ip ) {
		return $ip;
	}

	//Get IP address - if proxy lets get the REAL IP address
	if (!empty($_SERVER['REMOTE_ADDR']) AND !empty($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['REMOTE_ADDR'])) {
		$ip = $_SERVER['REMOTE_ADDR'];
	} elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = '0.0.0.0';
	}

	//Clean the IP and return it
	return $ip = preg_replace('/[^0-9\.]+/', '', $ip);
}


/**
 * Create a fairly random 32 character MD5 token
 * 
 * @return string
 */
function token()
{
	return md5(str_shuffle(chr(mt_rand(32, 126)). uniqid(). microtime(TRUE)));
}


/**
 * Encode a string so it is safe to pass through the URI
 * 
 * @param string $string
 * @return string
 */
function base64_url_encode($string = NULL)
{
	return strtr(base64_encode($string), '+/=', '-_~');
}


/**
 * Decode a string passed through the URI
 * 
 * @param string $string
 * @return string
 */
function base64_url_decode($string = NULL)
{
	return base64_decode(strtr($string, '-_~','+/='));
}


/**
 * Sends an HTML formatted email along with a plaintext version.
 * Original function from Tyler Hall <clickontyler.com>
 * 
 * @param array $to
 * @param string $subject
 * @param string $msg
 * @param string $from
 * @param string $plaintext
 * @return boolean
 */
function email($to, $subject, $msg, $from = '', $plaintext = '')
{

	// Make array
	$to = to_array($to);

	// Auto-set "from" address
	$from = $from ? $from : 'donotreply@'. DOMAIN;

	// Auto-Create HTML encoded version of email
	$plaintext = $plaintext ? $plaintext : h($msg);

	foreach($to as $address)
	{
		$boundary = uniqid(rand(), true);

		$headers  = "From: $from\n";
		$headers .= "MIME-Version: 1.0\n";
		$headers .= "Content-Type: multipart/alternative; boundary = $boundary\n";
		$headers .= "This is a MIME encoded message.\n\n";

		$headers .= "--$boundary\n" .
		"Content-Type: text/plain; charset=UTF-8\n" .
		"Content-Transfer-Encoding: base64\n\n";
		$headers .= chunk_split(base64_encode($plaintext), 70, "\n");

		$headers .= "--$boundary\n" .
		"Content-Type: text/html; charset=UTF-8\n" .
		"Content-Transfer-Encoding: base64\n\n";
		$headers .= chunk_split(base64_encode($msg), 70, "\n");
		$headers .= "--$boundary--\n" .

		//Remove CRLF dispite php.net's docs (fix GMail issue)
		$headers = str_replace("\r\n", "\n", $headers);

		//Send email keeping track of the success of the last message
		$return = mail($address, $subject, '', $headers);
	}

	return $return;
}
