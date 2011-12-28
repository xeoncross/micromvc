<?php
/**
 * Controller
 *
 * Basic outline for standard system controllers
 */
abstract class Controller
{
	/**
	 * Called before the controller method is run
	 *
	 * @param string $method name that will be run
	 */
	public function before($method) {}


	/* HTTP Request Methods
	public function index();	// Default for all non-defined request methods
	public function get();
	public function post();
	public function put();
	public function delete();
	public function options();
	public function head();
	*/

	/**
	 * Called after the controller method is run
	 *
	 * @param string $method name that will be run
	 */
	public function after($method) {}
}


/**
 * Prototype class based on anonymous functions
 *
 * @see http://php.net/manual/en/functions.anonymous.php
 */
class Prototype
{
	/**
	 * Constructor
	 * @param array $closures if set all values will be stored as a object properties
	 */
	public function __construct(array $closures = null)
	{
		foreach((array) $closures as $key => $closure)
		{
			$this->$key = $closure;
		}
	}

	/**
	 * Call the given closure function passing a copy of this object
	 * $proto->foo([$arg1, $arg2, ...])
	 */
	public function __call($key, $args)
	{
		// Pass $this as first argument of callback (PHP 5.4 will be here soon!)
		array_unshift($args, $this);
		return call_user_func_array($this->$key, $args);
	}
}


/**
 * Validation class based on anonymous functions
 *
 * @see http://php.net/manual/en/functions.anonymous.php
 */
class Validation extends Prototype
{
	/**
	 * Validate the given array of data using the functions set
	 *
	 * @param array $data to validate
	 * @return array
	 */
	public function validate(array $data)
	{
		$errors;
		foreach((array) $this as $key => $function)
		{
			if($error = $function(isset($data[$k]) ? $data[$k] : NULL, $key))
			{
				$errors[$key] = $error;
			}
		}
		return $errors;
	}


	/**
	 * See if the given string contains XML/HTML markup. This is useful
	 * for checking text input fields that should only contain usernames or
	 * post titles.
	 *
	 * @param string $string to check
	 * @return boolean
	 */
	public function markup($string)
	{
		// Simple check for markup tags
		if(mb_strpos($string, '<') === FALSE AND mb_strpos($string, '>') === FALSE)
		{
			// Now lets get serious
			if($xml = simplexml_load_string("<root>$string</root>"))
			{
				return $xml->count() !== 0;
			}
		}

		return TRUE;
	}


	/**
	 * Check to see if the email entered is valid.
	 *
	 * @param string $string to validate
	 * @return boolean
	 */
	public function isEmail($string)
	{
		return preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $string);
	}
}


/**
 * UTF-8 i18n and l10n class for working with strings and dates.
 * This class is based heavly on the great work done by Alix Alex.
 *
 * @see https://github.com/alixaxel/phunction
 */
class _
{
	/**
	 * Convert a string to UTF-8 and remove invalid bytes sequences.
	 *
	 * @param string $string to convert
	 * @param string $encoding current encoding of string (default to UTF-8)
	 * @param string $control TRUE to keep "Other" characters
	 * @return string
	 */
	static function convert($string, $encoding = 0, $control = 1)
	{
		// Try to figureout what the encoding is if posible
		if(function_exists('mb_detect_encoding')) $encoding = mb_detect_encoding($string, 'auto');

		// Convert to valid UTF-8
		if(($string = @iconv( ! $encoding ? 'UTF-8' : $encoding, 'UTF-8//IGNORE', $string)) !== false)
		{
			// Optionally remove "other" characters and windows useless "\r"
			return $control ? preg_replace('~\p{C}+~u', '', $string) : preg_replace(array('~\r\n?~', '~[^\P{C}\t\n]+~u'), array("\n", ''), $string);
		}
	}


	/**
	 * Return an IntlDateFormatter object using the current system locale
	 *
	 * @see IntlDateFormatter
	 * @param string $locale string
	 * @param integer $datetype IntlDateFormatter constant
	 * @param integer $timetype IntlDateFormatter constant
	 * @param string $timezone Time zone ID, default is system default
	 * @return IntlDateFormatter
	 */
	static function date($locale = 0, $datetime = IntlDateFormatter::MEDIUM, $timetype = IntlDateFormatter::SHORT, $timezone = NULL)
	{
		return new IntlDateFormatter($locale ?: setlocale(LC_ALL,0), $datetime, $timetype, $timezone);
	}


