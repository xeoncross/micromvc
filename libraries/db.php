<?php
/**
 * PDOORM - PDO Object/Relational Mapping System
 * By David Pennington
 *
 * This class extends the PDO class to provide DB query abstraction in the form
 * of an easy-to-use ORM pattern. This provides a simple way to query different
 * types of databases without needing to change your code.
 *
 * About PDO:
 * PDO provides a data-access abstraction layer, which means that, regardless of
 * which database you're using, you use the same functions to issue queries and
 * fetch data. PDO does not provide a database abstraction; it doesn't rewrite
 * SQL or emulate missing features. You should use a full-blown abstraction
 * layer if you need that facility. - http://php.net/manual/en/intro.pdo.php
 *
 * This class fills this gap and provides DB abstraction for common functions
 * like SELECT, REPLACE, INSERT, UPDATE, and DELETE queries. The actual PDO
 * object is not changed in anyway - each of these functions still returns a
 * PDOStatement Object and you can use all of the native PDO methods.
 *
 *
 * *******************
 * ** Example Usage **
 * *******************
 *
 *
 * ******************************************** Simple SELECT
 *
 * $db->where('users.id', 1);
 * $result = $db->get('users');
 *
 * -------------------------------------------------------------------------
 * SQL: SELECT * FROM users WHERE `users`.`id` = 1
 * -------------------------------------------------------------------------
 *
 *
 * ******************************************** Complex SELECT
 *
 * $db->select('u.id as uid,u.user_login,u.user_email,p.ID as pid,p.post_date,p.post_title');
 * $db->from('users AS u');
 * $db->join('posts AS p', 'p.post_author = u.id');
 * $db->limit(10, 2);
 *
 * //Count the rows first
 * print $db->count(). ' Rows found';
 *
 * //Run Query
 * $result = $db->get();
 *
 * -------------------------------------------------------------------------
 * SQL: SELECT `users`.`id` as uid,`users`.`user_login`,`users`.`user_email`,
 * `posts`.`ID` as pid,`posts`.`post_date`,`posts`.`post_title`
 * FROM users  LEFT JOIN posts  ON `posts`.`post_author` = `users`.`id`
 * WHERE `users`.`ID` = 1
 * LIMIT 2, 10
 * -------------------------------------------------------------------------
 *
 *
 * @todo		Add support for PostgreSQL and MSSQL
 * @todo		Auto-add table prefix (replace `table` with `prefix_table`)
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	Copyright (c) 2009 MicroMVC
 * @license		http://www.gnu.org/licenses/gpl-3.0.html
 * @link		http://micromvc.com
 * @version		1.1.0 <7/7/2009>
 ********************************** 80 Columns *********************************
 */


/** Config array:
$config = array(
	'type'	=> 'mysql',
	'dns'	=> 'host=127.0.0.1;port=3306;dbname=test',
	'port'	=> '',
	'name'	=> '',
	'user'	=> 'root',
	'pass'	=> '',
	'options'	=> null,//PDO::ATTR_PERSISTENT, PDO::ATTR_STATEMENT_CLASS, etc
);
*/

/*
 * Class DB provides a handle for the DB connection so that
 * it is accessible from anywhere in our script.
 */
class db {

	//DB Object instance
	private static $instance	= array();
	//DB Handle Resource
	public $pdo					= NULL;
	//Config object
	public $config				= array();
	//Should table aliases be expanded to real names?
	public $remove_alias		= FALSE;
	//Result Object (PDOStatement)
	public $result				= NULL;
	//Array of all queries run
	public $queries				= array();
	//Should we record queries?
	public $log_queries			= TRUE;
	//Should we replace aliases with real names?
	public $remove_aliases		= TRUE;
	//Should queries be run or returned
	public $return_query		= FALSE;
	//Should table/column names in queries be quoted?
	public $quote_fields		= TRUE;
	//Set default fetch mode
	public $fetch_mode			= PDO::FETCH_CLASS;
	//Show database errors?
	public $error_mode			= PDO::ERRMODE_WARNING;
	//Last table selected (passed to row class)
	public $last_table			= NULL;
	//Use the row class?
	public $row_class			= 'ORM_Row';

