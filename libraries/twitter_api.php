<?php
/**
 * Welcome
 *
 * PHP5 Class for accessing the Twitter API. Can handle XML and JSON requests
 * and returns a parsed object OR null for each request. If null you can
 * access the returned error message from $twitter_api->error();
 *
 * Uses the powerful cURL library to make requests to the twitter service.
 *
 * This class is built word-for-word to match the docs. It supports all
 * functions and options for each of the REST requests Twitter offers - even
 * the newer search and trends API's. It is the most complete class there is.
 *
 * Several functions were taken from the "php-twitter" class by
 * David Billingham, Aaron Brazell, and Keith Casey.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	Copyright (c) 2009 MicroMVC
 * @license		http://www.gnu.org/licenses/gpl-3.0.html
 * @link		http://micromvc.com
 * @version		1.1.0 <7/7/2009>
 ********************************** 80 Columns *********************************
 */

class twitter_api {

	/**
	 * Authenticating Twitter user
	 * @var string
	 */
	var $username = '';

	/**
	 * Autenticating Twitter user password
	 * @var string
	 */
	var $password = '';

	/**
	 * Recommend setting a user-agent so Twitter knows how to contact you
	 * in case of abuse. Include your email.
	 * @var string
	 */
	var $user_agent = DOMAIN;

	/**
	 * Can be set to JSON or XML - JSON is faster though...
	 * @var string
	 */
	var $type = 'json';

	/**
	 * It is unclear if Twitter header preferences are standardized, but I
	 * would suggest using them. More discussion at http://tinyurl.com/3xtx66
	 * @var array
	 */
	var $headers=array('X-Twitter-Client: MicroMVC','X-Twitter-Client-Version: 1.0','X-Twitter-Client-URL: micromvc.com');

	/**
	 * Response information that cURL recived from the last request
	 * @var array
	 */
	var $response_info=array();

	/*
	 * Variable set to last error
	 * @var string
	 */
	var $error = '';


	/*
	 * Constructor allows options to be set at startup
	 */
	function __construct($options=null) {

		if(!$options) { return; }

		$this->setup($options);
	}

	/*
	 * Setup the Twitter Request Options
	 */
	function setup($options=array()) {

		/* [Options]
		 * username		Your Twitter Username
		 * password		Your Twitter Password
		 * type			The type of request to make (JSON/XML)
		 */

		if(!$options || !is_array($options)) { return; }

		//If any of these are set
		foreach($options as $key => $value) {
			if($value) {
				$this->$key = $value;
			}
		}

	}


	/*
	 * Status Methods
	 */


	/**
	 * Send an unauthenticated request to Twitter for the public timeline.
	 * Returns the last 20 updates by default
	 * @param	array	$options
	 * @return	object
	 */
	function public_timeline($options=null) {

		$request = 'http://twitter.com/statuses/public_timeline.'. $this->type;
		$request .= ($options ? '?'. http_build_query($options) : '');

		//Process request and return object
		return $this->process($request);
	}

	/**
	 * Request the timeline of the users friends.
	 * @param	array	$options
	 * @return	object
	 */
	function friends_timeline($options=null) {

		/** [Options]
		 * since	statuses created after the specified HTTP-formatted date
		 * since_id	statuses with an ID greater than the specified ID
		 * count	number of statuses to retrieve
		 * page		page to start from
		 */

		$request = 'http://twitter.com/statuses/friends_timeline.'. $this->type;
		$request .= ($options ? '?'. http_build_query($options) : '');

		//print http_build_query($options);
		return $this->process($request);
	}

	/**
	 * Send an authenticated request to Twitter for the timeline of a user.
	 * @param	array	$options
	 * @return	object
	 */
	function user_timeline($options=null) {

		/** [Options]
		 * id		the ID of the user to get the timeline from
		 * count	number of statuses to retrieve
		 * since	statuses created after the specified HTTP-formatted date
		 * since_id	statuses with an ID greater than the specified ID
		 * page		page to start from
		 */

		$request = 'http://twitter.com/statuses/user_timeline.' . $this->type;
		$request .= ($options ? '?'. http_build_query($options) : '');

		//print http_build_query($options);
		return $this->process($request);
	}

	/**
	 * Returns a single status, specified by the id parameter below.
	 * The status's author will be returned inline.
	 * @param	integer		$id		The id number of the tweet to be returned.
	 * @return	object
	 */
	function show($id=null) {
		return $this->process('http://twitter.com/statuses/show/'
		. intval($id) . '.' . $this->type);
	}

	/**
	 * Send a status update to Twitter.
	 * @param	arary	$options
	 * @return	object
	 */
	function update($options=null) {

		/* [Options]
		 * status					The text of your status update
		 * in_reply_to_status_id	ID of an existing status to reply to
		 */

		$request = 'http://twitter.com/statuses/update.' . $this->type;

		//Process request and return object
		return $this->process($request, http_build_query($options));
	}

