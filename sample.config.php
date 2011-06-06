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

// Base site url
$config['site_url'] = '/';

// Enable debug mode?
$config['debug_mode'] = TRUE;

// Current theme
$config['theme'] = 'theme';

// Load init file?
$config['init'] = FALSE;

// Path to log directory
$config['log_path'] = 'system/log/';

// Default language file
$config['language'] = 'en';

/**
 * Database
 *
 * This system uses PDO to connect to MySQL, SQLite, or PostgreSQL.
 * Visit http://us3.php.net/manual/en/pdo.drivers.php for more info.
 */
$config['database'] = array(

	// MySQL
	'dns' => "mysql:host=127.0.0.1;port=3306;dbname=micromvc",
	'username' => 'root',
	'password' => '',

	// PostgreSQL
	//'dns' => "pgsql:host=localhost;port=5432;dbname=micromvc",
	//'username' => 'postgres',
	//'password' => '',

	'params' => array()
);

/**
 * URL Routing
 *
 * URLs are very important to the future usability of your site. Take
 * time to think about your structure in a way that is meaningful. Place
 * your most common page routes at the top for better performace.
 *
 * - Routes are matched from left-to-right.
 * - Regex can also be used to define routes if enclosed in "/.../"
 * - Each regex catch pattern (...) will be viewed as a parameter.
 * - The remaning (unmached) URL path will be passed as parameters.
 *
 ** Simple Example **
 * URL Path:	/forum/topic/view/45/Hello-World
 * Route:		"forum/topic/view" => 'Forum_Controller_Forum_View'
 * Result:		$Forum_Controller_Forum_View->action('45', 'Hello-World');
 *
 ** Regex Example **
 * URL Path:	/John_Doe4/recent/comments/3
 * Route:		"/^(\w+)/recent/comments/' => 'Comments_Controller_Recent'
 * Result:		$Comments_Controller_Recent->action($username = 'John_Doe4', $page = 3)
 */
$config['routes'] = array(
	''					=> 'Example_Controller_Index',
	'404'				=> 'Example_Controller_404',

	// Example Module
	'example/school'	=> 'Example_Controller_School',
	'example/form'		=> 'Example_Controller_Form',
	'example/upload'	=> 'Example_Controller_Upload',

	// Unit Tests
	'unittest'	=> 'Unittest_Controller_Index',

);

/**
 * System Events
 */
$config['events'] = array(
	//'pre_controller'	=> 'Class::method',
	//'post_controller'	=> 'Class::method',
);

/**
 * Cookie Handling
 *
 * To insure your cookies are secure, please choose a long, random key!
 * @link http://php.net/setcookie
 */
$config['cookie'] = array(
	'key' => 'very-secret-key',
	'timeout' => time()+(60*60*4), // Ignore submitted cookies older than 4 hours
	'expires' => 0, // Expire on browser close
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

//$config['XXX_api_key'] = '...';

return $config;
