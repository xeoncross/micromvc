<?php

namespace Model;

class Resource extends \Core\ORM
{
	public static $table = 'resource';
	public static $foreign_key = 'resource_id';
	public static $order_by = array('name' => 'desc');
	public static $cascade_delete = TRUE;

	public static $has = array(
		'permissions' => '\Model\Permission',
	);

	public static $has_many_through = array(
		'roles' => array(
			'resource_id' => '\Model\Permission',
			'role_id' => '\Model\Role'
		),
	);
}
