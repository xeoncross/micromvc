<?php
/**
 * Database
 *
 * Provides a database wrapper around the PDO service to help reduce the effort
 * to interact with a data source.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2011 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
namespace Micro;

/**
 * Query databases using PDO
 */
class Database
{
	public $i='`', $c, $statements = array(), $type;
	static $queries;

	/**
	 * Set the database connection on creation
	 *
	 * @param object $connection PDO connection object
	 */
	public function __construct($connection)
	{
		$this->c = $connection;
	}


	/**
	 * Quotes a string for use in a query
	 *
	 * @param mixed $value to quote
	 * @return string
	 */
	public function quote($value)
	{
		return $this->c->quote($value);
	}


	/**
	 * Fetch a column offset from the result set (COUNT() queries)
	 *
	 * @param string $query query string
	 * @param array $params query parameters
	 * @param integer $key index of column offset
	 * @return array|null
	 */
	public function column($query, $params = NULL, $key = 0)
	{
		if($statement = $this->query($query, $params))
			return $statement->fetchColumn($key);
	}


	/**
	 * Fetch a single query result row
	 *
	 * @param string $query query string
	 * @param array $params query parameters
	 * @param string $object the optional name of the class for this row
	 * @return mixed
	 */
	public function row($query, $params = NULL, $object = NULL)
	{
		if(! $statement = $this->query($query, $params)) return;

		$row = $statement->fetch();

		// If they want the row returned as a custom object
		if($object) $row = new $object($row);

		return $row;
	}


	/**
	 * Fetch all query result rows
	 *
	 * @param string $query query string
	 * @param array $params query parameters
	 * @param int $column the optional column to return
	 * @return array
	 */
	public function fetch($query, $params = NULL, $column = NULL)
	{
		if( ! $statement = $this->query($query, $params)) return;

		// Return an array of records
		if($column === NULL) return $statement->fetchAll();

		// Fetch a certain column from all rows
		return $statement->fetchAll(\PDO::FETCH_COLUMN, $column);
	}


	/**
	 * Prepare and send a query returning the PDOStatement
	 *
	 * @param string $query query string
	 * @param array $params query parameters
	 * @param boolean $cache_statement if true
	 * @return object|null
	 */
	public function query($query, $params = NULL, $cache_statement = FALSE)
	{
		$time = microtime(TRUE);

		// Should we cached PDOStatements? (Best for batch inserts/updates)
		if($cache_statement)
		{
			$hash = md5($query);

			if(isset($this->statements[$hash]))
			{
				$statement = $this->statements[$hash];
			}
			else
			{
				$statement = $this->statements[$hash] = $this->c->prepare($query);
			}
		}
		else
		{
			$statement = $this->c->prepare($query);
		}

		$statement->execute((array) $params);

		// Save query results by database type
		self::$queries[] = array(microtime(TRUE) - $time, $query);

		return $statement;
	}


	/**
	 * Issue a delete query
	 *
	 * @param string $table name
	 * @param array $where where conditions
	 * @return integer|null
	 */
	function delete($table, $where)
	{
		$params;

		// Process WHERE conditions
		if(is_array($where))
			list($where, $params) = $this->where($where);

		$i = $this->i;

		// Append WHERE conditions to query and add statement params
		if($statement = $this->query("DELETE FROM $i$table$i WHERE " . $where, $params))
			return $statement->rowCount();
	}


	/**
	 * Creates and runs an INSERT statement using the values provided
	 *
	 * @param string $table the table name
	 * @param array $data the column => value pairs
	 * @return int
	 */
	public function insert($table, array $data, $cache_statement = TRUE)
	{
		$sql = $this->insert_sql($table, $data);

		// PostgreSQL does not return the ID by default
		if($this->type == 'pgsql')
		{
			// Insert record and return the whole row (the "id" field may not exist)
			if($statement = $this->query($sql.' RETURNING "'.key($data).'"', array_values($data)))
			{
				// The first column *should* be the ID
				return $statement->fetchColumn(0);
			}

			return;
		}

		// Insert data and return the new row's ID
		return $this->query($sql, array_values($data), $cache_statement) ? $this->c->lastInsertId(key($data)) : NULL;
	}


	/**
	 * Create insert SQL
	 *
	 * @param array $data row data
	 * @return string
	 */
	public function insert_sql($table, $data)
	{
		$i = $this->i;

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
	public function update($table, $data, array $where = NULL, $cache_statement = TRUE)
	{
		$i = $this->i;

		// Column names come from the array keys
		$columns = implode("$i = ?, $i", array_keys($data));

		// Build prepared statement SQL
		$sql = "UPDATE $i$table$i SET $i" . $columns . "$i = ? WHERE ";

		// Process WHERE conditions
		list($where, $params) = $this->where($where);

		// Append WHERE conditions to query and statement params
		if($statement = $this->query($sql . $where, array_merge(array_values($data), $params), $cache_statement))
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
	public function select($column, $table, $where = NULL, $limit = NULL, $offset = 0, $order = NULL)
	{
		$i = $this->i;

		$sql = "SELECT $column FROM $i$table$i";

		// Process WHERE conditions
		list($where, $params) = $this->where($where);

		// If there are any conditions, append them
		if($where) $sql .= " WHERE $where";

		// Append optional ORDER BY sorting
		$sql .= self::order_by($order);

		if($limit)
		{
			// MySQL/SQLite use a different LIMIT syntax
			$sql .= $this->type == 'pgsql' ? " LIMIT $limit OFFSET $offset" : " LIMIT $offset, $limit";
		}

		return $this->fetch($sql, $params);
	}


	/**
	 * Generate the SQL WHERE clause options from an array
	 *
	 * @param array $where array of column => $value indexes
	 * @return array
	 */
	public function where($where = NULL)
	{
		$a = $s = array();

		if($where)
		{
			$i = $this->i;

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
	public function order_by($fields = NULL)
	{
		if( ! $fields) return;

		$i = $this->i;

		$sql = ' ORDER BY ';

		// Add each order clause
		foreach($fields as $k => $v) $sql .= "$i$k$i $v, ";

		// Remove ending ", "
		return substr($sql, 0, -2);
	}
}

// END
