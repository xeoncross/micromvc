<?php defined('SYSTEM_PATH') or die('No direct access');
/**
 * Load, Config, Lang, & Hook Classes
 *
 * These are the basic system classes need on every page. Do not change.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */

/**
 * Load Class
 *
 * Loads classes and keeps track of object instances to achieve singleton design
 */
class load {

	// Array of files and their locations
	public static $files = array();

	// Have any new files been loaded this request?
	public static $changed = FALSE;

	// Array of object instances
	public static $objects = array();

	// The locations to look for files
	public static $paths = array(SITE_PATH);

	/**
	 * On startup, set the modules loaded and load the files cache if enabled.
	 * 
	 * @param $modules the array of modules
	 */
	public static function init(array $modules)
	{
		// If we are also loading files from modules
		if($modules)
		{
			foreach($modules as $module)
			{
				self::$paths[] = SYSTEM_PATH. 'modules'. DS. $module;
			}
		}

		if($data = cache::get('load::init'))
		{
			// If the paths have NOT changed since last load
			if($data[1] == self::$paths)
			{
				self::$files = $data[0];
			}
		}
	}


	/**
	 * Auto-called when a class needs to be loaded
	 * 
	 * @param string $class the name of the class to load
	 * @return boolean
	 */
	public static function autoload($class)
	{
		$file = strtolower(str_replace('_', DS, $class));

		// If this class is found
		if($path = self::find_file($file))
		{
			return require_once($path);
		}
	}


	/**
	 * Returns the full system path of the file requested
	 *
	 * @param $file
	 * @param $dir
	 * @param $ext
	 * @return	string
	 */
	public static function find_file($file, $dir = 'classes', $ext = 'php')
	{

		$file .= '.'.$ext;

		// If we already know where this file is
		if(isset(self::$files[$dir.$file]))
		{
			return self::$files[$dir.$file];
		}

		// Look under each path
		foreach(self::$paths as $path)
		{
			// Build the path
			$path .= DS. $dir. DS;

			// Look for this file
			if(is_file($path. $file))
			{
				self::$changed = TRUE;
				return self::$files[$dir.$file] = $path. $file;
			}
		}

		return FALSE;
	}


	/**
	 * Load a class, create and store an instance of the object, and return the object.
	 *
	 * @param $class the name of the class to load (may also contain paths)
	 * @param $path the absolute path to load the class from
	 * @param $params the arguments to give to the object constructor
	 * @param $instantiate if false then only load file and don't create object
	 * @return object
	 */
	public static function singleton($class = NULL, $params = NULL, $instantiate = TRUE)
	{
		// Class case doesn't matter
		$class = strtolower($class);

		// If this class is already loaded
		if( ! empty(self::$objects[$class]))
		{
			return self::$objects[$class];
		}

		// If we just want to load the file - nothing more
		if ($instantiate === FALSE)
		{
			self::autoload($class);
			return TRUE;
		}

		return self::$objects[$class] = new $class($params);
	}


	/**
	 * Remove a loaded object by class name
	 * 
	 * @param string $class the name of the class
	 */
	public static function remove($class = NULL)
	{
		// Remove object (case doesn't matter)
		unset(self::$objects[strtolower($class)]);
	}
	
	
	/**
	 * Load a helper function file
	 *
	 * @param $name the name of the function file to load
	 * @return boolean
	 */
	public static function helper($name = NULL)
	{
		// Look for the helper file
		$path = self::find_file($name, 'helpers');

		// Try to load the file
		return require_once($path);
	}


	/**
	 * Load an error page
	 *
	 * @param string|array $message error message OR array of view data
	 * @param string $title the optional title
	 * @param int $header the HTTP status code to send
	 * @return string
	 */
	public static function error($message = NULL, $title = NULL, $header = 500)
	{
		// Get HTTP status
		if( ! $status = http_response_status( (int) $header))
		{
			// If failure - default to "Internal Server Error"
			$status = http_response_status($header = 500);
		}

		// Try to send an error header
		if( ! headers_sent())
		{
			// Send the header
			header('HTTP/1.0 '. $header. ' '. $status);
		}

		if( ! is_array($message))
		{
			// If a message is not given - try to find a matching HTTP status message
			$message = ($message ? $message : lang($header));
			
			// If a title is not given - use the HTTP status message
			$title = ($title ? $title : $status);
	
			// Build view data
			$message = array('message' => $message, 'title' => $title);
		}
		
		// Load error page
		return self::view('errors/error', $message);
	}