	//Active Record Clauses
	public $orm_select			= '*';
	public $orm_from			= '';
	public $orm_join			= array();
	public $orm_where			= array();
	public $orm_having			= array();
	public $orm_group_by		= '';
	public $orm_order_by		= '';
	public $orm_limit			= NULL;
	public $orm_offset			= NULL;
	public $orm_distinct		= NULL; //SELECT DISTINCT

	//Used for UPDATE and INSERT
	public $orm_set				= array();


	/*
	 ------------------------------------------
	 System Utility Functions
	 ------------------------------------------
	 */


	/*
	 * On first load register this object and connect
	 */
	public function __construct($config=null) {
		//Set the instance of this class
		self::$instance[] =& $this;

		//Set the config
		$this->setup($config);

		//Connect to the db
		$this->connect();
	}


	/*
	 * Use this same object from here out
	 */
	public static function get_instance($id=null){
		//Get the instance
		if($id) {
			return self::$instance[$id];
		} else {
			//return the last instance made
			return end(self::$instance);
		}
	}


	/*
	 * Test if this driver is installed
	 */
	public function driver_installed() {

		//If this install of PHP does NOT have the right PDO driver
		If(!in_array($this->config['type'], PDO::getAvailableDrivers())) {
			print_pre(PDO::getAvailableDrivers());
			trigger_error('The PDO Database type <b>'. $this->config['type']
			. '</b> is not supported on this PHP install.', E_USER_ERROR);
		}

		//Driver is installed
		return TRUE;
	}


