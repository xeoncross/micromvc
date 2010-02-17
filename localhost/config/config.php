<?php

// Characters allowed in the URI string ($_GET data) other than US letters and numbers
$config['permitted_uri_chars'] = '~ %.:_/-';

// Language
$config['language'] = 'english';

// Should debug info be shown at the bottom of each page?
$config['debug_mode'] = 0;

// Default controller to call
$config['default_controller'] = 'welcome';

// Default method to run
$config['default_method'] = 'index';

// Global Site Encoding
$config['encoding'] = 'utf-8';

// Audo-encode global variables ($_POST/$_GET)
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
	'bible/(?!(add)|(tag)|(search))' => array('bible', 'index'),
);


/*
 * When a user logs in a second cookie should be created known as the "logged_in"
 * cookie. The presence of this cookie gives us a heads-up that the user is *probably*
 * logged in - and so we should NOT be showing (or generating) cached pages for them.
 *
 * Set value to the name of this cookie set by the auth library or FALSE to disable.
 */
$config['caching_check_cookie'] = 'logged_in';


/**
 * Cache Life
 *
 * Here you can enable or disable the *default* setting for caching.
 * Set this to the number in seconds you want the cache to last 
 * (120 = 2 minutes). Setting this to FALSE will disable caching. 
 * Controllers can still override this individually if needed!
 */
$config['cache_life'] = 0;

/**
 * Some cache divers need additional options which can be provided here.
 * For example, the memcache class needs the severs to connect too.
 */
$config['cache_options'] = array(
	'servers' => array('localhost' => 11211),
	'compress' => FALSE
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
 * ourself (for security/usability perposes).
 */
$config['log_errors'] = TRUE;



/**
 * Session Handling
 */
$config['session'] = array(
	// The name of the session
	'name'				=> 'mvc_session',
	// Session storage class, FALSE for native php temp files
	'handler'			=> FALSE, //'session_handler_db',
	// If using a DB, what is the table name?
	'table'				=> 'sessions',
	// Require user agent fingerprint to match?
	'match_fingerprint'	=> TRUE,
	// Require the users IP to match (dangerous!)
	'match_ip'			=> FALSE,
	// Update the session ID every two hours (FALSE to disable)
	'regenerate'		=> 7200,
	// How long before the session expires (604800 = 7 days)
	'expiration'		=> 604800,
	// Chance (in 100) that old sessions will be removed
	'gc_probability'	=> 100,
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
	'default2' => array(
		'type'       => 'mysql',
		'connection' => array(
			'dsn'        => 'mysql:host=localhost;dbname=pdorm',
			'username'   => 'root',
			'password'   => '',
			'persistent' => FALSE,
		),
		'table_prefix'		=> '',
		'charset'			=> 'utf8',
		'cache_results'		=> FALSE,
		'cache_statements'	=> TRUE,
		'log_queries'		=> TRUE
	),
	'default' => array(
		'type'       => 'mysql',
		'connection' => array(
			'dsn'        => 'mysql:host=localhost;dbname=1jn2',
			'username'   => 'root',
			'password'   => '',
			'persistent' => FALSE,
		),
		'table_prefix'		=> '',
		'charset'			=> 'utf8',
		'cache_results'		=> 0,
		'cache_statements'	=> TRUE,
		'log_queries'		=> TRUE
	),
);


/**
 * API Keys and Secrets
 * 
 * Insert you API keys and other secrets here instead of creating
 * a new file. Use for Akismet, ReCaptcha, Twitter, and more!
 */

//$config['####_api_key'] = '...';


