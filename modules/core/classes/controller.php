<?php defined('SYSTEM_PATH') or die('No direct access');
/**
 * Controller
 *
 * This is the base class that all controllers extend
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
class Controller
{

	// Array of views for final site layout
	public $views			= array();
	// Name of final site layout file
	public $layout			= 'layout';
	// Type of content header to send (html/xml/json/etc..)
	public $content_type	= 'text/html'; //Danger: http://hixie.ch/advocacy/xhtml
	// Singleton instance object
	private static $instance;

	
	/**
	 * Basic setup on load
	 */
	public function __construct()
	{
		//Set singleton instance
		self::$instance = $this;

		//If this is an ajax request - auto-disable layout
		if(AJAX_REQUEST)
		{
			$this->layout = FALSE;
		}

		// The shutdown handler catches E_FATAL errors
		register_shutdown_function(array('controller', 'shutdown_handler'));
	}


	/**
	 * After the the class method has run there are often leftover
	 * controller objects we can unset to save memory.
	 */
	protected function memory_cleanup()
	{
		foreach($this as $key => $value)
		{
			if(is_object($value))
			{
				// Remove this object
				unset($this->$key);
				
				// Remove the singleton also
				load::remove($key);
			}
		}
	}
	
	
	/**
	 * Load and initialize the database connection
	 */
	public function load_database($name = 'default')
	{
		// Load the configuration for this database
		$config = config::get('database');

		// Fetch this instance
		return Database::instance($name, $config[$name]);
	}


	/**
	 * On close, show the output inside our layout template
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	public function render()
	{
		
		// If a content type is given AND headers haven't been sent
		if($this->content_type AND ! headers_sent())
		{
			// let the useragent know what type of content this is
			header('Content-Type: '.$this->content_type.'; charset=utf-8');
		}
		
		// If a layout file is set, then load it as the FINAL view
		if($this->layout)
		{
			$output = load::view($this->layout, $this->views);
		}
		else //no final view (i.e. RSS/TXT/JSON/etc.)
		{
			$output = $this->views['content'];
		}
		
		//Call the shutdown hook (to allow modifications)
		$output = hook::call('system_shutdown', $output);
		
		//If cookie checking is disabled (or the cookie is not found)
		if( ! config::get('caching_check_cookie') OR empty($_COOKIE[config::get('caching_check_cookie')]) )
		{
			// Create a unique name for this page
			$hash = 'routes::get_uri()'. routes::get_uri(). AJAX_REQUEST;
			
			// Cache the output (with content_type)
			cache::set($hash, $this->content_type.'::'.$output);
		}

		// Finally show the user the page!
		print $output;
		
		// Free memory
		unset($output, $this->views);
		
		// Run a cron (if needed)
		$this->cron();

	}


	/**
	 * Run a "cron" job. This is an easy way to do background tasks
	 * such as database cleanup or RSS fetching.
	 */
	public function cron()
	{
		// If cron jobs are enabled - randomly run one!
		if(config::get('cron') AND rand(1, config::get('cron')) === 1)
		{
			// Only on linux and PHP > 5.1.3
			if(function_exists('sys_getloadavg'))
			{
				// Make sure there is no heavy load on the server
				$load = sys_getloadavg();
				
				// If the load of the last 5 minutes is higher than our max load allowed
				if($load[1] > config('max_load_for_cron'))
					return;
			}
			
			// It is save to run a cron job
			hook::call('cron');
		}
	}
	
	
	/**
	 * Load a simple error page
	 */
	protected function show_error($message = NULL)
	{
		$this->views['content']	= load::error($message,NULL,400);
	}
	
	
	/**
	 * Load a simple "404 Not Found" page
	 */
	public function show_404()
	{
		$this->views['content']	= load::error(NULL,NULL,404);
	}
	
	
	/**
	 * Load a simple "400 Bad Request" page
	 */
	public function show_400()
	{
		$this->views['content']	= load::error(NULL,NULL,400);
	}
	
	
	/**
	 * Return this classes instance
	 * @return singleton
	 */
	public static function get_instance()
	{
		return self::$instance;
	}


	/*
	 * Wrappers to prevent loading of Error class before it is needed
	 * and to allow the controller to implement it's own error handling
	 */
	public static function error_handler($code, $error, $file = NULL, $line = NULL)
	{
		return Error::handler($code, $error, $file, $line);
	}
	
	public static function exception_handler(Exception $e)
	{
		return Error::exception_handler($e);
	}


	/**
	 * Catches fatal errors
	 * @author KohanaPHP
	 */
	public static function shutdown_handler()
	{
		// Cache any Fatal Errors
		if ($error = error_get_last())
		{
			// Fake an exception for nice debugging
			self::exception_handler(new ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']));
		}

		// Save any file path changes
		load::shutdown();
		
		// Load the debug view (only if a layout file was used!)
		if(config('debug_mode') AND self::$instance->layout)
		{
			load::view('debug', NULL, NULL);
		}
	}


} // End controller class

