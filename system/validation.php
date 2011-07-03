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
	public $errors = array();

	public $data = array();

	public $rules = array();

	// The text to put before an error
	public $error_prefix = '<div class="form_error">';

	// The text to put after an error
	public $error_suffix = '</div>';

	// Should we add a token to each form to prevents CSFR? (requires Session class)
	public $token = TRUE;


	/**
	 * Create a validation object for this data using these rules
	 */
	public function __construct( & $data, $rules)
	{
		$this->fields = $fields;
		$this->data = $data;
	}


	/**
	 * Run the given post data through the field rules to ensure it is valid.
	 *
	 * @param array $fields and matching rules
	 * @return boolean
	 */
	public function validates()
	{
		if(empty($this->data))
		{
			$this->create_token();
			return FALSE;
		}

		// First, validate the token
		$this->validate_token();

		foreach($this->rules as $field => $rules)
		{
			$rules = explode('|', $rules);

			// Skip fields that are not required
			if( ! in_array('required', $rules) AND ! isset($this->data[$field])) continue;

			$data = $this->data[$field];

			// Auto-trim non-empty string
			if($data AND is_string($data))
			{
				$data = trim($data);
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
			}
		}

		// If there were no problems
		if( ! $this->errors) return TRUE;

		// Create a new form token
		$this->create_token();
	}


	/**
	 * Return an array of values for the fields given
	 *
	 * @return array
	 */
	public function data()
	{
		if(empty($this->data)) return array();

		// Excess data may be given so we only want values with keys in fields
		return array_intersect_key($this->data, $this->fields);
	}

	/**
	 * Print the errors from the form validation check
	 *
	 * @return string
	 */
	public function display_errors()
	{
		if(empty($this->errors)) return;

		$output = '';
		foreach($this->errors as $error)
		{
			$output .= $this->error_prefix . $error . $this->error_suffix . "\n";
		}

		return $output;
	}


	/**
	 * Return the error (if any) for a given field
	 *
	 * @param $field to get error for
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


	/*
	 * Validation Methods
	 */


	/**
	 * Value is a string
	 *
	 * @param string $field name of the form element
	 * @param mixed $data to validate
	 * @return boolean
	 */
	public function string($field, $data)
	{
		if(is_string($data)) return TRUE;
		$this->errors[$field] = sprintf(lang('validation_string'), $field);
		return FALSE;
	}


	/**
	 * Value is an array
	 *
	 * @param string $field name of the form element
	 * @param mixed $data to validate
	 * @return boolean
	 */
	public function array($field, $data)
	{
		if(is_array($data)) return TRUE;
		$this->errors[$field] = sprintf(lang('validation_array'), $field);
		return FALSE;
	}


	/**
	 * Contains only numeric characters
	 *
	 * @param string $field name of the form element
	 * @param mixed $number to validate
	 * @return boolean
	 */
	public function integer($field, $number)
	{
		if(ctype_digit($number)) return TRUE;
		$this->errors[$field] = sprintf(lang('validation_integer'), $field);
		return FALSE;
	}


	/**
	 * Value is required (not empty)
	 *
	 * @param string $field name of the form element
	 * @param mixed $data to validate
	 * @return boolean
	 */
	public function required($field, $data)
	{
		if($data) return TRUE;
		$this->errors[$field] = sprintf(lang('validation_required'), $field);
		return FALSE;
	}


	/**
	 * Must only contain english, alphabetical characters
	 *
	 * @param string $field name of the form element
	 * @param mixed $word to validate
	 * @return boolean
	 */
	public function alphabetical($field, $word)
	{
		if(preg_match("/^([a-z])+$/i",$word)) return TRUE;
		$this->errors[$field] = sprintf(lang('validation_alphabetical'), $field);
		return FALSE;
	}


	/**
	 * Must only contain word characters (A-Za-z0-9_).
	 *
	 * @param string $field name of the form element
	 * @param mixed $word to validate
	 * @return boolean
	 */
	public function word($field, $word)
	{
		if(preg_match("/\W/", $word))
		{
			$this->errors[$field] = sprintf(lang('validation_word'), $field);
			return FALSE;
		}
		return TRUE;
	}


	/**
	 * Plain text that contains no HTML/XML markup.
	 *
	 * @param string $field name of the form element
	 * @param mixed $data to validate
	 * @return boolean
	 */
	public function plaintext($field, $data)
	{
		if(strrpos($string, '<') !== FALSE OR strrpos($string, '>') !== FALSE)
		{
			$this->errors[$field] = sprintf(lang('validation_plaintext'), $field);
			return FALSE;
		}
		return TRUE;
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
		$this->errors[$field] = sprintf(lang('validation_min_length'), $field, $length);
		return FALSE;
	}


	/**
	 * Maximum Length
	 *
	 * @param string $field name of the form element
	 * @param mixed $data to validate
	 * @param int $length of the string
	 * @return boolean
	 */
	public function max_length($field, $data, $length)
	{
		if(mb_strlen($data)<=$length)return TRUE;
		$this->errors[$field] = sprintf(lang('validation_max_length'), $field, $length);
		return FALSE;
	}


	/**
	 * Exact Length
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
		if( ! $this->token OR ! class_exists('session', FALSE))
		{
			return TRUE;
		}

		if(Session::token(post('token'))) return TRUE;
		$this->errors['token'] = sprintf(lang('validation_invalid_token'), 'token');
		return FALSE;
	}

}

// END
