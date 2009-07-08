<?php
/**
 * Base file
 *
 * This class should extend child classes to provide access to all of the objects
 * in the controller class. For example, using $this->db when in a model.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	Copyright (c) 2009 MicroMVC
 * @license		http://www.gnu.org/licenses/gpl-3.0.html
 * @link		http://micromvc.com
 * @version		1.1.0 <7/7/2009>
 ********************************** 80 Columns *********************************
 */

class base {

	public function __construct() {
		$this->assign_libraries();
	}

	/*
	 * Make all loaded objects available to the class that extends this
	 * class. In other words, extending your classes with this one will
	 * enable your classes to have access to all the objects the controller
	 * has loaded.
	 *
	 * @author	http://CodeIgniter.com
	 */
	public function assign_libraries() {

		//Get this classes name
		$name = get_class($this);

		//Get controller instance
		if(! $controller = get_instance()) {
			exit('Controller not loaded yet');
		}

		//Get all variable keys
		$object_vars = array_keys(get_object_vars($controller));

		foreach ($object_vars as $key) {

			//Only pass objects (other libraries) to this class
			if($key != $name && is_object($controller->$key)) {

				//If a property by this name doesn't already exist
				if (!isset($this->$key)) {
					$this->$key = $controller->$key;
				}

			}
		}
	}

}