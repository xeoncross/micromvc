<?php
/**
 * Core
 *
 * This is the parent class for all controllers. Controller must extend this class
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	Copyright (c) 2009 MicroMVC
 * @license		http://www.gnu.org/licenses/gpl-3.0.html
 * @link		http://micromvc.com
 * @version		1.0.1 <5/31/2009>
 ********************************** 80 Columns *********************************
 */
require(LIBRARY_PATH. 'loader.php');
class core extends loader {

	//Data for final site layout
	public $views = array();
	//Name of final site layout file
	public $layout = 'layout';
	//Singleton instance object
	private static $instance;
	//Config array
	public $config = array();
	//Is this a module?
	public $is_module = FALSE;


	/**
	 * Load the config values for this system
	 *
	 * @param array $config
	 */
	public function __construct($config=null) {

		//Set singleton instance
		self::$instance =& $this;

		//Set the core site config
		$this->config['config'] = $config;

		//Set pre-loaded classes in this controller
		foreach(array('hooks', 'cache', 'routes') as $name) {
			$this->$name = load_class($name);
		}

	}


	/**
	 * Load a config file
	 * @param string $config
	 *
	public function config($name=null) {

		//Only load once
		if(!empty($this->config[$name])) {
			return $this->config[$name];
		}

		//If this is a module - then look in the modules folder
		if($this->is_module && $name != 'database') {
			$path = MODULE_PATH. get_class($this). '/config/'. $name. '.php';

		} else { //Look in the normal site folder
			$path = SITE_PATH. 'config/'. $name. '.php';
		}

		//include the config
		require($path);

		//Set the values in our config array and return
		return $this->config[$name] = $$name;

	}


	/**
	 * Load and initialize the database connection
	 * @param array $config
	 *
	public function load_database() {

		//Don't load the DB object twice!!!
		if(!empty($this->db)) { return; }

		//Load the DB class (but don't create the class)
		load_class('db', NULL, NULL, NULL, FALSE);

		//Load the config for this database
		$config = $this->config('database');

		//Create a new instance of the database child class "mysql"
		$this->db = load_class($config['type'], NULL, $config);

	}


	/**
	 * Loads and instantiates models, libraries, and other classes
	 *
	 * @param	string	the name of the class
	 * @param	string	name for the class
	 * @param	array	params to pass to the model constructor
	 * @param	string	folder name of the class
	 * @return	void
	 *
	public function load($class = NULL, $name = NULL, $path = NULL, $params = NULL, $location = NULL) {

		//If a model is NOT given
		if ( ! $class) { return; }

		//If a name is not given
		if( ! $name) { $name = $class; }

		if($this->is_module) {
			$path = '';
		}

		//Load the class
		$this->$name = load_class($class, $path, $params, $location);

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
	 *
	public function view($__file = NULL, $__variables = NULL, $__return = TRUE, $__location = 1) {

		//If no file is given - just return false
		if(!$__file) { return; }

		if($__location == 1) {
			//If this is a view in the site view folder
			$__file = SITE_PATH. 'views/'. $__file. '.php';
		} else {
			//It is located in the modules folder
			$__file = MODULE_PATH. $__file. '.php';
		}

		if(is_array($__variables)) {
			//Make each value passed to this view available for use
			foreach($__variables as $key => $variable) {
				$$key = $variable;
			}
		}

		// Delete them now
		$__variables = null;

		if (!file_exists($__file)) {
			trigger_error('Unable to load the requested file: <b>'. $__file. '</b>');
			return;
		}

		// We just want to print to the screen
		if( ! $__return) {
			include($__file);
		}


		//Buffer the output so we can return it
		ob_start();

		// include() vs include_once() allows for multiple views with the same name
		include($__file);

		//Get the output
		$buffer = ob_get_contents();
		@ob_end_clean();

		//Return the view
		return $buffer;

	}
	*/



	/**
	 * Show a 400-500 Header error within the site theme
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	public function request_error($type='404') {

		//Clean the type of error from XSS stuff
		//$type = preg_replace('/[^a-z0-9]+/i', '', $type);

		//Check the type of error
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

		// Cache the file
		$this->cache->create(md5(PAGE_NAME. AJAX_REQUEST), $output);

		// Show the output
		print $output;

	}


	/**
	 * Return this classes instance
	 * @return singleton
	 */
	public static function get_instance() {
		return self::$instance;
	}



	/*
	 * Make all loaded libraries available to the given object
	 * @author	http://CodeIgniter.com
	 *
	public function assign_libraries($name = NULL) {

		//Get all variable keys
		$object_vars = array_keys(get_object_vars($this));

		foreach ($object_vars as $key) {

			//Only pass objects (other libraries) to this class
			if(is_object($this->$key)) {

				//If a propery by this name doesn't already exist -and it is not this classe
				if (!isset($this->$name->$key) AND $key != $name) {
					$this->$name->$key = $this->$key;
				}

			}
		}
	}*/

}
