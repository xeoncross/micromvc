<?php
/*
 * Validation Class
 *
 * Checks for and processes submitted form $_POST and $_FILE data.
 * Creates the POST values given so you don't have to worry about
 * non-set fields in the $_POST data.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	Copyright (c) 2009 MicroMVC
 * @license		http://www.gnu.org/licenses/gpl-3.0.html
 * @link		http://micromvc.com
 * @version		1.0.1 <5/31/2009>
 ********************************** 80 Columns *********************************
 */
class validation {

	//The Parent Controller Instance
	public $instance		= NULL;
	//An array of error messages
	public $error_messages	= array();
	//The array to hold form errors (if any)
	public $errors			= array();
	//The text to put before an error
	public $error_prefix	= '<p>';
	//The text to put after an error
	public $error_sufix		= '</p>';


	/*
	 * Get the Controller instance on load
	 */
	public function __construct() {
		$this->instance = get_instance();

		//Set error messages
		$this->error_messages = array(
			'no_rules'			=> 'Wow dude, there\'s like "no rules" set - sweet.',
			'required'			=> 'The %s field is required.',
			'rule_not_found'	=> 'The %s form rule was not found.',
			'alpha'				=> 'The %s field may only contain alphabetical characters.',
			'alpha_numeric'		=> 'The %s field may only contain alpha-numeric characters.',
			'numeric'			=> 'The %s field must contain only numbers.',
			'min_length'		=> 'The %s field must be at least %s characters in length.',
			'max_length'		=> 'The %s field can not exceed %s characters in length.',
			'exact_length'		=> 'The %s field must be exactly %s characters in length.',
			'valid_email'		=> 'The %s field must contain a valid email address.',
			'valid_base64'		=> 'The %s field must contian valid Base 64 characters.',
		);

	}


	/*
	 * Check and Filter the $_POST data submited to us.
	 *
	 * Based on the config values given, check each field
	 * and process it as required checking for the function
	 * first in the controller, then in this class, and
	 * finally as just a function.
	 */
	public function run($config=null) {

		//Reset error array
		$this->errors = array();

		//No rules?
		if(! $config) {
			$this->errors['no_rules'] = $this->error_messages['no_rules'];
			return FALSE;
		}

		//No data? (on first run there is no data)
		if(empty($_POST)) {

			//Set each value to NULL
			foreach($config as $field => $rules) {
				$_POST[$field] = NULL;
			}

			//Failed form validation
			return FALSE;
		}

		//Check each form element
		foreach($config as $field => $rules) {
			
			//Break apart rules
			if(strpos($rules, '|') !== FALSE) {
				$rules = explode('|', $rules);
			} else {
				$rules = array($rules);
			}

			//Fetch the data (if it exists)
			$data = empty($_POST[$field]) ? '' : $_POST[$field];

			//Pass the data to each rule
			foreach($rules as $rule) {

				//No rules? Skip this field (should we allow this?)
				if(! $rule) {
					break;
				}

				$params = NULL;

				//Check for extra functions params like "rule[my_params]"
				if (($position = strpos($rule, '[')) !== FALSE) {

					//Fetch the function arguments
					preg_match('/([a-z0-9_]+)\[(.*?)\]/i', $rule, $matches);
						
					//Fetch the rule name
					$rule = $matches[1];
						
					//Get the params
					$params = $matches[2];

				}

				//If required - it must exist!
				if($rule == 'required') {
					//If there is no data
					if(empty($data)) {
						$this->errors[$field] = sprintf($this->error_messages['required'], ucwords($field));
						break; //End the checks for this field
					}
					continue;
				}

				//Look for it in the current controller
				if(method_exists($this->instance, $rule)) {
					$result = $this->instance->$rule($field, $data, $params);

					//Look for it in this class
				} elseif(method_exists($this, $rule)) {
					$result = $this->$rule($field, $data, $params);

					//Else just look for a function with this name
				} elseif(function_exists($rule)) {
					$result = $rule($field, $data);

				} else {
					$this->errors[$field] = sprintf($this->error_messages['rule_not_found'], $rule);
					break; //End the checks for this field
				}

				/*
				 * The functions above can return one of two types of data:
				 * booleans (TRUE/FALSE) or a string with the edited data.
				 *
				 * If they return a boolean then we know it was just a value
				 * check - so we do NOT want to overwrite the data. If data
				 * is a string then we know that the data was edited successfully.
				 */
				if(!is_bool($result)) {
					//Only save the result to $data if not a TRUE/FALSE value
					$data = $result;
				}

			}

			//Now that we are done working with the data - save it
			$_POST[$field] = $data;
		}
		
		//print_pre($_POST);
		
		//If no errors then we are good!
		if(empty($this->errors)) {
			return TRUE;
		}

		return FALSE;
	}



