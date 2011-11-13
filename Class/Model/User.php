<?php
namespace Model;

class User extends \Core\ORM
{
	public static $table = 'user';
	public static $foreign_key = 'user_id';

	public static function byName($name)
	{
		$name = static::$db->quote("%$name%");
		return self::row(array("name LIKE $name"));
	}
}
