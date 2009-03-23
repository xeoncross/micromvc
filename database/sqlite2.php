<?php
/**
 * MicroMVC PDO ABSTRACTION
 *
 * This class extends the mvcpdo object to provide SQLite specific syntax.
 *
 *
 * @todo		Add support for creating tables from an array
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	Copyright (c) 2009 MicroMVC
 * @license		http://www.gnu.org/licenses/gpl-3.0.html
 * @link		http://micromvc.com
 * @version		1.0.0 <2/20/2009>
 ********************************** 80 Columns *********************************
 */
class sqlite2 extends mvcpdo {

	/**
	 * Returns the symbol the adapter uses for delimited identifiers.
	 * table = "table"
	 * column = "column"
	 *
	 * @return string
	 */
	public function getQuoteIdentifierSymbol() {
		return '"';
	}


	/**
	 * Set encoding for the database connection (Default: UTF-8)
	 */
	public function set_encoding($value='UTF-8') {
		$this->query('PRAGMA encoding = "'. $value. '"');
	}

	/**
	 * Show all tables in database that optionally match $like
	 */
	public function show_tables($like=null) {
		//$like can have wild cards like "%value%"
		return 'SELECT * FROM sqlite_master WHERE type = "table"'
		. ($like ? 'AND name LIKE \''. $like. '\'' : '');

	}

	/**
	 * Explain all columns within a table
	 */
	public function show_columns($table=null) {
		$db_object = $this->query('PRAGMA table_info('. $table. ')');
		/* RESULT:
		 Array
		 (
		 [cid] => 0
		 [name] => id
		 [type] => INTEGER
		 [notnull] => 99
		 [dflt_value] =>
		 [pk] => 1
		 )
		 */
		$columns = array();

		while ($row = $db_object->fetch(PDO::FETCH_ASSOC)) {
			$columns[] = array(
                'name' => $row['name'],
                'default' => $row['dflt_value'],
                'key' => ($row['pk'] ? 'PRI' : ''),
                'length' => ($row['notnull'] ? $row['notnull'] : null),
                'type' => $row['integer'],
			//For MySQL ENUM - not used here
                'values' => '');
		}
		return $columns;
	}

}