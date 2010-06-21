<?php defined('SYSTEM_PATH') or die('No direct access');
/**
 * Configuration File
 *
 * This file contains the base site configuration for things like the database.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */


// Characters allowed in the URI ($_GET data) (plus ASCII letters and numbers)
$config['permitted_uri_chars'] = '~ %._/-';

// Language
$config['language'] = 'english';

// Should debug info be shown at the bottom of each page?
$config['debug_mode'] = TRUE;

// Show extra infomation like config and language values? (dangerous!)
$config['debug_mode_excessive'] = FALSE;

// Default controller to call
$config['default_controller'] = 'welcome';

// Default method to run
$config['default_method'] = 'index';

// Global Site Encoding
$config['encoding'] = 'utf-8';

// Auto-encode global variables ($_POST/$_GET)
$config['encode_globals'] = FALSE;

/*
 * Define Custom Routes
 *
 * Set the URI to match to an array containing the controller and
 * method to call in stead of the one given in the URI.
 *
 * If you set the route to FALSE instead of an array then that
 * URI will result in a 404 error. This is a handy way to block
 * access to controllers on a per-site bases.
 *
 * Do not include a starting or ending slash.
 */
$config['routes'] = array(
	//'controller/method' => array('controller', 'othermethod'),
);


/*
 * When a user logs in a second cookie should be created known as the "logged_in"
 * cookie. The presence of this cookie gives us a heads-up that the user is *probably*
 * logged in - and so we should NOT be showing (or generating) cached pages for them.
 *
 * Set value to the name of this cookie set by the auth library or FALSE to disable.
 */
$config['caching_check_cookie'] = 'logged_in';

// The location of the cache library to use (PHP > 5.3 use sqlite3)
$config['cache_library'] = 'modules/cache/classes/cache/sqlite3.php';

/**
 * Some cache divers need additional options which can be provided here.
 * For example, the memcache cache class needs the severs to connect too.
 * 
 * Here you can set the *default* life of cached objects (in seconds).
 * Setting this to FALSE will disable caching but controllers can still 
 * override this individually if needed!
 */
$config['cache'] = array(
	'expires' => 0
	//'servers' => array('localhost' => 11211),
	//'compress' => FALSE
);



/**
 * Cron Job
 * 
 * There are often tasks that need to be performed on a regular basis.
 * For example, cleaning the database or fetching recent RSS entries.
 * By hooking into the 'cron' run your site can automate tasks after 
 * rendering the page so you users see no performance hit.
 */

// Set the "1 in __ chance" of running or FALSE to disable cron
$config['cron'] = 0;

// If the user CPU usage is higher than this the cron will not run
$config['max_load_for_cron'] = 90;


/**
 * Error Logging
 * 
 * By default the Error class overrides the PHP error & exception handling.
 * This means that it will handle displaying errors and also stop PHP from 
 * logging errors in the php error_log. Therefore, we must log errors 
 * our self (for security/usability purposes).
 */
$config['log_errors'] = TRUE;



/**
 * Session Handling
 */
$config['session'] = array(
	// The name of the session
	'name'				=> 'mvc_session',
	// The name of the database instance to use
	'database'			=> 'default', 
	// Require user agent fingerprint to match?
	'match_fingerprint'	=> TRUE,
	// Require the users IP to match (dangerous!)
	'match_ip'			=> FALSE,
	// Change the session ID every ####(seconds) (FALSE to disable)
	'regenerate'		=> FALSE,
	// How long before the session expires (604800 = 7 days)
	'expiration'		=> 604800,
	// Chance (in 100) that old sessions will be removed
	// Probability that the garbage collection routine is started
	'gc_probability'	=> 1,
	// gc_probability in gc_divisor chance of removing old sessions (1 in 100)
	'gc_divisor'		=> 100,
	// Session ID to use instead of Cookie passed (for flash uploads...)
	'session_id'		=> NULL,
	// The path it is accessible
	'cookie_path'		=> '/',
	// The domain the cookie works on
	'cookie_domain'		=> NULL,
	// Should cookies only be sent over secure connections?
	'cookie_secure'		=> NULL,
	// Only accessible through the HTTP protocol? (PHP 5.2+)
	'cookie_httponly'	=> NULL,
);


