<?php

// Error names
$lang = array(
	E_ERROR				=> 'Error',
	E_WARNING			=> 'Warning',
	E_PARSE				=> 'Parsing Error',
	E_NOTICE			=> 'Notice',
	E_CORE_ERROR		=> 'Core Error',
	E_CORE_WARNING		=> 'Core Warning',
	E_COMPILE_ERROR		=> 'Compile Error',
	E_COMPILE_WARNING	=> 'Compile Warning',
	E_USER_ERROR		=> 'User Error',
	E_USER_WARNING		=> 'User Warning',
	E_USER_NOTICE		=> 'User Notice',
	E_STRICT			=> 'Runtime Notice',
	//E_RECOVERABLE_ERROR => 'Recoverable Error',	PHP 5.2.0
	//E_DEPRECATED		=> 'Deprecated Code',		PHP 5.3.0
	//E_USER_DEPRECATED	=> 'Deprecated Code',		PHP 5.3.0
);


/*
 * Cookie key
 */
$lang['cookie_no_key'] = 'You must set a cookie key in the config file';


/*
 * Form Validation
 */
$lang['validation_no_rules'] = 'The %s field does not contain a valid rule (%s)';
$lang['validation_rule_not_found'] = 'The %s form rule was not found.';
$lang['validation_set']= 'The %s field must be submitted.';
$lang['validation_required'] = 'The %s field is required and cannot be empty.';

$lang['validation_alpha'] = 'The %s field may only contain alphabetical characters.';
$lang['validation_alpha_numeric'] = 'The %s field may only contain alpha-numeric characters.';
$lang['validation_numeric'] = 'The %s field must contain only numbers.';
$lang['validation_min_length'] = 'The %s field must be at least %s characters in length.';
$lang['validation_max_length'] = 'The %s field can not exceed %s characters in length.';
$lang['validation_exact_length'] = 'The %s field must be exactly %s characters in length.';
$lang['validation_valid_email'] = 'The %s field must contain a valid email address.';
$lang['validation_valid_base64'] = 'The %s field must contian valid Base 64 characters.';
$lang['validation_matches'] = 'The %s and %s fields do not match.';
$lang['validation_invalid_token'] = 'Your session token was invalid. Please try again.';


/*
 * HTML class
 */
$lang['pagination_previous'] = 'Previous';
$lang['pagination_first'] = '&lt;';
$lang['pagination_last'] = '&gt;';
$lang['pagination_next'] = 'Next';

// For data/time form element
$lang['html_months'] = array(1=>'Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
$lang['html_datetime'] = '%1$s %5$s %2$s @ %6$s:%7$s'; // Month/Day/Year @ Hour:Minute


/*
 * Time class
 */
$lang['time_units'] = array('year'=>31557600,'month'=>2635200,'week'=>604800,'day'=>86400,'hour'=>3600,'minute'=>60,'second'=>1);


return $lang;