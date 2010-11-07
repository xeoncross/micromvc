<?php

class Example_Model_Membership extends ORM
{
	public static $t = 'membership';
	public static $f = 'membership_id';
	
	public static $b = array(
		'student' => 'Example_Model_Student',
		'club' => 'Example_Model_Club',
	);
}
