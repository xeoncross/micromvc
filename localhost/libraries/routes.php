<?php
/**
 * Routes
 *
 * Holds and parses information from the URL and URI
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	Copyright (c) 2009 MicroMVC
 * @license		http://www.gnu.org/licenses/gpl-3.0.html
 * @link		http://micromvc.com
 * @version		1.0.1 <5/31/2009>
 ********************************** 80 Columns *********************************
 */
class routes {

	public $uri_string				= '';
	public $uri_segments			= array();
	public $permitted_uri_chars	= '';


	/**
	 * Parse the URI
	 */
	public function parse() {

		$this->get_uri();
		$this->parse_uri();
	}


	/**
	 * Create a URI string from $_SERVER values
	 */
	public function get_uri() {

		//The SERVER values to look for the path info in
		$server = array('PATH_INFO', 'REQUEST_URI', 'ORIG_PATH_INFO');

		foreach($server as $item) {

			//Try the REQUEST_URI
			if(isset($_SERVER[$item]) && trim($_SERVER[$item])) {

				// Remove the start/end slashes
				$string = trim($_SERVER[$item], '\\/');

				//If it is NOT a forward slash
				if(SITE_URL != '/') {
					// Remove the site path -ONLY ONE TIME!
					$string = preg_replace(
					'/^'. preg_quote(trim(SITE_URL, '\\/'), '/'). '(.+)?/i', '', $string, 1);

				}

				//Remove the INDEX.PHP file from url
				$string = str_replace('index.php', '', $string);


				//If anything is left
				if($string) {
					//Set the URI String
					$this->uri_string = $string;
					return;
				}
			}
		}

	}


	/**
	 * Clean and separate the URI string into an array
	 */
	public function parse_uri() {

		//Split the URI into an array
		$segments = explode('/', $this->uri_string);

		foreach($segments as $key => $segment) {

			//Delete Bad Charaters from URI
			$segment = preg_replace('/[^'. preg_quote($this->permitted_uri_chars). ']+/i', '', $segment);

			//If anything is left - add it to our array (allow elements that are ZERO)
			if($segment || $segment === 0) {
				$this->uri_segments[$key] = $segment;
			}

		}

	}

	/**
	 * Set the default controller/Method to use if none was found in the URI
	 */
	public function set_defaults($controller=null, $method=null, $permitted_uri_chars=null) {
	
		//If a controller was NOT set in the URL
		if(empty($this->uri_segments[0]) && $controller) {
			$this->uri_segments[0] = $controller;
		}
		//If a Method was NOT set in the URL
		if(empty($this->uri_segments[1]) && $method) {
			$this->uri_segments[1] = $method;
		}

		//Characters to allow in the URI
		if($permitted_uri_chars) {
			$this->permitted_uri_chars = $permitted_uri_chars;
		}
	}


	/**
	 * Returns the URI as a string, array, or array element
	 */
	public function fetch($type=null) {
		if($type === null) {
			return $this->uri_string;

		} elseif (is_int($type)) {

			//Only return it if it exists
			if(isset($this->uri_segments[$type])) {
				return $this->uri_segments[$type];
			}

		} else {
			return $this->uri_segments;
		}
	}

	//Print data about the URI
	public function debug() {
		print_pre($this->uri_segments, $this->uri_string, @$_SERVER['PATH_INFO'], @$_SERVER['REQUEST_URI']);
	}

	/**
	 * Returns a singleton reference to the current class.
	 *
	public static function & current() {
		static $instance = null;
		return $instance = (empty($instance)) ? new self() : $instance ;
	}*/

	//For testing
	public function __destruct() {
		//if(DEBUG_MODE) { $this->debug(); }
	}
	
}

