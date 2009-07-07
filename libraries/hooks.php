<?php
/*
 * Hooks Class
 *
 * Provides plugin points for classes and functions to run and/or filter data.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	Copyright (c) 2009 MicroMVC
 * @license		http://www.gnu.org/licenses/gpl-3.0.html
 * @link		http://micromvc.com
 * @version		1.0.1 <5/31/2009>
 ********************************** 80 Columns *********************************
 */
class hooks {

	//keeps us out of trouble with hook loops
	public $hook_in_progress = false;
	//List of hooks
	public $hooks = array();

	//Set Hook configuration
	public function __construct($config=null) {
		$this->hooks = $config;
	}

	/**
	 * Call
	 *
	 * Calls a particular hook(s). Also, alows data to be
	 * filtered by a hook(s) and the result returned.
	 *
	 * @access	public
	 * @param	string	the hook name
	 * @param	mixed	Data to be parsed
	 * @return	mixed
	 */
	public function call($name = '', $data = NULL) {

		//If no hook is given OR found with that name
		if (!$name OR empty($this->hooks[$name])) {
			return $data;
		}

		//If there are several hooks to call
		if(isset($this->hooks[$name][0]) && is_array($this->hooks[$name])) {

			//If no data was provided to be processed
			if(!$data) {

				//Run each hook
				foreach ($this->hooks[$name] as $val) {
					$this->run_hook($val);
				}

				//done
				return true;

			} else {

				//Run each hook and filter the data
				foreach ($this->hooks[$name] as $val) {
					$data = $this->run_hook($val, $data);
				}

				//return the result
				return $data;
			}

			//Else there is only one hook to run
		} else {

			//If we are to process/filter data
			if($data) {
				return $this->run_hook($this->hooks[$name], $data);
			}

			//Else just call the hook
			$this->run_hook($this->hooks[$name]);
			return TRUE;

		}

	}



	/**
	 * Remove Hook
	 *
	 * remove a particular function (or all) from the given hook trigger
	 *
	 * @access	private
	 * @param	string	the hook name
	 * @param	string	the function name
	 * @return	void
	 */
	public function remove($name = '', $function = NULL) {

		//If we are to remove all hooks for this trigger
		if( ! $function) {
			unset($this->hooks[$name]);
		}

		//If there are several hooks to clear
		if(isset($this->hooks[$name][0]) && is_array($this->hooks[$name])) {

			foreach($this->hooks[$name] as $key => $hook) {
				if($hook['function'] == $function) {
					unset($this->hooks[$name][$key]);
					return true;
				}
			}

			//Else it is only one hook
		} else {
			if($this->hooks[$name]['function'] == $function) {
				unset($this->hooks[$name]);
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
	public function run_hook($hook = NULL, $data = NULL) {

		//If it is NOT a hook config
		if (!is_array($hook)) {
			return $data;
		}

		/*
		 * Safety - Prevents run-away loops
		 *
		 * If the script being called happens to have the same
		 * hook call within it a loop can happen
		 */
		if ($this->hook_in_progress == TRUE) {
			return $data;
		}

		// Set each value to avoid php notices
		foreach(array('class', 'function', 'file', 'path') as $type) {
			$$type = empty($hook[$type]) ? NULL : $hook[$type];
		}

		/*
		 * Error Checking
		 */

		//If we are missing a lot of stuff...
		if (!$class && !$function) {
			return $data;
		}

		//If a function is being called we HAVE TO HAVE a file name!
		if (!$class && (!$file && $function)) {
			return $data;
		}

		//Default to "functions/" or "libraries/" for hooks
		if(!$path) {
			if($class) {
				$path = LIBRARY_PATH;
			} else {
				$path = FUNCTION_PATH;
			}
		}


		// -----------------------------------
		// Set the in_progress flag
		// -----------------------------------

		$this->hook_in_progress = TRUE;

		// -----------------------------------
		// Call the requested class and/or function
		// -----------------------------------

		if($class) {

			//Load the class
			$object = load_class($class, $path, $data);

			//Check for the object first
			if($object && method_exists($object, $function)) {

				//Run the function
				$data = $object->$function($data);
			}

		} else { //Else just run the function

			//If the function does not alreay exist
			if (!function_exists($function)) {
				require_once($path. $file. '.php');
			}

			$data = $function($data);
		}

		$this->hook_in_progress = FALSE;
		return $data;
	}

}