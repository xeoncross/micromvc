<?php
/**
 * Database
 *
 * Provides a database wrapper around the PDO service to help reduce the effort
 * to interact with a data source.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
class DB
{

public $pdo = NULL;

public $type = NULL;

protected $config = array();

public static $queries = array();

public static $last_query = NULL;

public static $i = '"';


/**
 * Set the database type and save the config for later.
 * 
 * @param array $config
 */
public function __construct(array $config)
{
	// Auto-detect database type from DNS
	$this->type = current(explode(':', $config['dns'], 2));
	
	// Save config for connection
	$this->config = $config;
	
	// MySQL uses a non-standard column identifier
	if($this->type == 'mysql') static::$i = '`';
}


/**
 * Database lazy-loading to setup connection only when finally needed
 */
public function connect()
{
	extract($this->config);
	
	// Clear config for security reasons
	$this->config = NULL;
	
	// Connect to PDO
	$this->pdo = new PDO($dns, $username, $password, $params);
	
	// PDO should throw exceptions
	$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}


/**
 * Quotes a string for use in a query
 * 
 * @param mixed $value to quote
 * @return string
 */
public function quote($value)
{
	if( ! $this->pdo) $this->connect();
	return $this->pdo->quote($value);
}


/**
 * Run a SQL query and return a single column (i.e. COUNT(*) queries)
 *
 * @param string $sql query to run
 * @param array $params the prepared query params
 * @param int $column the optional column to return
 * @return mixed
 */
public function column($sql, array $params = NULL, $column = 0)
{
	// If the query succeeds, fetch the column
	return ($statement = $this->query($sql, $params)) ? $statement->fetchColumn($column) : NULL;
}


/**
 * Run a SQL query and return a single row object
 *
 * @param string $sql query to run
 * @param array $params the prepared query params
 * @param string $object the optional name of the class for this row
 * @return array
 */
public function row($sql, array $params = NULL, $object = NULL)
{
	if( ! $statement = $this->query($sql, $params)) return;
	
	$row = $statement->fetch(PDO::FETCH_OBJ);
	
	// If they want the row returned as a custom object
	if($object) $row = new $object($row);
		
	return $row;
}


/**
 * Run a SQL query and return an array of row objects or an array
 * consisting of all values of a single column.
 *
 * @param string $sql query to run
 * @param array $params the optional prepared query params
 * @param int $column the optional column to return
 * @return array
 */
public function fetch($sql, array $params = NULL, $column = NULL)
{
	if( ! $statement = $this->query($sql, $params)) return;
	
	// Return an array of records
	if($column === NULL) return $statement->fetchAll(PDO::FETCH_OBJ);
	
	// Fetch a certain column from all rows
	return $statement->fetchAll(PDO::FETCH_COLUMN , $column);
}


/**
 * Run a SQL query and return the statement object
 *
 * @param string $sql query to run
 * @param array $params the prepared query params
 * @return PDOStatement
 */
public function query($sql, array $params = NULL)
{
	benchmark();
	
	self::$last_query = $sql;
	
	// Connect if needed
	if(!$this->pdo) $this->connect();
	
	if($params)
	{
		$statement = $this->pdo->prepare($sql);
		$statement->execute($params);
	}
	else
	{
		$statement = $this->pdo->query($sql);
	}

	// Save query results by database type
	self::$queries[$this->type][]=(benchmark() + array(2 => $sql));
	
	return $statement;
}


/**
 * Run a DELETE SQL query and return the number of rows deleted
 *
 * @param string $sql query to run
 * @param array $params the prepared query params
 * @return int
 */
public function delete($sql, array $params = NULL)
{
	if($statement = $this->query($sql, $params))
	{
		return $statement->rowCount();
	}
}


/**
 * Creates and runs an INSERT statement using the values provided
 *
 * @param string $table the table name
 * @param array $data the column => value pairs
 * @return int
 */
public function insert($table, array $data)
{
	$sql = $this->insert_sql($table, $data);
	
	// PostgreSQL does not return the ID by default
	if($this->type == 'pgsql')
	{
		// Insert record and return the whole row (the "id" field may not exist)
		if($statment = $this->query($sql.' RETURNING *', array_values($data)))
		{
			// The first column *should* be the ID
			return $statement->fetchColumn(0);
		}
	}
	
	// Insert data and return the new row's ID
	return $this->query($sql, array_values($data)) ? $this->pdo->lastInsertId() : NULL;
}


