<?php
/**
 * Dir
 *
 * Provides basic directory functions such as recursion and creation.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
class dir
{

/**
 * Create a recursive directory iterator object
 *
 * @param string $d the directory to load
 * @param boolean $r if recursive is TRUE then subfolders will be included.
 * @return object
 */
static function load($d,$r=FALSE)
{
	$i = new RecursiveDirectoryIterator($d);return ($r?new RecursiveIteratorIterator($i,RecursiveIteratorIterator::SELF_FIRST):$i);
}


/**
 * Create an array of all (or just one of) file/folders/link objects in a directory.
 *
 * @param string $d the directory to load
 * @param boolean $r if recursive is TRUE then subfolders will be included.
 * @param string $only set to one of "file", "dir", or "link" to filter results
 * @return array
 */
static function contents($d, $r = FALSE, $only = FALSE)
{
	$d=self::load($d,$r);if(!$only)return $d;$only='is'.$only;$r=array();foreach($d as$f)if($f->$only())$r[]=$f;return$r;
}


/**
 * Make sure that a directory exists and is writable by the current PHP process.
 *
 * @param string $d the directory to load
 * @param string $chmod
 * @return boolean
 */
static function usable($d, $chmod = '0744')
{
	if(!is_dir($d)&&!mkdir($d,$chmod,TRUE))return FALSE;if(!is_writable($d)&&!chmod($d,$chmod))return FALSE;return TRUE;
}

}

// END