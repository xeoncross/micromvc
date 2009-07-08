<?php
/**
 * Form
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
 * @copyright	Copyright (c) 2009 MicroMVC
 * @license		http://www.gnu.org/licenses/gpl-3.0.html
 * @link		http://micromvc.com
 * @version		1.1.0 <7/7/2009>
 ********************************** 80 Columns *********************************
 */
class form extends controller {

	public function index() {

		//Load the validation library
		$this->library('validation');

		$config = array();
		$config['name'] = 'required|alpha|name_callback';
		$config['age'] = 'required|numeric';
		$config['text'] = 'required|trim';
		$config['email'] = 'required|valid_email';
		$config['min'] = 'required|min_length[5]';
		$config['exact'] = 'required|exact_length[5]';
		$config['max'] = 'max_length[5]';
		$config['empty'] = '';


		//If the form submit fails
		if($this->validation->run($config) == FALSE) {
			$this->views['content'] = $this->view('form/form');
			return;
		}

		//Good job!
		$this->views['content'] = $this->view('form/success');

	}


	/*
	 * Check to make sure the name is David!
	 */
	public function name_callback($field, $data) {
		if($data != 'David') {
			$this->validation->errors[$field] = 'The name must be David!';
			return FALSE;
		}

		return TRUE;
	}


}
?>