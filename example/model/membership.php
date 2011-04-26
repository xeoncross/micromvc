<?php

class Example_Model_Membership extends ORM
{
	public static $table = 'membership';
	public static $foreign_key = 'membership_id';
	
	public static $belongs_to = array(
		'student' => 'Example_Model_Student',
		'club' => 'Example_Model_Club',
	);
}
