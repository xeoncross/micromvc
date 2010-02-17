<?php
/*
 * Example Hook
 *
 * The following hook will load the class "my_class" from the library
 * and then call $my_class->my_method() on the data passed to it.
 *
 * $config['hook_name'][] = array(
 *		'function'	=> 'my_method',
 *		'class'		=> 'my_class',
 *		'helper'	=> FALSE,
 *		'static'	=> FALSE
 * );
 *
 * Please note that if the class given does not yet exist in the scope
 * of the script, an attempt will be made to load it. If adding hooks 
 * at runtime, you can also give objects instead of class names.
 *
 * Each class loaded by hook calls will use the load class to prevent
 * excess object creation and adhere to the singleton pattern.
 *
 * If a function is not defined yet, then the function file given will
 * be loaded from the correct functions folder using the helper name
 * given.
 * 
 * If 'static' is TRUE then the class method will be called statically.
 */

/*
 * Run on system startup
 */
$config['system_startup'][] = array();

/*
 * Run to filter cache page before script exit
 */
$config['system_shutdown_cache'][] = array();

/*
 * Run after the system is fully loaded
 */
$config['system_loaded'][] = array();

/*
 * Run after the controller is loaded and before the method is called
 */
$config['system_pre_method'][] = array();

/**
 * Run after the method is called, but before rendering page
 */
$config['system_post_method'] = array();

/**
 * Run to filter final page output before script exit
 */
$config['system_shutdown'] = array();

