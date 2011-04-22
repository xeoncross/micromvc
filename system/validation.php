<?php
/**
 * Form Validation
 *
 * Checks for (and processes) submitted form $_POST and $_FILE data. Allowing
 * callbacks for custom processing and verification.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2011 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
class Validation
{

// The array of errors (if any)
public $errors = array();

// The text to put before an error
public $error_prefix = '<div class="form_error">';

// The text to put after an error
public $error_suffix = '</div>';

// Should we add a token to each form to prevents CSFR? (requires Session class)
public $token = TRUE;



/**
 * Run the given post data through the field rules to ensure it is valid.
 * 
 * @param array $fields and matching rules
 * @return boolean
 */
public function run(array $fields)
{
	if(empty($_POST))
	{
		$this->create_token();
		
		return FALSE;
	}
	
	// First, validate the token
	$this->validate_token();
	
	foreach($fields as $field => $rules)
	{
		$rules = explode('|', $rules);
		
		// Skip fields that are not required
		if( ! in_array('required', $rules)  AND ! isset($_POST[$field])) continue;
		
		// Fetch the post data
		$data = $_POST[$field];
		
		//If the data is a non-empty string
		if(is_string($data) AND $data)
		{
			$data = trim($data); // Auto-trim
		}
		
		foreach($rules as $rule)
		{
			$params = NULL;

			//Check for extra functions params like "rule[my_params]"
			if (strpos($rule, '[') !== FALSE)
			{
				//Fetch the public function arguments
				preg_match('/([a-z0-9_]+)\[(.*?)\]/i', $rule, $matches);

				//Fetch the rule name
				$rule = $matches[1];

				//Get the params
				$params = $matches[2];
			}
			
			if(method_exists($this, $rule))
			{
				$result = $this->$rule($field, $data, $params);
			}
			elseif(function_exists($rule))
			{
				$result = $rule($data);
			}
			else
			{
				throw new Exception (sprintf(lang('validation_rule_not_found'), $rule));
			}
			
			// Rules return boolean false on failure
			if($result === FALSE) break;
			
			// Rules return boolean true on success
			if($result !== TRUE)
			{
				// All other rules return data
				$data = $result;
			}
		}
		
		// Commit any changes
		$_POST[$field] = $data;
	}
	
	// If there were no problems
	if( ! $this->errors) return TRUE;
	
	// Create a new form token
	$this->create_token();
}


/**
 * Print the errors from the form validation check
 *
 * @return string
 */
public function display_errors($prefix = '', $suffix = '')
{
	if(empty($this->errors)) return;

	$output = '';
	foreach($this->errors as $error)
	{
		$output .= ($prefix ? $prefix : $this->error_prefix)
				. $error
				. ($suffix ? $suffix : $this->error_suffix). "\n";
	}

	return $output;
}
	

/**
 * Return the error (if any) for a given field
 *
 * @param $field
 * @param boolean $prefix TRUE to wrap error
 * @return string
 */
public function error($field, $prefix = TRUE)
{
	if( ! empty($this->errors[$field]))
	{
		if($prefix)
		{
			return $this->error_prefix . $this->errors[$field] . $this->error_suffix;
		}
		return $this->errors[$field];
	}
}


/**
 * Set a validation error message
 *
 * @param string $field name of the form element
 * @param string $error to set
 */
public function set_error($field, $error)
{
	$this->errors[$field] = $error;
}


/*
 * Validation Methods
 */


/**
 * String
 *
 * @param string $field name of the form element
 * @param mixed $word to validate
 * @return boolean
 */
public function string($field, $data)
{
	if($data = trim(str($data))) return $data;
	$this->errors[$field] = sprintf(lang('validation_required'), $field);
	return FALSE;
}


/**
 * Required
 *
 * @param string $field name of the form element
 * @param mixed $word to validate
 * @return boolean
 */
public function required($field, $data)
{
	if($data) return TRUE;
	$this->errors[$field] = sprintf(lang('validation_required'), $field);
	return FALSE;
}


/**
 * Set
 *
 * @param string $field name of the form element
 * @param mixed $word to validate
 * @return boolean
 */
