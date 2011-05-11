<?php if(PHP_SAPI!=='cli')die();
/**
 * CLI
 *
 * This file is the command-line interface (CLI) entry point for the system
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */

// System Start Time
define('START_TIME', microtime(true));

// System Start Memory
define('START_MEMORY_USAGE', memory_get_usage());

// Extension of all PHP files
define('EXT', '.php');

// Absolute path to the system folder
define('SP', realpath(dirname(__FILE__)). '/');

// Are we using windows?
define('WINDOWS', strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');

// Include bootstrap
require('bootstrap.php');

//Is this an AJAX request?
define('AJAX_REQUEST', 0);

// Custom init script?
if(config('init')) require('init.php');

// Require a CLI path
if(empty($argv[1])) die("Please enter a path to the CLI file.\nExample: " . colorize('php cli.php module/cli/file.php', 'blue') . "\n");

// Build path to file
$file = SP . str_replace(EXT, '', trim($argv[1], '/')) . EXT;

// Does the file exist?
if(!is_file($file)) die("Please enter a valid file path");

// Require a valid, safe path
if(!preg_match('/^[\w\-~\/\.+]{1,600}/', $argv[1])) die("Invalid path given\n");

/**
 * Color output text for the CLI
 *
 * @param string $text to color
 * @param string $color of text
 * @param string $background color
 */
function colorize($text, $color, $bold = FALSE)
{
	// Standard CLI colors
	$colors = array_flip(array(30 => 'gray', 'red', 'green', 'yellow', 'blue', 'purple', 'cyan', 'white', 'black'));

	// Escape string with color information
	return"\033[" . ($bold ? '1' : '0') . ';' . $colors[$color] . "m$text\033[0m";
}

// Color any error messages so they stand out
//set_exception_handler(function($e){print colorize("\nException: ".$e->getMessage()."\n".$e->getFile()." on line ".$e->getLine()."\n",'red',TRUE); return FALSE;});
set_error_handler(function($c,$e,$f=0,$l=0){print colorize("\n$e\n$f on line $l\n\n",'red',TRUE);return FALSE;});

require($file);

// End
