<?php

class Example_Model_Club extends ORM
{
	public static $t = 'club';
	public static $f = 'club_id';
	
	public static $h = array(
		'memberships' => 'Example_Model_Membership'
	);
	
	public static $hmt = array(
		'students' => array('Example_Model_Membership' => 'Example_Model_Student'),
	);
}