	/**
	 * Recent @replies (status updates prefixed with @username)
	 * @param	array	$options
	 * @return	object
	 */
	function replies($options=null) {

		/** [Options]
		 * page		Retrieves the 20 next most recent replies
		 * since	statuses created after the specified HTTP-formatted date
		 * since_id	statuses with an ID greater than the specified ID
		 */

		$request = 'http://twitter.com/statuses/replies.' . $this->type;
		$request .= ($options ? '?'. http_build_query($options) : '');

		//print http_build_query($options);
		return $this->process($request);
	}

	/**
	 * Destroys the status specified by the required ID parameter
	 * @param	int		$id - The ID of the status to delete
	 * @return	object
	 */
	function destroy($id=null) {

		$request = 'http://twitter.com/statuses/destroy/'
		. $id. '.' . $this->type;

		return $this->process($request, true);
	}


	/*
	 * User Methods
	 */


	/**
	 * Returns the authenticating user's friends, each with current
	 * status inline. Request another user's friends list via the id
	 * parameter below.
	 * @param	array		$options
	 * @return	object
	 */
	function friends($options=null) {

		/** [Options]
		 * id		The ID or screen name of the user for whom to request a list of friends
		 * page		Retrieves the next 100 friends
		 * since	Just those friendships created after the specified date
		 */

		$request = 'http://twitter.com/statuses/friends.' . $this->type;
		$request .= ($options ? '?'. http_build_query($options) : '');

		//print urldecode($request);
		return $this->process($request);

	}

	/**
	 * Returns a user's followers, each with current status inline.
	 * request another user's folowers list via the id option.
	 * @param array		$options - id,page,since
	 * @return object
	 */
	function followers($options=null) {

		/** [Options]
		 * id		The ID or screen name of the user for whom to request a list of followers
		 * page		Retrieves the next 100 followers
		 */

		$request = 'http://twitter.com/statuses/followers.'. $this->type;
		$request .= ($options ? '?'. http_build_query($options) : '');

		//print urldecode($request);
		return $this->process($request);

	}

	/**
	 * Returns extended information of a given user,
	 * specified by ID, email, or screen name.
	 * @param	array	$options
	 * @return	object
	 */
	function show_user($options=null) {

		/** [Options]
		 * id		The ID or screen name of a user
		 * email	The email address of a user
		 */

		$request = 'http://twitter.com/users/show.'. $this->type;
		$request .= ($options ? '?'. http_build_query($options) : '');

		//print urldecode($request);
		return $this->process($request);

	}


	/*
	 * Direct Message Methods
	 */

	/**
	 * 20 most recent direct messages sent to the authenticating user
	 * @param	array	$options
	 * @return	object
	 */
	function direct_messages($options=null) {

		/** [Options]
		 * since	sent after the specified HTTP-formatted date
		 * since_id	direct messages with an ID greater than the specified ID
		 * page		Retrieves the 20 next most recent direct messages
		 */

		$request = 'http://twitter.com/direct_messages.' . $this->type;
		$request .= ($options ? '?'. http_build_query($options) : '');

		return $this->process($request);

	}

	/**
	 * Returns a list of the 20 most recent direct messages sent by the user
	 * @param	array		$options
	 * @return	object
	 */
	function sent_messages($options=null) {

		/** [Options]
		 * since	sent after the specified HTTP-formatted date
		 * since_id	direct messages with an ID greater than the specified ID
		 * page		Retrieves the 20 next most recent direct messages sent
		 */

		$request = 'http://twitter.com/direct_messages/sent.' . $this->type;
		$request .= ($options ? '?'. http_build_query($options) : '');

		//print urldecode($request);
		return $this->process($request);

	}

	/**
	 * New direct message to the specified user from the authenticating user
	 * @param	array	$options
	 * @return	object
	 */
	function new_message($options=null) {

		/** [Options]
		 * user		The ID or screen name of the recipient user.
		 * text		The text of your direct message.
		 */

		$request = 'http://twitter.com/direct_messages/new.' . $this->type;

		return $this->process($request, http_build_query($options));

	}

	/**
	 * Destroys the direct message specified in the required ID parameter.
	 * @param	int		$id		 ID of the direct message to destroy
	 * @return object
	 */
	function destroy_message($id=null) {

		$request = 'http://twitter.com/direct_messages/destroy/'
		. intval($id). '.' . $this->type;

		return $this->process($request, true);

	}


	/*
	 * Friendship Methods
	 */


