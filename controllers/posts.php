<?php
/**
 * Posts
 *
 * Example of a "post" system that creates a simple SQLite table and fills
 * it with several rows useing a model. Then it prints out those rows with
 * a view. Each time this controller is called it deletes the sqlite file
 * and re-creates a new.
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

		//Load the Model for this controller
		$this->load('posts_model', 'posts', null, 'models');

		//Delete the database if it is found
		$this->posts->delete_table('sqlite2');

		//Load the database
		$this->load_database();

		//Create a new table
		$this->posts->create_table();

		//Add three rows
		$this->posts->insert();

	}

	//Show the lastest posts
	function index() {

		//Set config
		$data = array('tables' => 'posts');

		//Count the total posts
		$count = $this->db->count($data);

		//Select each row
		$result = $this->db->select($data);

		//Place in a view file
		$this->data['content'] = $this->view('posts/posts',
		array('result' => $result, 'count' => $count), true);

	}


}