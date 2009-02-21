<?php

/**
 * Convert Unix/Mysql timestamps back and forth (auto-detects)
 *
 * @param mixed $timestamp
 * @return mixed
 */
function convert_time($timestamp=null) {
	if(!$timestamp) {
		$timestamp = time();
	}
	//return Unix timestamp
	if(!ctype_digit($timestamp)) {
		return strtotime($timestamp);
	}
	//return Mysql timestamp
	return date('Y-m-d H:i:s', $timestamp);

}


/**
 * Turn 10 digit timestamp into an array
 * 
 * @param int $ts
 * @return array
 */
function time_to_array($ts=null) {
	//Return array
	return array(
    	'year' => date('Y', $ts),
    	'month' => date('m', $ts),
    	'day' => date('d', $ts),
    	'hour' => date('H', $ts),
    	'minute' => date('i', $ts),
    	'second' => date('s', $ts)
	);
}



/**
 * Turn an array into a UNIX timestamp (10 digit
 *
 * @param array $data
 * @return int
 */
function array_to_time($data=null) {

	//Now we return the value (a 10 digit UNIX timestamp).
	return mktime($data['hour'],$data['minute'],$data['second'],$data['month'],$data['day'],$data['year']);

}



/**
 * Turn a date/timestamp into select boxes
 * 
 * @param mixed $time
 * @return string
 */
function date_to_select($time=null) {

	//If no timestamp is given
	$time = $time ? $time : time();

	//If it is a timestamp - make an array
	if(ctype_digit($time)) {
		$time = time_to_array($time);
	}
	
	$output = '';
	
	foreach($time as $type => $value) {
		$output[$type] = '<select name="'. $type. '" class="'. $type. '">'. "\n";
		
		//Create years
		if($type == 'year') {
			for($x=-5; $x<=5; $x++) {
			
				$year = $value + $x;
					
				$output[$type] .= "\t". '<option value="'. $year. '"'
					. ($year == $value ? ' selected="selected"' : '')
					. '>'. date("Y", mktime(0, 0, 0, 0, 0, $year)). '</option>'. "\n";
			}
			
		}
		
		//Create months
		if($type == 'month') {
			for($x=1; $x<=12; $x++) {
				$output[$type] .= "\t". '<option value="'. $x. '"'
					. ($x == $value ? ' selected="selected"' : '')
					. '>'. date("M", mktime(0, 0, 0, $x)). '</option>'. "\n";
			}
		}
		
		//Create Days
		if($type == 'day') {
			$days_in_month = date('t', strtotime($time['year']. '-'. $time['month']. '-1'));
			
			for($x=1;$x<$days_in_month;$x++) {
				$output[$type] .= "\t". '<option value="'. $x. '"'
					. ($x == $value ? ' selected="selected"' : '')
					. '>'. date("j", mktime(0, 0, 0, 0, $x)). '</option>'. "\n";
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
					. ($value > $x && $value < $x + 5 ? ' selected="selected"' : '')
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

?>