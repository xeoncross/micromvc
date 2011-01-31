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

// Default controller to run
$config['index'] = 'example/index';

// Base site url
$config['site_url'] = '/';

// Enable debug mode?
$config['debug_mode'] = TRUE;

// Current theme
$config['theme'] = 'theme';

// Load init file?
$config['init'] = FALSE;

// Path to log directory
$config['log_path'] = 'log/';

// Default language file
$config['language'] = 'en';

/**
 * Database
 * 
 * This system uses PDO to connect to MySQL, SQLite, or PostgreSQL.
 * Visit http://us3.php.net/manual/en/pdo.drivers.php for more info.
 */
$config['database'] = array(
	'default' => array(
		'dns' => "mysql:dbname=swiftlogin;host=127.0.0.1;port=3306",
		'username' => 'root',
		'password' => '',
		'params' => array()
	),
);

// Disabled modules
$config['disabled_modules'] = array(
	'unittest',
);

/**
 * URL Routing
 * 
 * Regex can also be used to define routes
 */
$config['routes'] = array(
	//'page/name' => 'error/404' // Or hide pages
);

/**
 * System Events
 */
$config['events'] = array(
	'post_controller' => 'Theme_Class::render',
);

/**
 * Cookie Handling
 * 
 * To insure your cookies are secure, please choose a long, random key!
 * @link http://php.net/setcookie
 */
$config['cookie'] = array(
	'key' => 'key',
	'expires' => time()+(60*60*4), // 4 hour cookie
	'path' => '/',
	'domain' => '',
	'secure' => '',
	'httponly' => '',
);


/**
 * API Keys and Secrets
 * 
 * Insert you API keys and other secrets here.
 * Use for Akismet, ReCaptcha, Facebook, and more!
 */

//$config['-----_api_key'] = '...';

return $config;
