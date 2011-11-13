<?php
/**
 * MyController
 *
 * Basic DEMO outline for standard controllers
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2011 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
abstract class MyController extends \Core\Controller
{
	// Global view template
	public $template = 'Layout';

	/**
	 * Called after the controller is loaded, before the method
	 *
	 * @param string $method name
	 */
	public function initialize($method)
	{
		\Core\Session::start();
	}


	/**
	 * Load database connection
	 */
	public function load_database($name = 'database')
	{
		// Load database
		$db = new \Core\Database(config()->$name);

		// Set default ORM database connection
		if(empty(\Core\ORM::$db))
		{
			\Core\ORM::$db = $db;
		}

		return $db;
	}


	/**
	 * Show a 404 error page
	 */
	public function show_404()
	{
		headers_sent() OR header('HTTP/1.0 404 Page Not Found');
		$this->content = new \Core\View('404');
	}


	/**
	 * Save user session and render the final layout template
	 */
	public function send()
	{
		\Core\Session::save();

		headers_sent() OR header('Content-Type: text/html; charset=utf-8');

		$layout = new \Core\View($this->template);
		$layout->set((array) $this);
		print $layout;

		$layout = NULL;

		if(config()->debug_mode)
		{
			print new \Core\View('System/Debug');
		}
	}

}

// End