/**
 * Create insert SQL
 *
 * @param array $data row data
 * @return string
 */
public function insert_sql($table, $data)
{
	$i = static::$i;
	
	// Column names come from the array keys
	$columns = implode("$i, $i", array_keys($data));
	
	// Build prepared statement SQL
	return "INSERT INTO $i$table$i ($i".$columns."$i) VALUES (" . rtrim(str_repeat('?, ', count($data)), ', ') . ')';
}


/**
 * Builds an UPDATE statement using the values provided.
 * Create a basic WHERE section of a query using the format:
 * array('column' => $value) or array("column = $value")
 *
 * @param string $table the table name
 * @param array $data the column => value pairs
 * @return int
 */
public function update($table, $data, array $where = NULL)
{
	$i = static::$i;
	
	// Column names come from the array keys
	$columns = implode("$i = ?, $i", array_keys($data));
	
	// Build prepared statement SQL
	$sql = "UPDATE $i$table$i SET $i" . $columns . "$i = ? WHERE ";
	
	// Process WHERE conditions
	list($where, $params) = self::where($where);
	
	// Append WHERE conditions to query and statement params
	if($statement = $this->query($sql . $where, array_merge(array_values($data), $params)))
	{
		return $statement->rowCount();
	}
}


/**
 * Create a basic,  single-table SQL query
 *
 * @param string $columns
 * @param string $table
 * @param array $where array of conditions
 * @param int $limit
 * @param int $offset
 * @param array $order array of order by conditions
 * @return array
 */
public function select($column, $table, $where = array(), $limit = NULL, $offset = 0, $order = array())
{
	$i = static::$i;
	
	$sql = "SELECT $column FROM $i$table$i";
	
	// Process WHERE conditions
	list($where, $params) = self::where($where);
	
	// If there are any conditions, append them
	if($where) $sql .= " WHERE $where";
	
	// Append optional ORDER BY sorting
	$sql .= DB::order_by($ord);
	
	if($limit)
	{
		// MySQL/SQLite use a different LIMIT syntax
		$sql .= $this->type == 'pgsql' ? " LIMIT $limit OFFSET $offset" : " LIMIT $offset, $limit";
	}
	
	return array($sql, $params);
}


/**
 * Generate the SQL WHERE clause options from an array
 *
 * @param array $where array of column => $value indexes
 * @return array
 */
public static function where(array $where = NULL)
{
	$a = $s = array();
	
	if($where)
	{
		$i = static::$i;
		
		foreach($where as $c => $v)
		{
			// Raw WHERE conditions are allowed array(0 => '"a" = NOW()')
			if(is_int($c))
			{
				$s[] = $v;
			}
			else
			{
				// Column => Value
				$s[] = "$i$c$i = ?";
				$a[] = $v;
			}
		}
	}
	
	// Return an array with the SQL string + params
	return array(implode(' AND ', $s), $a);
}


/**
 * Create the ORDER BY clause for MySQL and SQLite (still working on PostgreSQL)
 * 
 * @param array $fields to order by
 */
public static function order_by(array $fields = NULL)
{
	if($fields)
	{
		$i = static::$i;
		
		$sql = ' ORDER BY ';
		
		// Add each order clause
		foreach($fields as $k => $v) $sql .= "$i$k$i $v, ";
		
		// Remove ending ", "
		return substr($sql, 0, -2);
	}
}


/**
 * Generate the SQL to join two tables
 *
 * @param string $table1 existing table name
 * @param string $table2 the table to join
 * @param boolean $foreign TRUE join the first table primary key to second table foreign key
 * @param string $type the join type (LEFT, RIGHT, INNER)
 * @return string
 */
public static function join($table1, $table2, $foreign = TRUE, $type = 'LEFT')
{
	$i = static::$i;
	
	$sql = " $type JOIN $t2 ON ";
	
	// Join table1 to table 2 foreign key
	if($foreign)
	{
		$a = $table1;
		$b = $table2;
	}
	else
	{
		// Join table1 foreign key to table2 primary key
		$a = $table2;
		$b = $table1;
	}
	
	return "$i$a$i.{$i}id$i = $i$b$i.$i{$a}_id$i";
}


/**
 * Generate an IN() selection from an array of numeric ID's.
 * 
 * @param array $ids
 * @return string
 */
public static function in(array $ids)
{
	return " in ('".implode("', '", array_map('to_int', $ids)) . "')";
}

}

// END
