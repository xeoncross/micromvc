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
 * @version		1.1.0 <7/7/2009>
 ********************************** 80 Columns *********************************
 */

class hook_test extends controller {

	public function __construct($config) {
		parent::__construct($config);

		$this->load_config('hooks', 'hook_test');

		print_pre($this->config['hooks']);
	}

	/*
	 * Show an example of how hooks work
	 */
	function index() {

		//Call a function from a hook
		$this->views['content'] = $this->hooks->call('my_first_hook', true);

		//Call two class methods from a hook
		$this->views['content'] .= $this->hooks->call('my_second_hook', 'MY_WORD');

	}


	/*
	 * Show an example of how to pass a value though the URI
	 */
	function say($word = 'You didn\'t say anything...') {

		//Can't just let anything in! Someone might send a XSS...
		$word = preg_replace("/([^a-z0-9_\-\. ]+)/i", ' ', trim($word));

		//Set this as the layout content
		$this->views['content'] = $word;
	}

}