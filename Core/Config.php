<?php
/**
 * Config
 *
 * Load a config file
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2011 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
namespace Core;

class Config implements \Iterator
{
	public $array;

	public function __construct($file, $module = 'App')
	{
		require(SP . $module . '/' . $file . EXT);
		$this->array = $config;
	}

	public function __get($key)
	{
		return $this->array[$key];
	}

	public function __isset($key)
	{
		return isset($this->array[$key]);
	}

	function rewind()
	{
		reset($this->array);
    }

    function current()
    {
		return current($this->array);
    }

    function key()
    {
		return key($this->array);
    }

    function next()
    {
		return next($this->array);
    }

    function valid()
    {
		return isset($this->array[key($this->array)]);
		//return TRUE;
    }

}
