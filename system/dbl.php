<?php
/**
 * Domain Blacklist (DBL)
 *
 * Provides a wrapper to check IP blacklists provided by different servers.
 * Currently only supports IP4.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2011 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
class DBL
{

	/**
	 * Checks that the given IP address is not a bad bot listed in the Http:BL
	 *
	 * @see http://www.projecthoneypot.org/
	 * @param string $ip address (IP4 only!)
	 * @param string $key Http:BL API key
	 * @param integer $threat_level bettween 0 and 255
	 * @param integer $max_age number of days since last activity
	 */
	public static function listed_in_httpbl($ip, $key, $threat_level = 20, $max_age = 30)
	{
		// Ignore localhost host
		if($ip == '127.0.0.1') return;

		// Convert
		$ip = implode('.', array_reverse(explode('.', $ip)));

		if($ip = gethostbyname("$key.$ip.dnsbl.httpbl.org"))
		{
			$ip = explode('.', $ip);

			// Verify the return format http://httpbl.org/httpbl_api.php
			if($ip[0] == 127 AND $ip[3] AND $ip[2] >= $threat_level AND $ip[1] <= $max_age)
			{
				return TRUE;
			}
		}
	}

}

// END
