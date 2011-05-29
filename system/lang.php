<?php
/**
 * Langauge
 *
 * Autoloads the correct language file based on cookie, useragent, and available
 * module languages . The entire language system is based on country codes in ISO
 * 3166-1 alpha-2 .
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2011 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
class Lang
{

	protected static $lang;


	/**
	 * Load a language file for the given module
	 *
	 * @param string $lang the language ISO
	 * @param string $m the module name
	 */
	static function load($language, $m = 'system')
	{
		require(SP . "$m/lang/$language" . EXT);
		self::$lang[$m] = $lang;
	}


	/**
	 * Get an array of all languages supported by useragent
	 *
	 * @return array
	 */
	static function accepted()
	{
		static $a;
		if($a)return $a;
		foreach(explode(',', server('HTTP_ACCEPT_LANGUAGE')) as $v)
		{
			$a[] = substr($v, 0, 2);
		}
		return $a;
	}


	/**
	 * Fetch a language key (loading the language file if needed)
	 *
	 * @param string $k the key name
	 * @param string $module the module name
	 * @return string
	 */
	static function get($k, $module = 'system')
	{
		if(empty(self::$lang[$module]))
		{
			self::load(self::choose($module), $module);
		}
		return self::$lang[$module][$k];
	}


	/**
	 * Figure out which language file to load for this module
	 *
	 * @param string $module the module name
	 * @return string
	 */
	static function choose($module = 'system')
	{
		$p = SP . $module . '/lang/';

		// Has the user choosen a custom language?
		if(!empty($_COOKIE['lang']) AND strlen($c = $_COOKIE['lang']) == 2 AND is_file($p . $c . EXT)) return $c;

		// Auto-detect the languages they want
		foreach(self::accepted() as $c)
		{
			if(is_file($p . $c . EXT)) return $c;
		}

		return config('language');
	}

}

// END
