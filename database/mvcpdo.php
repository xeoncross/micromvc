<?php
/**
 * MicroMVC PDO ABSTRACTION
 *
 * This class extends the PDO object to provide DB abstraction - a simple way
 * to query different types of databases without needing to change your code.
 *
 * About PDO:
 * PDO provides a data-access abstraction layer, which means that, regardless of
 * which database you're using, you use the same functions to issue queries and
 * fetch data. PDO does not provide a database  abstraction; it doesn't rewrite
 * SQL or emulate missing features. You should use a full-blown abstraction
 * layer if you need that facility. - http://php.net/manual/en/intro.pdo.php
 *
 * This class fills this gap and provides DB abstraction for common functions
 * like SELECT, REPLACE, INSERT, UPDATE, and DELETE queries. The actual PDO
 * object is not changed in anyway - each of these functions still returns a
 * PDO Statement Object.
 *
 *
 * @todo		Add support for PostgreSQL and MSSQL
 * @todo		Auto-add table prefix (replace `table` with `prefix_table`)
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	Copyright (c) 2009 MicroMVC
 * @license		http://www.gnu.org/licenses/gpl-3.0.html
 * @link		http://micromvc.com
 * @version		1.0.0 <2/20/2009>
 ********************************** 80 Columns *********************************
 */


/**
 * Extend the PDO Object
 */
class mvcpdo extends PDO {

	//Only needed while testing
	//function __construct() { }
	//function quote($text) { return "'". addslashes($text). "'"; }

	/*
	 * TOP LEVEL FUNCTIONS THAT RETURN A RESULT
	 */


	/**
	 * Creates a simple INSERT/REPLACE INTO query.
	 */
	public function insert($tables, $data=array(), $replace=null, $return=true) {
		$query = ($replace ? 'REPLACE' : 'INSERT')
		. ' INTO '. $this->table_list($tables)
		. '('. $this->column_list($data). ')'
		. ' VALUES '
		. $this->value_list($data);

		//Return the results -or a query string?
		return $return ? $this->query($query) : $query;

	}


	/**
	 * Creates a simple REPLACE INTO query.
	 */
	public function replace($tables, $data=array(), $return=true) {
		return $this->insert($tables, $data, true, $return);
	}


	/**
	 * Function: build_update
	 * Creates a full update query.
	 */
	public function update($tables, $data=array(), $conditions=null, $return=true) {
		$query = 'UPDATE '. $this->table_list($tables). ' SET '
		. $this->update_value_list($data)
		.($conditions ? $this->where_list($conditions) : '');

		//Return the results -or a query string?
		return $return ? $this->query($query) : $query;

	}


	/**
	 * Creates a SELECT query.
	 * Supports DISTINCT, FROM, WHERE, GROUP, HAVING, ORDER, LIMIT, JOIN
	 */
	public function select($data=null, $return=true) {

		//Check the columns
		$data['columns'] = (empty($data['columns']) ? null : $data['columns']);

		$query = 'SELECT '. $this->column_list($data['columns'])
		. ' FROM '. $this->table_list($data['tables'])
		. (!empty($data['joins']) ? $this->join($data['joins']) : '')
		. (!empty($data['conditions']) ? $this->where_list($data['conditions']) : '')
		. (!empty($data['group']) ? $this->group_by($data['group']) : '')
		. (!empty($data['order']) ? $this->order_by($data['order']) : '')
		. (!empty($data['limit']) ? $this->limit($data['limit'], (!empty($data['offset']) ? $data['offset'] : '')) : '');

		//Return the results -or a query string?
		return $return ? $this->query($query) : $query;
	}


	/**
	 * Creates a full delete query. Be careful when using this
	 * -if there are no conditions it will EMPTY the table.
	 */
	public function delete($tables, $conditions=null, $return=true) {
		$query = 'DELETE FROM '. $this->table_list($tables)
		.($conditions ? $this->where_list($conditions) : '');

		//Return the results -or a query string?
		return $return ? $this->query($query) : $query;
	}