	/**
	 * Create the PDO object and connect to the database
	 */
	public function connect() {

		//If there is no config
		if(!$this->config) { return; }

		//If the PDO driver is NOT installed
		if(!$this->driver_installed()) { return; }

		try {

			//Connect to the database
			$this->pdo = new PDO(
			$this->config['type']. ':'. $this->config['dns'],
			$this->config['user'],
			$this->config['pass'],
			$this->config['options']
			);

			//Set the error mode to show warning along with error codes
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, $this->error_mode);


		} catch (PDOException $error) {
			trigger_error($error->getMessage());
		}

	}


	/*
	 * Clear the AR records for the next query
	 */
	public function clear() {
		$this->orm_select	= '*';
		$this->orm_from		= '';
		$this->orm_join		= array();
		$this->orm_where	= array();
		$this->orm_group_by	= '';
		$this->orm_order_by	= '';
		$this->orm_having	= array();
		$this->orm_limit	= NULL;
		$this->orm_offset	= NULL;
		$this->orm_distinct	= NULL;
		$this->orm_set		= array();
		//print_pre($this->queries, $this->orm_where);
	}


	/*
	 * Setup the variables in this class
	 */
	public function setup($config=null) {
		$this->config = $config;
	}


	/*
	 ------------------------------------------
	 Active Record Functions
	 ------------------------------------------
	 */


	/**
	 * Returns the symbol the adapter uses for delimited identifiers.
	 * @return string
	 */
	public function quote_identifier() {
		return '"';
	}


	/**
	 * Quotes a value to make it database safe
	 *
	 * @param	mixed
	 * @return	mixed
	 */
	public function quote($value=null) {

		//Return "IS NULL"
		if(is_null($value)) {
			return 'NULL';//$value;
		}

		//Why quote a number?
		if(is_numeric($value)) {
			return $value;
		}

		//Allow prepared statements
		if($value == '?') {
			return $value;
		}

		//Quote using the database-specific method
		return $this->pdo->quote($value);
	}


	/**
	 * Inclose the table name with the proper quote identifier
	 *
	 * @param string	The table string
	 * @return string
	 */
	public function quote_table($table=null) {

		//If we are not allowed to quote stuff
		if( ! $this->quote_fields) {
			return $table;
		}

		//Get the identifier separator
		$sep = $this->quote_identifier();

		//If not already quoted
		if(strpos($table, $sep) !== FALSE) {
			return $table;
		}

		//Break it apart
		preg_match('/([a-z0-9\_]+)(\)?\s+AS\s+)?([a-z]+)?/i', $table, $matches);

		//print_pre($matches, $table);

		//If no alias
		if(empty($matches[2])) {
			return $sep. $matches[1]. $sep;
		}

		//Quote `table` AS `alias`
		return $sep. $matches[1]. $sep. ' AS '. $sep. $matches[3]. $sep;

	}

	/*
	 * Place the database "field quotes" around all table.column values
	 * in the query string. i.e. table.column = `table`.`column`
	 *
	 * Optional: Replace aliases with the full table name. Only use
	 * this on SELECT queries as it might alter INSERT/UPDATE data!
	 *
	 * You MUST use the "FROM/JOIN table AS alias" format when enabling the
	 * $remove_aliases option. "FROM/JOIN table alias" won't work!
	 *
	 * @param string
	 * @param boolean
	 * @return string
	 */
	public function quote_fields($string=null, $remove_aliases=NULL) {

		//Set alias option?
		if(!is_null($remove_aliases)) {
			$this->remove_aliases = $remove_aliases;
		}

		//Get the identifier separator
		$sep = $this->quote_identifier();

		//$time = microtime(true);

		//If we should replace all aliases with the real table names
		if($this->remove_aliases) {

			//Find all aliases
			preg_match_all('/(?:(?:FROM|JOIN)\s+\(?([a-z0-9\_]+)\)?\s+)(AS\s+([a-z]+))/i', $string, $table_aliases);

			//Array to store alias -> real_table_name pairs
			$aliases = array();

			//Go though each alias and assign them as the key for the real table
			foreach($table_aliases[3] as $key => $alias) {
				//Get the real table name
				$aliases[$alias] = $table_aliases[1][$key];

				//Remove the "JOIN posts AS p" and replace with "JOIN posts"
				$alias_line = str_replace($table_aliases[2][$key], '', $table_aliases[0][$key]);

				//Remove the alias command from the query
				$string = str_replace($table_aliases[0][$key], $alias_line, $string);
			}

		}

		//Find all table.column pairs
		preg_match_all("/\b([a-z\_]+)\.([a-z\_]+)\b/i", $string, $pairs);

		//print_pre($table_aliases);
		//print_pre($pairs);

		//Look for each pair (alias.column or table.column)
		foreach($pairs[1] as $key => $table) {

			//If this is an alias - replace with real table name
			if($this->remove_aliases && !empty($aliases[$table])) {
				$table = $aliases[$table];
			}

			//Format the pair in `table`.`column` format
			$pair = $sep. $table. $sep. '.'. $sep. $pairs[2][$key]. $sep;

			//Replace the old alias.column with `table`.`column`
			$string = str_replace($pairs[0][$key], $pair, $string);

		}

		//print '<h2>time: '. (microtime(true) - $time). 'ms</h2>';
		return $string;

	}


	/**
	 * The "set" function.  Allows key/value pairs to be set for inserting or updating
	 *
	 * @access	public
	 * @param	mixed
	 * @param	string
	 * @param	boolean
	 * @return	object
	 */
	function set($key=null, $value = '') {
		//Must have a key name
		if(!$key) {return;}

		//Make it something we can iterate over
		if (!is_array($key) && !is_object($key)){
			$key = array($key => $value);
		}

		//Add each key to the set
		foreach ($key as $k => $value) {
			$this->orm_set[$k] = $this->quote($value);
		}

	}


	public function delete($table = NULL, $where = NULL) {

		if($where) {
			$this->where($where);
		}

		//If there is a WHERE clause
		if(empty($this->orm_where)) {
			return FALSE;
		}

		//Create the Delete SQL
		$sql = 'DELETE FROM '. $this->quote_table($table);

		//Remove the first AND/OR condition as it is invalid
		$this->orm_where[0] = preg_replace('/(AND|OR) /', '', $this->orm_where[0]);

		//Add all of the where clauses
		$sql .= "\nWHERE ". implode("\n", $this->orm_where);

		return $this->exec($sql);
	}

	/**
	 * Creates a simple INSERT/REPLACE INTO query.
	 */
	public function insert($table, $data = NULL) {
		return $this->write($table, $data, NULL, 'insert');
	}


	/**
	 * Creates a simple INSERT/REPLACE INTO query.
	 */
	public function update($table, $data = NULL, $where=NULL) {
		return $this->write($table, $data, $where, 'update');
	}


	/**
	 * Insert statement
	 *
	 * Generates a platform-specific insert string from the supplied data
	 *
	 * @param	string	the table name
	 * @param	array	the insert keys
	 * @param	array	the insert values
	 * @return	string
	 */
	public function insert_string($table, $keys, $values) {

		//Get the identifier separator
		$sep = $this->quote_identifier();

		//Create column list
		$columns = $sep. implode($sep. ', '. $sep, $keys). $sep;

		//Return the query
		return 'INSERT INTO '. $this->quote_table($table) .' ('. $columns. ') VALUES (' .implode(', ', $values). ')';

	}


	/**
	 * Update statement
	 *
	 * Generates a platform-specific update string from the supplied data.
	 * When calling this function from outside this object make sure to
	 * escape/quote every value submitted.
	 *
	 * @param	string	the table name
	 * @param	array	the update data
	 * @return	string
	 */
	public function update_string($table, $values) {

		//Get the identifier separator
		$sep = $this->quote_identifier();

		//Get values
		foreach($values as $key => $value) {
			$fields[] = $sep. $key. $sep. " = ". $value;
		}

		$sql = "UPDATE ". $this->quote_table($table). " SET ". implode(', ', $fields);

		//If there is a WHERE clause
		if(!empty($this->orm_where) && is_array($this->orm_where)) {

			//Remove the first AND/OR condition as it is invalid
			$this->orm_where[0] = preg_replace('/(AND|OR) /', '', $this->orm_where[0]);

			$sql .= "\nWHERE ". implode("\n", $this->orm_where);
		}

		//Return the comepleted Query
		return $sql;
	}


	/**
	 * Creates a simple INSERT/REPLACE INTO query.
	 */
	public function write($table, $data, $where=null, $type) {

		//Set the data (if any)
		$this->set($data);

		if($where) {
			$this->where($where);
		}

		//Generate the INSERT string
		if($type == 'insert') {
			$sql = $this->insert_string($table, array_keys($this->orm_set), $this->orm_set);

			//Generate the UPDATE string
		} else {
			$sql = $this->update_string($table, $this->orm_set);
		}

		//Remove the AR data
		$this->clear();

		if($this->return_query) {
			return $sql;
		}

		//Fetch the result object
		$this->result = $this->query($sql);

	}


	/**
	 * Sets a flag which tells the query string compiler to add DISTINCT
	 *
	 * @param	bool
	 * @return	void
	 */
	public function distinct($val = TRUE) {
		$this->orm_distinct = ($val ? TRUE : NULL);
	}


	/**
	 * Generates the FROM portion of the query
	 *
	 * @access	public
	 * @param	string
	 * @return	void
	 */
	public function from($from=null) {

		//Quote the table name
		$from = $this->quote_table($from);

		//Add this to our from clauses
		$this->orm_from = 'FROM '. $from;

		//If we are going to use the row class, then we need to know where the row came from
		if($this->row_class) {

			//Look for a valid table name
			preg_match('/^(?:[\'"`]*)([a-z0-9\_]+)\b/i', $from, $table);

			//If a valid table was found
			if(!empty($table[1])) {
				//Set last table used for the $row->save() method
				$this->last_table = $table[1];
			} else {
				//Reset the value incase some query was run before this
				//and we might inherit the table name!
				$this->last_table = NULL;
			}
		}

	}


	/**
	 * Generates the JOIN portion of the query
	 *
	 * @access	public
	 * @param	string	the table name
	 * @param	string	the join condition
	 * @param	string	the type of join
	 * @return	object
	 */
	public function join($table=null, $condition=null, $type = 'LEFT') {

		//Make sure it is uppercase
		$type = strtoupper(trim($type));

		//Only allow this type if it is a REAL join type
		if(!in_array($type, array('LEFT', 'RIGHT', 'OUTER', 'INNER', 'LEFT OUTER', 'RIGHT OUTER'))) {
			$type = '';
		}

		// Assemble the JOIN statement
		$this->orm_join[] = $type.' JOIN '. $this->quote_table($table).' ON '.$condition;

	}


	/**
	 * alias for where()
	 */
	public function or_where($column = NULL, $value = NULL) {
		$this->where($column, $value, 'OR');
	}


	/**
	 * alias for where()
	 */
	public function where_in($column = NULL, $value = NULL) {
		$this->where($column, $value, 'AND');
	}


	/**
	 * alias for where()
	 */
	public function or_where_in($column = NULL, $value = NULL) {
		$this->where($column, $value, 'OR');
	}


	/**
	 * alias for where()
	 */
	public function where_not_in($column = NULL, $value = NULL) {
		$this->where($column, $value, 'AND', 'NOT');
	}


	/**
	 * alias for where()
	 */
	public function or_where_not_in($column = NULL, $value = NULL) {
		$this->where($column, $value, 'OR', 'NOT');
	}


	/**
	 * Where
	 *
	 * Generates the WHERE portion of the query. Separates multiple calls
	 * with AND or OR and can handle IS NULL, in(), NOT in(), =, !=, >, <,
	 * and plain column = value clauses.
	 *
	 * @param	mixed
	 * @param	mixed
	 * @param	string
	 * @param	string
	 * @return	void
	 */
	public function where($column = NULL, $value = NULL, $type = 'AND', $in = '') {

		//Must be in array format
		if (!is_array($column)){
			$column = array($column => $value);
		}

		//Go though each statement
		foreach ($column as $field => $value) {

			//Value appears not to have been set, set to "IS NULL"
			if (is_null($value)){
				//If is a NULL value - state that!
				$this->orm_where[] = $type. ' '. $field. ' IS NULL';


				//Else if this is an array
			} elseif($value && is_array($value)) {

				//Prepare each value
				foreach($value as $key => &$v) {
					$v = $this->quote($v);
				}

				//Create the where list
				$this->orm_where[] = $type. ' '. $field. ' '. $in. ' in ('. implode(',', $value). ')';


				//Else it is just a plain where clause
			} else {

				//If an operator is not already set!
				if(!preg_match('/[=><]/', $field)) {
					//Default to "equals"
					$field .= ' = ';
				}

				$this->orm_where[] = $type. ' '. $field. $this->quote($value);
			}

		}

	}


	/**
	 * alias for like()
	 */
	public function or_like($column=null, $match = '', $side = 'both') {
		$this->like($column, $match, $side, null, 'OR');
	}


	/**
	 * alias for like()
	 */
	public function not_like($column=null, $match = '', $side = 'both') {
		$this->like($column, $match, $side, 'NOT', 'AND');
	}


	/**
	 * alias for like()
	 */
	public function or_not_like($column=null, $match = '', $side = 'both') {
		$this->like($column, $match, $side, 'NOT', 'OR');
	}


	/**
	 * Generates a "column LIKE '%value%'" portion of the query. Separates
	 * multiple calls with "AND" or "OR" and can also handle "NOT LIKE".
	 *
	 * @param	mixed
	 * @param	mixed
	 * @param	string
	 * @param	string
	 * @return	object
	 */
	public function like($column=null, $match = '', $side = 'both', $not = '', $type = 'AND') {

		//Which side should we catch-all?
		if($side == 'right') {
			$match = $this->quote($match. '%');

		} elseif($side == 'left') {
			$match = $this->quote('%'. $match);

		} else {
			$match = $this->quote('%'. $match. '%');
		}

		//Add this to our where Clause
		$this->orm_where[] = "$type $column $not LIKE $match";

	}


	/**
	 * GROUP BY
	 */
	function group_by($value=null) {
		$this->orm_group_by = $value;
	}


	/**
	 * ORDER BY
	 */
	function order_by($value=null, $direction = 'DESC') {
		$this->orm_order_by = $value. ' '. $direction;
	}


	/**
	 * Sets the HAVING value and separates multiple calls with AND
	 *
	 * @param	mixed
	 * @param	string
	 * @param	string
	 * @param	boolean
	 */
	public function having($column, $value = '', $type = 'AND', $escape = TRUE) {

		//Must be in array format
		if (!is_array($column)){
			$column = array($column => $value);
		}

		//Go though each statement
		foreach ($column as $key => $value) {

			//Value appears not to have been set, set to "IS NULL"
			if (is_null($value)){
				//If is a NULL value - state that!
				$this->orm_having[] = $type. ' '. $column. ' IS NULL';

			} else {
				//If an operator is not already set!
				if(!preg_match('/[=><]/', $key)) {
					//Default to "equals"
					$key .= ' = ';
				}

				//Add this to our having
				$this->orm_having[] = $type. ' '. $key. ($escape ? $this->quote($value) : $value);
			}
		}

	}


	/**
	 * Sets the LIMIT value
	 *
	 * @param	integer	the limit value
	 * @param	integer	the offset value
	 */
	public function limit($value=NULL, $offset = NULL) {

		$this->orm_limit = $value;

		if (is_numeric($offset)) {
			$this->orm_offset = $offset;
		}

	}


	/**
	 * Sets the OFFSET value
	 *
	 * @param	integer	the offset value
	 */
	public function offset($offset=NULL) {
		$this->orm_offset = $offset;
	}


	/**
	 * Generates the SELECT portion of the query
	 *
	 * @param	string
	 */
	public function select($select = '*', $escape = NULL){
		//Register the select
		$this->orm_select = $select;
	}


	/**
	 * Compile the SELECT statement
	 *
	 * Generates a query string based on which functions were used.
	 * Should not be called directly.  The get() function calls it.
	 *
	 * @return	string
	 */
	public function compile_select() {

		// Write the "select" portion of the query
		$sql = ( ! $this->orm_distinct) ? 'SELECT ' : 'SELECT DISTINCT ';

		//Certain fields given?
		$sql .= ($this->orm_select ? $this->orm_select : '* ');

		// ----------------------------------------------------------------

		// Write the "FROM" portion of the query
		$sql .= "\n". $this->orm_from;

		// ----------------------------------------------------------------

		// Write the "JOIN" portion of the query
		if($this->orm_join && is_array($this->orm_join)) {
			//Add the join clauses
			$sql .= ' '. implode("\n", $this->orm_join);
		}

		/*
		 * Note that the WHERE clause contains the LIKE clauses as LIKE is
		 * just a string function and not really a clause of it's own.
		 * http://dev.mysql.com/doc/refman/5.0/en/string-functions.html
		 */

		//If there is a WHERE clause
		if(!empty($this->orm_where) && is_array($this->orm_where)) {

			//Remove the first AND/OR condition as it is invalid
			$this->orm_where[0] = preg_replace('/(AND|OR) /', '', $this->orm_where[0]);

			$sql .= "\nWHERE ". implode("\n", $this->orm_where);
		}

		// ----------------------------------------------------------------

		//If there is a having clause
		if(!empty($this->orm_having) && is_array($this->orm_having)) {

			//Remove the first AND/OR condition as it is invalid
			$this->orm_having[0] = preg_replace('/(AND|OR) /', '', $this->orm_having[0]);

			$sql .= "\nHAVING ". implode("\n", $this->orm_having);
		}

		// ----------------------------------------------------------------

		// Write the "GROUP BY" portion of the query
		if ($this->orm_group_by) {
			$sql .= "\nGROUP BY ". $this->orm_group_by;
		}

		// ----------------------------------------------------------------

		// Write the "ORDER BY" portion of the query
		if ($this->orm_order_by) {
			$sql .= "\nORDER BY ". $this->orm_order_by;
		}

		// ----------------------------------------------------------------

		// Write the "LIMIT" portion of the query
		if (is_numeric($this->orm_limit)) {
			$sql .= "\nLIMIT ";

			//Add an offset?
			if (is_numeric($this->orm_offset)) {
				$sql .= $this->orm_offset. ', ';
			}

			$sql .= $this->orm_limit;
		}

		return $sql;
	}


	/*
	 ------------------------------------------
	 Statement & Result Functions
	 ------------------------------------------
	 */


	/**
	 * Get
	 *
	 * Compiles the select statement based on the other functions called
	 * and runs the query
	 *
	 * @param	string	the table
	 * @param	boolean	return the query?
	 * @param	boolean	save the query?
	 * @return	object
	 */
	public function get($table = '', $return_query = NULL, $save = FALSE) {

		//If they passed the table
		if($table) {
			$this->from($table);
		}

		//If we are passing a save option
		if($return_query != null) {
			$this->return_query = $return_query;
		}

		//Build the query
		$sql = $this->compile_select();

		//Quote the fields
		if($this->quote_fields) {
			$sql = $this->quote_fields($sql);
		}

		//Remove the AR data?
		if($save) {
			$this->clear();
		}

		//If we are just returning the query
		if($this->return_query) {
			return $sql;
		}

		return $this->query($sql);

	}


	/**
	 * Wrapper for the PDO exec function which allows us to log the query
	 * @param $sql
	 * @return mixed
	 */
	public function exec($sql = NULL) {

		//Add the query to the list
		if($this->log_queries) {
			$this->queries[] = $sql;
		}

		return $this->pdo->exec($sql);
	}


	/*
	 * Run the PDO::query() method and return results
	 */
	public function query($sql=null) {

		//Add the query to the list
		if($this->log_queries) {
			$this->queries[] = $sql;
		}

		//Fetch and store the PDOStatement Object
		$this->result = $this->pdo->query($sql);

		if(!$this->result) { return; }

		//If this is NOT a class fetch
		if($this->fetch_mode != PDO::FETCH_CLASS) {
			//Set default fetch method
			$this->result->setFetchMode($this->fetch_mode);

		} else {
			//Set default fetch method to our Row class
			$this->result->setFetchMode(PDO::FETCH_CLASS, $this->row_class);
		}

		//return the object
		return $this->result;
	}


	/**
	 * Creates a SELECT COUNT query from the current AR Clauses
	 * Use this to figure out the number of rows for a query.
	 *
	 * @param string	COUNT(*) statement
	 * @param boolean	save the AR clauses
	 */
	 public function count($table = NULL, $select='COUNT(*)', $save=TRUE) {

	 	//We don't want to erase the current select statement
	 	$temp = $this->orm_select;

	 	//Set the new SELECT COUNT statement
		$this->select($select);

		//Fetch the result and save(?) the AR Clauses.
		$result = $this->get($table,NULL,$save);

	 	//If we are just returning the query
		if($this->return_query) {
			return $result;
		}

		//Restore the old select statement
		$this->select($temp);

		//Return the number of found rows
		return $result->fetchColumn();

	}


	/*
	 ------------------------------------------
	 Alias Functions
	 ------------------------------------------
	 */



	/**
	 * Alias for get and where
	 */
	public function get_where($table = '', $where = NULL, $return_query = NULL, $save = TRUE) {
		$this->where($where);
		return $this->get($table, $return_query, $save);
	}


	/*
	 * Alias for fetching the result from the PDOStatement
	 */
	public function fetch() {
		return $this->result->fetch();
	}


	/**
	 * Returns the last query run.
	 * @return  string
	 */
	public function last_query() {
	   return end($this->queries);
	}


	/*
	 * Returns the last insert ID number created
	 */
	public function insert_id() {
		return $this->pdo->lastInsertId();
	}


	/*
	 * Access PDO methods as it we were the PDO object
	 */
	public function __call($name, $arguments) {
		//return call_user_func(array($this->pdo, $name), $arguments);
		return $this->pdo->$name($arguments);
	}


	/*
	 * Access PDO properties as if we were the PDO object
	 */
	public function __get($name) {
		return $this->pdo->$name;
	}


	/*
	 * Set PDO properties as if we were the PDO object
	 */
	public function __set($name, $value) {
		$this->pdo->$name = $value;
	}


	/*
	 * If someone tries to use this object as a string
	 * just return the last query.
	 */
	public function __toString() {
		return $this->last_query();
	}


	/*
	 * Print out all of the queries run using <pre> tags
	 */
	public function print_queries() {
		foreach($this->queries as $query) {
			print '<pre>'. str_replace("\t", '', $query). '</pre>'. "\n\n";
		}
	}

}



