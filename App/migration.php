<?php

/* Example Column Options:
$column = array(
	'type' => 'primary|string|integer|boolean|decimal|datetime',
	'length' => NULL,
	'index' => FALSE,
	'null' => TRUE,
	'default' => NULL,
	'unique' => FALSE,
	'precision' => 0, // (optional, default 0) The precision for a decimal (exact numeric) column. (Applies only if a decimal column is used.)
	'scale' => 0, // (optional, default 0) The scale for a decimal (exact numeric) column. (Applies only if a decimal column is used.)
);
*/

$config = array(
	'student' => array(
		'id' => array('type' => 'primary'),
		'dorm_id' => array('type' => 'integer', 'length' => 10000),
		'name' => array('type' => 'string', 'length' => 100),
		'created' => array('type' => 'datetime'),
		'modified' => array('type' => 'datetime'),
	),
	'dorm' => array(
		'id' => array('type' => 'primary'),
		'name' => array('type' => 'string', 'length' => 100),
		'created' => array('type' => 'datetime'),
		'modified' => array('type' => 'datetime'),
	),
	'car' => array(
		'id' => array('type' => 'primary'),
		'name' => array('type' => 'string', 'length' => 100),
		'student_id' => array('type' => 'integer', 'index' => TRUE),
		'created' => array('type' => 'datetime'),
		'modified' => array('type' => 'datetime'),
	),
	'club' => array(
		'id' => array('type' => 'primary'),
		'name' => array('type' => 'string', 'length' => 100),
		'created' => array('type' => 'datetime'),
		'modified' => array('type' => 'datetime'),
	),
	'membership' => array(
		'id' => array('type' => 'primary'),
		'club_id' => array('type' => 'integer', 'index' => TRUE),
		'student_id' => array('type' => 'integer', 'index' => TRUE),
		'created' => array('type' => 'datetime'),
	),

	// User Table
	'user' => array(
		'id' => array('type' => 'primary'),
		'role_id' => array('type' => 'integer', 'length' => 100),
		'banned' => array('type' => 'boolean', 'null' => FALSE),
		'email' => array('type' => 'string', 'length' => 70),
		'name' => array('type' => 'string', 'length' => 50),
		'website' => array('type' => 'string', 'length' => 60),
		//'ip' => array('type' => 'ip'),
		'last_login' => array('type' => 'datetime', 'null' => FALSE),
		'created' => array('type' => 'datetime'),
		'modified' => array('type' => 'datetime'),
	),

	// User ACL
	'role' => array(
		'id' => array('type' => 'primary'),
		'name' => array('type' => 'string', 'length' => 50),
	),
	'resource' => array(
		'id' => array('type' => 'primary'),
		'name' => array('type' => 'string', 'length' => 50),
		'module' => array('type' => 'string', 'length' => 50, 'index' => TRUE),
	),
	'permission' => array(
		'id' => array('type' => 'primary'),
		'role_id' => array('type' => 'integer', 'index' => TRUE),
		'resource_id' => array('type' => 'integer', 'index' => TRUE),
	),
);
