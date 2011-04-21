<?php
/**
 * Error & Exception
 *
 * Provides global error and exception handling with detailed backtraces . 
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc . com/license
 ********************************** 80 Columns *********************************
 */
class Error
{

public static $found = FALSE;


public static function header()
{
	headers_sent() OR header('HTTP/1.0 500 Internal Server Error');
}

public static function fatal()
{
	if($e = error_get_last())
	{
		Error::exception(new ErrorException($e['message'], $e['type'], 0, $e['file'], $e['line']));
	}
}

public static function handler($code, $error, $file = 0, $line = 0)
{
	// Ignore errors less than the current error setting
	if((error_reporting() & $code) === 0) return TRUE;
	
	self::$found = 1;
	self::header();
	
	$view = new View('error', 'system');
	$view->error = $error;
	$view->title = lang($code);
	print $view;
	
	log_message("[$code] $error [$file] ($line)");
	return TRUE;
}


public static function exception(Exception $e)
{
	self::$found = 1;
	
	// If the view fails, at least we can print this message!
	$message = "{$e->getMessage()} [{$e->getFile()}] ({$e->getLine()})";
	
	try
	{
		log_message($message);
		self::header();
		
		$view = new View('exception', 'system');
		$view->exception = $e;
		
		print $view;
	}
	catch(Exception $e)
	{
		print $message;
	}
	
	exit(1);
}


/**
 * Fetch and HTML highlight serveral lines of a file . 
 *
 * @param string $f the file to open
 * @param integer $n the line number to highlight
 * @param integer $p the number of padding lines on both side
 * @return string
*/
public static function source($file, $number, $padding = 5)
{
	// Get lines from file
	$lines = array_slice(file($file), $number-$padding-1, $padding*2+1, 1);
	
	$html = '';
	foreach($lines as $i => $line)
	{
		$html .= '<b>' . sprintf('%' . strlen($number + $padding) . 'd', $i + 1) . '</b> '
			. ($i + 1 == $number ? '<em>' . h($line) . '</em>' : h($line));
	}
	return $html;
}


/**
 * Fetch a backtrace of the code
 *
 * @param int $o offset to start from
 * @param int $l limit of levels to collect
 * @return array
 */
public static function backtrace($offset, $limit = 5)
{
	$trace = array_slice(debug_backtrace(), $offset, $limit);
	
	foreach($trace as $i => &$v)
	{
		if(!isset($v['file']))
		{
			unset($trace[$i]);
			continue;
		}
		$v['source'] = self::source($v['file'], $v['line']);
	}
	
	return $trace;
}

}

// END
