<?php defined('SYSTEM_PATH') or die('No direct access');
/**
 * API Class
 *
 * This class handles making requests to web services using JSON, XML, HTML, and 
 * other formats. It caches the results and automatically refreshes caches at
 * the end of the script to avoid delays for the end user.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
class api {

	/**
	 * Config values required by an API (username, api_key, etc...)
	 * @var array
	 */
	public $config = array();


	/**
	 * Response information that cURL recived from the last request
	 * @var array
	 */
	public $response = array();


	/**
	 * URL's to re-fetch at the end of the page.
	 * @var array
	 */
	public $reload = array();


	/**
	 * Setup the API config
	 * @param array $config the config values
	 * @return unknown_type
	 */
	public function __construct( array $config = NULL)
	{
		$this->config = (array) $config + array('cache_life' => 300);
	}


	/**
	 * Fetch a URL using GET or POST
	 * @param string $url the url to fetch
	 * @param string $postargs optional post params
	 * @return string
	 */
	public function fetch($url = '', $postargs = NULL)
	{
		// Clear values
		$this->response = NULL;

		//Start the cURL instance
		$ch = curl_init($url);

		// Future classes can overload the set_options method
		$this->set_options($ch, $postargs);

		//Get response
		$response = trim(curl_exec($ch));

		//Get response infomation
		$this->response['info'] = curl_getinfo($ch);

		//Close cURL instance
		curl_close($ch);

		//Get the HTTP Status code
		$status_code = intval($this->response['info']['http_code']);

		//If there was a problem with the response
		if($status_code > 203 OR ! $response)
		{
			$this->response['body'] = $response;
			return;
		}

		return $response;
	}


	/**
	 * Creates a cURL instance and then sends a request to the given URL
	 * @param string $url the URL to load
	 * @param string $format the format to decode (xml, json, or php_serial)
	 * @param array $postargs the optional POST params
	 * @return object|bool
	 */
	public function process($url = '', $format = 'xml', $postargs = NULL)
	{
		//Build a query string
		$postargs = $this->build_query($postargs, 0);

		// Create MD5 hash of file name (to tell responses apart)
		$hash = md5($url. $postargs. $format);

		// Return the cached version
		if(cache::exists($hash))
		{
			// If it's time for a new copy of the file (add it to the queue)
			if(time() > cache::age($hash) + $this->config['cache_life'])
			{
				$this->reload[$hash] = array($url, $postargs);
			}

			return $this->objectify(cache::get($hash), $format);
		}

		// Try to fetch the page - on fail just return
		if( ! $response = $this->fetch($url, $postargs))
		{
			return;
		}

		// Save the page
		cache::set($hash, $response);

		// Decode the data from the format given
		return $this->objectify($response, $format);
	}


	/**
	 * When a cache expires we want to quietly reload it after the
	 * page has already finished running. This way the user doesn't
	 * have to wait for our cURL request.
	 */
	public function __destruct()
	{
		if( ! $this->reload)
		return;

		foreach($this->reload as $hash => $url)
		{
			// If error with request
			if( ! $response = $this->fetch($url[0], $url[1]))
			{
				continue;
			}

			// Save the page
			cache::set($hash, $response);
		}
	}


	/**
	 * Sets options for the cURL request. This function may need to
	 * be overloaded by a child class to accommodate a certain API.
	 *
	 * @param resource	$ch
	 * @param string	$postargs
	 */
	public function set_options(&$ch=null, $postargs=null)
	{
		//Add post data if given
		if($postargs)
		{
			curl_setopt ($ch, CURLOPT_POST, TRUE);
			curl_setopt ($ch, CURLOPT_POSTFIELDS, $postargs);
		}

		//Should HTTP authentication be used?
		if( ! empty($this->config['http_auth']))
		{
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		}

		// If using a custom useragent
		if(isset($this->config['user_agent']))
		{
			curl_setopt($ch, CURLOPT_USERAGENT, $this->config['user_agent']);
		}

		//Setup options
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_NOBODY, 0);
		curl_setopt($ch, CURLOPT_HEADER, 0);

		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		//Don't use a cached version of the url
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
	}


	/**
	 * Builds a URL query string
	 *
	 * @param string $params
	 * @param boolean $q
	 * @return string
	 */
	function build_query($params = NULL, $q = TRUE)
	{
		return count($params) ? ($q ? '?' : ''). http_build_query($params, NULL, '&') : '';
	}


	/**
	 * Process a XML, JSON, or PHP object strings into usable objects
	 * @param string $data the string to parse
	 * @param string $format the format of the string
	 * @return object
	 */
	public function objectify($data = NULL, $format = 'xml')
	{
		//If it is a JSON string and JSON loaded
		if($format === 'json')
		{
			if( ! function_exists('json_decode'))
			{
				trigger_error('JSON support is not installed.');
				return;
			}

			/*
			 * Remove the JS junk from a string (if it exists).
			 * Some API's (tumblr) add variables to the start and end
			 * of a JSON string so that JS clients can use the data
			 * more easily - but we are PHP so we don't want it!
			 */
			$char = substr($data, 0, 1);

			//If the first char is NOT a bracket
			if($char !== '{' AND $char !== '[')
			{
				//Clean JS from string!
				$data = preg_replace(array('/^[^\{]+(?=\{|\[)/i', '/(;)$/'), '', $data);
			}

			return json_decode($data);
		}
		elseif ($format === 'php_serial')
		{
			return unserialize($data);

		}
		elseif ($format === 'xml')
		{
			if( ! function_exists('simplexml_load_string'))
			{
				trigger_error('SimpleXML is not installed.');
				return;
			}

			return simplexml_load_string($data);
		}

		return $data;
	}
}