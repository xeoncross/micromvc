<?php defined('SYSTEM_PATH') or die('No direct access');
/**
 * String
 *
 * This class contains extra string functions for doing complex things with 
 * UTF-8 and unicode strings such as spliting strings at certain characters.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
class String
{
	/**
	 * split_text
	 *
	 * Split text into chunks ($inside contains all text inside
	 * $start and $end, and $outside contains all text outside)
	 *
	 * @param	String  Text to split
	 * @param	String  Start break item
	 * @param	String  End break item
	 * @return	Array
	 */
	public static function split_text($text='', $start='<code>', $end='</code>')
	{
		$tokens = explode($start, $text);
		$outside[] = $tokens[0];

		$num_tokens = count($tokens);
		for ($i = 1; $i < $num_tokens; ++$i) {
			$temp = explode($end, $tokens[$i]);
			$inside[] = $temp[0];
			$outside[] = $temp[1];
		}

		return array($inside, $outside);
	}


	/**
	 * Split a string by another string while taking escape character(s)
	 * into account. Returns an array with the peices that were contained
	 * "inside" and "outside" of the split character(s).
	 * @param $text
	 * @param $start
	 * @param $escape
	 * @return array
	 */
	public static function split_string($text = '', $start = '"', $escape = '\\')
	{

		//If the separator is not found
		if(stripos($text, $start) === FALSE) {
			return array(array(), array($text));
		}

		//Look for these markers
		preg_match_all('/'. preg_quote($escape). '*'. $start. '/u', $text, $matches, PREG_OFFSET_CAPTURE);

		//If the preg match failed
		if(empty($matches[0]) OR !$matches = $matches[0]) {
			return array(array(), array($text));
		}

		// We need to remove all matches that are escaped by the escape string
		foreach($matches as $id => $match) {
			//If the escape match is found
			if(mb_stripos($match[0], $escape) !== FALSE) {
				//If it is odd then the start is escaped!
				if(mb_substr_count($match[0], $escape) % 2) {
					unset($matches[$id]);
				}
			}
		}

		//Resort keys to fill in gaps
		sort($matches);

		$in = TRUE;
		$inside = array();
		$outside = array();

		//Add the first element
		$outside[] = mb_substr($text, 0, $matches[0][1] + mb_strlen($matches[0][0]) - mb_strlen($start));

		foreach($matches as $id => $match) {

			//We need to add the length of whatever we searched to the offset
			$offset = $match[1] + mb_strlen($match[0]);

			//If another element is after this
			if(isset($matches[$id + 1][1])) {
				$next = ($matches[$id + 1][1] + mb_strlen($matches[$id + 1][0])) - $offset - mb_strlen($start);
			} else {
				//Else it is the end of the string
				$next = mb_strlen($text) - $offset;
			}

			//If inside the char match
			if($in) {
				$inside[] = mb_substr($text, $offset, $next);
			} else {
				$outside[] = mb_substr($text, $offset, $next);
			}

			//Switch on and off
			$in = 1 - $in;

		}

		//Return the array
		return array($inside, $outside);
	}

	/**
	 * Regex version of split_string
	 * @param $text
	 * @param $start
	 * @param $escape
	 * @return array
	 */
	public static function regex_split_string($text = '', $start = '"', $escape = '\\\\')
	{
		return preg_split('/(?:[^'. $escape. '])'. $start. '/u', $text);
	}

	/**
	 * Join text that was split apart by the split_text or split_string functions.
	 * @param $inside
	 * @param $outside
	 * @param $with
	 * @return unknown_type
	 */
	public static function join_text($inside = NULL, $outside = NULL, $with = '"')
	{
		if (empty($inside) OR empty($outside)) {
			return $outside;
		}

		$text = '';
		$num_tokens = count($outside);

		for ($i = 0; $i < $num_tokens; ++$i) {
			$text .= $outside[$i];
			if (isset($inside[$i])){
				$text .= $with. $inside[$i]. $with;
			}
		}

		return $text;
	}


	/**
	 * Random Charaters
	 *
	 * Pass this public static function the number of chars you want
	 * and it will randomly make a string with that
	 * many chars. (I removed chars that look alike.)
	 *
	 * @param	Int		Length of character string
	 * @param	bool	$only_letters if true
	 * @return	Array
	 */
	public static function random_charaters($number, $only_letters = FALSE)
	{
		$chars = '';
		for($i=0; $i<$number; $i++)
		{
			$chars .= ( $only_letters ? chr(rand(33, 126)) : chr(rand(65, 90)) );
		}
		return $chars;
	}


	/**
	 * Add the HTTP Protocal to the start of a URL if needed
	 * @param	$url
	 * @return	string
	 */
	public static function prep_url($url = '')
	{

		if ($url == 'http://' OR $url == '') {
			return '';
		}

		if (mb_substr($url, 0, 7) != 'http://' && mb_substr($url, 0, 8) != 'https://') {
			$url = 'http://'. $url;
		}

		return $url;
	}


	/**
	 * Valid Email
	 * @param	string	email to check
	 * @return	boolean
	 */
	public static function valid_email($text)
	{
		return ( ! preg_match("/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b/i", $text)) ? FALSE : TRUE;
	}


	/**
	 * Highlight a string using the built in PHP code parser.
	 * Works with most "C" Styled code. If the second argument
	 * is set to true then we will use CSS class names instead
	 * of hard coding the colors.
	 *
	 * @param	string	$code
	 * @param	boolean	$css
	 * @return	string
	 */
	public static function highlight_code($code='', $css = TRUE, $trim = TRUE)
	{

		if($trim)
		{
			$code = trim($code);
		}

		/*
		 * We are going to trick the code parser into
		 * highlighting other forms of code by adding
		 * <?PHP and ?> around text.
		 */

		//If there is no opening tag
		if (mb_strpos($code, '<?php') === false)
		{
			$added_to_start = TRUE;
			$code = '<?php'. $code;
		}

		//If there is no closing PHP tag
		if (mb_strpos($code, '?>') === false)
		{
			$added_to_end = TRUE;
			$code .= '?>';
		}

		//If we have not already changed the default color codes to CSS names
		if($css && ini_get('highlight.default') != 'code_default')
		{
			ini_set('highlight.default', 'code_default');
			ini_set('highlight.comment', 'code_comment');
			ini_set('highlight.keyword', 'code_keyword');
			ini_set('highlight.string', 'code_string');
			ini_set('highlight.html', 'code_html');
		}

		//Highlight the code
		$code = highlight_string($code, true);
		//$code = htmlspecialchars($code);

		if($css) {
			//Replace the span color tags with CSS classes
			$code = str_replace('<span style="color: ', '<span class="', $code);
		}

		// Remove start PHP tag we added
		if(isset($added_to_start))
		{
			$code = str_replace('&lt;?php', '', $code);
		}

		// Remove end PHP tags we added
		if(isset($added_to_end))
		{
			$code = str_replace('?&gt;', '', $code);
		}

		return $code;
	}


	/**
	 * The following functions are mostly the work of these authors:
	 *
	 * @author	Harry Fuecks (PHP UTF-8)
	 * @author	Andreas Gohr & Chris Smith <dokuwiki.org>
	 *
	 * Functions may have additionally editing by wordpress.org or kohanaphp.com
	 */


	/**
	 * Strips out all non-7bit ASCII bytes.
	 *
	 * @param   string  string to clean
	 * @return  string
	 */
	public static function strip_non_ascii($str) {
		return preg_replace('/[^\x00-\x7F]+/S', '', $str);
	}


	/**
	 * Strips out device control codes in the ASCII range.
	 *
	 * @param   string  string to clean
	 * @return  string
	 */
	public static function strip_ascii_ctrl($str) {
		return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S', '', $str);
	}

	
	/**
	 * UTF-8 aware alternative to ord
	 * Returns the unicode ordinal for a character
	 * @param string UTF-8 encoded character
	 * @return int unicode ordinal for the character
	 * @see http://www.php.net/ord
	 * @see http://www.php.net/manual/en/function.ord.php#46267
	 */
	public static function utf8_ord($chr) {

		$ord0 = ord($chr);

		if ( $ord0 >= 0 && $ord0 <= 127 ) {
			return $ord0;
		}

		if ( !isset($chr{1}) ) {
			trigger_error('Short sequence - at least 2 bytes expected, only 1 seen');
			return FALSE;
		}

		$ord1 = ord($chr{1});
		if ( $ord0 >= 192 && $ord0 <= 223 ) {
			return ( $ord0 - 192 ) * 64
			+ ( $ord1 - 128 );
		}

		if ( !isset($chr{2}) ) {
			trigger_error('Short sequence - at least 3 bytes expected, only 2 seen');
			return FALSE;
		}
		$ord2 = ord($chr{2});
		if ( $ord0 >= 224 && $ord0 <= 239 ) {
			return ($ord0-224)*4096
			+ ($ord1-128)*64
			+ ($ord2-128);
		}

		if ( !isset($chr{3}) ) {
			trigger_error('Short sequence - at least 4 bytes expected, only 3 seen');
			return FALSE;
		}
		$ord3 = ord($chr{3});
		if ($ord0>=240 && $ord0<=247) {
			return ($ord0-240)*262144
			+ ($ord1-128)*4096
			+ ($ord2-128)*64
			+ ($ord3-128);

		}

		if ( !isset($chr{4}) ) {
			trigger_error('Short sequence - at least 5 bytes expected, only 4 seen');
			return FALSE;
		}
		$ord4 = ord($chr{4});
		if ($ord0>=248 && $ord0<=251) {
			return ($ord0-248)*16777216
			+ ($ord1-128)*262144
			+ ($ord2-128)*4096
			+ ($ord3-128)*64
			+ ($ord4-128);
		}

		if ( !isset($chr{5}) ) {
			trigger_error('Short sequence - at least 6 bytes expected, only 5 seen');
			return FALSE;
		}
		if ($ord0>=252 && $ord0<=253) {
			return ($ord0-252) * 1073741824
			+ ($ord1-128)*16777216
			+ ($ord2-128)*262144
			+ ($ord3-128)*4096
			+ ($ord4-128)*64
			+ (ord($c{5})-128);
		}

		if ( $ord0 >= 254 && $ord0 <= 255 ) {
			trigger_error('Invalid UTF-8 with surrogate ordinal '.$ord0);
			return FALSE;
		}

	}


	/**
	 * UTF-8 aware alternative to str_ireplace
	 * Case-insensitive version of str_replace
	 * Note: it's not fast and gets slower if $search / $replace is array
	 * Notes: it's based on the assumption that the lower and uppercase
	 * versions of a UTF-8 character will have the same length in bytes
	 * which is currently true given the hash table to strtolower
	 * @param string
	 * @return string
	 * @see http://www.php.net/str_ireplace
	 */
	public static function utf8_ireplace($search, $replace, $str, $count = NULL){

		if ( !is_array($search) ) {

			$slen = strlen($search);
			if ( $slen == 0 ) {
				return $str;
			}

			$lendif = strlen($replace) - strlen($search);
			$search = mb_strtolower($search);

			$search = preg_quote($search);
			$lstr = mb_strtolower($str);
			$i = 0;
			$matched = 0;
			while ( preg_match('/(.*)'.$search.'/Us',$lstr, $matches) ) {
				if ( $i === $count ) {
					break;
				}
				$mlen = strlen($matches[0]);
				$lstr = substr($lstr, $mlen);
				$str = substr_replace($str, $replace, $matched+strlen($matches[1]), $slen);
				$matched += $mlen + $lendif;
				$i++;
			}
			return $str;

		} else {

			foreach ( array_keys($search) as $k ) {

				if ( is_array($replace) ) {

					if ( array_key_exists($k,$replace) ) {

						$str = self::utf8_ireplace($search[$k], $replace[$k], $str, $count);

					} else {

						$str = self::utf8_ireplace($search[$k], '', $str, $count);

					}

				} else {

					$str = self::utf8_ireplace($search[$k], $replace, $str, $count);

				}
			}
			return $str;

		}

	}


	/**
	 * Replacement for str_pad. $padStr may contain multi-byte characters.
	 *
	 * @author Oliver Saunders <oliver (a) osinternetservices.com>
	 * @param string $input
	 * @param int $length
	 * @param string $padStr
	 * @param int $type ( same constants as str_pad )
	 * @return string
	 * @see http://www.php.net/str_pad
	 */
	public static function utf8_str_pad($input, $length, $padStr = ' ', $type = STR_PAD_RIGHT) {

		$inputLen = mb_strlen($input);
		if ($length <= $inputLen) {
			return $input;
		}

		$padStrLen = mb_strlen($padStr);
		$padLen = $length - $inputLen;

		if ($type == STR_PAD_RIGHT) {
			$repeatTimes = ceil($padLen / $padStrLen);
			return mb_substr($input . str_repeat($padStr, $repeatTimes), 0, $length);
		}

		if ($type == STR_PAD_LEFT) {
			$repeatTimes = ceil($padLen / $padStrLen);
			return mb_substr(str_repeat($padStr, $repeatTimes), 0, floor($padLen)) . $input;
		}

		if ($type == STR_PAD_BOTH) {

			$padLen/= 2;
			$padAmountLeft = floor($padLen);
			$padAmountRight = ceil($padLen);
			$repeatTimesLeft = ceil($padAmountLeft / $padStrLen);
			$repeatTimesRight = ceil($padAmountRight / $padStrLen);

			$paddingLeft = mb_substr(str_repeat($padStr, $repeatTimesLeft), 0, $padAmountLeft);
			$paddingRight = mb_substr(str_repeat($padStr, $repeatTimesRight), 0, $padAmountLeft);
			return $paddingLeft . $input . $paddingRight;
		}

		trigger_error('self::utf8_str_pad: Unknown padding type (' . $type . ')',E_USER_ERROR);
	}


	/**
	 * UTF-8 aware alternative to strcasecmp
	 * A case insensivite string comparison
	 * @param string
	 * @param string
	 * @return int
	 * @see http://www.php.net/strcasecmp
	 */
	public static function utf8_strcasecmp($strX, $strY) {
		$strX = mb_strtolower($strX);
		$strY = mb_strtolower($strY);
		return strcmp($strX, $strY);
	}


	/**
	 * UTF-8 aware alternative to strcspn
	 * Find length of initial segment not matching mask
	 * @param string
	 * @return int
	 * @see http://www.php.net/strcspn
	 */
	public static function utf8_strcspn($str, $mask, $start = NULL, $length = NULL) {

		if ( empty($mask) || strlen($mask) == 0 ) {
			return NULL;
		}

		$mask = preg_replace('!([\\\\\\-\\]\\[/^])!','\\\${1}',$mask);

		if ( $start !== NULL || $length !== NULL ) {
			$str = mb_substr($str, $start, $length);
		}

		preg_match('/^[^'.$mask.']+/u',$str, $matches);

		if ( isset($matches[0]) ) {
			return mb_strlen($matches[0]);
		}

		return 0;

	}


	/**
	 * UTF-8 aware alternative to stristr
	 * Find first occurrence of a string using case insensitive comparison
	 * @param string
	 * @param string
	 * @return int
	 * @see http://www.php.net/strcasecmp
	 */
	public static function utf8_stristr($str, $search) {

		if ( strlen($search) == 0 ) {
			return $str;
		}

		$lstr = mb_strtolower($str);
		$lsearch = mb_strtolower($search);
		preg_match('/^(.*)'.preg_quote($lsearch).'/Us',$lstr, $matches);

		if ( count($matches) == 2 ) {
			return substr($str, strlen($matches[1]));
		}

		return FALSE;
	}


	/**
	 * UTF-8 aware alternative to strspn
	 * Find length of initial segment matching mask
	 * @param string
	 * @return int
	 * @see http://www.php.net/strspn
	 */
	public static function utf8_strspn($str, $mask, $start = NULL, $length = NULL) {

		$mask = preg_replace('!([\\\\\\-\\]\\[/^])!','\\\${1}',$mask);

		if ( $start !== NULL || $length !== NULL ) {
			$str = mb_substr($str, $start, $length);
		}

		preg_match('/^['.$mask.']+/u',$str, $matches);

		if ( isset($matches[0]) ) {
			return mb_strlen($matches[0]);
		}

		return 0;

	}

	/**
	 * UTF-8 aware replacement for ltrim()
	 * Note: you only need to use this if you are supplying the charlist
	 * optional arg and it contains UTF-8 characters. Otherwise ltrim will
	 * work normally on a UTF-8 string
	 * @author Andreas Gohr <andi@splitbrain.org>
	 * @see http://www.php.net/ltrim
	 * @see http://dev.splitbrain.org/view/darcs/dokuwiki/inc/utf8.php
	 * @return string
	 */
	public static function utf8_ltrim( $str, $charlist = FALSE ) {
		if($charlist === FALSE) return ltrim($str);

		//quote charlist for use in a characterclass
		$charlist = preg_replace('!([\\\\\\-\\]\\[/^])!','\\\${1}',$charlist);

		return preg_replace('/^['.$charlist.']+/u','',$str);
	}


	/**
	 * UTF-8 aware replacement for rtrim()
	 * Note: you only need to use this if you are supplying the charlist
	 * optional arg and it contains UTF-8 characters. Otherwise rtrim will
	 * work normally on a UTF-8 string
	 * @author Andreas Gohr <andi@splitbrain.org>
	 * @see http://www.php.net/rtrim
	 * @see http://dev.splitbrain.org/view/darcs/dokuwiki/inc/utf8.php
	 * @return string
	 */
	public static function utf8_rtrim( $str, $charlist = FALSE ) {
		if($charlist === FALSE) return rtrim($str);

		//quote charlist for use in a characterclass
		$charlist = preg_replace('!([\\\\\\-\\]\\[/^])!','\\\${1}',$charlist);

		return preg_replace('/['.$charlist.']+$/u','',$str);
	}


	/**
	 * UTF-8 aware replacement for trim()
	 * Note: you only need to use this if you are supplying the charlist
	 * optional arg and it contains UTF-8 characters. Otherwise trim will
	 * work normally on a UTF-8 string
	 * @author Andreas Gohr <andi@splitbrain.org>
	 * @see http://www.php.net/trim
	 * @see http://dev.splitbrain.org/view/darcs/dokuwiki/inc/utf8.php
	 * @return string
	 */
	public static function utf8_trim( $str, $charlist = FALSE ) {
		if($charlist === FALSE) return trim($str);
		return self::utf8_ltrim(self::utf8_rtrim($str, $charlist), $charlist);
	}


	/**
	 * UTF-8 aware alternative to ucfirst
	 * Make a string's first character uppercase
	 * @param string
	 * @return string with first character as upper case (if applicable)
	 * @see http://www.php.net/ucfirst
	 * @see mb_strtoupper
	 */
	public static function utf8_ucfirst($str){
		preg_match('/^(.?)(.*)$/us', $str, $matches);
		return mb_strtoupper($matches[1]).$matches[2];
	}


	/**
	 * UTF-8 aware alternative to ucwords
	 * Uppercase the first character of each word in a string
	 * @param string
	 * @return string with first char of each word uppercase
	 * @see http://www.php.net/ucwords
	 */
	public static function utf8_ucwords($str) {

		static $mb = FALSE;

		//Use mb public static function if installed
		if ($mb OR function_exists('mb_convert_case') ) {
			$mb = TRUE;
			return mb_convert_case($str, MB_CASE_TITLE);
		}

		// [\x0c\x09\x0b\x0a\x0d\x20] matches form feeds, horizontal tabs, vertical tabs, linefeeds and carriage returns.
		// This corresponds to the definition of a 'word' defined at http://php.net/ucwords
		return preg_replace(
		'/(?<=^|[\x0c\x09\x0b\x0a\x0d\x20])[^\x0c\x09\x0b\x0a\x0d\x20]/ue',
		'mb_strtoupper(\'$0\')',
		$str
		);
	}


	/**
	 * Tools for conversion between UTF-8 and unicode
	 * The Original Code is Mozilla Communicator client code.
	 * The Initial Developer of the Original Code is
	 * Netscape Communications Corporation.
	 * Portions created by the Initial Developer are Copyright (C) 1998
	 * the Initial Developer. All Rights Reserved.
	 * Ported to PHP by Henri Sivonen (http://hsivonen.iki.fi)
	 * Slight modifications to fit with phputf8 library by Harry Fuecks (hfuecks gmail com)
	 * @see http://lxr.mozilla.org/seamonkey/source/intl/uconv/src/nsUTF8ToUnicode.cpp
	 * @see http://lxr.mozilla.org/seamonkey/source/intl/uconv/src/nsUnicodeToUTF8.cpp
	 * @see http://hsivonen.iki.fi/php-utf8/
	 */


	/**
	 * Takes an UTF-8 string and returns an array of ints representing the
	 * Unicode characters. Astral planes are supported ie. the ints in the
	 * output can be > 0xFFFF. Occurrances of the BOM are ignored. Surrogates
	 * are not allowed.
	 * Returns false if the input string isn't a valid UTF-8 octet sequence
	 * and raises a PHP error at level E_USER_WARNING
	 * Note: this public static function has been modified slightly in this library to
	 * trigger errors on encountering bad bytes
	 * @author <hsivonen@iki.fi>
	 * @param string UTF-8 encoded string
	 * @return mixed array of unicode code points or FALSE if UTF-8 invalid
	 * @see self::utf8_from_unicode
	 * @see http://hsivonen.iki.fi/php-utf8/
	 */
	public static function utf8_to_unicode($str) {
		$mState = 0;     // cached expected number of octets after the current octet
		// until the beginning of the next UTF8 character sequence
		$mUcs4  = 0;     // cached Unicode character
		$mBytes = 1;     // cached expected number of octets in the current sequence

		$out = array();

		$len = strlen($str);

		for($i = 0; $i < $len; $i++) {

			$in = ord($str{$i});

			if ( $mState == 0) {

				// When mState is zero we expect either a US-ASCII character or a
				// multi-octet sequence.
				if (0 == (0x80 & ($in))) {
					// US-ASCII, pass straight through.
					$out[] = $in;
					$mBytes = 1;

				} else if (0xC0 == (0xE0 & ($in))) {
					// First octet of 2 octet sequence
					$mUcs4 = ($in);
					$mUcs4 = ($mUcs4 & 0x1F) << 6;
					$mState = 1;
					$mBytes = 2;

				} else if (0xE0 == (0xF0 & ($in))) {
					// First octet of 3 octet sequence
					$mUcs4 = ($in);
					$mUcs4 = ($mUcs4 & 0x0F) << 12;
					$mState = 2;
					$mBytes = 3;

				} else if (0xF0 == (0xF8 & ($in))) {
					// First octet of 4 octet sequence
					$mUcs4 = ($in);
					$mUcs4 = ($mUcs4 & 0x07) << 18;
					$mState = 3;
					$mBytes = 4;

				} else if (0xF8 == (0xFC & ($in))) {
					/* First octet of 5 octet sequence.
					 *
					 * This is illegal because the encoded codepoint must be either
					 * (a) not the shortest form or
					 * (b) outside the Unicode range of 0-0x10FFFF.
					 * Rather than trying to resynchronize, we will carry on until the end
					 * of the sequence and let the later error handling code catch it.
					 */
					$mUcs4 = ($in);
					$mUcs4 = ($mUcs4 & 0x03) << 24;
					$mState = 4;
					$mBytes = 5;

				} else if (0xFC == (0xFE & ($in))) {
					// First octet of 6 octet sequence, see comments for 5 octet sequence.
					$mUcs4 = ($in);
					$mUcs4 = ($mUcs4 & 1) << 30;
					$mState = 5;
					$mBytes = 6;

				} else {
					/* Current octet is neither in the US-ASCII range nor a legal first
					 * octet of a multi-octet sequence.
					 */
					trigger_error(
                        'self::utf8_to_unicode: Illegal sequence identifier '.
                            'in UTF-8 at byte '.$i,
					E_USER_WARNING
					);
					return FALSE;

				}

			} else {

				// When mState is non-zero, we expect a continuation of the multi-octet
				// sequence
				if (0x80 == (0xC0 & ($in))) {

					// Legal continuation.
					$shift = ($mState - 1) * 6;
					$tmp = $in;
					$tmp = ($tmp & 0x0000003F) << $shift;
					$mUcs4 |= $tmp;

					/**
					 * End of the multi-octet sequence. mUcs4 now contains the final
					 * Unicode codepoint to be output
					 */
					if (0 == --$mState) {

						/*
						 * Check for illegal sequences and codepoints.
						 */
						// From Unicode 3.1, non-shortest form is illegal
						if (((2 == $mBytes) && ($mUcs4 < 0x0080)) ||
						((3 == $mBytes) && ($mUcs4 < 0x0800)) ||
						((4 == $mBytes) && ($mUcs4 < 0x10000)) ||
						(4 < $mBytes) ||
						// From Unicode 3.2, surrogate characters are illegal
						(($mUcs4 & 0xFFFFF800) == 0xD800) ||
						// Codepoints outside the Unicode range are illegal
						($mUcs4 > 0x10FFFF)) {

							trigger_error(
                                'self::utf8_to_unicode: Illegal sequence or codepoint '.
                                    'in UTF-8 at byte '.$i,
							E_USER_WARNING
							);

							return FALSE;

						}

						if (0xFEFF != $mUcs4) {
							// BOM is legal but we don't want to output it
							$out[] = $mUcs4;
						}

						//initialize UTF8 cache
						$mState = 0;
						$mUcs4  = 0;
						$mBytes = 1;
					}

				} else {
					/**
					 *((0xC0 & (*in) != 0x80) && (mState != 0))
					 * Incomplete multi-octet sequence.
					 */
					trigger_error(
                        'self::utf8_to_unicode: Incomplete multi-octet '.
                        '   sequence in UTF-8 at byte '.$i,
					E_USER_WARNING
					);

					return FALSE;
				}
			}
		}
		return $out;
	}


	/**
	 * Takes an array of ints representing the Unicode characters and returns
	 * a UTF-8 string. Astral planes are supported ie. the ints in the
	 * input can be > 0xFFFF. Occurrances of the BOM are ignored. Surrogates
	 * are not allowed.
	 * Returns false if the input array contains ints that represent
	 * surrogates or are outside the Unicode range
	 * and raises a PHP error at level E_USER_WARNING
	 * Note: this public static function has been modified slightly in this library to use
	 * output buffering to concatenate the UTF-8 string (faster) as well as
	 * reference the array by it's keys
	 * @param array of unicode code points representing a string
	 * @return mixed UTF-8 string or FALSE if array contains invalid code points
	 * @author <hsivonen@iki.fi>
	 * @see self::utf8_to_unicode
	 * @see http://hsivonen.iki.fi/php-utf8/
	 */
	public static function utf8_from_unicode($arr) {
		ob_start();

		foreach (array_keys($arr) as $k) {

			# ASCII range (including control chars)
			if ( ($arr[$k] >= 0) && ($arr[$k] <= 0x007f) ) {

				echo chr($arr[$k]);

				# 2 byte sequence
			} else if ($arr[$k] <= 0x07ff) {

				echo chr(0xc0 | ($arr[$k] >> 6));
				echo chr(0x80 | ($arr[$k] & 0x003f));

				# Byte order mark (skip)
			} else if($arr[$k] == 0xFEFF) {

				// nop -- zap the BOM

				# Test for illegal surrogates
			} else if ($arr[$k] >= 0xD800 && $arr[$k] <= 0xDFFF) {

				// found a surrogate
				trigger_error(
                'self::utf8_from_unicode: Illegal surrogate '.
                    'at index: '.$k.', value: '.$arr[$k],
				E_USER_WARNING
				);

				return FALSE;

				# 3 byte sequence
			} else if ($arr[$k] <= 0xffff) {

				echo chr(0xe0 | ($arr[$k] >> 12));
				echo chr(0x80 | (($arr[$k] >> 6) & 0x003f));
				echo chr(0x80 | ($arr[$k] & 0x003f));

				# 4 byte sequence
			} else if ($arr[$k] <= 0x10ffff) {

				echo chr(0xf0 | ($arr[$k] >> 18));
				echo chr(0x80 | (($arr[$k] >> 12) & 0x3f));
				echo chr(0x80 | (($arr[$k] >> 6) & 0x3f));
				echo chr(0x80 | ($arr[$k] & 0x3f));

			} else {

				trigger_error(
                'self::utf8_from_unicode: Codepoint out of Unicode range '.
                    'at index: '.$k.', value: '.$arr[$k],
				E_USER_WARNING
				);

				// out of range
				return FALSE;
			}
		}

		$result = ob_get_contents();
		ob_end_clean();
		return $result;
	}

}
