<?php
/**
 * Session Class
 *
 * Class for adding extra session security protection as well as new ways to
 * store sessions (such as databases).
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	Copyright (c) 2009 MicroMVC
 * @license		http://www.gnu.org/licenses/gpl-3.0.html
 * @link		http://micromvc.com
 * @version		1.1.0 <7/7/2009>
 ********************************** 80 Columns *********************************
 */
class session {

	public $match_ip			= FALSE;			//Require user IP to match?
	public $match_fingerprint	= TRUE;				//Require user agent fingerprint to match?
	public $match_token			= FALSE;			//Require this token to match?
	public $session_handler		= 'session_handler_db';	//Class to use for storage, FALSE for native php
	public $session_table		= 'sessions';		//If using a DB, what is the table name?
	public $session_name		= 'mvc_session';	//What should the session be called?
	public $session_id			= NULL;				//Specify a custom ID to use instead of default cookie ID

	public $cookie_path			= NULL;				//Path to set in session_cookie
	public $cookie_domain		= NULL;				//The domain to set in session_cookie
	public $cookie_secure		= NULL;				//Should cookies only be sent over secure connections?
	public $cookie_httponly		= NULL;				//Only accessible through the HTTP protocol?

	public $regenerate			= 300;				//Update the session every five minutes
	public $expiration			= 7200;				//The session expires after 2 hours of non-use
	public $gc_probability		= 100;				//Chance (in 100) that old sessions will be removed


	/**
	 * Configure some default session setting and then start the session.
	 * @param	array	$config
	 * @return	void
	 */
	public function __construct($config = NULL) {

		//Set the config
		if(is_array($config)) {
			foreach($config as $key => $value) {
				$this->$key = $value;
			}
		}

		// Configure garbage collection
		ini_set('session.gc_probability', $this->gc_probability);
		ini_set('session.gc_divisor', 100);
		ini_set('session.gc_maxlifetime', $this->expiration);

		// Set the session cookie parameters
		session_set_cookie_params(
			$this->expiration + time(),
			$this->cookie_path,
			$this->cookie_domain,
			$this->cookie_secure,
			$this->cookie_httponly
		);

		// Name the session, this will also be the name of the cookie
		session_name($this->session_name);

		//If we were told to use a specific ID instead of what PHP might find
		if($this->session_id) {
			session_id($this->session_id);
		}

		//Create a session (or get existing session)
		$this->create();

	}


	/**
	 * Start the current session, if already started - then destroy and create a new session!
	 * @return void
	 */
	function create() {

		//If this was called to destroy a session (only works after session started)
		$this->destroy();

		//If there is a class to handle CRUD of the sessions
		if($this->session_handler) {

			//Load the session handler class
			$handler = load_class($this->session_handler, LIBRARY_PATH);

			//Set the expiration and table name for the model
			$handler->expiration = $this->expiration;
			$handler->session_table = $this->session_table;

			// Register non-native driver as the session handler
			session_set_save_handler (
				array($handler, 'open'),
				array($handler, 'close'),
				array($handler, 'read'),
				array($handler, 'write'),
				array($handler, 'destroy'),
				array($handler, 'gc')
			);
		}

		// Start the session!
		session_start();

		//Check the session to make sure it is valid
		if( ! $this->check()) {
			//Destroy invalid session and create a new one
			return $this->create();
		}

	}


	/**
	 * Check the current session to make sure the user is the same (or else create a new session)
	 * @return unknown_type
	 */
	function check() {

		//On creation store the useragent fingerprint
		if(empty($_SESSION['fingerprint'])) {
			$_SESSION['fingerprint'] = $this->generate_fingerprint();

		//If we should verify user agent fingerprints (and this one doesn't match!)
		} elseif($this->match_fingerprint && $_SESSION['fingerprint'] != $this->generate_fingerprint()) {
			return FALSE;
		}

		//If an IP address is present and we should check to see if it matches
		if(isset($_SESSION['ip_address']) && $this->match_ip) {
			//If the IP does NOT match
			if($_SESSION['ip_address'] != ip_address()) {
				return FALSE;
			}
		}

		//Set the users IP Address
		$_SESSION['ip_address'] = ip_address();


		//If a token was given for this session to match
		if($this->match_token) {
			if(empty($_SESSION['token']) OR $_SESSION['token'] != $this->match_token) {
				//Remove token check
				$this->match_token = FALSE;
				return FALSE;
			}
		}

		//Set the session start time so we can track when to regenerate the session
		if(empty($_SESSION['regenerate'])) {
			$_SESSION['regenerate'] = time();

		//Check to see if the session needs to be regenerated
		} elseif($_SESSION['regenerate'] + $this->regenerate < time()) {

			//Generate a new session id and a new cookie with the updated id
			session_regenerate_id();

			//Store new time that the session was generated
			$_SESSION['regenerate'] = time();

		}

		return TRUE;
	}


