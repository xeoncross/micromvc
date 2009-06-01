<?php

class form extends core {
	
	public function index() {
		
		//Load the validation library
		$this->load('validation');
		
		$config = array();
		$config['name'] = 'required|alpha|name_callback';
		$config['age'] = 'required|numeric';
		$config['text'] = 'required|trim';
		$config['email'] = 'required|valid_email';
		$config['min'] = 'required|min_length[5]';
		$config['max'] = 'required|max_length[5]';
		$config['exact'] = 'required|exact_length[5]';
		$config['empty'] = '';
		
		
		
		//If the form submit fails
		if($this->validation->run($config) == FALSE) {
			$this->data['content'] = $this->view('form/form');
			return;
		}
		
		//Good job!
		$this->data['content'] = 'Success!';
		print_pre($_POST);
		
		
	}


	/*
	 * Check to make sure the name is david!
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