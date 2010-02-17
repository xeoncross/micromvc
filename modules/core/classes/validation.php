<?php defined('SYSTEM_PATH') or die('No direct access');
/**
 * Validation
 *
 * Checks for (and processes) submitted form $_POST and $_FILE data. Allowing
 * callbacks for custom processing.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
class Validation
{

	// The Controller Instance
	public $c				= NULL;
	// An array of error messages
	public $error_messages	= array();
	// The array to hold form errors (if any)
	public $errors			= array();
	// The text to put before an error
	public $error_prefix	= '<div class="form_error">';
	// The text to put after an error
	public $error_suffix	= '</div>';
	// Should we add a token to each form? (Prevents CSFR)
	public $token			= TRUE;


	/**
	 * Set the config for the validation run
	 * @param	array	$config
	 */
	public function setup($config)
	{
		$this->config = $config;
	}


	/**
	 * Add a field and the matching rules to our config
	 * @param	string	$field
	 * @param	string	$rules
	 */
	public function set_rule($field = NULL, $rules = '')
	{
		$this->config[$field] = $rules;
	}


	/**
	 * Set a validation error message
	 *
	 * @param $field
	 * @param $error
	 */
	public function set_message($field, $error)
	{
		$this->errors[$field] = $error;
	}

	/*
	 * Check and Filter the $_POST data submited to us.
	 *
	 * Based on the config values given, check each field
	 * and process it as required checking for the function
	 * first in the controller, then in this class, and
	 * finally as just a function.
	 */
	public function run($config = NULL)
	{
		//Get the controller instance
		$this->c = get_instance();

		//Reset error array
		$this->errors = array();

		//If the rules were not passed, then see if they are pre-set
		if( ! $config AND ! $config = $this->config)
		{
			$this->errors['no_rules'] = lang('validation_no_rules');
			return FALSE;
		}

		//No data? (on first run there is no data)
		if(empty($_POST))
		{
			//Create a form token (if needed)
			$this->create_token();

			//Failed form validation
			return FALSE;
		}

		//Check each form element
		foreach($config as $field => $rules)
		{

			//Break apart rules
			if(strpos($rules, '|') !== FALSE)
			{
				$rules = explode('|', $rules);
			}
			else
			{
				$rules = array($rules);
			}

			// Fetch the post data
			$data = post($field);

			//If the data is a non-empty string
			if(is_string($data) AND $data)
			{
				$data = trim($data); // Auto-trim
			}

			//Only run rule checks on fields that are required AND contain submitted data!
			if( ! in_array('required', $rules) AND empty($data))
			{
				continue; //Skip this element
			}

			//Pass the data to each rule
			foreach($rules as $rule)
			{

				//No rules? Skip this field (should we allow this?)
				if( ! $rule)
				{
					break;
				}

				$params = NULL;

				//Check for extra functions params like "rule[my_params]"
				if (($position = strpos($rule, '[')) !== FALSE)
				{
					//Fetch the public function arguments
					preg_match('/([a-z0-9_]+)\[(.*?)\]/i', $rule, $matches);

					//Fetch the rule name
					$rule = $matches[1];

					//Get the params
					$params = $matches[2];
				}

				//If required - it must exist and have a NON-FALSE value
				if($rule === 'required')
				{
					if( ! $data)
					{
						$this->errors[$field] = sprintf(lang('validation_required'), ucwords($field));
						break; //End the checks for this field
					}
					continue;
				}

				//If this form element is not set in the POST data
				if($rule === 'set')
				{
					if($data === NULL)
					{
						$this->errors[$field] = sprintf(lang('validation_set'), ucwords($field));
						break; //End the checks for this field
					}
					
					continue;
				}

				//Look for it in the current controller
				if(method_exists($this->c, $rule))
				{
					$result = $this->c->$rule($field, $data, $params);
					
				} //Look for it in this class
				elseif(method_exists($this, $rule))
				{
					$result = $this->$rule($field, $data, $params);
					
				} //Else just look for a public function with this name
				elseif(function_exists($rule))
				{
					$result = $rule($data);
				}
				else
				{
					$this->errors[$field] = sprintf(lang('validation_rule_not_found'), $field, $rule);
					break; //End the checks for this field
				}

				/*
				 * The functions above can return one of two types of data:
				 * booleans (TRUE/FALSE) or the edited data.
				 *
				 * If they return a boolean then we know it was just a value
				 * check - so we do NOT want to overwrite the data. Otherwise
				 * we know that the data was edited successfully.
				 */
				if( ! is_bool($result))
				{
					//Only save the result to $data if not a TRUE/FALSE value
					$data = $result;
				}

			}

			//Now that we are done working with the data - save it
			$_POST[$field] = $data;
		}


		//If we are using tokens - also try to validate the token
		$this->validate_token();

		//If no errors - we are good!
		if(empty($this->errors))
		{
			return TRUE;
		}

		//Create another token because there was a form error
		$this->create_token();
		return FALSE;

	}



	/**
	 * Print the errors from the form validation check
	 *
	 * @return string
	 */
	public function display_errors($prefix = '', $suffix = '') {

		//If no errors
		if(empty($this->errors)) {
			return;
		}

		$output = '';
		//Format each error
		foreach($this->errors as $error) {
			$output .= ($prefix ? $prefix : $this->error_prefix)
					. $error
					. ($suffix ? $suffix : $this->error_suffix). "\n\n";
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
	public function error($field, $prefix = TRUE) {

		//If this error exists
		if( ! empty($this->errors[$field]) ) {

			//If we should prefix it
			if( $prefix ) {
				return $this->error_prefix. $this->errors[$field]. $this->error_suffix;
			}

			return $this->errors[$field];
		}
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
	public function alpha($field, $data) {
		if(preg_match("/^([a-z])+$/i", $data)) {
			return TRUE;
		}

		//Set the error
		$this->errors[$field] = sprintf(lang('validation_alpha'), ucwords($field));
		return FALSE;
	}


	/**
	 * Alpha-numeric
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function alpha_numeric($field, $data) {
		if(preg_match("/^([a-z0-9])+$/i", $data)) {
			return TRUE;
		}

		//Set the error
		$this->errors[$field] = sprintf(lang('validation_alpha_numeric'), ucwords($field));
		return FALSE;
	}


	/**
	 * Is Numeric
	 *
	 * @access    public
	 * @param    string
	 * @return    bool
	 */
	public function numeric($field, $data) {
		if(is_numeric($data)) {
			return TRUE;
		}

		//Set the error
		$this->errors[$field] = sprintf(lang('validation_numeric'), ucwords($field));
		return FALSE;
	}


	/**
	 * Match one field against another
	 * @param	string	$field
	 * @param	mixed	$data
	 * @param	string	$name
	 * @return	bool
	 */
	function matches($field, $data, $name) {
		//IF the field exists and matches our first field
		if (isset($_POST[$name])&& $data == post($name)) {
			return TRUE;
		}

		//If the given field does not match the one we should compare it too
		$this->errors[$field] = sprintf(lang('validation_matches'), ucwords($field), ucwords($name));
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
	public function min_length($field, $data, $params) {

		if(mb_strlen($data) >= $params) {
			return TRUE;
		}

		//Set the error
		$this->errors[$field] = sprintf(lang('validation_min_length'), ucwords($field), $params);
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
	public function max_length($field, $data, $params) {

		if(mb_strlen($data) <= $params) {
			return TRUE;
		}

		//Set the error
		$this->errors[$field] = sprintf(lang('validation_max_length'), ucwords($field), $params);
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
	public function exact_length($field, $data, $params) {

		if(mb_strlen($data) == $params) {
			return TRUE;
		}

		//Set the error
		$this->errors[$field] = sprintf(lang('validation_exact_length'), ucwords($field), $params);
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

		if(preg_match("/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b/i", $data))
		{
			return TRUE;
		}

		//Set the error
		$this->errors[$field] = sprintf(lang('validation_valid_email'), ucwords($field));
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
	public function valid_base64($field, $data) {

		if( ! preg_match('/[^a-zA-Z0-9\/\+=]/', $data)) {
			return TRUE;
		}

		//Set the error
		$this->errors[$field] = sprintf(lang('validation_valid_base64'), ucwords($field));
		return FALSE;
	}


	/*
	 * Each time a form is created we will create a token
	 * then when the user submits that form we will check
	 * to make sure the tokens match.
	 */
	public function create_token()
	{
		//If tokens are enabled then create one
		if($this->token)
		{
			Session::create_token();
			//$this->c->session->create_token();
		}
	}


	/**
	 * Validate the form token
	 * @param	$token
	 * @return	boolean
	 */
	public function validate_token($token = NULL)
	{

		//If we should skip this
		if($this->token == FALSE)
		{
			return TRUE;
		}

		//If the token validates
		if(Session::validate_token($token))
		{
			return TRUE;
		}

		//Return an error
		$this->set_message('token', lang('validation_invalid_token'));
		return FALSE;
	}


	/**
	 * Validate that the the given file upload worked with no errors.
	 *
	 * @param	string
	 * @param	string
	 * @return	boolean
	 */
	function valid_upload($field, $param = NULL) {

		//Load file helper
		$this->c->helper('files');

		//If there is an error uploading this file
		if( $error = file_upload_error($field, $param) ) {
			$this->set_message($field, $error);
			return FALSE;
		}

		return TRUE;
	}


	/**
	 * If a file is uploaded (optional), then validate that the the given file
	 * upload worked with no errors.
	 *
	 * @param	string
	 * @param	string
	 * @return	boolean
	 */
	function optional_valid_upload($field, $param = NULL) {

		//Does a matching multiple or single field file upload exists?
		if( ($param AND isset($_FILES[$field]['error'][$param])) OR (!$param AND isset($_FILES[$field])) ) {
			return $this->valid_file_upload($field, $param);
		}

		//If no file was uploaded (i.e. optional) then also return TRUE
		return TRUE;
	}

}


/**
 * Show the validation error for the given field (if any)
 * @param $field
 * @param $prefix
 * @return unknown_type
 */
function validation_error($field, $prefix = TRUE)
{
	return load::singleton('Validation')->error($field, $prefix);
}


/**
 * Show the validation error for the given field (if any)
 * @param $field
 * @param $prefix
 * @return unknown_type
 */
function validation_errors($prefix = '', $suffix = '')
{
	return load::singleton('Validation')->display_errors($prefix, $suffix);
}

