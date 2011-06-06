<?php

class Example_Model_Dorm extends Example_Model_ORM
{
	public static $table = 'dorm';
	public static $foreign_key = 'dorm_id';

	public static $has = array(
		'students' => 'Example_Model_Student',
	);

}
