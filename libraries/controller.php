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
class controller extends loader {

	//Data for final site layout
	public $views = array();
	//Name of final site layout file
	public $layout = 'layout';
	//Singleton instance object
	private static $instance;
	//Config array
	public $config = array();


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


}
