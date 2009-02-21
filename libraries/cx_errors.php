<?php
/**
 * Error Handler
 *
 * A error handling class that replaces PHP's built in error
 * class. This class records all errors to a log file and presents
 * easier-to-understand messages to the end user.
 *
 * @package		CodeXplorer
 * @author		David Pennington
 * @copyright	Copyright (c) 2008 CodeXplorer
 * @license		http://www.gnu.org/licenses/gpl-3.0.html
 * @link		http://codexplorer.com
 * @version		1.0.0 <11/03/2008>
 ********************************** 80 Columns *********************************
 */
class cx_errors {

	//Stores array of Error
	public $_cx_errors = array();
	
	//Set handler on creation
	public function __construct() {
		set_error_handler(array(&$this,'record'));
	}
	
	//Record errors on close
	public function __destruct() {
		$this->log();
	}
	
	/**
	 * Record an error
	 *
	 * @param string $error
	 * @param string $message
	 * @param string $file
	 * @param string $line
	 * @param string $variables
	 */
	public function record($error='', $message='', $file='', $line='', $variables='') {

		//Set error types
		$error_types = array(
			E_ALL => 'All Errors',
			E_WARNING => 'Warning',
			E_USER_WARNING => 'User Warning',
			E_USER_ERROR => 'User Error',
			E_RECOVERABLE_ERROR => 'Recoverable error',
			E_NOTICE => 'Notice',
			E_USER_NOTICE => 'User Notice',
			E_STRICT => 'Strict Error',
		);

		//Only show the system file that had the problem - not the whole server dir structure!
		//$file_name = end(explode(SITE_DIR, $file_name));
		$file = str_replace(SITE_DIR, '', $file);

		//Record the error
		$this->_cx_errors[] = array(
			'time' => date(DATE_RSS),
			'ip' => IP_ADDRESS,
			'type' => $error,
			'error' => $message,
			'file_name' => $file,
			'line_number' => $line
		);
		
		//If we are not testing - send admin email!!!
		if(!DEBUG_MODE && $error < E_USER_WARNING) {
			$this->email(end($this->_cx_errors));
		}

	}
	
	/*
	 * Store errors in log file
	 */
	public function log() {
		
		$log = SITE_DIR. SITE_NAME. '/errors/log-'. date("Y-M-j"). '.php';
	
		//If no errors - or file too big
		if(!$this->_cx_errors || @filesize($path) > 10000) { return; }
		
		//Open file
		$log = fopen($log, 'a');
		
		//Log each error
		foreach($this->_cx_errors as $error) {
			fwrite($log, implode(' - ', $error). "\n");
		}
		//close file
		fclose($log);
	}


	/**
	 * Email notice of error
	 *
	 * @param int $id
	 */
	public function email($id=null) {
		//If we can't send email
		if(empty($this->_cx_errors[$id]) || !function_exists('mail')) { return; }

		$error = $this->_cx_errors[$id];
		$error->message = $error->message. "\n File $error->file on line $error->line";
		$headers = 'From: ' . $this->config['email']. "\r\n" . 
					'Reply-To: ' . $this->config['email']. "\r\n" .
		    		'X-Mailer: PHP/' . phpversion();

		mail($this->config['email'], $error->type, $error->message, $headers);

	}
}
