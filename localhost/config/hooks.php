<?php
/*
 * Run on system startup
 */
$hooks['system_startup'][] = array();

/*
 * Run after the controller is loaded
 */
$hooks['post_constructor'][] = array();

/**
 * Run after the method is called, but before rendering page
 */
$hooks['post_method'] = array();

/**
 * Called after everything is done
 */
$hooks['system_shutdown'] = array();

