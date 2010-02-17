<?php defined('SYSTEM_PATH') or die('No direct access');
/**
 * FormTest
 *
 * This controller demonstrates the use of the validation class for checking
 * submitted form data. The validation class removes the need for all those
 * long input checks that you might regularly write for each form on your site.
 *
 * Note, this controller is only for demonstration purposes! Remove this file
 * before you put your site online!
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
class Controller_FormTest extends Controller {

	public function index()
	{

		//Load the validation library
		$this->validation = load::singleton('validation');
		
		// Disable form session token checking (not-recommended!)
		$this->validation->token = FALSE;

		$config = array();
		$config['name'] = 'required|to_string|alpha|name_callback';
		$config['age'] = 'required|to_string|numeric';
		$config['text'] = 'required|to_string';
		$config['email'] = 'required|to_string|valid_email';
		$config['min'] = 'required|to_string|min_length[5]';
		$config['exact'] = 'required|to_string|exact_length[5]';
		$config['max'] = 'to_string|max_length[5]';
		$config['empty'] = 'set|to_string';


		//If the form submit fails
		if($this->validation->run($config) === FALSE)
		{
			$this->views['content'] = load::view('formtest/form');
			return;
		}

		//Good job!
		$this->views['content'] = load::view('formtest/success');

	}


	/*
	 * Check to make sure the name is Admin!
	 */
	public function name_callback($field, $name)
	{
		if($name !== 'Admin')
		{
			$this->validation->errors[$field] = 'The name must be Admin!';
			return FALSE;
		}

		return TRUE;
	}


}