	/**
	 * This function is used to load views files.
	 *
	 * @access	private
	 * @param	String	file path/name
	 * @param	array	values to pass to the view
	 * @param	boolean	return the output or print it?
	 * @return	void
	 */
	public static function view($__file = NULL, $__variables = NULL, $__return = TRUE)
	{
		// Fetch the path to the view file
		if( ! $__path = self::find_file($__file, 'views'))
		{
			throw new Exception($__file. ' view not found');
		}

		if(is_array($__variables))
		{
			// Make each value passed to this view available for use
			foreach($__variables as $__key => $__variable)
			{
				$$__key = $__variable;
			}
		}

		// Delete them now
		$__variables = null;

		// We just want to print to the screen
		if( ! $__return)
		{
			return include($__path);
		}

		// Buffer the output so we can save it to a string
		ob_start();

		// include() vs include_once() allows for multiple views with the same name
		include($__path);

		// Get the output
		$__buffer = ob_get_contents();
		ob_end_clean();
		
		return $__buffer;
	}


	/**
	 * Assigns a copy of all the objects loaded into the object given
	 *
	 * @param $object the object to load a copy of the objects into
	 */
	public static function objects_into($object = NULL)
	{
		foreach(self::$objects as $class => $obj)
		{
			if(isset($object->$class)) { continue; }
			$object->$class = $obj;
		}
	}

	
	/**
	 * Record the updated files array if caching is enabled.
	 */
	public static function shutdown()
	{
		if(self::$changed)
		{
			cache::set('load::init', array(self::$files, self::$paths));
		}
	}

}



/**
 * Config Class
 *
 * Loads and manages config settings for the current page request.
 */
class config {

	public static $config = array();

	/**
	 * Get the value of a config group key, loading it if needed.
	 *
	 * @param string $key the name of the config value to fetch
	 * @param string $group the name of the group to find the $key in
	 * @return mixed
	 */
	public static function get($key = NULL, $group = SITE_MODE)
	{
		// Load the config if needed
		if( empty(self::$config[$group]))
		{
			self::load($group);
		}

		// Return the key or the whole group config
		return ( $key === NULL ? self::$config[$group] : self::$config[$group][$key] );
	}


	/**
	 * Set the value of a config group key, loading it if needed
	 *
	 * @param string $key the name of the config value to fetch
	 * @param string $group the name of the group to find the $key in
	 * @param mixed $value the new value to set the $key to
	 */
	public static function set($key = NULL, $group = SITE_MODE, $value = NULL)
	{

		// Load the config if needed
		if( empty(self::$config[$group]))
		{
			self::load($group);
		}

		// Set the config key to the new value
		if($key !== NULL)
		{
			self::$config[$group][$key] = $value;
		}
		else
		{
			self::$config[$group] = $value;
		}

	}


	/**
	 * Load and store a config file
	 *
	 * @param string $group the name of the config file
	 * @param bool $overwrite if true, overwrite current values if set
	 */
	public static function load($group, $overwrite = FALSE)
	{

		// If the config is already loaded
		if( ! $overwrite AND isset(self::$config[$group]))
		{
			return;
		}

		if( ! $path = load::find_file($group, 'config'))
		{
			throw new Exception($group. ' config file not found');
		}

		// Include the config
		require($path);

		// Save the config
		self::$config[$group] = $config;

	}

	
	/**
	 * Remove the loaded config for a given group.
	 * This helps to free memory.
	 * 
	 * @param string $group the config group to remove
	 */
	public static function clear($group = NULL)
	{
		unset(self::$config[$group]);
	}
}



/**
 * Lang Class
 *
 * Loads and manages language strings for the current page request.
 */
class lang {

	public static $lang = array();
	public static $language = FALSE;

	/**
	 * Get the value of a lang group key, loading it if needed.
	 *
	 * @param string $line the name of the lang value to fetch
	 * @param string $group the name of the group to find the $line in
	 * @return mixed
	 */
	public static function get($line = NULL, $group = 'system')
	{

		// Load the lang if needed
		if(empty(self::$lang[$group]))
		{
			self::load($group);
		}

		// Return the key or the whole group lang
		return ($line === NULL ? self::$lang[$group] : self::$lang[$group][$line]);
	}


	/**
	 * Set the value of a lang group key, loading it if needed
	 *
	 * @param string $line the name of the lang value to fetch
	 * @param string $group the name of the group to find the $line in
	 * @param mixed $value the new value to set the $line to
	 */
	public static function set($line = NULL, $group = 'system', $value = NULL)
	{
		// Load the lang if needed
		if( empty(self::$lang[$group]))
		{
			self::load($group);
		}

		// Set the lang key to the new value
		if( $line !== NULL )
		{
			self::$lang[$group][$line] = $value;
		}
		else
		{
			self::$lang[$group] = $value;
		}
	}