	/**
	 * Print the errors from the form validation check
	 *
	 * @return string
	 */
	function display_errors() {

		//If no errors
		if(empty($this->errors)) {
			return;
		}

		$output = '';
		//Format each error
		foreach($this->errors as $error) {
			$output .= $this->error_prefix. $error. $this->error_sufix. "\n\n";
		}

		//Return the full errors string
		return $output;
	}


	/**
	 * Return the error (if any) for a given field
	 *
	 * @param $field
	 * @return string
	 */
	function error($field) {
		return empty($this->errors[$field]) ? NULL : $this->errors[$field];
	}


	/*
	 *  Validation Methods
	 */


	/**
	 * Alpha
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	function alpha($field, $data) {
		if(preg_match("/^([a-z])+$/i", $data)) {
			return TRUE;
		}

		//Set the error
		$this->errors[$field] = sprintf($this->error_messages['alpha'], ucwords($field));
		return FALSE;
	}


	/**
	 * Alpha-numeric
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	function alpha_numeric($field, $data) {
		if(preg_match("/^([a-z0-9])+$/i", $data)) {
			return TRUE;
		}

		//Set the error
		$this->errors[$field] = sprintf($this->error_messages['alpha_numeric'], ucwords($field));
		return FALSE;
	}


	/**
	 * Is Numeric
	 *
	 * @access    public
	 * @param    string
	 * @return    bool
	 */
	function numeric($field, $data) {
		if(is_numeric($data)) {
			return TRUE;
		}

		//Set the error
		$this->errors[$field] = sprintf($this->error_messages['numeric'], ucwords($field));
		return FALSE;
	}


	/**
	 * Minimum Length
	 *
	 * @access	public
	 * @param	string
	 * @param	value
	 * @return	bool
	 */
	function min_length($field, $data, $params) {
		//Try to use the MB extension
		if (function_exists('mb_strlen')) {
			if(mb_strlen($data) >= $params) {
				return TRUE;
			}
		}

		if(strlen($data) >= $params) {
			return TRUE;
		}

		//Set the error
		$this->errors[$field] = sprintf($this->error_messages['min_length'], ucwords($field), $params);
		return FALSE;
	}


	/**
	 * Max Length
	 *
	 * @access	public
	 * @param	string
	 * @param	value
	 * @return	bool
	 */
	function max_length($field, $data, $params) {
		//Try to use the MB extension
		if (function_exists('mb_strlen')) {
			if(mb_strlen($data) <= $params) {
				return TRUE;
			}
		}

		if(strlen($data) <= $params) {
			return TRUE;
		}

		//Set the error
		$this->errors[$field] = sprintf($this->error_messages['max_length'], ucwords($field), $params);
		return FALSE;
	}


	/**
	 * Max Length
	 *
	 * @access	public
	 * @param	string
	 * @param	value
	 * @return	bool
	 */
	function exact_length($field, $data, $params) {
		//Try to use the MB extension
		if (function_exists('mb_strlen')) {
			if(mb_strlen($data) == $params) {
				return TRUE;
			}
		}

		if(strlen($data) == $params) {
			return TRUE;
		}

		//Set the error
		$this->errors[$field] = sprintf($this->error_messages['exact_length'], ucwords($field), $params);
		return FALSE;
	}


	/**
	 * Check to see if the email entered is valid
	 * 
	 * @param $field
	 * @param $data
	 * @return boolean
	 */
	public function valid_email($field, $data) {
		if(valid_email($data)) {
			return TRUE;
		}
		
		//Set the error
		$this->errors[$field] = sprintf($this->error_messages['valid_email'], ucwords($field));
		return FALSE;
	}


	/**
	 * Valid Base64
	 *
	 * Tests a string for characters outside of the Base64 alphabet
	 * as defined by RFC 2045 http://www.faqs.org/rfcs/rfc2045
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	function valid_base64($field, $data) {

		if( ! preg_match('/[^a-zA-Z0-9\/\+=]/', $data)) {
			return TRUE;
		}

		//Set the error
		$this->errors[$field] = sprintf($this->error_messages['valid_base64'], ucwords($field));
		return FALSE;
	}

}