public function set($field, $data)
{
	if(isset($_POST[$field]))return TRUE;
	$this->errors[$field] = sprintf(lang('validation_set'), $field);
	return FALSE;
}


/**
 * Alpha
 *
 * @param string $field name of the form element
 * @param mixed $word to validate
 * @return boolean
 */
public function alpha($field, $word)
{
	if(preg_match("/^([a-z])+$/i",$word))return TRUE;
	$this->errors[$field] = sprintf(lang('validation_alpha'), $field);
	return FALSE;
}


/**
 * Alpha-numeric
 *
 * @param string $field name of the form element
 * @param mixed $data to validate
 * @return boolean
 */
public function alpha_numeric($field, $data)
{
	if(preg_match("/^([a-z0-9])+$/i", $data)) return TRUE;
	$this->errors[$field] = sprintf(lang('validation_alpha_numeric'), $field);
	return FALSE;
}


/**
 * Is Numeric
 * 
 * @param string $field name of the form element
 * @param mixed $number to validate
 * @return boolean
 */
public function numeric($field, $number)
{
	//if(is_numeric($number)) return TRUE;
	if(ctype_digit($number)) return TRUE;
	$this->errors[$field] = sprintf(lang('validation_numeric'), $field);
	return FALSE;
}


/**
 * Match one field against another
 * 
 * @param string $field name of the form element
 * @param mixed $data to validate
 * @param string $field2 name of the other form element
 * @return boolean
 */
public function matches($field, $data, $field2)
{
	if (isset($_POST[$field2]) AND $data === $_POST[$field2]) return TRUE;
	$this->errors[$field] = sprintf(lang('validation_matches'), $field, $field2);
	return FALSE;
}


/**
 * Minimum Length
 *
 * @param string $field name of the form element
 * @param mixed $data to validate
 * @param int $length of the string
 * @return boolean
 */
public function min_length($field, $data, $length)
{
	if(mb_strlen($data) >= $length) return TRUE;
	$this->errors[$field] = sprintf(lang('validation_min_length'), $field);
	return FALSE;
}


/**
 * Max Length
 *
 * @param string $field name of the form element
 * @param mixed $data to validate
 * @param int $length of the string
 * @return boolean
 */
public function max_length($field, $data, $length)
{
	if(mb_strlen($data)<=$length)return TRUE;
	$this->errors[$field] = sprintf(lang('validation_max_length'), $field);
	return FALSE;
}


/**
 * Max Length
 *
 * @param string $field name of the form element
 * @param mixed $data to validate
 * @param int $length of the string
 * @return boolean
 */
public function exact_length($field, $data, $length)
{
	if(mb_strlen($data) == $length) return TRUE;
	$this->errors[$field] = sprintf(lang('validation_exact_length'), $field, $length);
	return FALSE;
}


/**
 * Check to see if the email entered is valid
 *
 * @param string $field name of the form element
 * @param mixed $email to validate
 * @return boolean
 */
public function valid_email($field, $email)
{
	if(preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i', $email)) return TRUE;
	$this->errors[$field] = sprintf(lang('validation_valid_email'), $field);
	return FALSE;
}


/**
 * Tests a string for characters outside of the Base64 alphabet
 * as defined by RFC 2045 http://www.faqs.org/rfcs/rfc2045
 *
 * @param string $field name of the form element
 * @param mixed $data to validate
 * @return boolean
 */
public function valid_base64($field, $data)
{
	if(!preg_match('/[^a-zA-Z0-9\/\+=]/', $data)) return TRUE;
	$this->errors[$field] = sprintf(lang('validation_valid_base64'), $field);
	return FALSE;
}


/**
 * Each time a form is created we will create a token
 * then when the user submits that form we will check
 * to make sure the tokens match.
 */
public function create_token()
{
	if($this->token AND class_exists('session', FALSE))
	{
		Session::token();
	}
}


/**
 * Validate the form token
 * 
 * @return boolean
 */
public function validate_token()
{
	if(! $this->token OR ! class_exists('session', FALSE))
	{
		return TRUE;
	}
	
	if(Session::token(post('token'))) return TRUE;
	$this->errors['token'] = sprintf(lang('validation_invalid_token'), 'token');
	return FALSE;
}

}

// END
