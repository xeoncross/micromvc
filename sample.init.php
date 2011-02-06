<?php
/**
 * INIT
 *
 * This file contains initialization code run immediately after system setup.
 * Test for bad requests, spam IP's, check system state, or do other tasks that 
 * might need to terminate the script before it wastes time loading.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */

/*
 * Set the server timezone
 * see: http://us3.php.net/manual/en/timezones.php
 */
//date_default_timezone_set('GMT');

// Default Locale
setlocale(LC_ALL, 'en_US.utf-8');

// iconv encoding
iconv_set_encoding("internal_encoding", "UTF-8");

// multibyte encoding
mb_internal_encoding('UTF-8');

