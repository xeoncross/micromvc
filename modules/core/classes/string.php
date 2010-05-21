<?php defined('SYSTEM_PATH') or die('No direct access');
/**
 * String
 *
 * This class contains extra string functions for everything from working with 
 * UTF-8 and unicode to spliting strings at certain characters.
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
	 * Convert all array values to the UTF-8 encoding
	 * @param $array
	 * @return array
	 */
	public static function array_to_utf8($array = array()) {

		if ( is_array($array) ) {

			// Recursively convert array elements to UTF-8
			foreach ($array as $key => $val) {
				$array[$key] = self::array_to_utf8($val);
			}

		} elseif ( is_scalar($array) AND $array !== '') {

			// Remove control characters
			$array = self::strip_ascii_ctrl($array);

			//If not already ascii
			if ( ! self::is_ascii($array)) {
				$array = self::to_utf8($array);
			}

		} else {
			return;
		}

		return $array;
	}


	/**
	 * Convert all array values to the ISO-8859-1 (Latin-1) encoding
	 * @param $array
	 * @return array
	 */
	public static function array_to_ascii($array = array()) {

		if ( is_array($array) ) {

			// Recursively convert array elements to UTF-8
			foreach ($array as $key => $val) {
				$array[$key] = self::array_to_ascii($val);
			}

		} elseif ( is_scalar($array) AND $array !== '') {

			// Remove control characters
			$array = self::strip_ascii_ctrl($array);

			//If not already ascii
			if ( ! self::is_ascii($array)) {
				$array = self::to_ascii($array);
			}

		} else {
			return;
		}

		return $array;
	}


	/**
	 * Encode the given string to unicode UTF-8 format
	 * Auto-detect the format it came from.
	 *
	 * @param	$string the string to convert
	 * @return	string
	 */
	public static function to_utf8($string = '') {
		if( $string ) {
			$encoding = mb_detect_encoding($string, "UTF-8, ISO-8859-1, ISO-8859-15", true);
			return mb_convert_encoding($string, "UTF-8", $encoding);
		}
	}


	/**
	 * Convert a string to the ISO-8859-1 (Latin-1) encoding use by The Americas,
	 * Western Europe, Oceania, and much of Africa.
	 *
	 * @param	$string the string to convert
	 * @return	string
	 */
	public static function to_ascii($string = '') {
		if( $string ) {
			$encoding = mb_detect_encoding($string, "UTF-8, ISO-8859-1, ISO-8859-15", true);
			return mb_convert_encoding($string, "ISO-8859-1", $encoding);
		}
	}


	/**
	 * HTML encode each value to make it safe to display.
	 * Use with array_walk()
	 *
	 * @param $item
	 * @param $key
	 */
	public static function array_walk_h( &$item, $key) {
		$item = h($item);
	}


	/**
	 * Convert a string to the file/URL safe "slug" form
	 *
	 * @param string $string the string to clean
	 * @return string
	 */
	public static function slug($string = '')
	{
		// Convert accents to normal US characters
		$string = self::remove_accents(trim($string));

		// Remove non-file/URL safe characters
		$string = preg_replace("/([^a-zA-Z0-9_\-\.]+)/u", '-', $string);

		// Only allow one dash separator at a time and make string lowercase
		return strtolower(preg_replace('/--+/u', '-', $string));

	}


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
	public static function split_text($text='', $start='<code>', $end='</code>') {
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
	public static function split_string($text = '', $start = '"', $escape = '\\') {

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
	public static function regex_split_string($text = '', $start = '"', $escape = '\\\\') {
		return preg_split('/(?:[^'. $escape. '])'. $start. '/u', $text);
	}

	/**
	 * Join text that was split apart by the split_text or split_string functions.
	 * @param $inside
	 * @param $outside
	 * @param $with
	 * @return unknown_type
	 */
	public static function join_text($inside = NULL, $outside = NULL, $with = '"') {
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
	public static function random_charaters($number, $only_letters = FALSE) {
		$chars = '';
		for($i=0; $i<$number; $i++) {
			$chars .= ( $only_letters ? chr(rand(33, 126)) : chr(rand(65, 90)) );
		}
		return $chars;
	}


	/**
	 * Add the HTTP Protocal to the start of a URL if needed
	 * @param	$url
	 * @return	string
	 */
	public static function prep_url($url = '') {

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
	public static function valid_email($text){
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
	public static function highlight_code($code='', $css = TRUE, $trim = TRUE) {

		if( $trim ) {
			$code = trim($code);
		}

		/*
		 * We are going to trick the code parser into
		 * highlighting other forms of code by adding
		 * <?PHP and ?> around text.
		 */

		//If there is no opening tag
		if (mb_strpos($code, '<?php') === false) {
			$added_to_start = TRUE;
			$code = '<?php'. $code;
		}

		//If there is no closing PHP tag
		if (mb_strpos($code, '?>') === false) {
			$added_to_end = TRUE;
			$code .= '?>';
		}

		//If we have not already changed the default color codes to CSS names
		if($css && ini_get('highlight.default') != 'code_default') {
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
		if(isset($added_to_start)) {
			$code = str_replace('&lt;?php', '', $code);
		}

		// Remove end PHP tags we added
		if(isset($added_to_end)) {
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
	 * Tests whether a string contains only 7bit ASCII bytes.
	 *
	 * @param   str	string to check
	 * @return  bool
	 */
	public static function is_ascii($str) {
		return ! preg_match('/[^\x00-\x7F]/S', $str);
	}


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
	 * Checks to see if a string is utf8 encoded.
	 *
	 * NOTE: This public static function checks for 5-Byte sequences, UTF8
	 *       has Bytes Sequences with a maximum length of 4.
	 *
	 * @author	bmorel at ssi dot fr (modified)
	 * @param	string $str The string to be checked
	 * @return	bool True if $str fits a UTF-8 model, false otherwise.
	 */
	public static function seems_utf8($Str) {
		for ($i=0; $i<strlen($Str); $i++) {
			if (ord($Str[$i]) < 0x80) continue; # 0bbbbbbb
			elseif ((ord($Str[$i]) & 0xE0) == 0xC0) $n=1; # 110bbbbb
			elseif ((ord($Str[$i]) & 0xF0) == 0xE0) $n=2; # 1110bbbb
			elseif ((ord($Str[$i]) & 0xF8) == 0xF0) $n=3; # 11110bbb
			elseif ((ord($Str[$i]) & 0xFC) == 0xF8) $n=4; # 111110bb
			elseif ((ord($Str[$i]) & 0xFE) == 0xFC) $n=5; # 1111110b
			else return false; # Does not match any model
			for ($j=0; $j<$n; $j++) { # n bytes matching 10bbbbbb follow ?
				if ((++$i == strlen($Str)) || ((ord($Str[$i]) & 0xC0) != 0x80))
				return false;
			}
		}
		return true;
	}


	/**
	 * Converts all accent characters to ASCII characters.
	 *
	 * If there are no accent characters, then the string given is just returned.
	 *
	 * @param string $string Text that might have accent characters
	 * @return string Filtered string with replaced "nice" characters.
	 */
	public static function remove_accents($string) {
		if ( !preg_match('/[\x80-\xff]/', $string) )
		return $string;

		if (self::seems_utf8($string)) {
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
			chr(194).chr(163) => '');

			$string = strtr($string, $chars);
		} else {
			// Assume ISO-8859-1 if not UTF-8
			$chars['in'] = chr(128).chr(131).chr(138).chr(142).chr(154).chr(158)
			.chr(159).chr(162).chr(165).chr(181).chr(192).chr(193).chr(194)
			.chr(195).chr(196).chr(197).chr(199).chr(200).chr(201).chr(202)
			.chr(203).chr(204).chr(205).chr(206).chr(207).chr(209).chr(210)
			.chr(211).chr(212).chr(213).chr(214).chr(216).chr(217).chr(218)
			.chr(219).chr(220).chr(221).chr(224).chr(225).chr(226).chr(227)
			.chr(228).chr(229).chr(231).chr(232).chr(233).chr(234).chr(235)
			.chr(236).chr(237).chr(238).chr(239).chr(241).chr(242).chr(243)
			.chr(244).chr(245).chr(246).chr(248).chr(249).chr(250).chr(251)
			.chr(252).chr(253).chr(255);

			$chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";

			$string = strtr($string, $chars['in'], $chars['out']);
			$double_chars['in'] = array(chr(140), chr(156), chr(198), chr(208), chr(222), chr(223), chr(230), chr(240), chr(254));
			$double_chars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
			$string = str_replace($double_chars['in'], $double_chars['out'], $string);
		}

		return $string;
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
	 * UTF-8 aware alternative to str_split
	 * Convert a string to an array
	 * @param string UTF-8 encoded
	 * @param int number to characters to split string by
	 * @return string characters in string reverses
	 * @see http://www.php.net/str_split
	 */
	public static function utf8_str_split($str, $split_len = 1) {

		if ($split_len = to_int($split_len) < 1){
			return FALSE;
		}

		if ( mb_strlen($str) <= $split_len ) {
			return array($str);
		}

		preg_match_all('/.{'.$split_len.'}|[^\x00]{1,'.$split_len.'}$/us', $str, $ar);
		return $ar[0];

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
	 * UTF-8 aware alternative to strrev
	 * Reverse a string
	 * @param string UTF-8 encoded
	 * @return string characters in string reverses
	 */
	public static function utf8_strrev($str){
		preg_match_all('/./us', $str, $ar);
		return implode('',array_reverse($ar[0]));
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
	 * UTF-8 aware substr_replace.
	 * @see http://www.php.net/substr_replace
	 */
	public static function mb_substr_replace($str, $replacement, $start, $length = NULL ) {
		return mb_substr($str, 0, $start) . $replacement . mb_substr($str, $length + 1);
		/*
		 $length = ($length === NULL) ? mb_strlen($str) : (int) $length;
		 preg_match_all('/./us', $str, $str_array);
		 preg_match_all('/./us', $replacement, $replacement_array);

		 array_splice($str_array[0], $offset, $length, $replacement_array[0]);
		 return implode('', $str_array[0]);
		 */
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
