<?php
/**
 * Form Page
 *
 * Example HTML form.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2011 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
namespace Controller;

class Form extends \MyController
{
	public function run()
	{
		// Load the theme sidebar since we don't need the full page
		$this->sidebar = new \Micro\View('Sidebar');

		/**
		 * A validation object is setup
		 */
		$validation = new \Micro\Validation($_POST);

		$validation->field('email')
			->required('Please enter an email')
			->email('Must be a valid email');

		$validation->field('password')->required('Please enter a password');

		// Bio is optional, but if given it must be text
		$validation->field('bio')
			->max('Bio cannot be longer than 200 characters', 200)
			->plaintext('Special characters are not allowed');

		// Only two options allowed
		$validation->field('gender')->options('Please select an option', array('m', 'f'));

		/**
		 * A new form object is setup (which uses the validation)
		 */
		$form = new \Micro\Form($validation);

		// Create some form fields
		$form->email
			->wrap('p')
			->label('email');

		$form->password
			->wrap('p')
			->label('password')
			->attributes(array('type' => 'password'));

		$form->bio
			->wrap('div')
			->label('Bio Text')
			->textarea();

		$form->gender
			->wrap('p')
			->label('Select Gender')
			->select(array('m' => 'Male', 'f' => 'Female'));

		/**
		 * Load a view which just prints the form
		 */

		// Load the HTML form
		$view = new \Micro\View('Form/Index');
		$view->set(array('form' => $form));

		$this->content = $view;
	}
}