	/**
	 * Befriends the user specified in the ID parameter
	 * @param	array	$options
	 * @return	object
	 */
	function create_friend($options=null) {

		/** [Options]
		 * id		The ID or screen name of the user to befriend
		 * follow	Enable notifications in addition to becoming friends
		 */

		$request = 'http://twitter.com/friendships/create/'
		. intval($options['id']). '.' . $this->type;

		//print urldecode($request. '<br />'. $this->url_string($options));
		return $this->process($request, http_build_query($options));

	}

	/**
	 * Discontinues friendship with the user specified in the ID
	 * @param	int		$id - the ID of the user
	 * @return	object
	 */
	function destroy_friend($id=null) {

		$request = 'http://twitter.com/friendships/destroy/'
		. intval($id). '.' . $this->type;

		return $this->process($request, true);

	}

	/**
	 * Tests if a friendship exists between two users
	 * @param	array	$options
	 * @return	object
	 */
	function friend_exists($options=null) {

		/** [Options]
		 * user_a	ID/screen_name of the first user to test friendship for
		 * user_b	ID/screen_name of the second user to test friendship for
		 */

		$request = 'http://twitter.com/friendships/exists.' . $this->type;
		$request .= ($options ? '?'. http_build_query($options) : '');

		return $this->process($request);

	}


	/*
	 * Account Methods
	 */


	/**
	 * Test if supplied user credentials are valid with minimal overhead
	 * @return	object
	 */
	function verify_credentials() {

		$request = 'http://twitter.com/account/verify_credentials.'
		. $this->type;

		return $this->process($request, null);

	}

	/**
	 * Ends session of the user, returning a null cookie
	 * @return	object
	 */
	function end_session() {

		$request = 'http://twitter.com/account/end_session.' . $this->type;

		return $this->process($request, true);

	}

	/**
	 * Updates the location attribute of the authenticating user
	 * @param	string	$location - the location of the user
	 * @return	object
	 */
	function update_location($location=null) {

		$request = 'http://twitter.com/account/update_location.'
		. $this->type . '?'. http_build_query(array('location' => $location));

		return $this->process($request, true);

	}

	/**
	 * Sets which device Twitter delivers updates to
	 * Sending none as the device parameter will disable IM or SMS updates
	 *
	 * @param	string	$device - Must be one of: sms, im, none
	 * @return	object
	 */
	function update_delivery_device($device=null) {

		$request = 'http://twitter.com/account/update_delivery_device.'
		. $this->type. '?'. http_build_query(array('device' => $device));

		return $this->process($request, true);

	}

	/**
	 * Returns the remaining number of API requests available
	 * @return object
	 */
	function rate_limit_status() {
		return $this->process('http://twitter.com/account/rate_limit_status.'
		. $this->type);
	}


	/*
	 * Favorite Methods
	 */


	/**
	 * 20 most recent favorite statuses for current/ID specified user
	 * @param	array	$options
	 * @return	object
	 */
	function favorites($options=null) {

		/* [Options]
		 * id		ID/screen name of whom to request favorite statuses
		 * page		Retrieves the 20 next most recent favorite statuses
		 */

		$request = 'http://twitter.com/favorites.' . $this->type;
		$request .= ($options ? '?'. http_build_query($options) : '');

		return $this->process($request);

	}

	/**
	 * Favorites the status specified in the ID parameter
	 * @param	int		$id
	 * @return	object
	 */
	function create_favorite($id=null) {

		$request = 'http://twitter.com/favorites/create/'
		. $id. '.' . $this->type;

		return $this->process($request, true);

	}

	/**
	 * Un-favorites the status specified in the ID parameter
	 * @param	int		$id
	 * @return	object
	 */
	function destroy_favorite($id=null) {

		$request = 'http://twitter.com/favorites/destroy/'
		. $id. '.' . $this->type;

		return $this->process($request, true);

	}


	/*
	 * Notification Methods
	 */


	/**
	 * Enables notifications for updates from the specified user
	 * @param	int		$id
	 * @return	object
	 */
	function follow($id=null) {

		$request = 'http://twitter.com/notifications/follow/'
		. $id . '.' . $this->type;

		return $this->process($request, true);

	}

	/**
	 * Disables notifications for updates from the specified user
	 * @param	int		$id
	 * @return	object
	 */
	function leave($id=null) {

		$request = 'http://twitter.com/notifications/leave/'
		. $id . '.' . $this->type;

		return $this->process($request, true);

	}


	/*
	 * Block Methods
	 */


	/**
	 * Blocks the user specified in the ID parameter
	 * @param	int		$id
	 * @return	object
	 */
	function create_block($id=null) {
		$request = 'http://twitter.com/blocks/create/'. $id . '.'. $this->type;
		return $this->process($request, true);
	}