	/**
	 * Creates a SELECT COUNT(*) query.
	 */
	public function count($tables, $conditions=null, $return=true) {

		$query = 'SELECT COUNT(*) FROM '. $this->table_list($tables)
		. ($conditions ? $this->where_list($conditions) : '');

		//Return the results -or a query string?
		return $return ? $this->query($query) : $query;

	}


	/*
	 * SUPPORT FUNCTIONS
	 */


	/**
	 * Returns the symbol the adapter uses for delimited identifiers.
	 * table = "table"
	 * column = "column"
	 *
	 * @return string
	 */
	public function getQuoteIdentifierSymbol() {
		return '`';
	}


	/**
	 * Quote an identifier. (table = `table`)
	 * @param mixed	the table/column name(s) to quote
	 * @return mixed
	 */
	public function quoteIdentifier($items=null) {

		//Get the symbol used to quote items
		$symbol = $this->getQuoteIdentifierSymbol();

		//If it is an array of Columns or Tables
		if(is_array($items)) {

			foreach($items as $key => $item) {
				//IF it is a "table.field" then we need to break it up
				$item = explode('.', $item, 2);
				$items[$key] = $symbol. implode($symbol. '.'. $symbol, $item). $symbol;
			}

			//Else it is a single column or table name
		} else {
			$items = explode('.', $items, 2);
			$items = $symbol. implode($symbol. '.'. $symbol, $items). $symbol;
		}

		return $items;
	}


	/**
	 * Creates a table list from an array
	 */
	public function table_list($tables=null) {
		return implode(', ', (array)$this->quoteIdentifier($tables)). ' ';
	}


	/**
	 * Creates a column list from an array
	 */
	public function column_list($data=null) {
		//If no columns were given - use "ALL"
		if(!$data) { return '*'; }

		//If this is an array of columns -> values
		if(is_array($data)) {

			//If this is an array where the VALUES are the fields
			if(isset($data[0])) {
				return implode(',', $this->quoteIdentifier($data));
					
				//Else it is an array where the KEYS are the fields
			} else {
				return implode(',', $this->quoteIdentifier(array_keys($data)));
			}

			//Else it is just one column name
		} else {
			return $this->quoteIdentifier($data);
		}

	}


	/**
	 * Creates a list of values to add to DB.
	 * '1','Bob','blue'
	 */
	public function value_list($data=array()) {
		$row = '';
		foreach($data as $value) {
			$row .= $this->quote($value). ', ';
		}
		return '('. rtrim($row, ', '). ')';

	}


	/**
	 * Creates an insert header part.
	 * Full rewrite
	 * (id,name,age)
	 */
	public function group_by($data=null) {
		return ' GROUP BY '. implode(',', $this->quoteIdentifier(((array)$data)));
	}


	/**
	 * Creates an insert header part.
	 * Full rewrite
	 * (id,name,age)
	 */
	public function order_by($data=null) {
		return ' ORDER BY '. implode(',', $this->quoteIdentifier(((array)$data)));
	}


	/**
	 * Creates an update data part.
	 * `column` = 'value'
	 */
	public function update_value_list($data=array(), $type=null) {
		$output = '';

		if(is_array($data)) {
			foreach ($data as $field => $value) {
				$output .= $this->quoteIdentifier($field);
				$output .= ' = '. $this->quote($value). ', ';
			}

			//remove last comma
			return rtrim($output, ', ');
		}
	}


	/**
	 * Creates a WHERE list for use in a query.
	 */
	public function where_list($conditions=array()) {
		$output = ' WHERE ';
		foreach((array) $conditions as $column => $value) {
			//If the value is an aray it must be an IN clause
			if(is_array($value)) {
				$output .= $this->quoteIdentifier($column). $this->in_list($value);
			} else {
				$output .= $this->quoteIdentifier($column). ' = '
				. ($value == '?' ? $value : $this->quote($value)). ' AND ';
			}
		}
		return rtrim($output, ' AND ');
	}


