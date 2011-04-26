<?php

class Example_Model_Car extends ORM
{
	public static $table = 'car';
	public static $foreign_key = 'car_id';
	
	public static $belongs_to = array(
		'student' => 'Example_Model_Student',
	);
	
}
