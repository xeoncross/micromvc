<?php
/**
 * HTML Form
 *
 * Sample using the validation library and form builder
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
namespace Example\Controller;

class Form extends \Core\Controller
{
	public function action()
	{
		// Load the theme sidebar since we don't need the full page
		$this->sidebar = new \Core\View('Sidebar');

		// Validation rules for the input fields
		$fields = array(
			'username' => 'required|plaintext|max_length[50]',
			'password' => 'required|plaintext|min_length[8]',
			'bio' => 'required|plaintext|max_length[500]',
			'gender' => 'required|plaintext'
		);

		$validation = new \Core\Validation($_POST, $fields);

		// If they submitted the form correctly then we are done
		if($validation->validates())
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

		$form = new \Core\Form($validation, array('class' => 'formstyle'));
		$form->fields($fields);

		$this->content = $form;
	}
}
