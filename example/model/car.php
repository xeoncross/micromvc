<?php

class Example_Model_Car extends Example_Model_ORM
{
	public static $table = 'car';
	public static $foreign_key = 'car_id';

	public static $belongs_to = array(
		'student' => 'Example_Model_Student',
	);

}
