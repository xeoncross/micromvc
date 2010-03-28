<?php
/**
 * Here you can setup domain aliases to control which site directory 
 * should be used. This allows you to load and use a different site 
 * folder than the one in the host name. (not-required)
 * 
 * array( host => alias )
 */
$domains = array(

	/* 
	 * Making all example TLD's use the example.com folder
	 * www.example.com, sub.example.net, example.org, etc...
	'(.*\.)?example\.([a-z]{2,4})' => 'example.com'
	*/
);
