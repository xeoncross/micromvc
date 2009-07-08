<?php
/**
 * my_second_hook
 *
 * Shows an example of using hooks within objects/classes.
 * This class is run from controllers/welcome->hooks();
 * Call this library from http://site.com/welcome/hooks/
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	Copyright (c) 2009 MicroMVC
 * @license		http://www.gnu.org/licenses/gpl-3.0.html
 * @link		http://micromvc.com
 * @version		1.1.0 <7/7/2009>
 ********************************** 80 Columns *********************************
 */
class my_second_hook {

	//Run as soon as loaded
	public function __construct() {
		print '<b>'. get_class($this). ':'. __FUNCTION__. '()</b> was just called!<br />';
	}

	//Replace the word with a new one
	public function filter($word=null) {

		//overwrite the word
		$word = 'NEW WORD';
		return $word;

	}

	//return the word in a sentence
	public function say($word=null) {
		return '<b>'. get_class($this). ':'. __FUNCTION__. '()</b> said: '. $word. '<br />';
	}

	//return the word in a sentence
	public function speak($output=null) {
		return $output. 'Hello From <b>'. get_class($this). ':'. __FUNCTION__. '()</b><br />';
	}


	//Run on shutdown
	public function __destruct() {
		print '<b>'. get_class($this). ':'. __FUNCTION__. '()</b> was just called!<br />';
	}

}