/**
 * Database
 *
 * Here you can configure the settings for connecting to the database.
 */
$config['database'] = array(
	'default' => array(
		'dsn'        => 'mysql:host=localhost;dbname=database_1',
		'username'   => 'root',
		'password'   => '',
		'persistent' => FALSE,
	)
);


/**
 * API Keys and Secrets
 * 
 * Insert you API keys and other secrets here instead of creating
 * a new file. Use for Akismet, ReCaptcha, Twitter, and more!
 */

//$config['----_api_key'] = '...';



/*
 * Hooks
 *
 * The following hook will load the class "my_class" from the library
 * and then call $my_class->my_method() on the data passed to it.
 *
 * $config['hook_name'][] = array(
 *		'function'	=> 'my_method',
 *		'class'		=> 'my_class',
 *		'helper'	=> FALSE,
 *		'static'	=> FALSE
 * );
 *
 * Please note that if the class given does not yet exist in the scope
 * of the script, an attempt will be made to load it. If adding hooks 
 * at runtime, you can also pass objects instead of class names.
 *
 * Each class loaded by hook calls will use the load class to prevent
 * excess object creation and adhere to the singleton pattern.
 *
 * If a function is not defined yet, then the function file given will
 * be loaded from the correct functions folder using the helper name
 * given.
 * 
 * If 'static' is TRUE then the class method will be called statically.
 */

/*
 * Run on system startup
 */
$config['hooks']['system_startup'][] = array();

/*
 * Run to filter cache page before script exit
 */
$config['hooks']['system_shutdown_cache'][] = array();

/*
 * Run after the system is fully loaded
 */
$config['hooks']['system_loaded'][] = array();

/*
 * Run after the controller is loaded and before the method is called
 */
$config['hooks']['system_pre_method'][] = array();

/**
 * Run after the method is called, but before rendering page
 */
$config['hooks']['system_post_method'] = array();

/**
 * Run to filter final page output before script exit
 */
$config['hooks']['system_shutdown'] = array();



/*
 * Modules
 *
 * To enable a module please enter it's name here. The order they are
 * listed is the order they will be scanned for files.
 */
$config['modules'] = array(
	//'memcache',
	//'recaptcha',
	//'disqus',
	'database',
	'core',
);




/*
 * 
 * Additional server settings
 * 
 */


// Set the local used
setlocale(LC_ALL, 'en_US.utf-8');

/**
 * Gzip compress the page for faster transfers!
 * If you are unable to enable gzip in apache then uncomment
 * this line to enable it in PHP.
 */
//gzip_compression();


// Overload php.ini error reporting?
//error_reporting(E_ALL|E_STRICT);

/*
 * Set the server timezone
 * see: http://us3.php.net/manual/en/timezones.php
 */
date_default_timezone_set("America/Chicago");


/*
 * URL address paths
 */

// Absolute URL path to the site root. Default ("/")
define('SITE_URL', '/micromvc/');

// Absolute URL path to the themes directory
define('VIEW_URL', SITE_URL. DOMAIN_FOLDER. '/views/');

// Absolute URL path to the upload directory
define('UPLOAD_URL', SITE_URL. DOMAIN_FOLDER. '/uploads/');

// Absolute URL path to the shared javascript directory
define('JAVASCRIPT_URL', SITE_URL. 'js/');

// Absolute URL path to the modules directory
define('MODULE_URL', SITE_URL. 'modules/');

/*
 * File system paths
 */

//The file system path of the site's uploads folder
define('UPLOAD_PATH', SITE_PATH. 'uploads'. DS);

//The file system path of the site's cache folder
define('CACHE_PATH', SITE_PATH. 'cache'. DS);

//Define the base file system path to logs
define('LOG_PATH', SITE_PATH. 'logs'. DS);

