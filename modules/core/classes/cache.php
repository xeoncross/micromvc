<?php defined('SYSTEM_PATH') or die('No direct access');
/**
 * Cache Class
 *
 * This class handles caching of objects using the filesystem.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
class cache {

	/**
	 * Fetch an item from the cache
	 *
	 * @param $id the id of the cache item
	 * @param $cache_life the optional life of the item
	 * @return mixed
	 */
	public static function get($id, $cache_life = NULL)
	{
		//If no cache life was given - use default
		if( $cache_life === NULL )
		{
			$cache_life = config::get('cache_life');
		}

		// If caching is disabled
		if( ! $cache_life )
			return FALSE;

		//The full cache path
		$file = CACHE_PATH. sha1($id). '.cache';

		//If the file exists AND the cach life has not expired
		if( file_exists($file) && (time() - filemtime($file)) < $cache_life )
		{
			// Fetch the file and unserialize it!
			return unserialize(file_get_contents($file));
		}

		return FALSE;
	}


	/**
	 * Store an item in the cache
	 *
	 * @param $id the id of the cache item
	 * @param $data the item to store
	 * @param $cache_life the optional life of the item
	 * @return boolean
	 */
	public static function set($id, $data, $cache_life = NULL)
	{
		//If no cache life was given - use default
		if( $cache_life === NULL )
		{
			$cache_life = config::get('cache_life');
		}

		// If caching is disabled
		if( ! $cache_life )
		{
			return FALSE;
		}

		// Serialize data
		$data = serialize($data);

		// Open for writing and place the file pointer at the beginning
		// of the file and truncate the file (if it doesn't exist try to make it)
		if ( ! $handle = fopen(CACHE_PATH. sha1($id). '.cache', 'w') )
		{
			return FALSE;
		}

		// Write $content to our opened file.
		if ( fwrite($handle, $data) === FALSE )
		{
			return FALSE;
		}

		//Close the file
		fclose($handle);

		return TRUE;
	}

	
	/**
	 * Fetch an item's age
	 * @param string $id of the cache
	 * @return int|bool
	 */
	public static function age($id)
	{
		//The full cache path
		$file = CACHE_PATH. sha1($id). '.cache';
		
		return file_exists($file) ? (time() - filemtime($file)) : NULL;
	}


	/**
	 * Check for the existance of a cache file
	 * @param string $id
	 * @return bool
	 */
	public static function exists($id)
	{
		return (bool) file_exists(CACHE_PATH. sha1($id). '.cache');
	}
	
	
	/**
	 * Delete an item from the cache
	 * @return boolean
	 */
	public static function delete($id)
	{
		//Delete the file+path
		if(unlink(CACHE_PATH. sha1($id). '.cache')) {
			return TRUE;
		}
	}


	/**
	 * Flush all existing caches
	 * @return	boolean
	 */
	public static function delete_all()
	{
		if(destroy_dir(CACHE_PATH, FALSE))
		{
			return TRUE;
		}
	}

}