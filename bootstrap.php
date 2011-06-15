<?php
/**
 * Core Bootstrap
 *
 * This file contains all common system functions and View and Controller classes.
 *
 * @packageMicroMVC
 * @authorDavid Pennington
 * @copyright(c) 2010 MicroMVC Framework
 * @licensehttp://micromvc.com/license
 ********************************** 80 Columns *********************************
 */

/**
 * Record memory usage and timestamp and then return difference next run (and restart)
 *
 * @return array
 */
function benchmark()
{
	static$t,$m;$a=array((microtime(true)-$t),(memory_get_usage()-$m));$t=microtime(true);$m=memory_get_usage();return$a;
}


/**
 * System registry object for storing global values
 *
 * @param string $k the object name
 * @param mixed $v the object value
 * @return mixed
 */
function registry($k,$v=null)
{
	static$o;return(func_num_args()>1?$o[$k]=$v:(isset($o[$k])?$o[$k]:NULL));
}


/**
 * Set a message to show to the user (error, warning, success, or message).
 *
 * @param string $type of message
 * @param string $v the message to store
 */
function message($type = NULL, $v = NULL)
{
	static$m=array();$h='';if($v)$m[$type][]=$v;elseif($type){if(isset($m[$type]))foreach($m[$type] as$v)$h.="<div class=\"$type\">$v</div>";}else foreach($m as$t=>$d)foreach($d as$v)$h.="<div class=\"$t\">$v</div>";return$h;
}


/**
 * Attach (or remove) multiple callbacks to an event and trigger those callbacks when that event is called.
 *
 * @param string $k the name of the event to run
 * @param mixed $v the optional value to pass to each callback
 * @param mixed $callback the method or function to call - FALSE to remove all callbacks for event
 */
function event($k, $v = NULL, $callback = NULL)
{
	static$e;if($callback!==NULL)if($callback)$e[$k][]=$callback;else unset($e[$k]);elseif(isset($e[$k]))foreach($e[$k]as$f)$v=call_user_func($f,$v);return$v;
}


/**
 * Fetch a config value
 *
 * @param string $k the language key name
 * @param string $m the module name
 * @return string
 */
function config($k,$m='system')
{
	static $c;$c[$m]=empty($c[$m])?require(SP.($m!='system'?"$m/":'').'config'.EXT):$c[$m];return($k?$c[$m][$k]:$c[$m]);
}


/**
 * Fetch the language text for the given line.
 *
 * @param string $k the language key name
 * @param string $m the module name
 * @return string
 */
function lang($k,$m='system')
{
	return lang::get($k,$m);
}


/**
 * Returns the current URL path string (if valid)
 * PHP before 5.3.3 throws E_WARNING for bad uri in parse_url()
 *
 * @param int $k the key of URL segment to return
 * @param mixed $d the default if the segment isn't found
 * @return string
 */
function url($k = NULL, $d = NULL)
{
	static$s;if(!$s){foreach(array('REQUEST_URI','PATH_INFO','ORIG_PATH_INFO')as$v){preg_match('/^\/[\w\-~\/\.+%]{1,600}/',server($v),$p);if(!empty($p)){$s=explode('/',trim($p[0],'/'));break;}}}if($s)return($k!==NULL?(isset($s[$k])?$s[$k]:$d):implode('/',$s));
}


/**
 * Automatically load the given class
 *
 * @param string $class name
 */
function __autoload($class)
{
	require(SP.mb_strtolower((strpos($class,'_')===FALSE?'system/':'').str_replace('_','/',$class.EXT)));
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
	$s='';foreach(func_get_args()as$v){$s.='<pre>'.h($v===NULL?'NULL':(is_scalar($v)?$v:print_r($v,1)))."</pre>\n";}return$s;
}


/**
 * Safely get a value or return default if not set
 *
 * @param mixed $v value to get
 * @param mixed $d default if value is not set
 * @return mixed
 */
