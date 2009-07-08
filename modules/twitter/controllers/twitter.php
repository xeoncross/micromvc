<?php
/**
 * Twitter Controller
 *
 * Loads the twitter API and shows the latest tweets from around the world.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	Copyright (c) 2009 MicroMVC
 * @license		http://www.gnu.org/licenses/gpl-3.0.html
 * @link		http://micromvc.com
 * @version		1.1.0 <7/7/2009>
 ********************************** 80 Columns *********************************
 */

class twitter extends controller {

	/*
	 * Show and example of loading a API model and requesting data
	 */
	function index() {
		//See it live here
		//http://twitter.com/statuses/public_timeline.rss

		//Set my Username and password
		$options = array(
			'username'      => '',
			'password'      => '',
			'type'          => 'json' //or 'xml'
		);

		//Load the twitter model and create object
		$this->library('twitter_api', $options);

		//Get current user_timeline
		$object = $this->twitter_api->public_timeline();

		//If empty - fetch error
		if(!$object) {
			$object = $this->twitter_api->error();
		}

		//Register this object for the view
		$view['object'] = $object;

		//Render the view
		$this->views['content'] = $this->view('twitter/tweets', $view, TRUE, 'twitter');

	}
}