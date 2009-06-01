<?php
/**
 * Posts
 *
 * Example of a "post" system that fills a table with several rows useing a 
 * model. Then it prints out those rows with a view. Also see "posts_model.php"
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	Copyright (c) 2009 MicroMVC
 * @license		http://www.gnu.org/licenses/gpl-3.0.html
 * @link		http://micromvc.com
 * @version		1.0.0 <2/20/2009>
 ********************************** 80 Columns *********************************
 */
class posts extends core {

	function __construct($config=null) {

		//Load the core constructor
		parent::__construct($config);

		//Load the database
		$this->load_database();
		
		//Load the Model for this controller
		$this->load('posts_model', 'posts', null, 'models');

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
		$this->data['content'] = $this->view('posts/posts', $view_data);

	}


}