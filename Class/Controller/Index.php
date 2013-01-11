<?php
/**
 * Index Page
 *
 * Example index page.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2011 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
namespace Controller;

class Index extends \MyController
{
	public function run()
	{
		// Load database
		//$this->db = new DB(config('database'));

		// Set ORM database connection
		//ORM::$db = $this->db;

		// Load the theme sidebar since we don't need the full page
		$this->sidebar = new \Micro\View('Sidebar');

		// Load the welcome view
		$this->content = new \Micro\View('Index/Index');
	}
}
