<?php
/**
 * cURL
 *
 * Provides a cURL wrapper for making remote requests such as submitting and 
 * retrieving data from web service APIs.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
class cURL
{

/**
 * Send a DELETE request to the given URL
 *
 * @param string $url to request
 * @param array $params to URL encode
 * @param array $options for the cURL connection
 */
public static function delete($url, array $params = array(), array $options = array())
{
	return self::request($url,$params,($options+array(CURLOPT_CUSTOMREQUEST=>'DELETE')));
}


/**
 * Send a GET request to the given URL
 *
 * @param string $url to request
 * @param array $params to URL encode
 * @param array $options for the cURL connection
 */
public static function get($url, array $params = array(), array $options = array())
{
	if ($params){$url.=((stripos($url,'?')!==false)?'&':'?').http_build_query($params,'','&');}return self::request($url,array(),$options);
}


/**
 * Send a POST request to the given URL
 *
 * @param string $url to request
 * @param array $params to URL encode
 * @param array $options for the cURL connection
 */
public static function post($url, array $params = array(), array $options = array())
{
	return self::request($url,$params,($options+array(CURLOPT_POST=>1)));
}


/**
 * Make a cURL request
 *
 * @param string $url to request
 * @param array $params to URL encode
 * @param array $options for the cURL connection
 */
protected static function request($url, array $params = array(), array $options = array())
{
	$ch=curl_init($url);self::setopt($ch,$params,$options);$o=new stdClass;$o->response=curl_exec($ch);$o->error_code=curl_errno($ch);$o->error=curl_error($ch);$o->info=curl_getinfo($ch);curl_close($ch);return$o;
}


/**
 * Set the default cURL options (FAILONERROR, HEADER, RETURNTRANSFER, TIMEOUT, & POSTFIELDS)
 *
 * @param resource $ch the cURL connection handle
 * @param array $params to URL encode
 * @param array $options for the cURL connection
 */
protected static function setopt($ch, array $params = array(), array $options = array())
{
	curl_setopt_array($ch,($options+array(45=>1,42=>0,19913=>1,13=>10,10015=>http_build_query($params,'','&'))));
}


/**
 * Format custom headers for a request (use with CURLOPT_HTTPHEADER)
 *
 * @param array $headers to set
 */
public static function headers(array $headers = array())
{
	$h=array();foreach($headers as$k=>$v)$h[]="$k: $v";return$h;
}

}

// END