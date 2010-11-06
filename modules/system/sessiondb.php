<?php
/**
 * SessionDB (depreciated)
 *
 * Class for adding extra session security protection as well as new ways to
 * store sessions (such as databases). For flash uploaders and other non-browser
 * agents you should disable "regenerate", "match_fingerprint", and "match_ip"
 * when loading this class. 
 * 
 * CREATE TABLE IF NOT EXISTS `session` (
 *   `id` varchar(40) NOT NULL,
 *   `timestamp` int(10) unsigned NOT NULL,
 *   `data` text,
 *   PRIMARY KEY (`id`)
 * ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
 * 
 * Also make sure to call the token methods if using forms to help prevent CSFR.
 * <input value="<?php print session('token'); ?>" name="token" />
 * 
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
class SessionDB
{
	public static $table		= 'session';
	public static $db			= NULL;
	public static $expiration	= NULL;
	
	/**
	 * Configure the session settings, check for problems, and then start the session.
	 * 
	 * @param array $config an optional configuration array
	 */
	public static function start(DB $db, array $config = NULL)
	{
		/*
		die('Need to create a $_POST[remember_me] feature that turns the session from a session cookie
		to a long lasting cookie. This new value should also be the session expariation since nothing
		will be lasting longer than it.');
		*/
		
		// All sessions require a user agent - only bad bots don't use them
		if(empty($_SERVER['HTTP_USER_AGENT']))
		{
			exit('No User Agent Sent');
		}
		
		// Get the session
		$config = (array) $config + config('session');
		
		// Configure garbage collection
		ini_set('session.gc_probability', $config['gc_probability']);
		ini_set('session.gc_divisor', $config['gc_divisor']);
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
		
		// Register non-native driver as the session handler
		session_set_save_handler (
			array('Session', '_open'),
			array('Session', '_close'),
			array('Session', '_read'),
			array('Session', '_write'),
			array('Session', '_destroy'),
			array('Session', '_gc')
		);
	
		// Allow the user to start a session manually by passing the ID to use
		if($config['session_id'])
		{
			session_id($config['session_id']);
		}
		
		// Set the database instance to use below
		self::$db = $db;
		
		// How long do sessions last?
		self::$expiration = $config['expiration'];
		
		// Start the session
		session_start();
		
		// Different browser using another browsers session!?
		if($config['match_fingerprint'])
		{
			// Create a fingerprint
			$fingerprint = md5($_SERVER['HTTP_USER_AGENT']);
			
			if(empty($_SESSION['fingerprint']))
			{
				$_SESSION['fingerprint'] = $fingerprint;
			}
			elseif($_SESSION['fingerprint'] !== $fingerprint)
			{
				// Kill this session!
				session::destroy();
		
				// Start the session again
				return self::start($db);
			}
		}
		
		// Different IP using another IP's session!?
		if($config['match_ip'])
		{
			if(empty($_SESSION['ip_address']))
			{
				$_SESSION['ip_address'] = ip_address();
			}
			elseif($_SESSION['ip_address'] !== ip_address())
			{
				// Kill this session!
				session::destroy();
		
				// Start the session again
				return self::start($db);
			}
		}
		
		// If we regenerate the session after so many pages (disabled by default)
		if ($config['regenerate'])
		{
			if(empty($_SESSION['regenerate']))
			{
				$_SESSION['regenerate'] = 0;
			}
			elseif(($_SESSION['regenerate'] % $config['regenerate']) === 0)
			{
				// Regenerate session id and update session cookie
				session_regenerate_id(TRUE);
					
				// Reset the counter
				$_SESSION['regenerate'] = 0;
			}
			$_SESSION['regenerate']++;
		}

		// APC (and some versions of PHP 5) need this to save data before object destruction (i.e. DB)
		register_shutdown_function('session_write_close');
	}

	
	/**
	 * Destroys the current session, session data, and user agent cookie
	 */
	public static function destroy()
	{
		//If there is no session to delete (not started)
		if ( ! session_id())
		{
			return;
		}

		// Get the session name
		$name = session_name();

		// Delete the session cookie (if exists)
		if ( ! empty($_COOKIE[$name]))
		{
			//Get the current cookie config
			$params = session_get_cookie_params();

			// Delete the cookie from globals
			unset($_COOKIE[$name], $_SESSION);

			//Delete the cookie on the user_agent
			setcookie($name, '', time()-43200, $params['path'], $params['domain'], $params['secure']);
		}
		
		// Destroy the session
		session_destroy();
	}

	
	/**
	 * Create a fairly complex random token for use in forms.
	 */
	public static function create_token()
	{
		if(session_id())return$_SESSION['token']=token();
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


	/**
	 * Superfluous open/close functions
	 */
	public static function _open() { return TRUE; }
	public static function _close() { return TRUE; }
	

	/**
	 * Attempt to read a session from the database.
	 * @param string $id of the session
	 */
	public static function _read($id = NULL)
	{
		$sql = 'SELECT "data" FROM "'.self::$table.'" WHERE "id" = ?';

		if($result = self::$db->fetch($sql, array($id)))
		{
			return $result[0]->data;
		}
	}


	/**
	 * Attempt to create or update a session in the database. The $data is already 
	 * serialized by PHP. Note that we do not need to update the old row if the session
	 * id changed during the script because session_regenerate_id() already removed it.
	 *
	 * @param string $id of the session
	 * @param string $data the session data
	 */
	public static function _write($id = NULL, $data = '')
	{
		// Setup session data
		$row = array(
			'id'		=> $id,
			'timestamp'	=> time(),
			'data'		=> $data
		);

		// Update the row with the new data
		if(self::$db->column('SELECT COUNT(*) FROM "'.self::$table.'" WHERE "id" = ?', array($id)))
		{
			self::$db->update(self::$table, $row, array('id' => $id));
		}
		else
		{
			self::$db->insert(self::$table, $row);
		}
	}


	/**
	 * Delete a session from the database
	 *
	 * @param string $id of the session
	 * @return boolean
	 */
	public static function _destroy($id = NULL)
	{
		return self::$db->delete('DELETE FROM "'.self::$table.'" WHERE "id" = ?', array($id));
	}


	/**
	 * Garbage collector method to remove old sessions
	 */
	public static function _gc()
	{
		//The max age of a session
		$time = time() + self::$expiration;

		// Remove all old sessions
		return self::$db->delete('DELETE FROM "'.self::$table.'" WHERE "timestamp" < ?', array($time));
	}
	
}
