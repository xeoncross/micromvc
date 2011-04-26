<?php

class Example_Model_Dorm extends ORM
{
	public static $table = 'dorm';
	public static $foreign_key = 'dorm_id';
	
	public static $has = array(
		'students' => 'Example_Model_Student',
	);
	
}
