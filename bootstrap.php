<?php
/**
 * Core Bootstrap
 *
 * This file contains all common system functions and View and Controller classes.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2011 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */

/**
 * Record memory usage and timestamp and then return difference next run (and restart)
 *
 * @return array
 */
function benchmark()
{
	static $time, $memory;
	$result = array((microtime(true) - $time), (memory_get_usage() - $memory));
	$time = microtime(true);
	$memory = memory_get_usage();
	return $result;
}


/**
 * System registry for storing global objects and services
 *
 * @return object
 */
function registry()
{
	static $service;
	return $service ? $service : ($service = new Service);
}


/**
 * Set a message to show to the user (error, warning, success, or message).
 *
 * @param string $type of message
 * @param string $value the message to store
 */
function message($type = NULL, $value = NULL)
{
	static $message = array();
	
	$h = '';
	
	if($value)
	{
		$message[$type][] = $value;
	}
	elseif($type)
	{
		if(isset($message[$type]))
		{
			foreach($message[$type] as $value)
			{
				$h .= "<div class = \"$type\">$value</div>";
			}
		}
	}
	else
	{
		foreach($message as $type => $data)
		{
			foreach($data as $value)
			{
				$h .= "<div class = \"$type\">$value</div>";
			}
		}
	}
	
	return $h;
}


/**
 * Attach (or remove) multiple callbacks to an event and trigger those callbacks when that event is called.
 *
 * @param string $k the name of the event to run
 * @param mixed $v the optional value to pass to each callback
 * @param mixed $callback the method or function to call - FALSE to remove all callbacks for event
 */
function event($key, $value = NULL, $callback = NULL)
{
	static $events;
	
	// Adding or removing a callback?
	if($callback !== NULL)
	{
		if($callback)
		{
			$events[$key][] = $callback;
		}
		else
		{
			unset($events[$key]);
		}
	}
	elseif(isset($events[$key])) // Fire a callback
	{
		foreach($events[$key] as $function)
		{
			$value = call_user_func($function, $value);
		}
		return $value;
	}
}


/**
 * Fetch a config value
 *
 * @param string $key the config key name
 * @param string $module the module name
 * @return string
 */
function config($key, $module = 'system')
{
	static $c;
	
	if(empty($c[$module]))
	{
		$c[$module] = require(SP . ($module != 'system' ? "$module/" : '') . 'config' . EXT);
	}
	
	return ($key ? $c[$module][$key] : $c[$module]);
}


/**
 * Fetch the language text for the given line.
 *
 * @param string $key the language key name
 * @param string $module the module name
 * @return string
 */
function lang($key, $module = 'system')
{
	return lang::get($key, $module);
}


/**
 * Returns the current URL path string (if valid)
 * PHP before 5.3.3 throws E_WARNING for bad uri in parse_url()
 *
 * @param int $k the key of URL segment to return
 * @param mixed $d the default if the segment isn't found
 * @return string
 */
function url($key = NULL, $default = NULL)
{
	static $uri = NULL;
	
	if($uri === NULL)
	{
		foreach(array('REQUEST_URI', 'PATH_INFO', 'ORIG_PATH_INFO') as $v)
		{
			preg_match('/^\/[\w\-~=\/\.+%]{1,600}/', server($v), $parts);
			
			if( ! empty($parts[0]))
			{
				$uri = explode('/', trim($parts[0], '/'));
				break;
			}
		}
		
		// Still nothing? Then mark as empty for the next call
		if($uri === NULL)
		{
			$uri = '';
		}
	}
	
	if($uri)
	{
		return($key !== NULL ? (isset($uri[$key]) ? $uri[$key] : $default) : implode('/', $uri));
	}
	
	return $default;
}


/**
 * Automatically load the given class
 *
 * @param string $class name
 */
function __autoload($class)
{
	// System classes will *not* have an underscore
	require (mb_strtolower(SP . (strpos($class, '_') === FALSE ? 'system/' : '') . str_replace('_', '/', $class . EXT)));
}


/**
 * Return an HTML safe dump of the given variable(s) surrounded by "pre" tags.
 * You can pass any number of variables (of any type) to this function.
 *
 * @param mixed
 * @return string
 */
function dump()
{
	$string = '';
	foreach(func_get_args() as $value)
	{
		$string .= '<pre>' . h($value === NULL ? 'NULL' : (is_scalar($value) ? $value : print_r($value, TRUE))) . "</pre>\n";
	}
	return $string;
}


/**
 * Safely fetch a $_POST value, defaulting to the value provided if the key is
 * not found.
 *
 * @param string $k the key name
 * @param mixed $d the default value if key is not found
 * @param boolean $s true to require string type
 * @return mixed
 */
function post($key, $default = NULL, $string = FALSE)
{
	if(isset($_POST[$key]))
	{
		return $string ? str($_POST[$key], $default) : $_POST[$key];
	}
	return $default;
}


/**
 * Safely fetch a $_GET value, defaulting to the value provided if the key is
 * not found.
 *
 * @param string $k the key name
 * @param mixed $d the default value if key is not found
 * @param boolean $s true to require string type
 * @return mixed
 */
