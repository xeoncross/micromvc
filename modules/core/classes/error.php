<?php defined('SYSTEM_PATH') or die('No direct access');
/**
 * Error
 *
 * The custom Error and Exception handler used instead of the native PHP handler
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
class Error {

	// On error, should the script exit() or keep running?
	public static $is_fatal = FALSE;

	// PHP error constants
	public static $error_codes = array(
		E_ERROR				=> 'Error',
		E_WARNING			=> 'Warning',
		E_PARSE				=> 'Parsing Error',
		E_NOTICE			=> 'Notice',
		E_CORE_ERROR		=> 'Core Error',
		E_CORE_WARNING		=> 'Core Warning',
		E_COMPILE_ERROR		=> 'Compile Error',
		E_COMPILE_WARNING	=> 'Compile Warning',
		E_USER_ERROR		=> 'User Error',
		E_USER_WARNING		=> 'User Warning',
		E_USER_NOTICE		=> 'User Notice',
		E_STRICT			=> 'Runtime Notice',
		//E_RECOVERABLE_ERROR => 'Recoverable Error',	PHP 5.2.0
		//E_DEPRECATED		=> 'Deprecated Code',		PHP 5.3.0
		//E_USER_DEPRECATED	=> 'Deprecated Code',		PHP 5.3.0
	);
	
	
	/**
	 * Display all PHP errors (not suppressed by PHP) to the user.
	 * If debug_mode is enabled, a backtrace will also be shown.
	 * If $is_fatal is enabled, the script will exit.
	 *
	 * @param int $code
	 * @param string $error
	 * @param string $file
	 * @param int $line
	 * @return bool
	 */
	public static function handler($code, $error, $file = NULL, $line = NULL)
	{
		// If this error is suppressed by current error reporting settings
		if ((error_reporting() & $code) === 0)
		{
			// Do not execute the PHP error handler
			return TRUE;
		}
		
		// Remove system path
		$file = mb_substr($file, mb_strlen(SYSTEM_PATH));

		// Setup backtrace array
		$backtrace = array();
		
		// Show a backtrace (if in development mode)
		if(config::get('debug_mode'))
		{
			// Get backtrace
			if($backtrace = backtrace(5))
			{
				// Remove the first element (this function)
				array_shift($backtrace);
				
				// Format the backtrace
				$backtrace = self::backtrace($backtrace);
			}
			
		}
	
		// If we should also log the error
		if(config('log_errors'))
		{
			log_message($error.' in file '.$file.' on line '.$line);
		}
		
		// Set view data
		$data = array(
			'title'		=> self::$error_codes[$code],
			'message'	=> $error,
			'backtrace'	=> $backtrace,
			'line'		=> $line,
			'file'		=> $file
		);

		// Load an error
		print load::error($data, NULL, 500);
		
		// If we should exit the script on error
		if(self::$is_fatal)
		{
			// Clean the output buffer(s) if one exists
			if(ob_get_level())
			{
				while (ob_get_level())
				{
					ob_end_flush();
				}
			}
				
			// Exit with an error status
			exit(1);
		}
		
		// Do not execute the PHP error handler
		return TRUE;
	}


	/**
	 * Display exception errors along with as much supporting information as
	 * posible (only if in "debug_mode") and then kill the script.
	 *
	 * @param object $e the Exception
	 */
	public static function exception_handler(Exception $e) {

		//print dump($e); For tricky exceptions!
		
		// If we should also log the error
		if(config('log_errors'))
		{
			log_message($e->getMessage().' in file '.$e->getFile().' on line '.$e->getLine());
		}
		
		// Something might fail - even in here!
		try {

			// Get exception information
			$title		= get_class($e);
			$code		= $e->getCode();
			$message	= $e->getMessage();
			$file		= $e->getFile();
			$line		= $e->getLine();
			$backtrace	= array();

			//Get Human-friendly error title
			if(isset(self::$error_codes[$code]))
			{
				$title = self::$error_codes[$code];
			}

			//Remove system path
			$file = mb_substr($file, mb_strlen(SYSTEM_PATH));

			//Show a backtrace (if in development mode)
			if(config::get('debug_mode'))
			{

				//Get backtrace
				$backtrace = $e->getTrace();

				if($backtrace)
				{
					//If this use to be an error...
					if ($e instanceof ErrorException)
					{
						// Remove the fist element (controller::*_handler())
						array_shift($backtrace);

						// Workaround for a bug in ErrorException::getTrace() that exists in
						// all PHP 5.2 versions. @see http://bugs.php.net/bug.php?id=45895
						// @author KohanaPHP.com
						if (version_compare(PHP_VERSION, '5.3', '<'))
						{
							for ($i = count($backtrace) - 1; $i > 0; --$i)
							{
								if (isset($backtrace[$i - 1]['args']))
								{
									// Re-position the args
									$backtrace[$i]['args'] = $backtrace[$i - 1]['args'];

									// Remove the args
									unset($backtrace[$i - 1]['args']);
								}
							}
						}

					}

					// Clean and filter the backtrace
					$backtrace = self::backtrace($backtrace);
				}
			}

			// Set view data
			$data = array(
				'title'		=> $title,
				'message'	=> $message,
				'backtrace'	=> $backtrace,
				'line'		=> $line,
				'file'		=> $file
			);

			// Load an error
			print load::error($data, NULL, 500);

		} catch (Exception $e) {

			print '<h2>Error while in Exception!</h2>';
			print $e->getMessage(). ' on line '. $e->getLine(). ' in '. $e->getFile();

		}

		// Clean the output buffer(s) if one exists
		if(ob_get_level())
		{
			while (ob_get_level())
			{
				ob_end_flush();
			}
		}

		// Exit with an error status
		exit(1);
	}


	/**
	 * Returns an HTML string highlighting a specific line of a file, with some
	 * number of lines padded above and below and optionally code highlighted.
	 *
	 * @author	KohanaPHP
	 * @param   string   file to open
	 * @param   integer  line number to highlight
	 * @param   integer  number of padding lines
	 * @param	boolean  highlight the file code?
	 * @return  string
	 */
	public static function debug_source($file, $line_number, $padding = 5, $highlight = TRUE)
	{
		// Open the file and set the line position
		$file = fopen($file, 'r');
		$line = 0;

		// Set the reading range
		$start = $line_number - $padding;
		$end = $line_number + $padding;

		// Set the zero-padding amount for line numbers
		$format = '% ' . strlen($end) . 'd';

		$source = '';
		while (($row = rtrim(fgets($file))) !== FALSE) {

			// Increment the line number
			if (++$line > $end) {
				break;
			}

			if ($line >= $start) {

				if( $highlight && function_exists('highlight_code') ) {

					// Highlight the code on this line
					$row = highlight_code($row, FALSE, FALSE);

					//Remove the <code> tag and any newlines
					$row = str_replace(array("\r", "\n", '<code>', '</code>', '<br />'), '', $row);
				}

				// Add the line number to row
				$row = '<span class="number">'.sprintf($format, $line).'</span> '. $row;

				// Highlight this row if it is the one we want
				if ($line === $line_number) {
					$row = '<span class="highlight_line">'. $row. '</span>';
				}

				// Add to the captured source
				$source .= $row. "\n";
			}
		}

		// Close the file
		fclose($file);

		return $source;
	}


	/**
	 * Filters the backtrace to make it ready to present to the user.
	 *
	 * @param array $backtrace the backtrace array
	 * @return array
	 */
	public static function backtrace(array $backtrace)
	{
		//Max backtrace levels
		if(count($backtrace) > 6)
		{
			$backtrace = array_chunk($backtrace, 6, TRUE);
			$backtrace = $backtrace[0];
		}

		// The final backtrace array
		$trace = array();

		// start backtrace
		foreach ($backtrace as $v)
		{

			//If any of these are missing then skip
			if ( ! isset($v['function'], $v['file'], $v['line']))
				continue;

			$v['source'] = '';

			if ( ! isset($v['class']))
			{
				$v['class'] = NULL;
			}
			
			if ( ! isset($v['type']))
			{
				$v['type'] = '::';
			}
			
			//Also get the source of the file
			if(file_exists($v['file']))
			{
				$v['source'] = self::debug_source($v['file'], $v['line']);
			}

			//Remove system path
			$v['file'] = mb_substr($v['file'], mb_strlen(SYSTEM_PATH));

			$args = array();
			if(isset($v['args']))
			{
				foreach ($v['args'] as $a)
				{
					$type = gettype($a);

					if($type == 'integer' OR $type == 'double'){
						$args[] = $a;
							
					} elseif ($type == 'string') {
						//Longer than 25 chars?
						$a = mb_strlen($a) > 50 ? mb_substr($a, 0, 50). '...' : $a;
						$args[] = '"' . htmlspecialchars($a, ENT_QUOTES, 'UTF-8') . '"';

					} elseif ($type == 'array') {
						$args[] = 'Array(' . count($a) . ')';

					} elseif ($type == 'object') {
						$args[] = 'Object(' . get_class($a) . ')';

					} elseif ($type == 'resource') {
						$args[] = 'Resource('.strstr($a, '#').')';

					} elseif ($type == 'boolean') {
						$args[] = ($a ? 'TRUE' : 'FALSE');

					} else {
						$args[] = $type;
					}
				}

				//If only a couple arguments were given - convert to string
				if(count($args) < 4)
				{
					$args = implode(', ', $args);
				}

			}

			// Save args
			$v['args'] = $args;

			//Add trace data
			$trace[] = $v;
		}

		// Return the final backtrace
		return $trace;
	}
}
