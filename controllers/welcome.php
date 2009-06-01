<?php
/**
 * Welcome Controller
 *
 * Shows several examples of how to load, call, and render pages.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	Copyright (c) 2009 MicroMVC
 * @license		http://www.gnu.org/licenses/gpl-3.0.html
 * @link		http://micromvc.com
 * @version		1.0.1 <5/31/2009>
 ********************************** 80 Columns *********************************
 */

class welcome extends core {
	
	/*
	 * Load a view that shows a welcome message
	 */
	function index() {
	
		//Create Some data
		$view['class'] = get_class($this);
		$view['function'] = __FUNCTION__;
		
		//Fetch it and set it as the layout content
		$this->data['content'] = $this->view('welcome/welcome', $view, true);
		
	}
	
	
	/*
	 * Show an example of how hooks work
	 */
	function hooks() {
	
		//Call a function from a hook
		$this->data['content'] = $this->hooks->call('my_first_hook', true);
		
		//Call two class methods from a hook
		$this->data['content'] .= $this->hooks->call('my_second_hook', 'MY_WORD');
		
	}
	
	
	/*
	 * Show an example of how to pass a value though the URI
	 */
	function say($word='You didn\'t say anything...') {
		
		//Can't just let anything in! Someone might send a XSS...
		$word = preg_replace("/([^a-z0-9_\-\. ]+)/i", ' ', trim($word));
		
		//Set this as the layout content
		$this->data['content'] = $word;
	}
	
	
	/*
	 * Show and example of loading a API model and requesting data
	 */
	function twitter() {
		//See it live here
		//http://twitter.com/statuses/public_timeline.rss
		
		//Set my Username and password
		$options = array(
			'username'      => '',
			'password'      => '',
			'type'          => 'json' //or 'xml'
		);
		
		//Load the twitter model and create object
		$this->load('twitter_api', null, $options, 'libraries');
		
		
		//Get current user_timeline
		$object = $this->twitter_api->public_timeline();
		
		//If empty - fetch error
		if(!$object) {
			$object = $this->twitter_api->error();
		}
		
		//Register this object for the view
		$view['object'] = $object;
		
		//Render the view
		$this->data['content'] = $this->view('welcome/twitter', $view);

	}
}