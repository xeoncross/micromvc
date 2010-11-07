<?php
/**
 * Welcome Controller
 *
 * Shows several examples of using system libraries and rendering pages.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	Copyright (c) 2009 MicroMVC
 * @license		http://www.gnu.org/licenses/gpl-3.0.html
 * @link		http://micromvc.com
 * @version		1.1.0 <7/7/2009>
 ********************************** 80 Columns *********************************
 */
class Controller_Welcome extends Controller
{

	// Since all pages will use it...
	public function __construct()
	{
		// Load global theme sidebar
		$this->sidebar = new View('sidebar', FALSE);
	}
	
	
	/*
	 * Load a view that shows a welcome message
	 */
	function index()
	{
		// Load the welcome view
		$this->content = new View('welcome', 'example');
	}
	
	/**
	 * Create a simple form
	 */
	function form()
	{
		$this->css[] = module_url('formstyle.css');
		
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
	
	
	/**
	 * Show a simple upload form (dangerous)
	 */
	public function upload()
	{
		// Check to see if we are uploading a new file
		if($_FILES AND !empty($_FILES['userfile']))
		{
			if($file = upload::file($_FILES['userfile'], SP.'uploads/'))
			{
				message('message', '<a href="/uploads/'. $file. '">'. $file. '</a> Uploaded!');
			}
		}
		
		// Load form view ( or you can use the Form class like above )
		$this->content = new View('upload', 'example');
		
		// Get all current files
		$this->content->files = dir::contents(SP.'uploads/');
	}
	
	
	/**
	 * Trigger a simple error
	 */
	public function error()
	{
		trigger_error('Oops! You seem to have caused a problem!');
		$this->content = '';
	}
	
	
	/**
	 * Trigger an uncaught exception
	 */
	public function exception()
	{
		throw new Exception('Not a flying toy');
		$this->content = '';
	}
	
}