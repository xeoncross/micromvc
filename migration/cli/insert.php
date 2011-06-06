<?php

// Load database config
$config = config('database');

// Start database connection
$db = new DB($config['default']);

ORM::$db = $db;

$dorm = new Example_Model_Dorm;
$dorm->name = 'Dorm 1';
$dorm->save();

$club = new Example_Model_Club;
$club->name = 'Tennis';
$club->save();

foreach(array('Mary', 'John', 'Sam') as $name)
{
	$student = new Example_Model_Student;
	$student->name = $name;
	$student->dorm_id = $dorm->id;
	$student->save();

	$car = new Example_Model_Car;
	$car->name = $name. ' Ford Ranger';
	$car->student_id = $student->id;
	$car->save();
	
	$membership = new Example_Model_Membership;
	$membership->student_id = $student->id;
	$membership->club_id = $club->id;
	$membership->save();
}

print colorize('Created Records Successfully', 'blue')."\n";
