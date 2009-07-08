<?php

//Call a simple function from a hook
$hooks['my_first_hook'] = array(
	'function'	=> 'hook_test',
	'file'		=> 'hook_test',
);

//Call a method of a class from a hook
$hooks['my_second_hook'][] = array(
	'class'		=> 'my_second_hook',	//Name of class to find function in
	'function'	=> 'filter',			//The method to call
	'file'		=> 'my_second_hook',	//File that contains the class
);

//Then call this hook before returning the data
$hooks['my_second_hook'][] = array(
	'class'		=> 'my_second_hook',	//Name of object to find function in
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
);

