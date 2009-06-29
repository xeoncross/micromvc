<?php
/*
 * Run on system startup
 */
$hooks['startup'][] = array();


//Call a simple function from a hook
$hooks['my_first_hook'] = array(
	'function'	=> 'hook_test',
	'file'		=> 'hook_test.php'
);



//Call a method of a class from a hook
$hooks['my_second_hook'][] = array(
	'class'		=> 'my_second_hook',	//Name of class to find function in
	'object'	=> 'my_second_hook',	//Name of object that class would be in (or will be called)
	'function'	=> 'filter',			//The method to call
	'file'		=> 'my_second_hook.php',//File that contains the class
	'path'		=> 'libraries'			//The folder to look for the class in
);

//Then call this hook before returning the data
$hooks['my_second_hook'][] = array(
	'object'	=> 'my_second_hook',	//Name of object to find function in
	'function'	=> 'say',
);

//Then call this hook before returning the data
$hooks['my_second_hook'][] = array(
	'class'		=> 'my_second_hook',	//Name of class to find function in
	'function'	=> 'speak',
);

//Then call this hook before returning the data
$hooks['my_second_hook'][] = array(
	'class'		=> 'my_second_hook',	//Name of class to find function in
	'object'	=> 'my_second_hook_obj'
);

