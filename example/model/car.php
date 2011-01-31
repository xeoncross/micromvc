<?php

class Example_Model_Car extends ORM
{
	public static $t = 'car';
	public static $f = 'car_id';
	
	public static $b = array(
		'student' => 'Example_Model_Student',
	);
	
}
