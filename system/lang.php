<?php
/**
 * Langauge
 *
 * Autoloads the correct language file based on cookie, useragent, and available 
 * module languages. The entire language system is based on country codes in ISO 
 * 3166-1 alpha-2.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
class Lang
{

protected static $l;

/**
 * Load a language file for the given module
 *
 * @param string $l the language ISO
 * @param string $m the module name
 */
static function load($l,$m='system')
{
	self::$l[$m]=require(SP."$m/lang/$l".EXT);
}


/**
 * Get an array of all languages supported by useragent
 *
 * @return array
 */
static function accepted()
{
	static $a;if($a)return$a;foreach(explode(',',server('HTTP_ACCEPT_LANGUAGE'))as$v){$a[]=substr($v,0,2);};return $a;
}


/**
 * Fetch a language key (loading the language file if needed)
 *
 * @param string $k the key name
 * @param string $m the module name
 * @return string
 */
static function get($k, $m='system')
{
	isset(self::$l[$m]) OR self::load(self::choose($m),$m);return self::$l[$m][$k];
}


/**
 * Figure out which language file to load for this module
 *
 * @param string $m the module name
 * @return string
 */
static function choose($m='system')
{
	$p=SP.$m.'/lang/';if(!empty($_COOKIE['lang'])&&strlen($c=$_COOKIE['lang'])==2&&is_file($p.$c.EXT))return$c;foreach(self::accepted()as$c)if(is_file($p.$c.EXT))return$c;return config('language');
}

}

// END