	/**
	 * Creates the in() part of a query.
	 */
	public function in_list($data=array()) {
		$output = ' in(';

		foreach((array) $data as $value) {
			$output .= $this->quote($value). ',';
		}
		return rtrim($output, ','). ')';

	}


	/**
	 * Creates the JOIN part of a query.
	 * [LEFT | RIGHT | FULL] [OUTER | INNER | CROSS]
	 * Only allows one table at a time (but infinite column conditions).
	 */
	public function join($joins=array()) {

		$output = '';

		if($joins && is_array($joins)) {
			foreach($joins as $join) {
				//The type of join
				$output .= ' '. (!empty($join['type']) ? strtoupper($join['type']) : 'LEFT');
				$output .= ' JOIN '. $this->quoteIdentifier($join['table']). ' ON ';
				foreach($join['conditions'] as $field1 => $field2) {
					$output .= $this->quoteIdentifier($field1). ' = '
					. $this->quoteIdentifier($field2);

				}
			}
			return $output;
		}

	}


	/**
	 * Adds a LIMIT and/or OFFSET clause to a statement.
	 *
	 * @param  integer $count
	 * @param  integer $offset OPTIONAL
	 * @return string
	 */
	public function limit($count=0, $offset=0) {
		$count = intval($count);
		if ($count <= 0) {
			trigger_error("LIMIT argument count=$count is not valid");
		}

		$offset = intval($offset);
		if ($offset < 0) {
			trigger_error("LIMIT argument offset=$offset is not valid");
		}

		return " LIMIT $count". ($offset > 0 ? " OFFSET $offset" : '');
	}


	/*
	 * ADDTIONAL METHODS TO BE OVERLOADED BY DB CLASSES
	 */


	/**
	 * Set encoding for the database connection (Default: UTF-8)
	 */
	public function set_encoding($value='UTF-8') {
		return;
	}


	/**
	 * Show all tables in database that optionally match $like
	 */
	public function show_tables($like=null) {
		return array();
	}


	/**
	 * Explain all columns within a table
	 */
	public function show_columns($table=null) {
		return array();
	}


	/**
	 * Show all columns/fields within a table
	 */
	public function show_fields($table=null) {
		return array();
	}

}



/*
 * Class DB provides a handle for the DB connection so that
 * it is accessible from anywhere in our script.
 */
class db {

	// Declare instance
	static $instance = NULL;

	/**
	 * Return DB instance or create intitial connection
	 *
	 * @return object (PDO)
	 * @access public
	 */
	public function instance($config=null) {

		//If there is NO instance already
		if (!self::$instance) {
			//Create one!
			self::connect($config);
		}

		//Return the instance
		return self::$instance;
	}

	/**
	 * Function: connect
	 * Connects to the SQL database.
	 */
	public function connect($config=null) {

		//global $config;
		if(!$config) { return; }

		//If this install of PHP does NOT have the right driver
		If(!in_array($config['type'], PDO::getAvailableDrivers())) {

			//Die because the rest of the site will not work!
			trigger_error('The PDO Database type <b>'. $config['type']
			. '</b> is not supported on this PHP install.', E_USER_ERROR);
			return;

		}


		//Require the class that we are going to use
		require_once($config['type']. '.php');


		try {

			self::$instance = new $config['type']($config['type']. ':'
			//If the host name is set
			. ($config['host'] ? 'host='. $config['host']. ';' : '')
			//If the port number is set
			. ($config['port'] ? 'port='. $config['port']. ';' : '')
			//If the database is some other type than SQLite
			. ($config['type'] != 'sqlite2' ? 'dbname=' : '')
			//Database name
			. $config['name'],
			//The Username (if any)
			($config['user'] ? $config['user'] : null),
			//The Password (if any)
			($config['pass'] ? $config['pass'] : null),
			//Use a Persistent Connection? http://php.net/manual/en/pdo.connections.php
			($config['persistent'] ? array(PDO::ATTR_PERSISTENT => true) : null));

			//Set the error mode to show warning along with error codes
			self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

		} catch (PDOException $error) {
			trigger_error($error->getMessage());
		}
	}

}