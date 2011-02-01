<?php
// Sample HTML form
class Example_Controller_Form extends Controller
{
	public function action()
	{
		// Load the theme sidebar since we don't need the full page
		$this->sidebar = new View('sidebar');
		
		// Validation rules for the input fields
		$rules = array(
			'username' => 'required|string|max_length[50]',
			'password' => 'required|string|min_length[8]',
			'bio' => 'required|string|max_length[500]',
			'gender' => 'required|string'
		);
		
		$validation = new Validation();
		
		// If they submitted the form correctly then we are done
		if($validation->run($rules))
		{
			$this->content = '<h1>Success!</h1>';
			return;
		}
		
		// Form fields to create
		$fields = array(
			'username' => array(),
			'password' => array('type' => 'password'),
			'gender' => array('type'=> 'select', 'options' => array('m' => 'Male', 'f' => 'Female', 'bbq' => 'BBQ')),
			'bio' => array('type'=> 'textarea'),
			'submit' => array('type' => 'submit', 'value' => 'Submit')
		);
		
		$form = new Form($validation, array('class' => 'formstyle'));
		$form->fields($fields);
		
		$this->content = $form;
	}
}