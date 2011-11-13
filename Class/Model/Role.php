<?php
namespace Model;

class Role extends \Core\ORM
{
	public static $table = 'role';
	public static $foreign_key = 'role_id';
	public static $cascade_delete = FALSE;

	public static $has = array(
		'permissions' => '\Model\Permission',
		'users' => '\Model\User',
	);

	public static $has_many_through = array(
		'resources' => array('\Model\Permission' => '\Model\Resource'),
	);

	public function has_permission($resource_id)
	{
		$i = static::$db->i;

		return (bool) static::$db->column('SELECT * FROM '. $i. 'permission'. $i. ' WHERE role_id = ? AND resource_id = ?', array($this->id, $resource_id));
	}
}
