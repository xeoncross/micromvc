<?php
/**
 * MicroMVC PDO ABSTRACTION
 *
 * This class extends the mvcpdo object to provide MySQL specific syntax.
 *
 *
 * @todo		Add support for creating tables from an array or object
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	Copyright (c) 2009 MicroMVC
 * @license		http://www.gnu.org/licenses/gpl-3.0.html
 * @link		http://micromvc.com
 * @version		1.0.0 <2/20/2009>
 ********************************** 80 Columns *********************************
 */
class mysql extends mvcpdo {

	/**
	 * Returns the symbol the adapter uses for delimited identifiers.
	 * table = `table`
	 * column = `column`
	 *
	 * @return string
	 */
	public function getQuoteIdentifierSymbol() {
		return "`";
	}


	/**
	 * Set encoding for the database connection (Default: UTF-8)
	 */
	public function set_encoding($value='utf8') {
		$this->query('SET NAMES '. $value .';');
	}

	/**
	 * Show all tables in database that optionally match $like
	 */
	public function show_tables($like=null) {
		//$like can have wild cards like "%value%"
		return 'SHOW TABLES'. ($like ? ' LIKE \''. $like.'\'' : '');
	}

	/**
	 * Explain all columns within a table
	 */
	public function show_columns($table=null) {
		 
		//if the table name is empty/null
		if(!$table) { return; }

		//Show all tables as a database
		$db_object = $this->query('SHOW COLUMNS FROM '. $table);

		/* RESULT:
		 Array
		 (
		 [Field] => id
		 [Type] => int(7)
		 [Null] =>
		 [Key] => PRI
		 [Default] =>
		 [Extra] => auto_increment
		 )
		 */

		//Define the variable
		$columns = array();

		/* NOT working yet...
		 //If we found some rows
		 if ($db_object->fetchColumn() == 0) {
		 return;
		 }
		 */

		while ($row = $db_object->fetch(PDO::FETCH_ASSOC)) {

			//If we found the "ENUM" column name in the string (we need to get the values)
			if(strpos($row['Type'], 'enum') !== false) {

				$row['type'] = 'enum';
				$row['length'] = null;

				//Get the posible enum values for this column (i.e. "enum('active','disabled')")
				$options = explode("','", preg_replace("/(enum|set)\('(.+?)'\)/","\\2", $row['Type']));
				foreach ($options as $value) {
					$row['values'][] = $value;
				}

				//Else it is a varchar, text, blob, int, etc..
			} else {

				//We need to break the "Type" value from "int(20)" into "int" and "20"
				preg_match('/([a-z]+)(\(([0-9]{1,5})\))?/i', $row['Type'], $matches);

				//Add column type
				$row['type'] = $matches[1];
				$row['values'] = null;

				//If there was a number (i.e. 11) on the Type value
				//add it to a new array element called "Length"
				$row['length'] = (isset($matches[3]) ? $matches[3] : null);

			}

			//Create a custom array descibing the column
			$columns[strtolower($row['Field'])] = array('name' => $row['Field'], 'default' => $row['Default'], 'key' => $row['Key'],
                    'length' => $row['length'],'type' => $row['type'], 'values' => $row['values']);

			//NOTE: Not all DB's are like MySQL so we can't just return ($columns[] = $row)
			//Instead we must make an array with values that are common among DB's (like "type", "length", and "name")

		}

		//Return an array containing columns and their values
		return $columns;

	}

}
