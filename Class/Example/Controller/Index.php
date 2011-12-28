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
namespace Example\Controller;

class Index extends \Example\Controller
{
	public function run()
	{
		// Load the theme sidebar since we don't need the full page
		$this->sidebar = new \Micro\View('Example/Sidebar');

		// Load the welcome view
		$this->content = new \Micro\View('Example/Index');
	}
}
