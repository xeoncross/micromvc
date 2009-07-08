<?php
/**
 * Posts
 *
 * Example of a "post" system that fills a table with several rows useing a
 * model. Then it prints out those rows with a view. Also see "posts_model.php"
 * This model uses auto-generated queries as well as hand written SQL to show
 * how both work.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	Copyright (c) 2009 MicroMVC
 * @license		http://www.gnu.org/licenses/gpl-3.0.html
 * @link		http://micromvc.com
 * @version		1.1.0 <7/7/2009>
 ********************************** 80 Columns *********************************
 */
class posts extends controller {

	function __construct($config=null) {

		//Load the core constructor
		parent::__construct($config);

		//Load the database
		$this->load_database();

		//Load the Model for this controller
		$this->model('posts_model', NULL, 'posts');

		//Check to see if the "posts" table is installed
		$this->posts->check_install();
	}


	//Show the lastest posts
	function index() {

		//Count the total posts
		$count = $this->db->count('posts');

		//If there are no rows
		if($count < 1) {
			//Add three rows
			$this->posts->insert();
		}

		//Fetch every row
		$result = $this->posts->fetch();

		//Setup dat for the view
		$view_data = array(
			'result' => $result,
			'count' => $count
		);

		//Place in a view file
		$this->views['content'] = $this->view('posts/posts', $view_data);

	}


}