	/**
	 * Format the given string using the current system locale
	 * Basically, it's sprintf on i18n steroids.
	 *
	 * @see MessageFormatter
	 * @param string $string to parse
	 * @param array $params to insert
	 * @return string
	 */
	static function format($string, array $params = NULL)
	{
		return msgfmt_format_message(setlocale(LC_ALL,0), $string, $params);
	}


	/**
	 * Normalize the given UTF-8 string
	 *
	 * @see http://stackoverflow.com/a/7934397/99923
	 * @param string $string to normalize
	 * @param int $form to normalize as
	 * @return string
	 */
	static function normalize($string, $form = Normalizer::FORM_D)
	{
		return normalizer_normalize($string, $form);
	}


	/**
	 * Remove accents from characters
	 *
	 * @param string $string to remove accents from
	 * @return string
	 */
	static function unaccent($string)
	{
		// Only process if there are entities
		if(strpos($string = htmlentities($string, ENT_QUOTES, 'UTF-8'), '&') !== false)

		// Remove accent HTML entities
		return html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|tilde|uml);~i', '$1', $string), ENT_QUOTES, 'UTF-8');
	}


	/**
	 * Convert a string to an ASCII/URL/file name safe slug
	 *
	 * @param string $string to convert
	 * @param string $character to separate words with
	 * @param string $extra characters to include
	 * @return string
	 */
	static function slug($string, $character = '-', $extra = null)
	{
		$string = strtolower(trim(preg_replace('~[^0-9a-z' . preg_quote($extra,'~') . ']+~i', $character, self::unaccent($string)), $character));
		// Need to add this
		return preg_replace("/$character{2,}/", $character, $string);
	}


	/**
	 * Tests whether a string contains only 7bit ASCII characters.
	 *
	 * @param string $string to check
	 * @return bool
	 */
	static function is_ascii($string)
	{
		return ! preg_match('/[^\x00-\x7F]/S', $string);
	}


	/**
	 * Encode a string so it is safe to pass through the URL
	 *
	 * @param string $string to encode
	 * @return string
	 */
	static function base64_url_encode($string = NULL)
	{
		return strtr(base64_encode($string), '+/=', '-_~');
	}


	/**
	 * Decode a string passed through the URL
	 *
	 * @param string $string to decode
	 * @return string
	 */
	static function base64_url_decode($string = NULL)
	{
		return base64_decode(strtr($string, '-_~', '+/='));
	}
}


/**
 * Fetch input values safely with support for default values.
 */
class I
{
	public static function __callStatic($method, $args)
	{
		// Function calls are slow
		//$method = '_' . strtoupper($method);

		$types = array(
			'session' => '_SESSION',
			'post' => '_POST',
			'get' => '_GET',
			'server' => '_SERVER',
			'files' => '_FILES',
			'cookie' => '_COOKIE',
			'env' => '_ENV',
			'request' => '_REQUEST'
		);

		$method = $types[$method];

		if(isset($GLOBALS[$method][$args[0]]))
		{
			return $GLOBALS[$method][$args[0]];
		}

		return isset($args[1]) ? $args[1] : NULL;
	}
}


/**
 * HTML template views
 */
class View
{
	static $path, $ext = '.php';
	public $view;

	/**
	 * Returns a new view object for the given view.
	 *
	 * @param string $file the view file to load
	 * @param string $path to load from
	 */
	public function __construct($file)
	{
		$this->view = $file;
	}


	/**
	 * Convert special characters to HTML safe entities.
	 *
	 * @param string $string to encode
	 * @return string
	 */
	public function e($string)
	{
		return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
	}


	/**
	 * Convert dangerous HTML entities into special characters.
	 *
	 * @param string $s string to decode
	 * @return string
	 */
	public function d($string)
	{
		return htmlspecialchars_decode($string, ENT_QUOTES, 'UTF-8');
	}

	/**
	 * Set an array of values
	 *
	 * @param array $array of values
	 * @return this
	 */
	public function set($values)
	{
		foreach($values as $key => $value) $this->$key = $value;
		return $this;
	}


	/**
	 * Load the given view
	 *
	 * @param string $__f file name
	 * @return string
	 */
	public function load($__f)
	{
		ob_start();
		extract((array) $this);
		require self::$path.$__f.self::$ext;
		return ob_get_clean();
	}


	/**
	 * Allows setting view values while still returning the object instance.
	 * $view->title($title)->text($text);
	 *
	 * @return this
	 */
	public function __call($key, $args)
	{
		$this->$key = $args[0];
		return $this;
	}


