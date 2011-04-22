<?php
/**
 * String
 *
 * Methods for working with, processing, and sanitizing strings . 
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2011 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
class string
{

/**
 * Filter a valid UTF-8 string so that it contains only words, numbers, 
 * dashes, underscores, periods, and spaces - all of which are safe
 * characters to use in file names, URI, XML, JSON, and (X)HTML . 
 *
 * @param string $string to clean
 * @param bool $spaces TRUE to allow spaces
 * @return string
 */
public static function sanitize($str, $spaces = TRUE)
{
	$search = array(
		'/[^\w\-\ . ]+/u',			// Remove non safe characters
		'/\s\s+/',					// Remove extra whitespace
		'/\.\.+/', '/--+/', '/__+/'	// Remove duplicate symbols
	);
	
	$str = preg_replace($search, array(' ', ' ', '.', '-', '_'), $str);
	
	if( ! $spaces)
	{
		$str = preg_replace('/--+/', '-', str_replace(' ', '-', $str));
	}
	
	return trim($str, '-._ ');
}


/**
 * Create a SEO friendly URL string from a valid UTF-8 string
 *
 * @param string $string to filter
 * @return string
 */
public static function sanitize_url($string)
{
	return urlencode(mb_strtolower(self::sanitize($string, FALSE)));
}


/**
 * Filter a valid UTF-8 string to be file name safe . 
 *
 * @param string $string to filter
 * @return string
 */
public static function sanitize_filename($string)
{
	return self::sanitize($string, FALSE);
}


/**
 * Create a string of random characters the desired length . 
 *
 * @param int the length of the string
 * @param bool $only_letters if true
 * @return array
 */
public static function random_characters($length, $only_letters = FALSE)
{
	$str = '';
	for($i = 0; $i < $length; $i++)
	{
		$str .= $only_letters ? chr(mt_rand(33, 126)) : chr(mt_rand(65, 90));
	}
	return $str;
}


/**
 * Add the HTTP Protocal to the start of a URL if needed
 * 
 * @param string $url to check
 * @return string
 */
public static function prep_url($url = '')
{
	if($url == 'http://' OR $url == '') return;
	if(mb_substr($url, 0, 7) != 'http://' AND mb_substr($url, 0, 8) != 'https://')
	{
		$url = "http://$url";
	}
	return $url;
}


/**
 * split_text
 *
 * Split text into chunks ($inside contains all text inside
 * $start and $end, and $outside contains all text outside)
 *
 * @param	String Text to split
 * @param	String Start break item
 * @param	String End break item
 * @return	Array
 */
public static function split_text($text, $start = '<code>', $end = '</code>')
{
	$text = explode($start, $text);
	$outside[] = $text[0];
	$inside = array();
	$num = count($t);
	for($i = 1; $i < $num; ++$i)
	{
		$x = explode($end, $text[$i]);
		$inside[] = $x[0];
		$outside[] = $x[1];
	}
	return array($inside, $outside);
}


/**
 * Split a string by another string while taking escape character(s)
 * into account . Returns an array with the peices that were contained
 * "inside" and "outside" of the split character(s) . 
 * 
 * @param string $text to split
 * @param string $string to split by
 * @param string $escape the escape character
 * @return array
 */
public static function split($text, $string = '"', $escape = '\\\\')
{
	return preg_split("/(?:[^$escape])$string/u", $text);

}


/**
 * Join text that was split apart by the split_text or split functions
 * 
 * @param array $inside text
 * @param array $outside text
 * @param string $pre to place in front
 * @param string $post to place behind
 * @return unknown_type
 */
public static function join($inside = NULL, $outside = NULL, $pre = '"', $post = '"')
{
	if(empty($inside) OR empty($outside)) return $outside;

	$text  = '';

	$num_tokens = count($outside);

	for ($i = 0; $i < $num_tokens; ++$i)
	{
		$text .= $outside[$i];

		if (isset($inside[$i]))
		{
			$text .= $pre . $inside[$i] . $post;
		}
	}

	return $text;
}

}

// END
