<?php
/**
 * Core
 *
 * This is the base class for all controllers.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	Copyright (c) 2009 MicroMVC
 * @license		http://www.gnu.org/licenses/gpl-3.0.html
 * @link		http://micromvc.com
 * @version		1.0.0 <2/20/2009>
 ********************************** 80 Columns *********************************
 */
class core {

	//Data for final site Layout
	public $data = null;
	//Name of final site layout file
	public $layout = 'layout';
	//Site Config
	public $config = array();
	//keeps us out of trouble with hook loops
	public $hook_in_progress = false;
	//Database object
	public $db = null;
	//Singleton instance object
	private static $instance;
	//Store cache setting in var so it can be overwritten
	public $caching = CACHING;

	/**
	 * Load the config values for this system
	 *
	 * @param array $config
	 */
	public function __construct($config=null) {

		//Set singleton instance
		self::$instance =& $this;

		//Set the core site config
		$this->config['site'] = $config;

		//load other config files
		foreach(array('hooks') as $name) {

			//Load it
			$this->load_config($name);

		}

	}


	/**
	 * Load a config file
	 * @param array $config
	 */
	public function load_config($name=null) {

		//Should we only allow a load once or can users re-load configs?

		//Only load once
		if(!empty($this->config[$name])) { return; }

		//Path to the config file
		$path = SITE_DIR. 'sites/'. SITE_NAME. '/'. $name. '.php';

		//Check to see if it exists
		if(file_exists($path)) {

			//include the config
			require($path);

			//Set the values in our object
			//Overwrite if we have too ;D
			$this->config[$name] = $$name;

		} else {
			trigger_error($name. ' config does not exist!');
		}

	}

	/**
	 * Load and initialize the database connection
	 * @param array $config
	 */
	public function load_database() {

		//Don't load the DB object twice!!!
		if(!empty($this->db)) { return; }

		//Load the database config
		$this->load_config('database');

		//If the db class isn't already loaded - load it
		if (!class_exists('mvcpdo')) {
			require_once(SITE_DIR. 'database/mvcpdo.php');
		}

		//Create a new instance of the database
		$this->db = db::instance($this->config['database']);

	}


	/**
	 * Load a file containing helper functions
	 * @param string $file
	 */
	public function load_function($file=null) {
		require_once(SITE_DIR. 'functions/'. $file. '.php');
	}



