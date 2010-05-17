<?php
/**
 * php_utf8
 *
 * A simple collection of functions to provide a standardized framework for
 * working with multibyte strings (like UTF-8) in a variety of server
 * environments. Requires either mbstring or iconv to work!
 *
 * @author David Pennington <xeoncross.com>
 * @link http://sourceforge.net/projects/phputf8/
 * @link http://github.com/Xeoncross/php_utf8
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */

// Is PCRE compiled with UTF-8 support? Please say YES!!!!
define('PCRE_SUPPORTS_UTF8', preg_match('/^.{1}$/u',"Ã±"));

// Default to English UTF-8
setlocale(LC_ALL, 'en_US.UTF8');

if(extension_loaded('mbstring'))
{
	if (ini_get('mbstring.func_overload') & MB_OVERLOAD_STRING)
	{
		trigger_error
		(
			'The <a href="http://php.net/mbstring">mbstring</a> extension is overloading '.
			'PHP\'s native string functions. Disable this by setting mbstring.func_overload '.
			'to 0, 1, 4 or 5 in php.ini or a .htaccess file.',
			E_USER_ERROR
		);
	}

	// Set internal character encoding to UTF-8
	mb_internal_encoding("UTF-8");

}
elseif (extension_loaded('iconv'))
{
	// Set internal character encoding to UTF-8
	iconv_set_encoding("internal_encoding", "UTF-8");
}
else
{
	trigger_error
	(
		'Neither the <a href="http://php.net/iconv">iconv</a> nor <a href="http://'.
		'php.net/mbstring">mbstring</a> PHP extensions are loaded. Without one of '.
		'these, UTF-8 strings cannot be properly handled.',
		E_USER_ERROR
	);
}


// Enable basic multibyte string support if mbstring is not installed!
if( ! extension_loaded('mbstring'))
{
	/**
	 * Unicode aware replacement for strlen(). Returns the number of characters
	 * in the string (not the number of bytes), replacing multibyte characters
	 * with a single byte equivalent utf8_decode() converts characters that are
	 * not in ISO-8859-1 to '?', which, for the purpose of counting, is alright
	 * - It's much faster than iconv_strlen.
	 *
	 * Note: this function does not count bad UTF-8 bytes in the string
	 *
	 * @author <chernyshevsky at hotmail dot com>
	 * @param string $string a valid UTF-8 string
	 * @return int
	 */
	function mb_strlen($string)
	{
		return strlen(utf8_decode($string));
	}


	/**
	 * UTF-8 aware alternative to substr
	 * Return part of a string given character offset (and optionally length)
	 *
	 * @param string $string to parse
	 * @param int $start the starting offset
	 * @param int $length of part to return
	 * @param string $encoding defaults to UTF-8
	 * @return string
	 */
	function mb_substr($string, $start, $length, $encoding = NULL)
	{
		return iconv_substr($string, $start, $length);
	}


	/**
	 * UTF-8 aware alternative to strpos
	 * Find position of first occurrence of a string
	 *
	 * @param string $haystack to search
	 * @param string $needle substring to look for
	 * @param int $offset to start from
	 * @param string $encoding defaults to UTF-8
	 * @return int
	 */
	function mb_strpos($haystack, $needle, $offset = 0, $encoding = NULL)
	{
		return iconv_strpos($haystack, $needel, $offset);
	}


	/**
	 * UTF-8 aware alternative to strrpos
	 * Finds the last occurrence of a needle within a haystack
	 *
	 * @param string $haystack to search
	 * @param string $needle substring to look for
	 * @param string $encoding defaults to UTF-8
	 * @return int
	 */
	function mb_strrpos($haystack, $needle, $encoding = NULL)
	{
		return iconv_strrpos($haystack, $needle);
	}


	/**
	 * Convert a UTF-8 string to lowercase
	 *
	 * @param string $string to convert
	 * @param string $encoding defaults to UTF-8
	 * @return string
	 */
	function mb_strtolower($string, $encoding)
	{
		return $string;
	}


	/**
	 * Convert a UTF-8 string to uppercase
	 *
	 * @param string $string to convert
	 * @param string $encoding defaults to UTF-8
	 * @return string
	 */
	function mb_strtoupper($string, $encoding)
	{
		return $string;
	}
}


/**
 * UTF-8 aware alternative to str_split to convert a string to an array
 *
 * @param string $string to split
 * @param int $split_len of characters to split string by
 * @return string
 */
function mb_str_split($string, $split_len = 1)
{
	if (mb_strlen($string) <= $split_len)
		return array($string);

	preg_match_all('/.{'.$split_len.'}|[^\x00]{1,'.$split_len.'}$/us', $string, $array);
		return $array[0];
}


/**
 * UTF-8 aware substr_replace.
 *
 * @param string $string to process
 * @param string $replacement text
 * @param int $start offset
 * @param int $length to replace
 * @return string
 */
function mb_substr_replace($string, $replacement, $start, $length = NULL )
{
	return mb_substr($str, 0, $start) . $replacement . mb_substr($str, $length + 1);
}


/**
 * UTF-8 aware alternative to strrev
 * Reverse a string
 *
 * @param string $string to reverse
 * @return string
 */
function mb_strrev($string)
{
	preg_match_all('/./us', $string, $ar);
	return join('',array_reverse($ar[0]));
}


/**
 * Tests whether a string contains only 7bit ASCII bytes.
 *
 * @param string $string to check
 * @return bool
 */
