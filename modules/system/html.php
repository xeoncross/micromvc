<?php
/**
 * HTML
 *
 * Provides quick HTML snipets for common tasks
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
class html
{

/**
 * Create a gravatar <img> tag
 *
 * @param $email the users email address
 * @param $size	the size of the image
 * @param $alt the alt text
 * @param $rating max image rating allowed
 * @return string
 */
public static function gravatar($email = '', $size = 80, $alt = 'Gravatar', $rating = 'g')
{
	return '<img src="http://www.gravatar.com/avatar/'.md5($email)."?s=$size&d=wavatar&r=$rating\" alt=\"$alt\" />";
}


/**
 * Generates an obfuscated HTML version of an email address.
 *
 * @param string $email address
 * @return string
 */
public static function email($email)
{
	$s='';foreach(str_split($email)as$l){switch(rand(1,3)){case 1:$s.='&#'.ord($l).';';break;case 2:$s.='&#x'.dechex(ord($l)).';';break;case 3:$s.=$l;}}return$s;
}


/**
 * Convert a multidimensional array to an unfiltered HTML UL. You can
 * pass attributes such as id, class, or javascript.
 *
 * @param array $ul the array of elements
 * @param array $attributes the array of HTML attributes
 * @return string
 */
static function ul_from_array(array $ul, array $attributes = array())
{
	$h='';foreach($ul as$k=>$v)if(is_array($v))$h.=self::tag('li',$k.self::ul_from_array($v));else$h.=self::tag('li',$v);return self::tag('ul',$h,$attributes);
}


/**
 * Compiles an array of HTML attributes into an attribute string and
 * HTML escape it to prevent malicious data.
 *
 * @param array $attributes the tag's attribute list
 * @return string
 */
public static function attributes(array $attributes = array())
{
	if(!$attributes)return;asort($attributes);$h='';foreach($attributes as$k=>$v)$h.=" $k=\"".h($v).'"';return$h;
}


/**
 * Create an HTML tag
 *
 * @param string $tag the tag name
 * @param string $text the text to insert between the tags
 * @param array $attributes of additional tag settings
 * @return string
 */
public static function tag($tag, $text = '', array $attributes = array())
{
	return"\n<$tag".self::attributes($attributes).($text===0?' />':">$text</$tag>");
}


/**
 * Create an HTML Link
 *
 * @param string $url for the link
 * @param string $text the link text
 * @param array $attributes of additional tag settings
 * @return string
 */
public static function link($url, $text = '', array $attributes = array())
{
	return self::tag('a',$text,($attributes+array('href'=>site_url($url))));
}


/**
 * Auto creates a form select dropdown from the options given.
 *
 * @param string $name the select element name
 * @param array $options the select options
 * @param mixed $selected the selected options(s)
 * @param array $attributes of additional tag settings
 * @return string
 */
public static function select($name, array $options = array(), $selected = NULL, array $attributes = array())
{
	$h='';foreach($options as$k=>$v){$a=array('value'=>$k);if($selected&&in_array($k,(array)$selected))$a['selected']='selected';$h.=self::tag('option',$v,$a);}return self::tag('select',$h,$attributes+array('name'=>$name));
}


/**
 * Turn a date/timestamp into HTML form elements
 *
 * @param mixed $time
 * @param string $name to prefix elements with
 * @param string $class name to give elements
 * @return string
 */
public static function datetime($ts = NULL, $name = 'datetime', $class = 'datetime')
{
	$ts=new Time($ts);$t=$ts->getArray($ts);$e[]=self::month_select($t['month'],$name);foreach(lang('time_units')as$k=>$v)$e[]=html::tag('input',0,array('name'=>"{$name}[$k]",'type'=>'text','value'=>isset($t[$k])?$t[$k]:0,'class'=>$k));return vsprintf(lang('html_datetime'),$e);
}


/**
 * Create a select box for months based on the user language file
 *
 * @param int $month current selected month
 * @param string $name to prefix elements with
 * @param string $class name to give element
 * @return string
 */
public static function month_select($month = 1, $name = 'datetime', $class = 'month')
{
	return html::select("{$name}[month]",lang('html_months'),$month,array('class'=>$class));
}

}

// END
