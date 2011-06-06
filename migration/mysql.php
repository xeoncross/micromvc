<?php

class Migration_MySQL extends Migration_Base
{

	// Backup all existing data
	public function backup_data()
	{
		if( ! $this->tables) die('No tables given');

		$tables = array();

		// Build list of all tables
		foreach($this->db->fetch('SHOW TABLES') as $row) $tables[] = current($row);

		$results = array();

		// Backup all data in this schema
		foreach($this->tables as $table => $schema)
		{
			// Don't try to back it up if it doesn't exist
			if( ! in_array($table, $tables))
			{
				// Report status to user
				print 'Skipping '. colorize($table, 'yellow')."\n";
				continue;
			}

			// Report status to user
			print 'Backing up '. colorize($table, 'green')."\n";

			// Fetch all records
			$results[$table] = $this->db->fetch("SELECT * FROM `$table`");
		}

		// Now save the data in a file
		if($results)
		{
			$this->save_backup($results);
		}
		else
		{
			print colorize('Nothing to backup', 'yellow'). "\n\n";
		}
	}

	/**
	 * Create database schema
	 */
	public function create_schema()
	{
		if( ! $this->tables) die('No tables given');

		// First force the schema to use UTF-8
		//$this->db->query("ALTER DATABASE `micromvc` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci");

		// Create each table
		foreach($this->tables as $table => $schema)
		{
			// Report status to user
			print 'Dropping table '. colorize($table, 'green')."\n";

			// Remove table
			$this->db->query("DROP TABLE IF EXISTS `$table`");

			$sql = "CREATE TABLE `$table` (\n";

			$index = array();
			$unique = array();
			$primary = NULL;

			// Defaults for columns
			$defaults = array(
				//'type' => 'primary|text|integer|boolean|decimal|datetime', REQUIRED!
				'length' => NULL,
				'index' => FALSE,
				'null' => TRUE,
				'default' => NULL,
				'unique' => FALSE,
				'precision' => 0,
				'scale' => 0,
			);

			foreach($schema as $column => $data)
			{
				$data = $data + $defaults;

				$type = $data['type'];

				// Integer?
				if($type == 'primary' OR $type == 'integer')
				{
					// Default to int
					$length = $data['length'] ? $data['length'] : 2147483647;

					if($length <= 127)
						$type = 'TINYINT';
					elseif($length <= 32767)
						$type = 'SMALLINT';
					elseif($length <= 8388607)
						$type = 'MEDIUMINT';
					elseif($length <= 2147483647)
						$type = 'INT';
					else
						$type = 'BIGINT';

					// Is this the primary column?
					if($data['type'] == 'primary')
					{
						$primary = $column;

						// Primary keys are special
						$sql .= "\t`$column` $type unsigned NOT NULL AUTO_INCREMENT,\n";
						continue;
					}
				}
				elseif($type == 'string')
				{
					// Default to text
					$length = $data['length'] ? $data['length'] : 65535;

					if($length <= 255)
						$type = 'VARCHAR('. $length.')';
					elseif($length <= 65535)
						$type = 'TEXT';
					elseif($length <= 16777215)
						$type = 'MEDIUMTEXT';
					else
						$type = 'LONGTEXT';
				}
				elseif($type == 'boolean')
				{
					$type = 'TINYINT(1)';
				}
				elseif($type == 'decimal')
				{
					$type = 'DECIMAL('. $data['precision'].','. $data['scale'].')';
				}
				else
				{
					$type = 'DATETIME';
				}

				// Build Column Definition
				$sql .= "\t`$column` $type";

				if(! $data['null']) $sql .= ' NOT NULL';

				if($data['default']) $sql .= ' DEFAULT \''. $data['default']. "'";

				$sql .= ",\n";

				// Is the column unique?
				if($data['unique']) $unique[] = $column;

				// Index the column?
				if($data['index']) $index[] = $column;
			}

			if($primary) $sql .= "PRIMARY KEY (`$primary`),\n";

			foreach($unique as $column)
			{
				$sql .= "UNIQUE KEY `$column` (`$column`),\n";
			}

			foreach($index as $column)
			{
				$sql .= "KEY `$column` (`$column`),\n";
			}

			// Remove ending comma
			$sql = substr($sql,0,-2)."\n";

			$sql .=') ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';
			//$sql .=') ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci';

			// Create table
			$this->db->query($sql);

			// Report status to user
			print 'Created table '. colorize($table, 'green')."\n";
		}

		print colorize('Schema Created', 'blue')."\n\n";
	}

}