	/**
	 * Destroys the current session and user agent cookie
	 * @return  void
	 */
	public function destroy() {

		//If there is no session to delete (not started)
		if (session_id() === '') {
			return;
		}

		// Get the session name
		$name = session_name();

		// Destroy the session
		session_destroy();

		// Delete the session cookie (if exists)
		if (isset($_COOKIE[$name])) {

			//Get the current cookie config
			$params = session_get_cookie_params();

			// Delete the cookie from globals
			unset($_COOKIE[$name]);

			//Delete the cookie on the user_agent
			setcookie($name, '', time()-43200, $params['path'], $params['domain'], $params['secure']);
		}
	}


	/**
	 * Generates key as protection against Session Hijacking & Fixation. This
	 * works better than IP based checking for most sites due to constant user
	 * IP changes (although this method is not as secure as IP checks).
	 * @return string
	 */
	function generate_fingerprint()  {
		//We don't use the ip-adress, because it is subject to change in most cases
		foreach(array('ACCEPT_CHARSET', 'ACCEPT_ENCODING', 'ACCEPT_LANGUAGE', 'USER_AGENT') as $name) {
			$key[] = empty($_SERVER['HTTP_'. $name]) ? NULL : $_SERVER['HTTP_'. $name];
		}
		//Create an MD5 has and return it
		return md5(implode("\0", $key));
	}


}


/**
 * Default session handler for storing sessions in the database. Can use
 * any type of database from SQLite to MySQL. If you wish to use your own
 * class instead of this one please set session::$session_handler to
 * the name of your class (see session class). If you wish to use memcache
 * then then set the session::$session_handler to FALSE and configure the
 * settings shown in http://php.net/manual/en/memcache.examples-overview.php
 */
class session_handler_db extends base {

	//Store the starting session ID so we can check against current id at close
	public $session_id		= NULL;
	//Table to look for session data in
	public $session_table	= NULL;
	// How long are sessions good?
	public $expiration		= NULL;

	/**
	 * Record the current sesion_id for later
	 * @return boolean
	 */
	public function open() {
		//Store the current ID so if it is changed we will know!
		$this->session_id = session_id();
		return TRUE;
	}


	/**
	 * Superfluous close function
	 * @return boolean
	 */
	public function close() {
		return TRUE;
	}


	/**
	 * Attempt to read a session from the database.
	 * @param	string	$id
	 */
	public function read($id = NULL) {

		//Select the session
		$result = $this->db->select('data')->where('session_id', $id)->get($this->session_table);

		//Check to see if there is a result
		if($result && $row = $result->fetch(PDO::FETCH_ASSOC)) {
			return $row['data'];
		}

		return '';
	}


	/**
	 * Attempt to create or update a session in the database.
	 * The $data is already serialized by PHP.
	 *
	 * @param	string	$id
	 * @param	string 	$data
	 */
	public function write($id = NULL, $data = '') {

		/*
		 * Case 1: The session we are now being told to write does not match
		 * the session we were given at the start. This means that the ID was
		 * regenerated sometime durring the script and we need to update that
		 * old session id to this new value. The other choice is to delete
		 * the old session first - but that wastes resources.
		 */

		//If the session was not empty at start && regenerated sometime durring the page
		if($this->session_id && $this->session_id != $id) {

			//Update the data and new session_id
			$data = array('data' => $data, 'session_id' => $id);

			//Then we need to update the row with the new session id (and data)
			$this->db->update($this->session_table, $data, array('session_id' => $this->session_id));

			return;
		}

		/*
		 * Case 2: We check to see if the session already exists. If it does
		 * then we need to update it. If not, then we create a new entry.
		 */
		if($this->db->where('session_id', $id)->count($this->session_table)) {
			$this->db->update($this->session_table, array('data' => $data), array('session_id' => $id));

		} else {
			$this->db->insert($this->session_table, array('data' => $data, 'session_id' => $id));
		}

	}


	/**
	 * Delete a session from the database
	 * @param	string	$id
	 * @return	boolean
	 */
	public function destroy($id) {
		$this->db->delete($this->session_table, array('session_id' => $id));
		return TRUE;
	}


	/**
	 * Garbage collector method to remove old sessions
	 */
	public function gc() {
		//The max age of a session
		$time = date('Y-m-d H:i:s', time() - $this->expiration);
		//Remove all old sessions
		$this->db->delete($this->session_table, array('last_activity < ' => $time));
		return TRUE;
	}
}