function v(&$v, $d = NULL)
{
	return isset($v)?$v:$d;
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
function post($k, $d = NULL, $s = FALSE)
{
	if(isset($_POST[$k]))return$s?str($_POST[$k],$d):$_POST[$k];return$d;
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
function get($k, $d = NULL, $s = FALSE)
{
	if(isset($_GET[$k]))return$s?str($_GET[$k],$d):$_GET[$k];return$d;
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
	return isset($_SERVER[$k])?$_SERVER[$k]:$d;
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
	return isset($_SESSION[$k])?$_SESSION[$k]:$d;
}


/**
 * Create a random 32 character MD5 token
 *
 * @return string
 */
function token()
{
	return md5(str_shuffle(chr(mt_rand(32, 126)).uniqid().microtime(TRUE)));
}


/**
 * Write to the log file
 *
 * @param string $m the message to save
 * @return bool
 */
function log_message($m)
{
	if(!$fp=@fopen(SP.config('log_path').date('Y-m-d').'.log','a'))return 0;$m=date('H:i:s ').h(server('REMOTE_ADDR'))." $m\n";flock($fp,LOCK_EX);fwrite($fp,$m);flock($fp,LOCK_UN);fclose($fp);return 1;
}


/**
 * Send a HTTP header redirect using "location" or "refresh".
 *
 * @param string $uri the URI string
 * @param int $c the HTTP status code
 * @param string $method either location or redirect
 */
function redirect($u='',$c=302,$m='location')
{
	$u=site_url($u);header($m=='refresh'?"Refresh:0;url=$u":"Location: $u",TRUE,$c);
}


/**
 * Type cast a scalar variable into an a valid integer between the given min/max values.
 * If the value is not a valid numeric value then min will be returned.
 *
 * @param int $int the value to convert
 * @param int $default the default value to assign
 * @param int $min the lowest value allowed
 * @return int|null
 */
function int($int, $min = NULL, $max = NULL)
{
	$i=is_numeric($int)?(int)$int:$min;if($min!==NULL&&$i<$min)$i=$min;if($max!==NULL&&$i>$max)$i=$max;return$i;
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
	return(is_scalar($str)?(string)$str:$default);
}


/**
 * Return the full URL to a path on this site or another.
 *
 * @param string $uri may contain another sites TLD
 * @return string
 */
function site_url($uri = NULL)
{
	return (strpos($uri,'://')===FALSE?DOMAIN.'/':'').$uri;
}


/**
 * Return the full URL to the theme folder
 *
 * @param string $uri
 * @return string
 */
function theme_url($uri = NULL)
{
	return site_url(config('theme').'/'. $uri);
}


/**
 * Return the full URL to a module view file
 *
 * @param string $uri
 * @return string
 *
function module_url($uri, $module)
{
	return site_url("$module/view/$uri");
}
*/

/**
 * Convert a string from one encoding to another encoding
 * and remove invalid bytes sequences.
 *
 * @param string $string to convert
 * @param string $to encoding you want the string in
 * @param string $from encoding that string is in
 * @return string
 */
function encode($string,$to='UTF-8',$from='UTF-8')
{
	return$to==='UTF-8'&&is_ascii($string)?$string:@iconv($from,$to.'//TRANSLIT//IGNORE',$string);
}


/**
 * Tests whether a string contains only 7bit ASCII characters.
 *
 * @param string $string to check
 * @return bool
 */
function is_ascii($string)
{
	return!preg_match('/[^\x00-\x7F]/S',$string);
}


/**
 * Encode a string so it is safe to pass through the URI
 *
 * @param string $string to encode
 * @return string
 */
function base64_url_encode($string = NULL)
{
	return strtr(base64_encode($string),'+/=','-_~');
}


/**
 * Decode a string passed through the URI
 *
 * @param string $string to encode
 * @return string
 */
function base64_url_decode($string = NULL)
{
	return base64_decode(strtr($string,'-_~','+/='));
}


/**
 * Convert special characters to HTML safe entities.
 *
 * @param string $str the string to encode
 * @return string
 */
function h($data)
{
	return htmlspecialchars($data,ENT_QUOTES,'utf-8');
}


/**
 * Checks that the given IP address is not a bad bot listed in the Http:BL
 * 
 * @see http://www.projecthoneypot.org/
 * @param string $ip address (IP4 only!)
 * @param string $key Http:BL API key
 * @param integer $threat_level bettween 0 and 255
 * @param integer $max_age number of days since last activity
 */
function bad_bot($ip, $key, $threat_level = 20, $max_age = 30)
{
	if($ip=='127.0.0.1')return;$ip=implode('.',array_reverse(explode('.',$ip)));if($ip=gethostbyname("$key.$ip.dnsbl.httpbl.org")){$ip=explode('.',$ip);return$ip[0]==127&&$ip[3]&&$ip[2]>=$threat_level&&$ip[1]<=$max_age;}
}


/** USE TIME CLASS
 * Return a SQLite/MySQL/PostgreSQL datetime string
 * 
 * @param int $t timestamp
 *
function sql_date($t = NULL)
{
	return date('Y-m-d H:i:s',$t?$t:time());
}
*/

/*
 * Core Classes
 */


/**
 * Controller Class
 */
class Controller
{

public $template = 'layout';

/**
 * Override PHP's default error handling if in debug mode
 */
public function __construct()
{
	if(config('debug_mode')){set_error_handler(array('error','handler'));register_shutdown_function(array('error','fatal'));set_exception_handler(array('error','exception'));}
}

/**
 * Show a 404 error page
 */
public function show_404()
{
	headers_sent()||header('HTTP/1.0 404 Page Not Found');$this->content=new View('404');
}

/**
 * Render the final layout template
 */
public function render()
{
	headers_sent()||header('Content-Type: text/html; charset=utf-8');$l=new View($this->template);$l->set((array)$this);print$l;$l=0;if(config('debug_mode'))print new View('debug','system');
}

}


/**
 * View Class
 * @author http://github.com/tweetmvc/tweetmvc-app
 */
class View
{

/**
 * Returns a new view object for the given view.
 *
 * @param string $f the view file to load
 * @param string $m the module name (blank for current theme)
 */
public function __construct($f, $m = NULL)
{
	$this->__f=SP.($m?$m:config('theme'))."/view/".$f.EXT;
}

/**
 * Set an array of values
 *
 * @param array $a array of values
 */
public function set($a)
{
	foreach($a as$k=>$v)$this->$k=$v;
}

/**
 * Return the view's HTML
 * @return string
 */
public function __toString()
{
	ob_start();extract((array)$this);require$__f;return ob_get_clean();
}

}

// END