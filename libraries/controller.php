<?php
/**
 * Controller
 *
 * This is the parent class for all controllers. Controllers must extend this
 * class. If you want, you can also extend this class with another class which
 * your controllers can then extend.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	Copyright (c) 2009 MicroMVC
 * @license		http://www.gnu.org/licenses/gpl-3.0.html
 * @link		http://micromvc.com
 * @version		1.1.0 <7/7/2009>
 ********************************** 80 Columns *********************************
 */
class controller {

	//Data for final site layout
	public $views	= array();
	//Name of final site layout file
	public $layout	= 'layout';
	//Singleton instance object
	private static $instance;
	//Config array
	public $config	= array();
	//Language array
	public $lang	= array();
	//Is this a module? (changes default load path for views and models)
	public $module = FALSE;


	/**
	 * Setup some basic controller items on load
	 * @param array $config
	 */
	public function __construct($config = null) {

		//Set singleton instance
		self::$instance =& $this;

		//If this is an ajax request
		if(AJAX_REQUEST) {
			$this->layout = 'ajax';
		}

		//Set the core site config
		$this->config['config'] = $config;

		//Set pre-loaded classes in this controller
		foreach(array('hooks', 'cache', 'routes') as $name) {
			$this->$name = load_class($name);
		}

	}


	/**
	 * Load a library
	 * @param $class
	 * @param $params
	 * @param $name
	 * @param $module
	 * @return boolean
	 */
	public function library($class = NULL, $params = NULL, $name = NULL, $module = FALSE) {

		//Is this a module's library -or a global system library?
		$path = ($module ? MODULE_PATH. $module. DS : SYSTEM_PATH). 'libraries'. DS;

		//Try to load the class
		return $this->object($class, $name, $path, $params);
	}


	/**
	 * Load a model
	 * @param $class
	 * @param $params
	 * @param $name
	 * @param $module
	 * @return boolean
	 */
	public function model($class = NULL, $params = NULL, $name = NULL, $module = FALSE) {

		//Is this a module's model - or a site model?
		if($module OR $module = $this->module) {
			$path = MODULE_PATH. $module. DS. 'models'. DS;
		} else {
			$path = SITE_PATH. 'models'. DS;
		}

		//Try to load the class
		return $this->object($class, $name, $path, $params);
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
	public function object($class = NULL, $name = NULL, $path = NULL, $params = NULL) {

		//If a model is NOT given
		if ( ! $class OR ! $path) { return FALSE; }

		//Allow classes to be located in subdirectories (sub/sub2/class)
		if (strpos($class, '/') !== FALSE) {

			// explode the path so we can separate the filename from the path
			$x = explode('/', $class);

			// Get class name from end of string
			$class = array_pop($x);

			// Glue the path back together (minus the filename)
			$path .= implode($x, DS). DS;
		}

		//If a name is not given
		if( ! $name) { $name = $class; }

		//Load the class
		$this->$name = load_class($class, $path, $params);

		return TRUE;
	}


	/**
	 * Load a helper function file
	 * @param $name
	 * @param $module
	 * @return boolean
	 */
	public function helper($name = NULL, $module = FALSE) {

		//Is this a module's library -or a global system library?
		$path = ($module ? MODULE_PATH. $module. DS : SYSTEM_PATH). 'functions'. DS. $name. '.php';

		//Try to load the file
		return require_once($path);
	}


	/**
	 * Load a config file
	 * @param string $name
	 * @param string $module
	 * @return array
	 */
	public function load_config($name=null, $module = FALSE) {

		//Is this a module's config -or a site config?
		$path = ($module ? MODULE_PATH. $module. DS : SITE_PATH). 'config'. DS. $name. '.php';

		//include the config
		require($path);

		//If this element already exists - mearge the new array in (overwritting as needed)
		if( ! empty($this->config[$name])) {
			$this->config[$name] = array_merge($this->config[$name], $$name);

		} else { //Set the values in our config array
			$this->config[$name] = $$name;
		}

		//Return new config array
		return $this->config[$name];

	}


	/**
	 * Load a language file
	 * @param string $name
	 * @param string $module
	 * @return array
	 */
	public function load_lang($name=null, $module = FALSE) {

		//If this lang file was already loaded
		if( ! empty($this->lang[$name])) {
			return $this->lang[$name];
		}

		//Is this a module's config -or a site config?
		$path = $module ? MODULE_PATH. $module. DS : SITE_PATH;

		//Add the rest of the path
		$path .= 'lang'. DS. $this->config['config']['language']. DS. $name. '.php';

		//include the config
		require($path);

		//Add these language options to our language array
		$this->lang = array_merge($this->lang, $lang);

		//Return a copy too
		return $lang;
	}


	/**
	 * Load and initialize the database connection
	 * @param array $config
	 */
	public function load_database() {

		//Don't load the DB object twice!!!
		if(!empty($this->db)) { return; }

		//Load the DB class (but don't create the class)
		load_class('db', LIBRARY_PATH, NULL, FALSE);

		//Load the config for this database
		$config = $this->load_config('database');

		//Create a new instance of the database child class "mysql"
		$this->db = load_class($config['type'], NULL, $config);
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
	public function view($__file = NULL, $__variables = NULL, $__return = TRUE, $__module = FALSE) {

		//If no file is given - just return false
		if(!$__file) { return; }

		//Is this a module's view - or a site view?
		$__path = $__module ? MODULE_PATH. $__module. DS : SITE_PATH;

		//Add the path
		$__path .= 'views'. DS. $__file. '.php';

		if(is_array($__variables)) {
			//Make each value passed to this view available for use
			foreach($__variables as $__key => $__variable) {
				$$__key = $__variable;
			}
		}

		// Delete them now
		$__variables = null;

		// We just want to print to the screen
		if( ! $__return) {
			if( ! include($__path)) {
				return FALSE;
			}
		}

		//Buffer the output so we can save it to a string
		ob_start();

		// include() vs include_once() allows for multiple views with the same name
		include($__path);

		//Get the output
		$__buffer = ob_get_contents();
		ob_end_clean();

		return $__buffer;
	}


	/**
	 * On close, show the output inside our layout template
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	public function render() {

		// Load the template
		$output = $this->view($this->layout, $this->views);

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


}
