<?php defined('SYSTEM_PATH') or die('No direct access');
/**
 * Welcome Controller
 *
 * Loads a simple welcome view.
 * 
 * Note, this controller is only for demonstration purposes! Remove this file
 * before you put your site online!
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
class Controller_Welcome extends Controller {

	// Load a view that shows a welcome message
	function index()
	{
		// Load the content
		$this->views['content'] = load::view('welcome/welcome');
		
		// Load the sidebar
		$this->views['sidebar'] = load::view('sidebar');
	}
}
