<?php
/**
 * Common Functions
 *
 * This file contains a growing list of common functions for use in throughout
 * the MicroMVC system.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	Copyright (c) 2009 MicroMVC
 * @license		http://www.gnu.org/licenses/gpl-3.0.html
 * @link		http://micromvc.com
 * @version		1.0.1 <5/31/2009>
 ********************************** 80 Columns *********************************
 */


/**
 * Try to fetch the current users IP address
 * @return string
 */
function ip_address() {

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
	return sanitize_text($ip, 2);
}


/**
 * Get the current domain (sub.site.tld)
 * @return	string
 */
function current_domain() {

	// Get the Site Name: www.site.com -also protects from XSS/CSFR attacks
	$regex = '/((([a-z0-9\-]{1,70}\.){1,5}[a-z]{2,4})|localhost)/i';

	//Match the name
	preg_match($regex,(!empty($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : $_SERVER['HTTP_HOST']), $match);

	//MUST HAVE A HOST!
	if(empty($match[0])) {
		die('Sorry, host not found');
	}

	return $match[0];
}


/**
 * Check to see if this request is an ajax request
 * @return boolean
 */
function is_ajax_request() {
	if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
		return TRUE;
	}
	return FALSE;
}


/**
 * Record memory usage and timestamp and then return difference next run (and restart)
 * @return	array
 */
function benchmark() {
	static $start_time;
	static $memory_usage;

	//Caculate result
	$result = array((microtime(true) - $start_time), (memory_get_usage() - $memory_usage));

	//Set new times
	$start_time = microtime(true);
	$memory_usage = memory_get_usage();

	//return
	return $result;
}


/**
 * Class registry
 *
 * This function acts as a singleton. If the requested class does not
 * exist it is instantiated and set to a static variable. If it has
 * previously been instantiated the variable is returned.
 *
 * @param	string	Class name being requested
 * @param	string	Folder name to find it in
 * @param	mixed	Optional params to pass
 * @param	bool	Location to look (1 = SITE, 2 = SYSTEM, 3 = MODULES)
 * @param	bool	(flag) load class but do not instantiate
 * @return	object
 */
function load_class($class = NULL, $path = NULL, $params = NULL, $instantiate = TRUE) {

	static $objects = array();

	//If a class is NOT given
	if ( ! $class) {
		trigger_error('Attempted to load a non-existent class with no name.');
		return FALSE;
	}

	//If this class is already loaded
	if(!empty($objects[$class])) {
		return $objects[$class];
	}

	// If the class is not already loaded
	if ( ! class_exists($class)) {
		//Require the file
		require_once($path. $class. '.php');
	}

	//If we just want to load the file - nothing more
	if ($instantiate == FALSE) {
		return TRUE;
	}

	return $objects[$class] = new $class($params);
}


/**
 * Custom error handler which shows more details when needed, yet can hide scary data
 * from the user. Auto-detects the level of errors you allow in your php.ini file and
 * only shows those errors and higher.
 *
 * @param $level
 * @param $message
 * @param $file
 * @param $line
 * @param $variables
 * @return void
 */
