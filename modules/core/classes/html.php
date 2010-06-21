<?php defined('SYSTEM_PATH') or die('No direct access');
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

	// The value to replace in the URL
	public static $pagination_page_identifier = '[[page]]';

	// The name of the pagination class
	public static $pagination_class = 'pagination';


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
		return '<img src="http://www.gravatar.com/avatar/'. md5($email). '?s='
		. $size. '&d=wavatar&r='. $rating. '" alt="'. $alt. '" />';
	}


	/**
	 * Generates an obfuscated version of an email address.
	 *
	 * @param   string  email address
	 * @return  string
	 */
	public static function email($email)
	{
		$safe = '';
		foreach (str_split($email) as $letter)
		{
			switch (rand(1, 3))
			{
				// HTML entity code
				case 1: $safe .= '&#'.ord($letter).';'; break;
				// Hex character code
				case 2: $safe .= '&#x'.dechex(ord($letter)).';'; break;
				// Raw (no) encoding
				case 3: $safe .= $letter;
			}
		}

		return $safe;
	}


	/**
	 * Auto creates a form dropdown from the options given.
	 * @author	http://codeigniter.com
	 * @param $name
	 * @param $options
	 * @param $selected
	 * @param $extra
	 * @return string
	 */
	public static function select($name = '', $options = array(), $selected = array(), array $attributes = NULL)
	{

		if ( ! is_array($selected)) {
			$selected = array($selected);
		}

		// If no selected state was submitted we will attempt to set it automatically
		if (count($selected) === 0) {
			// If the form name appears in the $_POST array we have a winner!
			if (isset($_POST[$name])) {
				$selected = array($_POST[$name]);
			}
		}

		//Set the name
		$attributes['name'] = $name;

		//Add an ID value if not given
		if( empty($attributes['id']) ) {
			$attributes['id'] = $name;
		}

		if( count($selected) > 1 ) {
			$attributes['multiple'] = 'multiple';
		}

		$form = '<select'. self::attributes($attributes). ">\n";

		foreach ($options as $key => $val) {

			$attributes = array('value' => $key);

			if( in_array($key, $selected) ) {
				$attributes['selected'] = 'selected';
			}

			$form .= '<option'. self::attributes($attributes) .'>'. h(to_string($val))."</option>\n";
		}

		$form .= '</select>';

		return $form;
	}


	/**
	 * Convert a multidimensional array to an HTML UL. You can
	 * pass attributes such as id, class, or start.
	 * 
	 * @param array $array the array of elements
	 * @param array $attributes the array of HTML attributes
	 * @param int $site_url prefix string keys with the site url?
	 * @return string
	 */
	function ul_from_array(array $array, array $attributes = NULL, $site_url = TRUE)
	{
		$output = '<ul'. self::attributes($attributes).'>';
		foreach($array as $key => $value)
		{
			if(is_array($value))
			{
				$output .= '<li class="group">'.h($key).array_to_ul($value)."</li>\n";
			}
			elseif(is_int($key))
			{
				$output .= '<li>'.h($value)."</li>\n";
			}
			else
			{
				$output .= '<li><a href="'. $site_url ? site_url($key) : h($key).'">'.h($value)."</a></li>\n";
			}
		}
		return "\n$output</ul>\n";
	}
	

	/**
	 * Creates a style sheet link.
	 *
	 * @param   string  file name
	 * @param   array   default attributes
	 * @param   boolean  include the index page
	 * @return  string
	 */
	public static function style($file, array $attributes = NULL) {

		// Add the base URL if needed
		if (strpos($file, '://') === FALSE){
			$file = site_url($file);
		}

		// Set the stylesheet link
		$attributes['href'] = $file;
		// Set the stylesheet rel
		$attributes['rel'] = 'stylesheet';
		// Set the stylesheet type
		$attributes['type'] = 'text/css';

		return '<link'. self::attributes($attributes). ' />';
	}


	/**
	 * Creates a script link.
	 *
	 * @param   string   file name
	 * @param   array    default attributes
	 * @param   boolean  include the index page
	 * @return  string
	 */
	public static function script($file, array $attributes = NULL) {

		// Add the base URL if needed
		if (strpos($file, '://') === FALSE){
			$file = site_url($file);
		}

		// Set the script link
		$attributes['src'] = $file;
		$attributes['type'] = 'text/javascript';

		return '<script'. self::attributes($attributes). '></script>';
	}


	/**
	 * Creates pagination links for the total number of pages
	 *
	 * @param $total the total number of items
	 * @param $url the URI value to place in the links (must include [[page]] )
	 * @param $current the current page
	 * @param $per_page the number to show each page (default 10)
	 * @param $links the number of page links to show
	 * @return string
	 */
	public static function pagination($total, $url = '', $current = 1, $per_page = 10, $links = 2) {

		// The key to replace in the URL
		$key = self::$pagination_page_identifier;

		// Force these values to (INT)
		$total = ( ($total = to_int($total) ) < 1 ? 0 : $total);
		$current = ( ($current = to_int($current) ) < 1 ? 1 : $current);
		$per_page = to_int($per_page);

		//The Number of pages based on the total number of items and the number to show each page
		$total = ceil($total / $per_page);

		// If no total was given, this is an invalid page, or there is only one page!
		if( ! $total OR $current > $total OR $total <= 1) {
			return;
		}

		// Start the pagination HTML
		$html = '<div class="'. self::$pagination_class. '">';

		//If this is NOT the first page - show a previous link
		if( $current > 1 ) {
			$html .= '<a href="'. str_replace($key, ($current - 1), $url)
			. '" class="previous">'. lang::get('html_pagination_previous'). '</a>';
		}

		//Show first page?
		if($current > $links + 1) {
			$html .= '<a href="'. str_replace($key, 1, $url)
			. '" class="first">'. lang::get('html_pagination_first'). '</a>';
		}


		//Only if we have more pages than links to show
		$start = (($current - $links) > 0) ? $current - ($links) : 1;
		$end   = (($current + $links) < $total) ? $current + $links : $total;

		//For each page, create the URL
		for($i = $start; $i <= $end; $i++) {

			$link = str_replace($key, $i, $url);

			if($current == $i) {
				$html .= '<a name="current_page" class="current_page">'. $i. '</a>';

			} else {
				$html .= '<a href="'. $link. '" class="page_'. $i. '">'. $i. '</a>';
			}

		}

		//Show last page?
		if($current + $links < $total) {
			$html .= '<a href="'. str_replace($key, $total, $url)
			. '" class="last">'. lang::get('html_pagination_last'). '</a>';
		}

		//If this isn't the last page - add a "next" link
		if($current < $total) {
			$html .= '<a href="'. str_replace($key, ($current + 1), $url)
			. '" class="next">'. lang::get('html_pagination_next'). '</a>';
		}

		$html .= '</div>';

		return $html;
	}


	/**
	 * Compiles an array of HTML attributes into an attribute string.
	 *
	 * @param   array   attribute list
	 * @return  string
	 */
	public static function attributes(array $attributes = NULL) {

		if( ! $attributes ) { return; }

		$compiled = '';
		foreach ($attributes as $key => $value) {

			// Skip attributes that have NULL values
			if ($value === NULL) {
				continue;
			}

			// Add the attribute value
			$compiled .= ' '. to_string($key). '="'. h(to_string($value)). '"';
		}

		return $compiled;
	}

}
