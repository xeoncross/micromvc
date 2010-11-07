<?php

class Example_Model_Dorm extends ORM
{
	public static $t = 'dorm';
	public static $f = 'dorm_id';
	
	public static $h = array(
		'students' => 'Example_Model_Student',
	);
	
}