	/**
	 * Load and store a lang file
	 *
	 * @param string $group the name of the lang file
	 * @param bool $overwrite if true, overwrite current values if set
	 */
	public static function load($group = 'system', $overwrite = FALSE)
	{

		// If the lang is already loaded
		if( ! $overwrite AND isset(self::$lang[$group]) )
		{
			return;
		}

		if( ! $path = load::find_file(config::get('language').'/'.$group, 'lang'))
		{
			throw new Exception($group.' language file not found');
		}

		// Include the lang
		require($path);

		// Save the lang
		self::$lang[$group] = $lang;

	}
	
	
	/**
	 * Remove the loaded lang for a given group.
	 * This helps to free memory.
	 * 
	 * @param string $group the lang group to remove
	 */
	public static function clear($group = NULL)
	{
		unset(self::$lang[$group]);
	}
}



/*
 * Hook Class
 *
 * Provides plugin points for classes and functions to run and/or filter data.
 */
class hook {

	// Keeps us out of trouble with repeating hook loops
	public static $hook_in_progress = FALSE;
	// Array of hooks
	public static $hooks = array();

	/**
	 * Calls a particular hook allowing data to be
	 * filtered by the hook(s) and the result returned.
	 *
	 * @param string $name the hook name
	 * @param mixed $data the data to be parsed
	 * @return mixed
	 */
	public static function call($name = '', $data = NULL)
	{

		// If no hook is given OR found with that name
		if ( ! $name OR empty(self::$hooks[$name]))
		{
			return $data;
		}

		// Run each hook and filter the data
		foreach (self::$hooks[$name] as $val)
		{
			$data = self::run_hook($val, $data);
		}

		return $data;
	}


	/**
	 * Verify whether a hook exists - returning the array index if found
	 *
	 * @param string $name the hook name
	 * @param string $hook the function to look for
	 * @return bool
	 */
	public static function exists($name = '', $hook = NULL)
	{

		// If invaild data was given - or a hook by this name is not found
		if( ! $name OR ! is_array($hook) OR empty(self::$hooks[$name]))
		{
			return FALSE;
		}

		// Return array index or FALSE
		return array_search($hook, self::$hooks[$name]);
	}


	/**
	 * Remove the given function (or all) from the given hook trigger
	 * and return TRUE if that hook was found or FALSE if not
	 *
	 * @param string $name the hook name
	 * @param string $hook the function to remove
	 * @return boolean
	 */
	public function remove($name = '', $hook = NULL)
	{

		// If we are to remove all hooks for this trigger
		if( ! $function)
		{
			self::$hooks[$name] = array();
			return TRUE;
		}

		// Find and remove this particular hook
		if($key = self::exists($name, $hook))
		{
			unset(self::$hooks[$name][$key]);
			return TRUE;
		}

		return FALSE;
	}


	/**
	 * Add a hook to the list for the given hook trigger
	 *
	 * @param string $name the hook name
	 * @param array $hook the new hook array
	 * @return boolean
	 */
	public static function add($name = '', $hook = NULL)
	{
		// If invaild data was given
		if( ! $name OR ! is_array($hook))
			return FALSE;

		// If this hook already has been added
		if( self::exists($name, $hook))
			return TRUE;

		// Add the hook
		self::$hooks[$name][] = $hook;

		return TRUE;
	}


	/**
	 * Runs the given hook
	 *
	 * @param array $hook the hook details
	 * @param mixed $data the optional data to be filtered
	 * @return bool
	 */
	public static function run_hook($hook = NULL, $data = NULL)
	{

		// If it is NOT a hook config
		if ( ! is_array($hook) OR empty($hook))
			return $data;

		/*
		 * Safety - Prevents run-away loops
		 *
		 * If the script being called happens to have the same
		 * hook call within it a loop can happen.
		 */
		if (self::$hook_in_progress == TRUE)
			return $data;

		// Set each value to avoid php notices
		foreach(array('class', 'function', 'helper', 'static') as $type)
		{
			$$type = empty($hook[$type]) ? NULL : $hook[$type];
		}

		// If we are missing a lot of stuff...
		if ( ! $class AND ! $function)
		{
			trigger_error(lang::get('hooks_no_function'));
			return $data;
		}

		// If a function is being called we HAVE TO HAVE a file name!
		if ( ! $class AND ( ! $helper AND $function))
		{
			trigger_error(sprintf(lang::get('hooks_no_file'), $function));
			return $data;
		}

		// Set the in_progress flag
		self::$hook_in_progress = TRUE;

		// Call the class method
		if($class)
		{
				
			// If we should call the object statically
			if($static)
			{
				$data = call_user_func(array($class, $function), $data);
			}
			else
			{
				// If not already an object (i.e. not added at runtime)
				if( ! is_object($class))
				{
					$class = load::singleton($class);
				}

				// Run the function
				$data = $class->$function($data);
			}
				
		}
		else //Else just run the function
		{

			// If the function does not alreay exist
			if ( ! function_exists($function))
			{
				load::helper($helper);
			}

			$data = $function($data);
		}

		self::$hook_in_progress = FALSE;
		return $data;
	}

}

