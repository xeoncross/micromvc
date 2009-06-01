<?php

class cache {

	//Life of cache files (can be overwritten)
	public $cache = CACHING;


	/**
	 * Fetch a cache file
	 *
	 * @param	string	Name of the cache File
	 * @param	int		max file life
	 * @param	boolean	return data or print it out?
	 * @return	void
	 */
	function fetch($file = '') {
	
		//If caching is disabled
		if(!$this->cache) { return; }
		
		//set the file path
		$path = SITE_DIR. 'cache/'. $file;
	
		//IF the file exists AND the cach life has not expired
		if(file_exists($path) && ((time() - filemtime($path)) < $this->cache)) {
		
			//return string containing the file contents
			return file_get_contents($path);
		
		}
	}


	/**
	 * Create Cache
	 *
	 * @param	string	Name of the cache File
	 * @param	string	String of contents to insert
	 * @return	void
	 */
	function create($file=null, $contents=null) {
	
		//If cacheing is not enabled - quit function
		if (!$this->cache) { return true; }
	
		//If one isn't set - return
		if(!$file || !$contents) { return; }
	
		//Set the file path
		$path = SITE_DIR. 'cache/'. $file;
	
		// Open for writing and place the file pointer at the beginning
		// of the file and truncate the file (if it doesn't exist try to make it)
		if (!$handle = fopen($path, 'w')) {
			return;
		}
	
		// Write $content to our opened file.
		if (fwrite($handle, $contents) === FALSE) {
			return;
		}
	
		//Close the file
		fclose($handle);
	
		return true;
	
	}


	/**
	 * Delete a Cache
	 * @return	boolean
	 */
	function delete($file=null) {
		if(!$file) { return; }
	
		//Delete the file+path
		if(unlink(SITE_DIR. 'cache/'. $file)) {
			return true;
		}
	
	}


	/**
	 * Delete ALL Caches
	 * @return	boolean
	 */
	function delete_caches() {
		//Destroy all files in the cache dir
		if(destroy_dir(SITE_DIR. 'cache/', false)) {
			return true;
		}
	}
}