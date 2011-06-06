<?php

class Example_Model_Club extends Example_Model_ORM
{
	public static $table = 'club';
	public static $foreign_key = 'club_id';

	public static $has = array(
		'memberships' => 'Example_Model_Membership'
	);

	public static $has_many_through = array(
		'students' => array('Example_Model_Membership' => 'Example_Model_Student'),
	);
}
