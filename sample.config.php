<?php
/**
 * Config
 *
 * Core system configuration file
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
$config['index'] = 'welcome/index';
$config['site_url'] = '/';
$config['debug_mode'] = TRUE;
$config['theme'] = 'default';
$config['cookie_salt'] = 'something-really-really-complex-and-long';
$config['init'] = FALSE;

/**
 * Database
 * 
 * This system uses PDO to connect to MySQL, SQLite, or PostgreSQL.
 * Visit http://us3.php.net/manual/en/pdo.drivers.php for more info.
 */
$config['database'] = array(
	'default' => array(
		'dns' => "mysql:dbname=test;host=127.0.0.1;port=3306",
		'username' => 'root',
		'password' => '',
		'params' => array()
	),
);

// Modules loaded
$config['modules'] = array(
	'example',
);

/**
 * URL Routing
 * 
 * Regex can also be used to define routes
 */
$config['routes'] = array(
	//'welcome' => 'controller/show_404' // Or hide pages
);

/**
 * Session Handling
 * 
 * The two main values are timeout (server-side) and expire (client-side). Since
 * clients are not trustworth we must enforce a server-side timeout to insure
 * old sessions are not re-used.
 * 
 * @link http://php.net/setcookie
 */
$config['session'] = array(
	'name'		=> 'session',
	'timeout'	=> 3600,	// Max time in seconds between page requests
	'expire'	=> 0,		// Cookie life in seconds (0 = deleted on browser close)
	'path'		=> '/',
	'domain'	=> '',
	'secure'	=> FALSE,
	'httponly'	=> TRUE,
);



/**
 * API Keys and Secrets
 * 
 * Insert you API keys and other secrets here instead of creating
 * a new file. Use for Akismet, ReCaptcha, Twitter, and more!
 */

//$config['api_key'] = '...';


return $config;
