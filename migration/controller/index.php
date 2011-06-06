<?php

class Migration_Controller_Index() extends Controller
{
	public function action()
	{
		// Load config settings
		$config = config('database');

		// Load database
		$db = new DB($config['default']);

		// Set default database object for all models
		ORM::$db = $db;

		// Set name of migration object
		$migration = 'Migration_'. $db->type;

		// Create migration object
		$migration = new $migration;

		// Setup database connection
		$migration->db = $db;

		// Set the database name
		$migration->name = 'default';

		// Load table configuration
		$migration->tables = config(NULL, 'migration');
	}
}