function _error_handler($level='', $message='', $file='', $line='', $variables='') {

	//If this error isn't worth reporting (or below the set level) - skip it
	if ($level == E_STRICT OR ($level & error_reporting()) !== $level) {
		return;
	}

	//Only show the system file that had the problem - not the whole server dir structure!
	$file = str_replace(SYSTEM_PATH, '', $file);

	//Set error types
	$error_levels = array(
	E_ERROR				=>	'Error',
	E_WARNING			=>	'Warning',
	E_PARSE				=>	'Parsing Error',
	E_NOTICE			=>	'Notice',
	E_CORE_ERROR		=>	'Core Error',
	E_CORE_WARNING		=>	'Core Warning',
	E_COMPILE_ERROR		=>	'Compile Error',
	E_COMPILE_WARNING	=>	'Compile Warning',
	E_USER_ERROR		=>	'User Error',
	E_USER_WARNING		=>	'User Warning',
	E_USER_NOTICE		=>	'User Notice',
	E_STRICT			=>	'Runtime Notice'
	);

	//Get Human-safe error title
	$error = $error_levels[$level];

	if(DEBUG_MODE) {

		//Create sentence
		$line_info = 'On line '. $line. ' in '. $file;

		//If the database class is loaded - get the queries run (if any)
		if(class_exists('db')) {
			$db = db::get_instance();
		}

		//Get backtrace and remove last entry (this function)
		$backtrace = debug_backtrace();
		//Remove first entry (this error function)
		unset($backtrace[0]);

		if($backtrace) {

			//Store the array of backtraces
			$trace = array();

			//Max of 5 levels deep
			if(count($backtrace) > 5) {
				$backtrace = array_chunk($backtrace, 5, TRUE);
				$backtrace = $backtrace[0];
			}

			// start backtrace
			foreach ($backtrace as $key => $v) {

				if(!isset($v['line'])) {
					$v['line'] = ($key === 1 ? $line : '(unknown)');
				}
				if(!isset($v['file'])) {
					$v['file'] = ($key === 1 ? $file : '(unknown)');
				}

				$args = array();
				if(isset($v['args'])) {
					foreach ($v['args'] as $a) {
						$type = gettype($a);
						if($type == 'integer' OR $type == 'double') {
							$args[] = $a;

						} elseif ($type == 'string') {
							//Longer than 25 chars?
							$a = strlen($a) > 45 ? substr($a, 0, 45). '...' : $a;
							$args[] = '"'. htmlentities($a, ENT_QUOTES, 'utf-8'). '"';

						} elseif ($type == 'array') {
							$args[] = 'Array('.count($a).')';

						} elseif ($type == 'object') {
							$args[] = 'Object('.get_class($a).')';

						} elseif ($type == 'resource') {
							$args[] = 'Resource('.strstr($a, '#').')';

						} elseif ($type == 'boolean') {
							$args[] = ($a ? 'True' : 'False'). '';

						} elseif ($type == 'Null') {
							$args[] = 'Null';
						} else {
							$args[] = 'Unknown';
						}
					}

					//If only a couple arguments were given - convert to string
					if(count($args) < 4) {
						$args = implode(', ', $args);
					}
				}

				// Compose Backtrace
				$string = '';

				if(!empty($trace)) {
					$string .= 'Called by ';
				}

				//If this is a class
				if (isset($v['class'])) {
					$string .= 'Method <b>'.$v['class']. '->'. $v['function']. '('. (is_string($args) ? $args : ''). ')</b>';
				} else {
					$string .= 'Function <b>'. $v['function']. '('. (is_string($args) ? $args : ''). ')</b>';
				}

				//Add line number and file
				$string .= ' on line '. $v['line']. ' in '. str_replace(SYSTEM_PATH, '', $v['file']). '<br />';

				//Create an element containing the trace and function args (only if still an array)
				$trace[] = array($string, (is_string($args) ? '' : $args));

			}
		}
	}

	//Flush any output buffering first
	if(ob_get_level()) { ob_end_flush(); }

	//Load the view
	include(VIEW_PATH. 'errors'. DS. 'php_error.php');

	exit();
}


/**
 * Load a HTTP header error page and then exit script
 * @param $type
 */
function request_error($type = '404') {

	//Check the type of error
	if ($type == '400') {
		header("HTTP/1.0 400 Bad Request");
	} elseif ($type == '401') {
		header("HTTP/1.0 401 Unauthorized");
	} elseif ($type == '403') {
		header("HTTP/1.0 403 Forbidden");
	} elseif ($type == '500') {
		header("HTTP/1.0 500 Internal Server Error");
	} else {
		$type = '404';
		header("HTTP/1.0 404 Not Found");
	}

	//Load the view
	include(VIEW_PATH. 'errors'. DS. $type. '.php');

	//Exit
	exit();
}


/**
 * Load an error using the general error template and then exit the script
 * @param $message
 * @param $title
 */
function show_error($message = '', $title = 'An Error Was Encountered') {
	//Load the view
	exit(include(VIEW_PATH. 'errors'. DS. 'general.php'));
}


/**
 * Print <pre> tags around objects you want to dump.
 * @param mixed $data
 */
function print_pre($data = NULL) {
	print '<pre style="padding: 1em; margin: 1em 0; background: #eee;">';
	if(func_num_args() < 2) {
		print_r($data);
	} else {
		print_r(func_get_args());
	}
	print '</pre>';
}


/**
 * Return data dump surrounded by <pre> tags.
 * @param mixed $data
 */
