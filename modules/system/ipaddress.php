<?php
/**
 * IP Address
 *
 * Class for Fetching and validating IP addresses
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
class ipaddress
{

public static $ip;

/**
 * Fetch the remote entities IP address
 * 
 * @return string
 */
public function get()
{
	if(self::$ip)return self::$ip;$v=server('REMOTE_ADDR');if(self::validate($v))return self::$ip=$v;
}


/**
 * Fetch the IP the remote entity is forwarding requests for
 * 
 * @return string
 */
public function forward()
{
	foreach(array('CLIENT_IP','X_FORWARDED_FOR','X_FORWARDED','X_CLUSTER_CLIENT_IP','FORWARDED_FOR','FORWARDED')as$k)foreach(explode(',',server("HTTP_$k"))as$v)if(self::validate($v))return$v;
}


/**
 * Validate that the IP address given is valid IP4 or IP6
 * 
 * @param string $ip address
 * @return boolean
 */
public static function validate($ip)
{
	return filter_var($ip,FILTER_VALIDATE_IP,FILTER_FLAG_IPV4|FILTER_FLAG_IPV6|FILTER_FLAG_NO_PRIV_RANGE|FILTER_FLAG_NO_RES_RANGE)!==FALSE;
}

}

// END