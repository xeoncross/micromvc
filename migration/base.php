<?php

abstract class Migration_Base
{
	public $tables;
	public $db;
	public $name;

	// Backup all existing data
	public function backup_data() { }

	// Drop database schema
	//public function drop_schema() { }

	// Create database schema
	public function create_schema() { }

	// Insert backed-up data into new database schema
	public function restore_data()
	{
		if( ! $this->tables) die('No tables given');

		$file = $this->backup_path().$this->name.'_current_backup.json';

		if(!is_file($file))
		{
			// Report status to user
			print 'Backup file not found ('. colorize($file, 'yellow').")\n";

			return;
		}

		// Decode the JSON file
		$tables = json_decode(file_get_contents($file));

		if(empty($tables)) die(colorize('Cannot restore backup, invalid JSON data', 'red')."\n");

		try
		{
			// Start transaction
			$this->db->pdo->beginTransaction();

			foreach($this->tables as $table => $columns)
			{
				// Has this table been removed from the schema?
				if(!isset($tables->$table))
				{
					print colorize("$table does not exist in backup",'red')."\n";

					$tables->$table = NULL;
					continue;
				}

				// Schema column list
				$defaults = array_flip(array_keys($columns));

				foreach($tables->$table as $row)
				{
					// Insert row taking schema columns into account
					$this->db->insert($table, array_intersect_key((array) $row, $defaults));
				}

				print colorize("$table", 'green')." data has been restored\n";
			}

			// Commit Transaction
			$this->db->pdo->commit();

			print colorize('Finished Restoring Data', 'blue'). "\n\n";
		}
		catch(PDOException $e)
		{
			// Roolback changes (all or nothing)
			$this->db->pdo->rollBack();

			die(colorize($e->getMessage(), 'red')."\n");
		}

	}

	// Path to backup files
	public function backup_path()
	{
		return SP. basename(__DIR__). '/backups/';
	}

	// Save backup data to file
	public function save_backup($data)
	{
		// Build path to backup directory
		$path =  $this->backup_path(). get_class($this). '.'. $this->name. '.'.date("Y.m.d_H:i").'.json';

		// Save file
		file_put_contents($path, json_encode($data));

		// Make this file the new masterbackup
		copy($path, $this->backup_path().$this->name.'_current_backup.json');

		// Report status to user
		print 'Backup saved to '. colorize($path, 'blue')."\n\n";
	}
}