function return_pre($data = NULL) {
	$string = '<pre style="padding: 1em; margin: 1em 0; background: #eee;">';
	if(func_num_args() < 2) {
		$string .= print_r($data, TRUE);
	} else {
		$string .= print_r(func_get_args(), TRUE);
	}
	return $string. '</pre>';
}


/**
 * Cleans text of all bad characters
 * @param string	$text	text to clean
 * @param boolean	$level	Set to TRUE to only enable file safe chars
 * @return void
 */
function sanitize_text($text, $level=0){
	if(!$level) {
		//Delete anything that isn't a letter, number, or common symbol - then HTML encode the rest.
		return trim(htmlentities(preg_replace("/([^a-z0-9!@#$%^&*()_\-+\]\[{}\s\n<>:\\/\.,\?;'\"]+)/i", '', $text), ENT_QUOTES, 'UTF-8'));
	} else {
		//Make the text file/title/emailname safe
		return preg_replace("/([^a-z0-9_\-\.]+)/i", '_', trim($text));
	}
}


/**
 * split_text
 *
 * Split text into chunks ($inside contains all text inside
 * $start and $end, and $outside contains all text outside)
 *
 * @param	String  Text to split
 * @param	String  Start break item
 * @param	String  End break item
 * @return	Array
 */
function split_text($text='', $start='<code>', $end='</code>') {
	$tokens = explode($start, $text);
	$outside[] = $tokens[0];

	$num_tokens = count($tokens);
	for ($i = 1; $i < $num_tokens; ++$i) {
		$temp = explode($end, $tokens[$i]);
		$inside[] = $temp[0];
		$outside[] = $temp[1];
	}

	return array($inside, $outside);
}


/**
 * Random Charaters
 *
 * Pass this function the number of chars you want
 * and it will randomly make a string with that
 * many chars. (I removed chars that look alike.)
 *
 * @param	Int		Length of character string
 * @param	Int		Charater set to use
 * @return	Array
 */
function random_charaters($number, $type=0) {
	$ascii[0] = 'ACEFGHJKLMNPRSTUVWXY345679';
	$ascii[1] = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJK.MNOPQRSTUVWXYZ'
	. '!"#$%&\'()*+`-.\\/0123456789:;<=>?@{|}~';
	$chars = null;
	for($i=0; $i<$number; $i++) {
		$chars .= $ascii[$type]{rand(0,strlen($ascii[$type])-1)};
	}
	return $chars;
}


/**
 * Valid Email
 * @param	string	email to check
 * @return	boolean
 */
function valid_email($text){
	return ( ! preg_match("/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b/i", $text)) ? FALSE : TRUE;
}


/**
 * Gzip/Compress Output
 * Original function came from wordpress.org
 * @return void
 */