function is_ascii($string)
{
	return ! preg_match('/[^\x00-\x7F]/S', $string);
}


/**
 * Checks to see if a string is utf8 encoded.
 *
 * NOTE: This function checks for 5-Byte sequences, UTF8
 *       has Bytes Sequences with a maximum length of 4.
 *
 * @author bmorel at ssi dot fr (modified)
 * @param string $str The string to be checked
 * @return bool
 */
function seems_utf8($str)
{
	$length = strlen($str);
	for ($i=0; $i < $length; $i++) {
		$c = ord($str[$i]);
		if ($c < 0x80) $n = 0; # 0bbbbbbb
		elseif (($c & 0xE0) == 0xC0) $n=1; # 110bbbbb
		elseif (($c & 0xF0) == 0xE0) $n=2; # 1110bbbb
		elseif (($c & 0xF8) == 0xF0) $n=3; # 11110bbb
		elseif (($c & 0xFC) == 0xF8) $n=4; # 111110bb
		elseif (($c & 0xFE) == 0xFC) $n=5; # 1111110b
		else return false; # Does not match any model
		for ($j=0; $j<$n; $j++) { # n bytes matching 10bbbbbb follow ?
			if ((++$i == $length) || ((ord($str[$i]) & 0xC0) != 0x80))
			return false;
		}
	}
	return true;
}


/**
 * Converts most Latin accent characters to ASCII characters. If there are no
 * accent characters, then the string given is returned unchanged.
 *
 * @author wordpress.org
 * @param string $string that might have accent characters
 * @return string
 */
function remove_accents($string)
{
	// We only need to translate from U+0080 to U+00FF
	if ( ! preg_match('/[\x80-\xff]/', $string))
		return $string;

	$chars = array(
	// Decompositions for Latin-1 Supplement
	chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
	chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
	chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
	chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
	chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
	chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
	chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
	chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
	chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
	chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
	chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
	chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
	chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
	chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
	chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
	chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
	chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
	chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
	chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
	chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
	chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
	chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
	chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
	chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
	chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
	chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
	chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
	chr(195).chr(191) => 'y',
	// Decompositions for Latin Extended-A
	chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
	chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
	chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
	chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
	chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
	chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
	chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
	chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
	chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
	chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
	chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
	chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
	chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
	chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
	chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
	chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
	chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
	chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
	chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
	chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
	chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
	chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
	chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
	chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
	chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
	chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
	chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
	chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
	chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
	chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
	chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
	chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
	chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
	chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
	chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
	chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
	chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
	chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
	chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
	chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
	chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
	chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
	chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
	chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
	chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
	chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
	chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
	chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
	chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
	chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
	chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
	chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
	chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
	chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
	chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
	chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
	chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
	chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
	chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
	chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
	chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
	chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
	chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
	chr(197).chr(190) => 'z', chr(197).chr(191) => 's',
	// Euro Sign
	chr(226).chr(130).chr(172) => 'E',
	// GBP (Pound) Sign
	chr(194).chr(163) => ''
	);

	return strtr($string, $chars);
}


/**
 * Filter a valid UTF-8 string so that it contains only words, numbers,
 * dashes, underscores, periods, and spaces - all of which are safe
 * characters to use in file names, URI, XML, JSON, and (X)HTML.
 *
 * @param string $string to clean
 * @param bool $remove_spaces if set to TRUE
 * @return string
 */
function sanitize($string, $remove_spaces = FALSE)
{
	// Only allow words (letters or numbers) and a couple other characters
	$string = preg_replace('/[^\w\-\. ]+/u', ' ', $string);

	// Remove doubles of all non-word characters
	$string = preg_replace(array('/\s\s+/', '/\.\.+/', '/--+/', '/__+/'), array(' ', '.', '-', '_'), $string);

	// Remove spaces?
	if($remove_spaces)
	{
		$string = preg_replace('/--+/', '-', str_replace(' ', '-', $string));
	}

	// Remove starting/ending symbols
	return trim($string, '-._ ');
}


/**
 * Create a SEO friendly URL string from a valid UTF-8 string
 *
 * @param string $string to filter
 * @return string
 */
function sanitize_url($string)
{
	return urlencode(remove_accents(mb_strtolower(sanitize($string, TRUE))));
}


/**
 * Filter a valid UTF-8 string to be file name safe.
 *
 * @param string $string to filter
 * @return string
 */
function sanitize_filename($string)
{
	return sanitize($string, TRUE);
}


/**
 * Convert a string from one encoding to another encoding (Defaults to UTF-8)
 *
 * @param string $string to convert
 * @param string $to_encoding you want the string in
 * @param string $from_encoding that string is in
 * @return string
 */
function encode($string, $to_encoding = 'UTF-8', $from_encoding = 'UTF-8')
{
	// ASCII-7 is valid UTF-8 already
	if ($to_encoding === 'UTF-8' AND is_ascii($string))
		return $string;

	if(function_exists('iconv'))
	{
		// Disable notices
		$ER = error_reporting(~E_NOTICE);

		$string = iconv($from_encoding, $to_encoding.'//TRANSLIT', $string);

		// Turn notices back on
		error_reporting($ER);

		return $string;
	}
	else
	{
		return mb_convert_encoding($string, $to_encoding, mb_detect_encoding($string, "auto", TRUE));
	}
}
