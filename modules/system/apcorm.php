<?php
/**
 * APC ORM
 *
 * Provides ORM result caching using APC.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
class APCORM extends ORM
{

public static function cache_set($k,$v)
{
	apc_store($k,$v,static::$cache);
}


public static function cache_get($k)
{
	return apc_fetch($k);
}


public static function cache_delete($k)
{
	return apc_delete($k);
}


public static function cache_exists($k)
{
	return apc_exists($k);
}

}

// END