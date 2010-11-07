<?php

class Example_Model_Student extends ORM
{
	public static $t = 'student';
	public static $f = 'student_id';
	
	public static $h = array(
		'car' => 'Example_Model_Car',
		'memberships' => 'Example_Model_Membership'
	);
	
	public static $b = array(
		'dorm' => 'Example_Model_Dorm',
	);
	
	public static $hmt = array(
		'clubs' => array('Model_Membership' => 'Example_Model_Club'),
	);
}