/**********************************************************
 **********************************************************
 * Part 2: Child Drivers
 **********************************************************
 *********************************************************/



/*
 * MySQL Class for MySQL PDO Driver
 *
 * This class extends the db class to fine-tune the settings
 * to work with the MySQL database.
 */
class mysql extends db {

	/**
	 * Returns the symbol the adapter uses for delimited identifiers.
	 * @return string
	 */
	public function quote_identifier() {
		return '`';
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
	public function list_tables($like=null) {
		//$like can have wild cards like "%value%"
		$result = $this->query('SHOW TABLES'. ($like ? ' LIKE \''. $like.'\'' : ''));

		//For each result we added it to the array
		$tables = array();
		while($table = $result->fetchColumn()) {
			$tables[] = $table;
		}
		return $tables;
	}

	/**
	 * Explain all columns within a table
	 */
	public function list_columns($table=null) {

		//if the table name is empty/null
		if(!$table) { return; }

		//Show all tables as a database
		$result = $this->query('SHOW COLUMNS FROM '. $table);

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

		//Fetch as an array
		while ($row = $result->fetch(PDO::FETCH_ASSOC)) {

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


/*
 * SQLite Class for PDO SQLite Driver
 *
 * This class extends the db class to fine-tune the settings
 * to work with the SQLite database.
 */
class sqlite extends db {

	/**
	 * Set encoding for the database connection (Default: UTF-8)
	 */
	public function set_encoding($value='UTF-8') {
		$this->query('PRAGMA encoding = "'. $value. '"');
	}


	/**
	 * Show all tables in database that optionally match $like
	 */
	public function list_tables($like=null) {
		//$like can have wild cards like "%value%"
		$result = $this->query('SELECT * FROM sqlite_master WHERE type = "table"'
		. ($like ? 'AND name LIKE \''. $like. '\'' : ''));

		//For each result we added it to the array
		$tables = array();
		while($table = $result->fetchColumn()) {
			$tables[] = $table;
		}
		return $tables;
	}


	/**
	 * Explain all columns within a table
	 */
	public function list_columns($table=null) {
		$result = $this->query('PRAGMA table_info('. $table. ')');
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

		while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$columns[] = array(
                'name' => $row['name'],
                'default' => $row['dflt_value'],
                'key' => ($row['pk'] ? 'PRI' : ''),
                'length' => ($row['notnull'] ? $row['notnull'] : null),
                'type' => $row['integer'],
				//For MySQL ENUM - not used here
                'values' => ''
			);
		}
		return $columns;
	}

}


/*
 * Postgre Class for PDO Postgre Driver
 *
 * This class extends the db class to fine-tune the settings
 * to work with the Postgre database.
 */
class pgsql extends db {

	/**
	 * Set encoding for the database connection (Default: UTF-8)
	 */
	public function set_encoding($value='UTF-8') {
		$this->query('PRAGMA encoding = "'. $value. '"');
	}


	/**
	 * Show all tables in database that optionally match $like
	 */
	public function list_tables($like=null) {
		//$like can have wild cards like "%value%"

		$sql = "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'"
		. ($like ? ' AND table_name LIKE \''. $like. '\'' : '');

		$result = $this->query($sql);

		//For each result we added it to the array
		$tables = array();
		while($table = $result->fetchColumn()) {
			$tables[] = $table;
		}
		return $tables;
	}


	/**
	 * Explain all columns within a table
	 */
	public function list_columns($table=null) {

		//Create query
		//$query = "SELECT column_name, column_default, data_type,  FROM '

		//Busted for now - until Postgre admin fixes this
		$query = 'SELECT * FROM'
		. " information_schema.columns WHERE table_name ='". $table ."'";

		$result = $this->query($query);
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

		while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
			//Busted for now - just return data until a Postgre
			//Admin fixes this
			$columns[] = $row;
			/*
			$columns[] = array(
                'name' => $row['column_name'],
                'default' => $row['dflt_value'],
                'key' => ($row['pk'] ? 'PRI' : ''),
                'length' => ($row['notnull'] ? $row['notnull'] : null),
                'type' => $row['integer'],
				//For MySQL ENUM - not used here
                'values' => ''
			);
			*/
		}
		return $columns;
	}

}


/*
 * ORM Row class to enable the easy saving of database rows once data
 * has been updated. This can be enabled by seting DB::row_class to
 * this class name (ORM_Row) or another class if you want to build
 * your own.
 */
Class ORM_Row {

	public function save($instance_id=null) {

		static $db = null;

		//Must have an ID column - change if you want
		if(empty($this->id)) {
			trigger_error('Can\'t update row, No "id" column found.', E_USER_WARNING);
			return;
		}

		//Fetch the database instance
		if(!$db) {
			//Get the last database insance
			$db = db::get_instance($instance_id);
		}

		//Update the database row
		return $db->update($db->last_table, $this, array('id' => $this->id));
	}

}