function get($key, $default = NULL, $string = FALSE)
{
	if(isset($_GET[$key]))
	{
		return $string ? str($_GET[$key], $default) : $_GET[$key];
	}
	return $default;
}


/**
 * Safely fetch a $_SERVER value, defaulting to the value provided if the key is
 * not found.
 *
 * @param string $k the key name
 * @param mixed $d the default value if key is not found
 * @return mixed
 */
function server($k, $d = NULL)
{
	return isset($_SERVER[$k]) ? $_SERVER[$k] : $d;
}


/**
 * Safely fetch a $_SESSION value, defaulting to the value provided if the key is
 * not found.
 *
 * @param string $k the post key
 * @param mixed $d the default value if key is not found
 * @return mixed
 */
function session($k, $d = NULL)
{
	return isset($_SESSION[$k]) ? $_SESSION[$k] : $d;
}


/**
 * Create a random 32 character MD5 token
 *
 * @return string
 */
function token()
{
	return md5(str_shuffle(chr(mt_rand(32, 126)) . uniqid() . microtime(TRUE)));
}


/**
 * Write to the log file
 *
 * @param string $m the message to save
 * @return bool
 */
function log_message($message)
{
	if(! $fp = @fopen(SP . config('log_path') . date('Y-m-d') . '.log', 'a'))
	{
		return FALSE;
	}
	
	// Append date and IP to log message
	$message = date('H:i:s ') . h(server('REMOTE_ADDR')) . " $message\n";
	
	flock($fp, LOCK_EX);
	fwrite($fp, $message);
	flock($fp, LOCK_UN);
	fclose($fp);
	
	return TRUE;
}


/**
 * Send a HTTP header redirect using "location" or "refresh".
 *
 * @param string $uri the URI string
 * @param int $c the HTTP status code
 * @param string $method either location or redirect
 */
function redirect($uri = '', $code = 302, $method = 'location')
{
	$uri = site_url($uri);
	header($method == 'refresh' ? "Refresh:0;url = $uri" : "Location: $uri", TRUE, $code);
}


/**
 * Type cast a scalar variable into an a valid integer between the given min/max values.
 * If the value is not a valid numeric value then min will be returned.
 *
 * @param int $int the value to convert
 * @param int $min the lowest value allowed
 * @param int $max the heighest value allowed
 * @return int|null
 */
function int($int, $min = NULL, $max = NULL)
{
	$int = is_int($int) OR ctype_digit($int) ? (int) $int : $min;
	
	if($min !== NULL AND $int < $min)
	{
		$int = $min;
	}
	
	if($max !== NULL AND $int > $max)
	{
		$int = $max;
	}
	
	return $int;
}


/**
 * Type cast the given variable into a string - on fail return default.
 *
 * @param mixed $string the value to convert
 * @param string $default the default value to assign
 * @return string
 */
function str($str, $default = '')
{
	return is_scalar($str) ? (string) $str : $default;
}


/**
 * Return the full URL to a path on this site or another.
 *
 * @param string $uri may contain another sites TLD
 * @return string
 */
function site_url($uri = NULL)
{
	return (strpos($uri, '://') === FALSE ? DOMAIN . '/' : '') . ltrim($uri, '/');
}


/**
 * Return the full URL to the theme folder
 *
 * @param string $uri
 * @return string
 */
function theme_url($uri = NULL)
{
	return site_url(config('theme') . '/' . ltrim($uri, '/'));
}


/**
 * Convert a string from one encoding to another encoding
 * and remove invalid bytes sequences.
 *
 * @param string $string to convert
 * @param string $to encoding you want the string in
 * @param string $from encoding that string is in
 * @return string
 */
function encode($string, $to = 'UTF-8', $from = 'UTF-8')
{
	// ASCII is already valid UTF-8
	if($to == 'UTF-8' AND is_ascii($string))
	{
		return $string;
	}
	
	// Convert the string
	return @iconv($from, $to . '//TRANSLIT//IGNORE', $string);
}


/**
 * Tests whether a string contains only 7bit ASCII characters.
 *
 * @param string $string to check
 * @return bool
 */
function is_ascii($string)
{
	return!preg_match('/[^\x00-\x7F]/S', $string);
}


/**
 * Encode a string so it is safe to pass through the URI
 *
 * @param string $string to encode
 * @return string
 */
function base64_url_encode($string = NULL)
{
	return strtr(base64_encode($string), '+/=', '-_~');
}


/**
 * Decode a string passed through the URI
 *
 * @param string $string to encode
 * @return string
 */
function base64_url_decode($string = NULL)
{
	return base64_decode(strtr($string, '-_~', '+/='));
}


/**
 * Convert special characters to HTML safe entities.
 *
 * @param string $str the string to encode
 * @return string
 */
function h($data)
{
	return htmlspecialchars($data, ENT_QUOTES, 'utf-8');
}


/**
 * Return a SQLite/MySQL/PostgreSQL datetime string
 * 
 * @param int $timestamp
 */
function sql_date($timestamp = NULL)
{
	return date('Y-m-d H:i:s', $timestamp ?: time());
}

// End
