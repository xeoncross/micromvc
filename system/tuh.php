<?php
/**
 * tuh HTML Parser
 *
 * A Tiny, UTF-8 valid HTML Parser in PHP. Useful for processing text comments
 * for display in webpages. Can also unparse HTML back into text comments.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
class tuh 
{

protected static $tags = 'b|i|em|ul|li|ol|pre|blockquote|q|table|td|tr|th';

/**
 * Parse text into valid HTML Markup
 *
 * @param string $text UTF-8 text string
 * @param boolean $d TRUE if the text is dangerous (i.e. user input)
 */
public static function parse($t, $d = TRUE)
{
	$t=preg_split('/<code>(.+?)<\/code>/is',$t,-1,2);$c=$s='';foreach($t as$p)if($c=1-$c){if($p=trim($p)){$p=preg_replace(array("/\r/","/\n\n+/"),array('',"\n\n"),$p);$s.=tuh::text($p,$d);}}else$s.=tuh::code($p)."\n\n";return$s;
}

/**
 * Unparse HTML back into plain text
 *
 * @param string $text UTF-8 HTML string
 * @param boolean $d TRUE if the text is dangerous (i.e. user input)
 */
public static function unparse($t, $d = TRUE)
{
	$t=preg_split('/<code>(.+?)<\/code>/is',$t,-1,2);$c=$s='';foreach($t as$p)if($c=1-$c)$s.=str_replace(array('<p>','</p>','<br />'),'',($d?tuh::unh($p):$p));else$s.=tuh::uncode($p);return$s;
}

/**
 * Convert a text string into a valid UTF-8 string
 *
 * @param string $text string
 */
public static function to_utf8($t)
{
	if(!preg_match('/[^\x00-\x7F]/S',$t)){$e=error_reporting(~E_NOTICE);$t=iconv('UTF-8','UTF-8//IGNORE',$t);error_reporting($e);}return$t;
}

/**
 * Close all open HTML tags
 *
 * @param string $text UTF-8 HTML string
 */
public static function close($h)
{
	preg_match_all('/<(?!\/|a)([^<>]+)\b[^\/|\>]*>/',$h,$o);preg_match_all('/<\/(?!a)([^<>]+)\b[^\/|\>]*>/',$h,$c);return(($a=array_diff($c[1],$o[1]))?'<'.join('><',$a).'>':'').$h.(($b=array_diff($o[1],$c[1]))?'</'.join('></',array_reverse($b)).'>':'');
}

/*
 * Internal Processing Methods
 */

protected static function code($t)
{
	$s=$e=0;if(strpos($t,'<?php')===FALSE){$s=1;$t='<?php'.$t;}if(strpos($t,'?>')===FALSE){$e=1;$t.='?>';}$t=highlight_string(trim($t),TRUE);if($s)$t=str_replace('&lt;?php','',$t);if($e)$t=str_replace('?&gt;','',$t);return$t;
}

protected static function text($t, $d = TRUE)
{
	$s='';foreach(explode("\n\n",$t)as$l){$l=tuh::quote(tuh::link($l));if($d)$l=tuh::decode(tuh::h($l));$l=tuh::close($l);$s.=(preg_match('/^<([a-z][a-z0-9]+)\b[^>]*>.*?<\/\1>$/is',$l)?$l:nl2br("<p>$l</p>"))."\n\n";}return$s;
}

protected static function decode($t)
{
	return preg_replace('/&lt;(\/)?('.tuh::$tags.')&gt;/i','<\1\2>',$t);
}

protected static function uncode($t)
{
	return "<code>\n".tuh::unh(trim(str_replace('&nbsp;',' ',str_ireplace('<br />', "\n",strip_tags($t)))))."\n</code>";
}

protected static function h($t)
{
	return htmlspecialchars($t,ENT_QUOTES,'UTF-8');
}

protected static function unh($t)
{
	return htmlspecialchars_decode($t,ENT_QUOTES);
}

protected static function link($t, $d = TRUE)
{
	return preg_replace('/\[([a-z]+:\/\/(([a-z0-9-]{1,70}\.){1,4}([a-z]{2,4})(:\d{2,4})?(\/[\w\/.\-?=&;%]{1,200})?))\]/i', '<a href="$1" '.($d?' rel="nofollow"':'').'>$1</a>',$t);
}

protected static function quote($t)
{
	return preg_replace('/^"([^"]{10,1000})"( *- *[a-z0-9 ]+)?$/i','<blockquote>$1$2</blockquote>',$t);
}

}

// END