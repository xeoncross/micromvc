<?php

// Load the class that contains all the tests
$micromvc = new UnitTest_Micromvc();

// Run each test
foreach(get_class_methods($micromvc) as $method)
{
	$time = microtime(TRUE);
	$memory = memory_get_usage();
	
	// Run the test
	$result = $micromvc->$method();
	
	// Record the result
	$this->tests[$method] = array($result, microtime(TRUE)-$time, memory_get_usage()-$memory);
}

// We will be using a custom layout for this ;)
$view = new View('layout', 'unittest');
$view->set((array)$this);
print $view;

die();
