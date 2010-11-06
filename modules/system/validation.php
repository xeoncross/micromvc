<?php
/**
 * Form Validation
 *
 * Checks for (and processes) submitted form $_POST and $_FILE data. Allowing
 * callbacks for custom processing and verification.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
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
	if($_POST){foreach($fields as$f=>$r){$r=explode('|',$r);if(!in_array('required',$r)&&!post($f))continue;$this->_rules($f,post($f),$r);$this->validate_token();}if(!$this->errors)return 1;}$this->create_token();
}


/**
 * Run all rules on the given field data
 * 
 * @param string $f the field name
 * @param mixed $d the data
 * @param array $rules to run
 * @param object $o the object to search
 */
protected function _rules($f, $d, array $rules)
{
	foreach($rules as$r){list($r,$p)=$this->_parse_rule($r);if(method_exists($this,$r))$o=$this->$r($f,$d,$p);else$o=$r($d);if($o===FALSE)break;if($o!==TRUE)$_POST[$f]=$o;}
}


/**
 * Parse a rule to get any parameters given
 * 
 * @param string $rule to parse
 * @return array
 */
protected function _parse_rule($rule)
{
	$r=$rule;$p=NULL;if(strpos($r,'[')!==FALSE){preg_match('/(\w+)\[(.*?)\]/i',$r,$m);$r=$m[1];$p=$m[2];}return array($r,$p);
}


/**
 * Set an internal validation error message
 * 
 * @param string $field name of the form element
 * @param string $name of the validatition error string
 * @param array $params to insert into the string
 */
protected function _set_error($field, $name, array $params = array())
{
	$this->errors[$field]=vsprintf(lang('validation_'.$name),array_merge(array($field),$params));
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


/**
 * Print the errors from the form validation check
 *
 * @return string
 */
public function display_errors($prefix = '', $suffix = '')
{
	if(!$this->errors)return;$h = '';foreach($this->errors as$e)$h.=($prefix?$prefix:$this->error_prefix).$e.($suffix?$suffix:$this->error_suffix)."\n\n";return $h;
}


/**
 * Return the error (if set) for a given field
 *
 * @param string $field
 * @param boolean $prefix TRUE to wrap error in HTML block
 * @return string
 */
public function error($field, $prefix = TRUE)
{
	if(isset($this->errors[$field])){if($prefix)return $this->error_prefix.$this->errors[$field].$this->error_suffix;return $this->errors[$field];}
}


/*
 *  Validation Methods
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
	if($data=trim(str($data)))return$data;$this->_set_error($field,'required');return FALSE;
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
	if($data)return TRUE;$this->_set_error($field,'required');return FALSE;
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
	if(isset($_POST[$field]))return TRUE;$this->_set_error($field,'set');return FALSE;
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
	if(preg_match("/^([a-z])+$/i",$word))return TRUE;$this->_set_error($field,'alpha');return FALSE;
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
	if(preg_match("/^([a-z0-9])+$/i",$data))return TRUE;$this->_set_error($field,'alpha_numeric');return FALSE;
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
	if(is_numeric($number))return TRUE;$this->_set_error($field,'numeric');return FALSE;
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
	if (isset($_POST[$field2])&&$data===post($field2))return TRUE;$this->_set_error($field,'matches',array($field2));return FALSE;
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
	if(mb_strlen($data)>=$length)return TRUE;$this->_set_error($field,'min_length',array($length));return FALSE;
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
	if(mb_strlen($data)<=$length)return TRUE;$this->_set_error($field,'max_length',array($length));return FALSE;
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
	if(mb_strlen($data)==$length)return TRUE;$this->_set_error($field,'exact_length',array($length));return FALSE;
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
	if(preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i',$email))return TRUE;$this->_set_error($field,'valid_email');return FALSE;
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
	if(!preg_match('/[^a-zA-Z0-9\/\+=]/',$data))return TRUE;$this->_set_error($field,'valid_base64');return FALSE;
}


/**
 * Each time a form is created we will create a token
 * then when the user submits that form we will check
 * to make sure the tokens match.
 */
public function create_token()
{
	if($this->token&&class_exists('session',0))Session::token();
}


/**
 * Validate the form token
 * 
 * @param string $token
 * @return boolean
 */
public function validate_token()
{
	if(!$this->token||!class_exists('session',0))return TRUE;if(Session::token(post('token')))return TRUE;$this->_set_error('token','invalid_token');return FALSE;
}

}

// END