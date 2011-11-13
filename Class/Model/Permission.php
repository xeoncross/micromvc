<?php

namespace Model;

class Permission extends \Core\ORM
{
	public static $table = 'permission';
	public static $foreign_key = 'permission_id';

	public static $belongs_to = array(
		'role' => '\Model\Role',
		'resource' => '\Model\Resource',
	);

	/**
	 * Die scum!
	 */
	public function purge()
	{
		$i = static::$db->i;

		static::$db->query('TRUNCATE TABLE '. $i. static::$table. $i);
	}

}