function gzip_compression() {

	//If no encoding was given - then it must not be able to accept gzip pages
	if(!isset($_SERVER['HTTP_ACCEPT_ENCODING'])) { return false; }

	//If zlib is not ALREADY compressing the page - and ob_gzhandler is set
	if (( ini_get('zlib.output_compression') == 'On'
	|| ini_get('zlib.output_compression_level') > 0 )
	|| ini_get('output_handler') == 'ob_gzhandler' ) {
		return false;
	}

	//Else if zlib is loaded start the compression.
	if ( (extension_loaded( 'zlib' ))
	&& (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ) {
		ob_start('ob_gzhandler');
	}

	/* Debug Data
	 print $_SERVER['HTTP_ACCEPT_ENCODING']. '<br />'.
	 'extension_loaded("zlib") = '. extension_loaded( 'zlib' ). '<br />'.
	 'ini_get("zlib.output_compression") = '. ini_get('zlib.output_compression'). '<br />'.
	 'ini_get("output_handler") = '. ini_get('output_handler'). '<br />';
	 */
}


/**
 * Return a singleton instance of the current controller
 * @return object
 */
function get_instance(){
	return controller::get_instance();
}


/**
 * Creates pagination links for the number of pages given
 *
 * @param array $options
 * @return array
 */
function pagination($options=null) {

	/** [Options]
	 * total		Total number of items
	 * per_page		Items to show each page
	 * current_page	The current page that the user is on
	 * url			URI value to place in the links (must include "[[page]]")
	 * 				Example: /home/blog/page/[[page]]/
	 */

	//Don't allow page 0 or lower
	if($options['current_page'] < 0) {
		$options['current_page'] = 0;
	}


	//Initialize
	$data = array(
		'links' => null,
		'next' => null,
		'previous' => null,
		'total' => null,
		'offset' => 0,
	);

	//The offset to start from. This is useful if you are running a DB query
	if($options['current_page'] > 1) {
		$data["offset"] = (($options['per_page'] * $options['current_page']) - $options['per_page']);
	}

	//The Number of pages based on the total number of items and the number to show each page
	$data['total'] = ceil($options['total'] / $options['per_page']);

	//If there is more than one page...
	if($data['total'] > 1) {

		//If this is NOT the first page - show a previous link
		if($options['current_page'] > 1) {
			$data['previous'] = str_replace('[[page]]', ($options['current_page'] - 1), $options['url']);
		}

		//If this isn't the last page - add a "next" link
		if($options['current_page'] + 1 < $data['total']) {
			$data["next"] = str_replace('[[page]]', ($options['current_page'] + 1), $options['url']);
		}
	}

	//For each page, create the URL
	for($i = 0; $i < $data['total']; $i++) {
		if($options['current_page'] == $i) {
			$data['links'][$i] = '';
		} else {
			//Replace [[page]] with the page number
			$data["links"][$i] = str_replace('[[page]]', $i, $options['url']);
		}
	}

	return $data;
}


/**
 * Write Log File
 *
 * @access	public
 * @param	string	the error level
 * @param	string	the error message
 * @param	bool	whether the error is a native PHP error
 * @return	bool
 */
function log_message($message = '') {

	$filepath = LOG_PATH. DOMAIN. '_log-'. date('Y-m-d'). '.php';

	//Add a exit header to the file
	if ( ! file_exists($filepath)) {
		$message = "<". "?php exit('Access Denied'); ?".">\n\n". $message;
	}

	if ( ! $fp = @fopen($filepath, 'a')) {
		return FALSE;
	}

	//Add a timestamp
	$message .= ' - '. date("M j, Y, g:i a"). "\n";

	flock($fp, LOCK_EX);
	fwrite($fp, $message);
	flock($fp, LOCK_UN);
	fclose($fp);

	return TRUE;
}


/**
 * Header redirection for both location and refresh types.
 *
 * @param	string	the URL
 * @param	string	the method: location or redirect
 * @return	void
 */
function redirect($uri = '', $method = 'location', $http_response_code = 302) {

	//Do we need to add the site URL prefix?
	$uri = strpos($uri, '://') !== FALSE ? $uri : 'http://'. DOMAIN. SITE_URL . $uri;

	if($method == 'refresh') {
		header("Refresh:0;url=". $uri);
	} else {
		header("Location: ". $uri, TRUE, $http_response_code);
	}
	exit;
}


/**
 * Return the site url prefixed to the uri path
 * @param $uri
 * @return unknown_type
 */
function site_url($uri = '') {
	return SITE_URL. trim($uri, '/'). '/';
}


/**
 * Fetch a $_POST value (or the default if not found)
 * @param	string	$key
 * @param	mixed	$value
 * @return	mixed
 */
function post($key, $value = NULL) {
	return isset($_POST[$key]) ? $_POST[$key] : $value;
}


/**
 * Fetch the $_SESSION value (or default if not found)
 * @param	string	$key
 * @param	mixed	$value
 * @return	mixed
 */
function session($key, $value = NULL) {
	return isset($_SESSION[$key]) ? $_SESSION[$key] : $value;
}


/**
 * Encode a string so it is safe to pass through the URI
 * @param	string	$string
 * @return	string
 */
function base64_url_encode($string=null){
  return strtr(base64_encode($string), '+/=', '-_~');
}


/**
 * Decode a string passed through the URI
 * @param	string	$string
 * @return	string
 */
function base64_url_decode($string=null) {
  return base64_decode(strtr($string, '-_~','+/='));
}


/**
 * Fetch the following line from the loaded language array
 * @param	$line
 * @return	mixed
 */
function lang($line = NULL) {
	static $mvc = NULL;

	if($mvc == NULL) {
		$mvc = get_instance();
	}

	if(isset($mvc->lang[$line])) {
		return $mvc->lang[$line];
	}
}


