<?php

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