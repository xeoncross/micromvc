<?php
/**
 * Posts Model
 *
 * Provides example DB functionality for the Posts controller
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	Copyright (c) 2009 MicroMVC
 * @license		http://www.gnu.org/licenses/gpl-3.0.html
 * @link		http://micromvc.com
 * @version		1.1.0 <7/7/2009>
 ********************************** 80 Columns *********************************
 */
class posts_model extends base {


	//Check to see if the table exists - install it if not!
	function check_install() {

		//Get the database name
		$dbname = preg_replace('/.+?dbname=([a-z0-9_]+)/i', '$1', $this->db->config['dns']);

		//Create query
		$sql = "SELECT count(*) FROM information_schema.tables
				WHERE table_schema = '". $dbname. "' AND table_name = 'posts'";

		//Send query
		$result = $this->db->query($sql);

		//If the table is not found
		if( ! $result->fetchColumn()) {
			$this->posts->create_table();
		}
	}


	// Create the posts table
	function create_table() {
		$sql = 'CREATE TABLE IF NOT EXISTS `posts` (
			  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `title` varchar(255) NOT NULL,
			  `author` int(10) unsigned NOT NULL,
			  `text` text NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM ;';

		//Create the table
		$this->db->exec($sql);
	}


	/**
	 * Add some sample rows to the database
	 * @return	void
	 */
	function insert() {

		//CREATE NEW ROWS IN THE TABLE
		$data[] = array(
			'title' => 'My First Post',
			'text' => 'Today I finished the beta of my new website!',
			'author' => 'Me'
		);

		$data[] = array(
			'title' => 'My Second Post',
			'text' => 'Now that my site is done I can go relax!',
			'author' => 'Myself'
		);

		$data[] = array(
			'title' => 'My Third Post',
			'text' => 'I\'ll add to this later',
			'author' => 'and I'
		);

		//Add them
		foreach($data as $row) {
			$this->db->insert('posts', $row);
		}

	}


	/*
	 * Get all the posts
	 */
	function fetch() {
		return $this->db->get('posts');
	}


}

