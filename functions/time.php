<?php

/**
 * Convert Unix/Mysql timestamps back and forth (auto-detects)
 *
 * @param mixed $timestamp
 * @return mixed
 */
function convert_time($timestamp=null) {

	//If there is nothing set - default to now()
	if( ! $timestamp || '0000-00-00' == $timestamp
		|| '0000-00-00 00:00:00' == $timestamp) {
		return time();
	}

	//Return Unix timestamp
	if( ! ctype_digit($timestamp)) {
		return strtotime($timestamp);
	}

	//return Mysql timestamp
	return date('Y-m-d H:i:s', $timestamp);

}


/**
 * Show a human-readable time difference ("10 seconds")
 *
 * @author	m4rw3r	<codeigniter.com>
 * @param	int		$from
 * @param	int		$to
 * @return	string
 */
function time_difference($from = 0, $to = 0) {

	//Get the difference between the timestamps
	$diff = abs($from - $to);

	$units = array(
    	'year'	=> 31557600,
    	'month'	=> 2635200,
    	'week'	=> 604800,
    	'day'	=> 86400,
    	'hour'	=> 3600,
    	'minute'=> 60,
    	'second'=> 1
	);

	$str = '';
	foreach($units as $title => $length) {
		if($d = floor($diff / $length)) {
			$str[] = $d . ' ' . $title . ($d > 1 ? 's' : '');
			$diff -= $length * $d;
		}
	}

	return implode(' ', $str);
}


/**
 * Turn 10 digit timestamp into an array
 *
 * @param int $ts
 * @return array
 */
function timestamp_to_array($ts=null) {
	//Return array
	return array(
    	'year' => date('Y', $ts),
    	'month' => date('m', $ts),
    	'day' => date('d', $ts),
    	'hour' => date('H', $ts),
    	'minute' => date('i', $ts),
    	'second' => date('s', $ts),
		'ampm' => date("A", $ts),
		'gmt' => date("O", $time),
	);
}



/**
 * Turn an array into a UNIX timestamp (10 digit
 *
 * @param array $data
 * @return int
 */
function array_to_timestamp($data = NULL) {
    //Sometimes hours/minutes/seconds aren't set; default to zero;
    $hour   = @$data['hour']   ? $data['hour']   : 0;
    $minute = @$data['minute'] ? $data['minute'] : 0;
    $second = @$data['second'] ? $data['second'] : 0;

    //Return the value (ten digit UNIX timestamp).
    return mktime($hour, $minute, $second, $data['month'], $data['day'], $data['year']);
}



/**
 * Turn a date/timestamp into select boxes
 *
 * @param mixed $time
 * @param string $prefix prefix elements with
 * @return string
 */
function date_to_select($time=null, $name='date') {

	//If it is a timestamp - make an array
	if(!ctype_digit($time) OR ! $time) {
		$time = convert_time($time);
	}

	//Convert to an array
	$time = timestamp_to_array($time);

	$output = array();

	foreach($time as $type => $value) {
		$output[$type] = '<select name="'. $name. '['. $type. ']" class="'. $type. '">'. "\n";

		//Create years
		if($type == 'year') {
			for($x=-2; $x<=2; $x++) {

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

		/* Can't caculate days based off current month!!!!
		//Create Days
		if($type == 'day') {
			$days_in_month = date('t', strtotime($time['year']. '-'. $time['month']. '-1'));

			for($x=1;$x<$days_in_month;$x++) {
				$output[$type] .= "\t". '<option value="'. $x. '"'
					. ($x == $value ? ' selected="selected"' : '')
					. '>'. date("j", mktime(0, 0, 0, 0, $x)). '</option>'. "\n";
			}
		}
		*/

		if($type == 'day') {
			for($x=1;$x<32;$x++) {
				$output[$type] .= "\t". '<option value="'. $x. '"'
					. ($x == $value ? ' selected="selected"' : '')
					. '>'. $x. '</option>'. "\n";
			}
		}



		if($type == 'hour') {
			for($x=1;$x<24;$x++) {
				$output[$type] .= "\t". '<option value="'. $x. '"'
					. ($x == $value ? ' selected="selected"' : '')
					. '>'. $x. '</option>'. "\n";
			}
		}

		if($type == 'minute' OR $type == 'second') {
			for($x=0;$x<60;$x+=5) {
				$output[$type] .= "\t". '<option value="'. $x. '"'
					. ($value >= $x && $value <= $x + 4 ? ' selected="selected"' : '')
					. '>'. $x. '</option>'. "\n";
			}
		}

		//Close
		$output[$type] .= '</select>'. "\n";
	}

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
function calendar($month=null, $year=null)
{
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
function between_days($start=null, $end=null) {

	//Check the dates
	foreach(array('start', 'end') as $type) {
		//Dates must be given
		if(!$$type) {
			return FALSE;
		}
		//If it is NOT a timestamp - make it one!
		if(!ctype_digit($$type)) {
			$$type = strtotime($$type);
		}

	}

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