	/**
	 * Return the view's HTML
	 *
	 * @return string
	 */
	public function __toString()
	{
		try {
			return $this->load($this->view);
		}
		catch(Exception $exception)
		{
			return '' . $exception;
		}
	}


	/**
	 * Compiles an array of HTML attributes into an attribute string and
	 * HTML escape it to prevent malformed (but not malicious) data.
	 *
	 * @param array $a the tag's attribute list
	 * @return string
	 */
	public static function attr($array)
	{
		$h = '';
		foreach((array)$array as $k => $v) $h .= " $k=\"$v\"";
		return $h;
	}


	/**
	 * Auto creates a form select dropdown from the options given .
	 *
	 * @param string $name the select element name
	 * @param array $options the select options
	 * @param mixed $selected the selected options(s)
	 * @param array $a of additional tag settings
	 * @return string
	 */
	public function select($name, $options, $selected = NULL, $attr = NULL)
	{
		$attr['name'] = $name;
		$h = '<select ' . self::attr($attr) .'>';
		foreach($options as $key => $value)
		{
			$attr = array('value' => $this->e($key));
			if(in_array($key, (array) $selected)) $attr['selected'] = 'selected';
			$h .= self::option($this->e($value), $attr);
		}

		return"$h</select>\n";
	}


	/**
	 * The magic call static method is triggered when invoking inaccessible
	 * methods in a static context. This allows us to create tags from method
	 * calls.
	 *
	 *     Html::div('This is div content.', array('id' => 'myDiv'));
	 *
	 * @param string $tag The method name being called
	 * @param array $args Parameters passed to the called method
	 * @return string
	 */
	public static function __callStatic($tag, $args)
	{
		$args[1] = isset($args[1]) ? self::attr($args[1]) : '';
		return "<$tag{$args[1]}>{$args[0]}</$tag>\n";
	}
}


/**
 * Adds template inheritance to standard view objects.
 * This is a powerful class!
 */
class Template extends View
{
	public $blocks, $append;

	/**
	 * Extend this parent view
	 *
	 * @param string $__f name of view
	 */
	public function extend($__f)
	{
		ob_end_clean(); // Ignore this child class and load the parent!
		print $this->load($__f);
		ob_start();
	}


	/**
	 * Start a new block
	 */
	public function start()
	{
		ob_start();
	}


	/**
	 * Empty default block to be extended by child templates
	 *
	 * @param string $name of block
	 */
	public function block($name)
	{
		if(isset($this->blocks[$name]))
		{
			print $this->blocks[$name];
		}
	}


	/**
	 * End a block
	 *
	 * @param string $name name of block
	 * @param mixed $filter functions
	 * @param boolean $keep_parent true to append parent block contents
	 */
	public function end($name, $filters = NULL, $keep_parent = FALSE)
	{
		$buffer = ob_get_clean();

		foreach((array) $filters as $filter)
		{
			$buffer = $filter($buffer);
		}

		// This block is already set
		if( ! isset($this->blocks[$name]))
		{
			$this->blocks[$name] = $buffer;
			if($keep_parent) $this->append[$name] = TRUE;
		}
		elseif(isset($this->append[$name]))
		{
			$this->blocks[$name] .= $buffer;
		}

		print $this->blocks[$name];
	}
}


/**
 * Session
 *
 * Stores session data in encrypted cookies to save database/memcached load.
 * Sessions stored in cookies must be under 4KB.
 */
class Session
{
	/**
	 * Start the session
	 *
	 * @param string $name of the session cookie
	 * @return mixed
	 */
	public static function start($name = 'session')
	{
		if( ! empty($_SESSION)) return $_SESSION = Cookie::get($name);
	}


	/**
	 * Called at end-of-page to save the current session data to the session cookie
	 *
	 * @param string $name of the session cookie
	 * return boolean
	 */
	public static function save($name = 'session')
	{
		return Cookie::set($name, $_SESSION);
	}


	/**
	 * Destroy the current users session
	 *
	 * @param string $name of the session cookie
	 */
	public static function destroy($name = 'session')
	{
		Cookie::set($name, '');
		unset($_COOKIE[$name], $_SESSION);
	}
}


/*
 * Common Functions
 */


/**
 * Fetch a config value from a module configuration file
 *
 * @param string $file name of the config
 * @param boolean $clear to clear the config object
 * @return object
 */
function config($file = 'Config', $clear = FALSE)
{
	static $configs = array();

	if($clear)
	{
		unset($configs[$file]);
		return;
	}

	if(empty($configs[$file]))
	{
		require(SP . 'Config/' . $file . EXT);
		$configs[$file] = (object) $config;
	}

	return $configs[$file];
}


