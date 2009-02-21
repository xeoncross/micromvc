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
 * @version		1.0.0 <2/20/2009>
 ********************************** 80 Columns *********************************
 */
class posts_model {


	/**
	 * Add some sample rows to the sample database
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

	/**
	 * Create a new SQLite table
	 * @return	void
	 */
	function create_table() {

		$query = '
		CREATE TABLE posts ( 
		id INTEGER PRIMARY KEY,
		title varchar(100),
		text text,
		author INTEGER
		);';

		//Create the new table
		$this->db->query($query);
	}

	/**
	 * Delete the SQLite DB file if it exists
	 * @param	string	$name = table name
	 * @return	void
	 */
	function delete_table($name=null) {
		if(file_exists($name)) {
			unlink($name);
		}
	}

}