	/**
	 * Un-blocks the user specified in the ID parameter
	 * @param	int		$id
	 * @return	object
	 */
	function destroy_block($id=null) {
		$request = 'http://twitter.com/blocks/destroy/'
		. $id . '.'. $this->type;

		return $this->process($request, true);
	}


	/*
	 * Help Methods
	 */


	/**
	 * Returns the string "ok" in the requested format
	 * @return	object
	 */
	function test() {
		$request = 'http://twitter.com/help/test.' . $this->type;
		return $this->process($request);
	}

	/**
	 * Returns the same text displayed on http://twitter.com/home
	 * when a maintenance window is scheduled
	 * @return	object
	 */
	function downtime_schedule($id=null) {
		$request = 'http://twitter.com/help/downtime_schedule.' . $this->type;
		return $this->process($request);
	}


	/*
	 * Support Functions
	 */


	/**
	 * Uses the http://tinyurl.com API to produce a shortened URL.
	 * @param	string	$url URL to shortened
	 * @return	string
	 */
	function tinyurl($url=null) {

		//Get a TinyURL
		$request = 'http://tinyurl.com/api-create.php?url=' . $url;
		return $this->process($request, null, false);

	}

	/**
	 * Use the twitter search API
	 * @param	array	$options
	 * @return	object
	 */
	function search($options=null) {
		/** [Options]
		 * q			query to search for
		 * lang			ISO code of country to limit results too ("en")
		 * rpp			number of tweets to return per page, up to a max of 100
		 * page			page number to return, up to a max of roughly 1500
		 * since_id		tweets with status ids greater than the given id
		 * geocode		located within a given radius of latitude,longitude,mi
		 * show_user	"true" adds "<user>:" to the beginning of the tweet
		 */

		//http://developer.yahoo.com/maps/rest/V1/geocode.html
		//$geocode needs to be built = "37.416397,-122.025055,mi";

		//XML is not supported yet so only allow json/atom
		$type = ($this->type == 'json' ? $this->type : 'atom');

		$request = 'http://search.twitter.com/search.' . $type;
		$request .= ($options ? '?'. http_build_query($options) : '');

		//var_dump($request);
		return $this->process($request);
	}

	/**
	 * Top ten queries that are currently trending on Twitter
	 * @return	object
	 */
	function trends() {

		//Only supports JSON queries
		if($this->type != 'json') { return null; }

		return $this->process('http://search.twitter.com/trends.json');
	}

	/**
	 * Function to turn a 10 digit timestamp into a HTTP Date for API calls
	 * @param	int		$timestamp - Optional
	 * @return	string
	 */
	function date($timestamp=null) {
		return date(DATE_RFC1123, $timestamp);
	}

	/**
	 * Return the error message
	 * @return	string
	 */
	function error() {
		if($this->error) {
			return $this->error;
		}
	}

	/**
	 * Creates a cURL instance and then sends a request to the given URL
	 * @param	string	$url		Required
	 * @param	string	$postargs	Optional
	 * @param	boolean	$object		Optional
	 * @return	mixed
	 */
	function process($url='', $postargs=false, $object=true) {

		//Reset the error string
		$this->error = '';

		//Start the cURL instance
		$ch = curl_init($url);

		//Add post data if given
		if($postargs !== false) {
			curl_setopt ($ch, CURLOPT_POST, true);
			curl_setopt ($ch, CURLOPT_POSTFIELDS, $postargs);
		}

		//If a username and password are set
		if($this->username && $this->password) {
			curl_setopt($ch, CURLOPT_USERPWD, $this->username.':'.$this->password);
		}

		//Setup options
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_NOBODY, 0);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);

		//Get response
		$response = curl_exec($ch);

		//Get response infomation
		$this->response_info = curl_getinfo($ch);

		curl_close($ch);

		//If everything went ok
		if(intval($this->response_info['http_code']) == 200 ) {
			//Return an object or a string?
			return ($object ? $this->objectify($response) : $response);
		}

		//If there is a problem - log the error message
		$object = $this->objectify($response);
		$this->error = (isset($object->error) ? $object->error : $object);

		//For debuging - otherwise die quietly...
		//return $response;
	}

	/**
	 * Process a XML or JSON string into a useable Object
	 * @param	string	$data
	 * @return	object
	 */
	function objectify($data=null) {

		//If it is a JSON string
		if($this->type == 'json') {
			return json_decode($data);
		}

		//Else if it is a XML string
		if($this->type == 'xml') {

			//If simpleXML is loaded
			if(function_exists('simplexml_load_string')) {

				$object = simplexml_load_string($data);

				//If there is NO status type
				if(!$object->status) {
					return $object;
				}

				$statuses = array();

				//We only want the statuses
				foreach($object->status as $status) {
					$statuses[] = $status;
				}

				return (object) $statuses;
			}
		}

		//Anything else fails
		return $data;

	}
}