/**
 * SplClassLoader implementation that implements the technical interoperability
 * standards for PHP 5.3 namespaces and class names.
 *
 * http://groups.google.com/group/php-standards/web/final-proposal
 *
 * @param string $class name
 */
function __autoload($className)
{
	// Each namespace might have a custom path
	$namespaces = config()->namespaces;

	$className = explode('/', str_replace('\\', '/', ltrim($className, '\\')));
	$fileName = str_replace('_', '/', array_pop($className));

	// Is there a namespace left?
	if($className)
	{
		$namespace = array_shift($className);

		if(isset($namespaces[$namespace]))
		{
			$namespace = $namespaces[$namespace];
		}

		array_unshift($className, $namespace);

		$fileName = join('/', $className) . '/' . $fileName;
	}
	else // Double-up the file name ('Micro' => 'Micro/Micro.php')
	{
		//$fileName .= '/' . $fileName;
	}

	require SP . 'Class/' . $fileName . EXT;
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
 * Write to the application log file using error_log
 *
 * @param string $message to save
 * @return bool
 */
function log_message($message)
{
	$path = SP . 'Storage/Log/' . date('Y-m-d') . '.log';

	// Append date and IP to log message
	return error_log(date('H:i:s ') . getenv('REMOTE_ADDR') . " $message\n", 3, $path);
}


/**
 * Send a HTTP header redirect using "location" or "refresh".
 *
 * @param string $url the URL string
 * @param int $c the HTTP status code
 * @param string $method either location or redirect
 */
function redirect($url = NULL, $code = 302, $method = 'location')
{
	if(strpos($url, '://') === FALSE)
	{
		$url = site_url($url);
	}

	header($method == 'refresh' ? "Refresh:0;url = $url" : "Location: $url", TRUE, $code);
}


/**
 * Return the full URL to a location on this site
 *
 * @param string $path to use or FALSE for current path
 * @param array $params to append to URL
 * @return string
 */
function site_url($path = NULL, array $params = NULL)
{
	// In PHP 5.4, http_build_query will support RFC 3986
	return DOMAIN . ($path ? '/'. trim($path, '/') : PATH)
		. ($params ? '?'. str_replace('+', '%20', http_build_query($params, TRUE, '&')) : '');
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


/**
 * Color output text for the CLI
 *
 * @param string $text to color
 * @param string $color of text
 * @param string $background color
 */
function colorize($text, $color, $bold = FALSE)
{
	// Standard CLI colors
	$colors = array_flip(array(30 => 'gray', 'red', 'green', 'yellow', 'blue', 'purple', 'cyan', 'white', 'black'));

	// Escape string with color information
	return"\033[" . ($bold ? '1' : '0') . ';' . $colors[$color] . "m$text\033[0m";
}


/**
 * Make a request to the given URL using cURL.
 *
 * @param string $url to request
 * @param array $options for cURL object
 * @return object
 */
function curl_request($url, array $options = NULL)
{
	$ch = curl_init($url);

	$defaults = array(
		CURLOPT_HEADER => 0,
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_TIMEOUT => 5,
	);

	// Connection options override defaults if given
	curl_setopt_array($ch, (array) $options + $defaults);

	// Create a response object
	$object = new stdClass;

	// Get additional request info
	$object->response = curl_exec($ch);
	$object->error_code = curl_errno($ch);
	$object->error = curl_error($ch);
	$object->info = curl_getinfo($ch);

	curl_close($ch);

	return $object;
}


/**
 * Create a RecursiveDirectoryIterator object
 *
 * @param string $dir the directory to load
 * @param boolean $recursive to include subfolders
 * @return object
 */
function directory($dir, $recursive = TRUE)
{
	$i = new \RecursiveDirectoryIterator($dir);

	if( ! $recursive) return $i;

	return new \RecursiveIteratorIterator($i, \RecursiveIteratorIterator::SELF_FIRST);
}


/**
 * Make sure that a directory exists and is writable by the current PHP process.
 *
 * @param string $dir the directory to load
 * @param string $chmod value as octal
 * @return boolean
 */
function directory_is_writable($dir, $chmod = 0755)
{
	// If it doesn't exist, and can't be made
	if(! is_dir($dir) AND ! mkdir($dir, $chmod, TRUE)) return FALSE;

	// If it isn't writable, and can't be made writable
	if(! is_writable($dir) AND !chmod($dir, $chmod)) return FALSE;

	return TRUE;
}

