<?php defined('SYSTEM_PATH') or die('No direct access');
/**
 * Time
 *
 * This class contains extra time functions that help to handle converting times
 * to database, unix, and HTML formats.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
class Time
{


	public static $format = 'M j, Y \a\t g:ia';
	public static $short_format = 'M j, Y';

	/**
	 * Convert a timestamp or string into a MySQL datetime format
	 * @param $time
	 * @return string
	 */
	public static function to_mysql($time = NULL)
	{
		// Return an SQL timestamp string
		return date('Y-m-d H:i:s', self::to_timestamp($time));
	}

	/**
	 * Convert a string, int, or date array into a UNIX timestamp
	 * @param mixed $time
	 * @return int
	 */
	public static function to_timestamp( $time = NULL )
	{

		// If it is a timestamp
		if(ctype_digit($time))
			return (int) $time;

		//If there is nothing set - default to now()
		if( ! $time OR $time === '0000-00-00' OR $time === '0000-00-00 00:00:00')
			return time();

		// If the time is an array
		if(is_array($time))
			return self::from_array($time);

		// If the time is a date string
		return strtotime($time);
	}


	/**
	 * Show a human-readable time difference ("10 seconds")
	 *
	 * @author	m4rw3r	<codeigniter.com>
	 * @param	int		$from
	 * @param	int		$to
	 * @return	string
	 */
	public static function time_difference($from = 0, $to = 0, $segments = 1)
	{

		//Get the difference between the timestamps
		$diff = abs($from - $to);

		if( ! $diff ) { return; }

		$units = array(
	    	'year'	=> 31557600,
	    	'month'	=> 2635200,
	    	'week'	=> 604800,
	    	'day'	=> 86400,
	    	'hour'	=> 3600,
	    	'minute'=> 60,
	    	'second'=> 1
		);

		$str = array();
		foreach($units as $title => $length)
		{
			if($d = floor($diff / $length))
			{
				$str[] = $d . ' ' . $title . ($d > 1 ? 's' : '');
				$diff -= $length * $d;
			}
			
			// We only want # (at most) unit $segments
			if(count($str) === $segments)
				break;
		}
		
		//Return the largest time - or the full time
		return implode(', ', $str);
	}


	/**
	 * Returns a the closest human friendly format of the date.
	 * If the second param is set to TRUE then it will return a
	 * compressed version of the date.
	 *
	 * @param	mixed	$timestamp
	 * @param	boolean	$short
	 * @return	string
	 */
	public static function human_friendly($time = NULL)
	{
		// Convert to Unix timestamp
		$time = self::to_timestamp($time);

		// Difference between now and then
		$diff = time() - $time;

		// If less than 3 hours ago
		if( $diff < 10800 )
			return self::time_difference($time, time()). ' ago';

		// If today
		if($diff < 86400 AND date('j') === date('j', $time))
			return 'today at '. date('g:ia', $time);

		// Show the proper date
		return date(self::$format, $time);
	}



	/**
	 * Turn 10 digit timestamp into an array
	 *
	 * @param int $ts
	 * @return array
	 */
	public static function timestamp_to_array($ts=null)
	{
		return array(
	    	'year' => date('Y', $ts),
	    	'month' => date('m', $ts),
	    	'day' => date('d', $ts),
	    	'hour' => date('H', $ts),
	    	'minute' => date('i', $ts),
	    	'second' => date('s', $ts),
			'ampm' => date("A", $ts),
			'gmt' => date("O", $ts),
		);
	}



	/**
	 * Turn an array into a UNIX timestamp (10 digit
	 *
	 * @param array $data
	 * @return int
	 */
	public static function from_array($data = NULL)
	{
		//Sometimes values aren't set - default to zero
		$year	= isset($data['year'])	? $data['year'] : 0;
		$month	= isset($data['month']) ? $data['month'] : 0;
		$day	= isset($data['day'])	? $data['day'] : 0;
		$hour	= isset($data['hour'])	? $data['hour'] : 0;
		$minute	= isset($data['minute'])? $data['minute'] : 0;
		$second	= isset($data['second'])? $data['second'] : 0;

		//Return the value (ten digit UNIX timestamp).
		return mktime($hour, $minute, $second, $month, $day, $year);
	}



	/**
	 * Turn a date/timestamp into select boxes
	 *
	 * @param mixed $time
	 * @param string $prefix prefix elements with
	 * @return string
	 */
	public static function to_select($time = NULL, $name = 'date', $class = '') {

		// Force a timestamp
		$time = self::to_timestamp($time);

		//Convert to an array
		$time = self::timestamp_to_array($time);

		$output = array();

		foreach($time as $type => $value) {
			$output[$type] = '<select name="'. $name. '['. $type. ']" class="'. $type. ' '. $class. '">'. "\n";

			//Create years
			if($type == 'year') {
				for($x=-5; $x<=5; $x++) {

					$year = $value + $x;

					$output[$type] .= "\t". '<option value="'. $year. '"'
					. ($year == $value ? ' selected="selected"' : '')
					. '>'. $year. '</option>'. "\n";
				}

			}

			//Create months
			if($type == 'month') {

				/*
				 We need to hard code the months because timezones can
				 throw off the PHP date function and return two month
				 choices that are the same.
				 */

				$months = array(
				1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May',
				6 => 'Jun', 7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct',
				11 => 'Nov', 12 => 'Dec'
				);

				for($x=1; $x<=12; $x++) {
					$output[$type] .= "\t". '<option value="'. $x. '"'
					. ($x == $value ? ' selected="selected"' : '')
					. '>'. $months[$x]. '</option>'. "\n";
				}
			}

			// Cannot caculate days based off current month! So we must just list all 32!
			if($type == 'day') {
				for($x=1;$x<32;$x++) {
					$output[$type] .= "\t". '<option value="'. $x. '"'
					. ($x == $value ? ' selected="selected"' : '')
					. '>'. $x. '</option>'. "\n";
				}
			}

			if($type == 'hour') {
				for($x=0;$x<24;$x++) {
					$output[$type] .= "\t". '<option value="'. $x. '"'
					. ($x == $value ? ' selected="selected"' : '')
					. '>'. $x. '</option>'. "\n";
				}
			}

			if($type == 'minute' OR $type == 'second') {
				for($x=0;$x<60;$x+=5) {
					$output[$type] .= "\t". '<option value="'. $x. '"'
					. ($value >= $x AND $value <= $x + 4 ? ' selected="selected"' : '')
					. '>'. $x. '</option>'. "\n";
				}
			}

			//Close
			$output[$type] .= '</select>'. "\n";
		}

		return $output;

	}

	
	/**
	 * Turn a date/timestamp into select boxes
	 *
	 * @param mixed $time
	 * @param string $prefix prefix elements with
	 * @return string
	 */
	public static function to_form($time = NULL, $name = 'date', $class = '') {

		// Force a timestamp
		$time = self::to_timestamp($time);

		//Convert to an array
		$time = self::timestamp_to_array($time);

		
		$output = array();
		
		// Set month as a drop-down
		$output['month'] = '<select name="'. $name. '[month]" class="month '. $class. '">'. "\n";
		
		/*
		We need to hard code the months because timezones can
		throw off the PHP date function and return two month
		choices that are the same.
		*/
		$months = array(
			1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May',
			6 => 'Jun', 7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct',
			11 => 'Nov', 12 => 'Dec'
		);

		for($x=1; $x<=12; $x++)
		{
			$output['month'] .= "\t". '<option value="'. $x. '"'
			. ($x == $time['month'] ? ' selected="selected"' : '')
			. '>'. $months[$x]. '</option>'. "\n";
		}
		
		// Close the select box
		$output['month'] .= "</select>\n";
		
		// Year input
		$output['year'] = '<input type="text" name="'. $name. '[year]" class="year '. $class. '" value="'. $time['year']. '" />';
		
		// Day input
		$output['day'] = '<input type="text" name="'. $name. '[day]" class="day '. $class. '" value="'. $time['day']. '" />';
		
		// Hour input
		$output['hour'] = '<input type="text" name="'. $name. '[hour]" class="hour '. $class. '" value="'. $time['hour']. '" />';
		
		// Minute input
		$output['minute'] = '<input type="text" name="'. $name. '[minute]" class="minute '. $class. '" value="'. $time['minute']. '" />';
		
		return $output;

	}
	
	/**
	 * Returns an array representation of the given calendar month.
	 * The array values are timestamps which allow you to easily format
	 * and manipulate the dates as needed.
	 *
	 * @author simple-php-framework
	 * @param int $month
	 * @param int $year
	 * @return array
	 */
	public static  function calendar($month=null, $year=null) {

		if(!$month) $month = date('n');
		if(!$year) $year = date('Y');

		$first = mktime(0, 0, 0, $month, 1, $year);
		$last = mktime(23, 59, 59, $month, date('t', $first), $year);

		$start = $first - (86400 * date('w', $first));
		$stop = $last + (86400 * (7 - date('w', $first)));

		$out = array();
		while($start < $stop)
		{
			$week = array();
			if($start > $last) break;
			for($i = 0; $i < 7; $i++)
			{
				$week[$i] = $start;
				$start += 86400;
			}
			$out[] = $week;
		}

		return $out;
	}


	/**
	 * Caculate the total, elapsed, percent complete, and days left between two dates.
	 *
	 * @param mixed $start
	 * @param mixed $end
	 * @return array
	 */
	public static function between_dates($start = NULL, $end = NULL) {

		// Force timestamps
		$start = self::to_timestamp($start);
		$end = self::to_timestamp($end);

		//Array
		$date = array();

		//Days challenge lasts
		$date['total'] = round(($end - $start) / 86400);

		//Days until end of challenge
		$date['left'] = round(($end - time()) / 86400);

		//Only valid for challenges that have started!
		if(time() > $start) {

			//Days that have been completed
			$date['completed'] = round((time() - $start) / 86400);

			//Already started
			$date['starts'] = null;

			//Percent completed
			$date['percent'] = round(($date['completed'] / $date['total']) * 100);

		} else {
			//Not started yet
			$date['completed'] = 0;

			//Days until challenge starts
			$date['starts'] = round(($start - time()) / 86400);

			//Percent completed
			$date['percent'] = 0;
		}

		//If way past end date - limit to 100%
		if($date['percent'] > 100) {
			$date['percent'] = 100;
		}

		//If way past end date - limit to 0 days left
		if($date['left'] < 0) {
			$date['left'] = 0;
		}

		//Return an array
		return $date;

	}

}
