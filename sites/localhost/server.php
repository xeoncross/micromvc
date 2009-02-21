<?php
/**
 * PHP Server Config
 */

/**
 * Enable or Disable caching for this site
 *
 * Set to FALSE to disable caching
 * Set to a number (in seconds) to enable:
 * i.e. 60 * 2 = 2 minutes
 */
define('CACHING', FALSE);

//Enable error reporting?
error_reporting(E_ALL);

//Should Debug info be shown with errors and such? (true or false)
define('DEBUG_MODE', true);




/* 
 * FILE SYSTEM PATHS
 */

// Absolute file system path to the root
define('SITE_DIR', rtrim(realpath(dirname(__FILE__). "/../../"), '/\\'). '/');

// Absolute file system path to the themes directory
define('THEME_DIR', SITE_DIR. 'themes/'. $config['theme']. '/');

// Absolute file system path to /includes
define('INCLUDES_DIR', SITE_DIR. "includes/");

// Absolute file system path to /includes
define('LIBRARIES_DIR', SITE_DIR. "libraries/");

// Absolute file system path to /includes
define('MODELS_DIR', SITE_DIR. "models/");

// Absolute file system path to /includes
define('FUNCTIONS_DIR', SITE_DIR. "functions/");

//The file system path of the upload dir
define('UPLOAD_DIR', SITE_DIR. 'uploads/');

//The file system path of the cache dir
define('CACHE_DIR', SITE_DIR. 'cache/');


/* 
 * URL ADDRESS PATHS
 */

// Absolute URL path to the system root
// Leave blank unless this site is in a subfolder.
define('SITE_PATH', '/MicroMVC/');

// Absolute URL path to the themes directory
define('THEME_PATH', SITE_PATH. 'themes/'. $config['theme']. '/');

// Absolute URL path to the upload directory
define('UPLOAD_PATH', SITE_PATH. 'uploads/');

// Absolute URL path to the cache directory
define('CACHE_PATH', SITE_PATH. 'cache/');



/*
 * Advanced Options
 */

//Set the server timezone
date_default_timezone_set("America/Chicago");
//http://us3.php.net/manual/en/timezones.php

//Show errors to the user?
//ini_set('display_errors', true);

//Maximum life of sessions in seconds
//PHP's default is "1440" (24min)
//ini_set("session.gc_maxlifetime", (2*60*60)); //2 hours

//Maximum time in seconds that PHP is allowed to run. Only change if you
//think that you will be doing BIG tasks like uploading 200MB files or
//creating large image files with PHP. //PHP's Default is 30 seconds.
//ini_set("max_execution_time", 30);

//Maximum memory that one PHP script is allowed to use in megabytes (M)
//Only set if you get "Error: Allowed memory size of XXX bytes exhausted"
//PHP's Default is 128M.
//ini_set("memory_limit", "128M");

//Maximum file size that can be uploaded in bytes
//PHP's default is 2048b (2 Megabytes)
//YOU MUST CHANGE IT IN "php.ini" as you can't access it directly
//ini_set("upload_max_filesize", "128M"); //128Mb

//Maximum size that $_POST is allowed to be
//PHP's Default is "8M"
//ini_set("post_max_size", (128*1024*1024));