	/**
	 * Loads and instantiates models, libraries, and other classes
	 *
	 * @param	string	the name of the class
	 * @param	string	name for the class
	 * @param	array	params to pass to the model constructor
	 * @param	string	folder name of the class
	 * @return	void
	 */
	public function load($class=null, $name=null, $params=null, $path='libraries') {

		//If a model is NOT given
		if (!$class) { return; }

		//If a name is not given
		if(!$name) { $name = $class; }

		//If a model matches a variable - or it was already loaded
		if (isset($this->$name)) {
			return true;
		}

		//If the model file doesn't exist
		if (!file_exists(SITE_DIR. $path. '/'. $class. '.php')){
			trigger_error('Unable to locate the class "<b>'. $class. '</b>".');
			return;
		}

		//If the class isn't already loaded - load it
		if (!class_exists($class)) {
			require_once(SITE_DIR. $path. '/'. $class. '.php');
		}

		//Ok - lets create the object!
		$this->$name = new $class(($params ? $params : ''));

		/*
		 * Make everything available to the object that was just created
		 * @author	http://CodeIgniter.com
		 */

		foreach (array_keys(get_object_vars($this)) as $key) {

			//If a propery by this name doesn't already exist
			//And it is not this classe
			if (!isset($this->$name->$key) AND $key != $name) {
					
				// In some cases using references can cause
				// problems so we'll conditionally use them
				// If the magic __get() or __set() methods are used
				// in a Model references can't be used.
				if ((!method_exists($this->$name, '__get') && !method_exists($this->$name, '__set'))) {

					// Needed to prevent reference errors with some configurations
					$this->$name->$key = '';
					$this->$name->$key =& $this->$key;

				} else {
					$this->$name->$key = $this->$key;
				}

			}
		}

		return true;
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
	public function view($__file=null, $__variables=null, $__return=true) {

		//If no file is given - just return false
		if(!$__file) { return; }

		if(is_array($__variables)) {
			//Make each value passed to this view available for use
			foreach($__variables as $key => $variable) {
				$$key = $variable;
			}
		}

		//Delete them now
		$__variables = null;

		if (!file_exists(THEME_DIR. $__file. '.php')) {
			trigger_error('Unable to load the requested file: <b>'. $__file. '.php</b>');
			return;
		}

		/*
		 * Buffer the output so we can return it
		 */
		if($__return) {
			ob_start();

			// include() vs include_once() allows for multiple views with the same name
			include(THEME_DIR. $__file. '.php');

			//Get the output
			$buffer = ob_get_contents();
			@ob_end_clean();

			//Return the view
			return $buffer;

			//Else we just want to output to the screen
		} else {
			include(THEME_DIR. $__file. '.php');
		}

	}

	/**
	 * Show a 400-500 Header error within the site theme
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	public function request_error($type='404') {

		//Clean the type of error from XSS stuff
		$type = preg_replace('/[^a-z0-9]+/i', '', $type);

		if ($type == '400') {
			header("HTTP/1.0 400 Bad Request");
		} elseif ($type == '401') {
			header("HTTP/1.0 401 Unauthorized");
		} elseif ($type == '403') {
			header("HTTP/1.0 403 Forbidden");
		} elseif ($type == '500') {
			header("HTTP/1.0 500 Internal Server Error");
		} else {
			$type = '404';
			header("HTTP/1.0 404 Not Found");
		}

		$this->data['content'] = $this->view('errors/'. $type);

	}



	/**
	 * On close, show the output inside our layout template
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	public function render() {

		//If the user has NOT overriden this value
		if($this->layout == 'layout') {
			//Check to see if it is an ajax request
			if(AJAX_REQUEST) {
				$this->layout = 'ajax';
			}
		}

		// Load the template
		$output = $this->view($this->layout, $this->data);
		
		//If we are to use caching
		if($this->caching) {
			// Cache the file
			create_cache(md5(PAGE_NAME. AJAX_REQUEST), $output);
		}
		
		// Show the output
		print $output;

	}








	/*************************** HOOK MANAGEMENT ********************/


	/**
	 * Call Hook
	 *
	 * Calls a particular hook(s). Also, alows data to be
	 * filtered by a hook(s) and the result returned.
	 *
	 * @access	public
	 * @param	string	the hook name
	 * @param	mixed	Data to be parsed
	 * @return	mixed
	 */
	public function call_hook($name='', $data=null) {

		//If no hook is given OR found with that name
		if (!$name || !isset($this->config['hooks'][$name])) {
			return FALSE;
		}

		//If there are several hooks to call
		if(isset($this->config['hooks'][$name][0]) && is_array($this->config['hooks'][$name])) {

			//If no data was provided to be processed
			if(!$data) {

				//Run each hook
				foreach ($this->config['hooks'][$name] as $val) {
					$this->run_hook($val);
				}

				//done
				return true;

			} else {

				//Run each hook and filter the data
				foreach ($this->config['hooks'][$name] as $val) {
					$data = $this->run_hook($val, $data);
				}

				//return the result
				return $data;
			}

			//Else there is only one hook to run
		} else {

			//If we are to process/filter data
			if($data) {
				return $this->run_hook($this->config['hooks'][$name], $data);
			}

			//Else just call the hook
			$this->run_hook($this->config['hooks'][$name]);
			return TRUE;

		}

	}



	/**
	 * Remove Hook
	 *
	 * remove a particular function from the given hook
	 *
	 * @access	private
	 * @param	string	the hook name
	 * @param	string	the function name
	 * @return	void
	 */
	public function remove($name='', $function='') {

		//If there are several hooks to clear
		if(isset($this->config['hooks'][$name][0]) && is_array($this->config['hooks'][$name])) {
			foreach($this->config['hooks'][$name] as $key => $hook) {
				if($hook['function'] == $function) {
					unset($this->config['hooks'][$name][$key]);
					return true;
				}
			}

			//Else it is only one hook
		} else {
			if($this->config['hooks'][$name]['function'] == $function) {
				unset($this->config['hooks'][$name]);
				return true;
			}
		}

	}



	/**
	 * Run Hook
	 *
	 * Runs a particular hook
	 *
	 * @access	public
	 * @param	array	the hook details
	 * @param	array	optional data to be filtered
	 * @return	bool
	 */
	public function run_hook($hook=null, $data=null) {

		//If it is NOT a hook config
		if (!is_array($hook)) {
			return $data;
		}

		// -----------------------------------
		// Safety - Prevents run-away loops
		// -----------------------------------

		// If the script being called happens to have the same
		// hook call within it a loop can happen

		if ($this->hook_in_progress == TRUE) {
			return $data;

		}


		// -----------------------------------
		// Check each value
		// -----------------------------------

		foreach(array('class', 'object', 'function', 'file', 'path') as $type) {
			if(!empty($hook[$type])) {
				$$type = $hook[$type];
			} else {
				$$type = null;
			}
		}


		// -----------------------------------
		// Error Checking
		// -----------------------------------

		//If we are missing a lot of stuff...
		if (!$class && !$function && !$object) {
			return $data;
		}

		//If a function is being called we HAVE TO HAVE a file name!
		if ((!$class && !$object) && (!$file && $function)) {
			return $data;
		}

		//Default to "functions/" or "libraries/" for classes
		if(!$path) {
			if($class || $object) {
				$path = 'libraries';
			} else {
				$path = 'functions';
			}
		}


		// -----------------------------------
		// Set the in_progress flag
		// -----------------------------------

		$this->hook_in_progress = TRUE;

		// -----------------------------------
		// Call the requested class and/or function
		// -----------------------------------


		//Check for the object first
		if($object && isset($this->$object) && method_exists($this->$object, $function)) {

			//Run the function
			$output = $this->$object->$function($data);


			//Else try to run the class
		} elseif ($class) {

			//If a object name is not set
			if(!$object) {
				$object = $class;
			}

			//If we are able to load the class
			$this->load($class, $object, $data, $path);

			//If it was loaded
			if(isset($this->$object) && method_exists($this->$object, $function)) {

				//Run the function
				$output = $this->$object->$function($data);

				//Else the class just wanted to be loaded - so return the data
			} else {
				$output = $data;
			}

			//Else just run the function
		} elseif($function) {

			//If the function does not alreay exist
			if (!function_exists($function)) {
				require_once($path. '/'. $file);
			}

			$output = $function($data);

			//If a class/object/function was not found
		} else {

			$output = $data;
		}


		$this->hook_in_progress = FALSE;
		return $output;
	}


	/**
	 * Return this classes instance
	 * @return singleton
	 */
	public static function &get_instance() {
		return self::$instance;
	}

}
