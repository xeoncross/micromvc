<?php defined('SYSTEM_PATH') or die('No direct access');
/**
 * Session
 *
 * Class for adding extra session security protection as well as new ways to
 * store sessions (such as databases). For flash uploaders and other non-browser
 * agents you should disable "regenerate", "match_fingerprint", and "match_ip"
 * when loading this class. Also make sure to call the token methods if using
 * forms to help prevent CSFR.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
class Session
{

	public $match_ip			= NULL;
	public $match_fingerprint	= NULL;
	public $session_handler		= NULL;
	public $session_table		= NULL;
	public $regenerate			= NULL;
	public $expiration			= NULL;		

	// These values can be changed at runtime for session work-arounds
	public $session_id			= NULL;
	public $token				= NULL;

	/**
	 * Configure some default session setting and then start the session.
	 * 
	 * @param array $config an optional configuration array
	 */
	public function __construct(array $config = NULL)
	{

		//Set the config
		if(is_array($config))
		{
			foreach($config as $key => $value)
			{
				$this->$key = $value;
			}
		}
		
		// Get the session
		$config = (array) $config + config::get('session');
		
		// Save regenerate time
		$this->regenerate = $config['regenerate'];
		$this->expiration = $config['expiration'];
		$this->session_table = $config['table'];
		$this->session_handler = $config['handler'];
		$this->match_fingerprint = $config['match_fingerprint'];
		$this->match_ip = $config['match_ip'];
		
		// Configure garbage collection
		ini_set('session.gc_probability', $config['gc_probability']);
		ini_set('session.gc_divisor', 100);
		ini_set('session.gc_maxlifetime', $config['expiration']);

		// Set the session cookie parameters
		session_set_cookie_params(
			$config['expiration'],
			$config['cookie_path'],
			$config['cookie_domain'],
			$config['cookie_secure'],
			$config['cookie_httponly']
		);

		// Name the session, this will also be the name of the cookie
		session_name($config['name']);

		//Create a session (or get existing session)
		$this->create();

	}


	/**
	 * Start the current session, if already started - then destroy and create a new session!
	 * @return void
	 */
	function create()
	{

		//If this was called to destroy a session (only works after session started)
		$this->destroy();

		//If we were told to use a specific ID instead of what PHP might find
		if($this->session_id)
		{
			session_id($this->session_id);
			$this->session_id = FALSE;
		}

		//If there is a class to handle CRUD of the sessions
		if($this->session_handler)
		{
			//Load the session handler class
			//$handler = load::singleton($this->session_handler);
			$handler = new $this->session_handler();

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
		if( ! $this->check())
		{
			//Destroy invalid session and create a new one
			return $this->create();
		}

	}


	/**
	 * Check the current session to make sure the user is the same (or else create a new session)
	 * @return boolean
	 */
	function check()
	{

		//On creation store the useragent fingerprint
		if(empty($_SESSION['fingerprint']))
		{
			$_SESSION['fingerprint'] = $this->generate_fingerprint();

		} //If we should verify user agent fingerprints (and this one doesn't match!)
		elseif($this->match_fingerprint AND $_SESSION['fingerprint'] != $this->generate_fingerprint())
		{
			return FALSE;
		}

		//If an IP address is present and we should check to see if it matches
		if(isset($_SESSION['ip_address']) AND $this->match_ip)
		{
			//If the IP does NOT match
			if($_SESSION['ip_address'] != ip_address())
			{
				return FALSE;
			}
		}

		//Set the users IP Address
		$_SESSION['ip_address'] = ip_address();

		//If we are required to make sure a token matches
		if($this->token AND $this->token != session('token'))
		{
			$this->token = FALSE;	// Remove token check
			return FALSE;			// Reset the session
		}

		//Set the session start time so we can track when to regenerate the session
		if(empty($_SESSION['regenerate']))
		{
			$_SESSION['regenerate'] = time();

		} //Check to see if the session needs to be regenerated
		elseif($this->regenerate AND $_SESSION['regenerate'] + $this->regenerate < time())
		{
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
	public function destroy()
	{

		//If there is no session to delete (not started)
		if ( ! session_id())
		{
			return;
		}

		// Get the session name
		$name = session_name();

		// Destroy the session
		session_destroy();

		// Delete the session cookie (if exists)
		if (isset($_COOKIE[$name]))
		{
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
	function generate_fingerprint()
	{
		//We don't use the ip-adress, because it is subject to change in most cases
		foreach(array('ACCEPT_CHARSET', 'ACCEPT_ENCODING', 'ACCEPT_LANGUAGE', 'USER_AGENT') as $name)
		{
			$key[] = empty($_SERVER['HTTP_'. $name]) ? NULL : $_SERVER['HTTP_'. $name];
		}

		//Create an MD5 hash and return it
		return md5(implode("\0", $key));
	}


	/**
	 * Create a fairly complex random token for use in forms.
	 */
	public static function create_token()
	{
		return $_SESSION['token'] = token();
	}


	/**
	 * Check that the given token matches the one in the users session.
	 * If not token is given we will look for one in the $_POST data.
	 * @param $token
	 * @return boolean
	 */
	public static function validate_token($token = NULL)
	{
		// If the token was not passed - look in $_POST data
		$token = $token ? $token : post('token');

		// If the tokens match
		if($token AND $token == session('token'))
		{
			return TRUE;
		}

		return FALSE;
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
class session_handler_db
{

	//Store the starting session ID so we can check against current id at close
	public $session_id		= NULL;

	//Table to look for session data in
	public $session_table	= NULL;

	// How long are sessions good?
	public $expiration		= NULL;

	//The database handle
	public $db				= NULL;

	// Store data
	public $data			= NULL;


	/**
	 * Record the current session_id for later
	 * @return boolean
	 */
	public function open()
	{
		// Load the DB connection
		$this->db = Database::instance();

		// Store the current ID so if it is changed we will know!
		$this->session_id = session_id();

		return TRUE;
	}


	/**
	 * Superfluous close function
	 * @return boolean
	 */
	public function close()
	{
		return TRUE;
	}


	/**
	 * Attempt to read a session from the database.
	 * @param string $id
	 */
	public function read($id = NULL)
	{
		// Prepare the statement
		//$statement = $this->db->select('data')->where('session_id')->from($this->session_table)->prepare();
		$statement = $this->db->prepare('SELECT "data" FROM "'.$this->session_table.'" WHERE "session_id" = ?');
		
		// Disable caching!!!
		$statement->cache_results = FALSE;
		
		// Bind params
		$statement->execute(array($id));

		// Fetch the data column
		if($data = $statement->fetchColumn())
		{
			return $this->data = $data;
		}

		return '';
	}


	/**
	 * Attempt to create or update a session in the database.
	 * The $data is already serialized by PHP.
	 *
	 * @param string $id
	 * @param string $data
	 */
	public function write($id = NULL, $data = '')
	{

		// Setup session data
		$row = array(
			'last_activity' => time(),
			'session_id'	=> $id
		);

		// If the data has changed since it was read - update it too!
		if($this->data != $data)
		{
			$row['data'] = $data;
		}


		/*
		 * Case 1: The session we are now being told to write does not match
		 * the session we were given at the start. This means that the ID was
		 * regenerated sometime during the script and we need to update that
		 * old session id to this new value. (The other choice is to delete
		 * the old session first - but that wastes resources!)
		 */
		if($this->session_id AND $this->session_id != $id)
		{

			//Then we need to update the row with the new session id (and data)
			$this->db->update($this->session_table, $row, array('session_id' => $this->session_id));

		}
		elseif($this->db->where('session_id = ?')->from($this->session_table)->count(array($id)))
		{

			// A session already exists - so update it!
			$this->db->update($this->session_table, $row, array('session_id' => $id));

		}
		else
		{
			// Create a new session
			$this->db->insert($this->session_table, $row);
		}

	}


	/**
	 * Delete a session from the database
	 *
	 * @param string $id
	 * @return boolean
	 */
	public function destroy($id)
	{
		$this->db->where('session_id =?')->delete($this->session_table, array($id));
		return TRUE;
	}


	/**
	 * Garbage collector method to remove old sessions
	 */
	public function gc()
	{
		//The max age of a session
		$time = (time() - $this->expiration);

		//Remove all old sessions
		$this->db->where('last_activity < ?')->delete($this->session_table, array($time));
		return TRUE;
	}


	/**
	 * PHP 5.0.5 needs to call the write before this object is destroyed!
	 */
	public function __destruct()
	{
		session_write_close();
	}

}

/** TOKENS **
 * Each time a form is shown we need to include a token with it to prevent
 * CSFR (i.e. submission of forms by a third party attacker). To do this
 * we should check for a POST/GET token on each page load. If found it means
 * that the form was sent with that token and if it matches the one in the
 * session then we know the author sent that form request and it is not an
 * attack.
 *
 * By only creating a token when there is no POST/GET data we can insure
 * that the same token can be used several times from one form with AJAX.
 * If a form submit fails, a token should be regenerated as well.
 *
 * We can't build auto-token checking into the session class because there
 * is no way to notify outside code of invalid tokens -other than to reset
 * the session. While being a secure method this has the side effect of
 * logging people out when we should instead show an error message.
 *
 * If using tokens please make sure to place a matching input in your form:
 * <input value="<?php print session('token'); ?>" name="token" />
 */