<?php

class Example_Model_Student extends ORM
{
	public static $table = 'student';
	public static $foreign_key = 'student_id';
	public static $cascade_delete = TRUE;
	
	public static $has = array(
		'car' => 'Example_Model_Car',
		'memberships' => 'Example_Model_Membership'
	);
	
	public static $belongs_to = array(
		'dorm' => 'Example_Model_Dorm',
	);
	
	public static $has_many_through = array(
		'clubs' => array('Example_Model_Membership' => 'Example_Model_Club'),
	);
}
