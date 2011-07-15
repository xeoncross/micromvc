<?php
/**
 * Time
 *
 * Extends the DateTime class to make it easier to caculate time differences and
 * display human-readable representations .
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2011 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
class Time extends DateTime
{

	/**
	 * Create a new datetime object from a string or timestamp
	 */
	public function __construct($time = 'now', DateTimeZone $timezone = NULL)
	{
		if(is_array($time))
		{
			$time = self::fromArray($time);
		}

		if(is_int($time) OR ctype_digit($time))
		{
			$time = "@$time";
		}

		if($timezone)
		{
			parent::__construct($time, $timezone);
		}
		else
		{
			parent::__construct($time);
		}
	}


	/**
	 * Return difference between $this and $now
	 *
	 * @param Datetime|String $now
	 * @return DateInterval
	 */
	public function diff($now = 'NOW', $absolute = FALSE)
	{
		if( ! $now instanceOf DateTime)
		{
			$now = new Time($now);
		}
		return parent::diff($now, $absolute);
	}


	/**
	 * Return a SQL date string
	 *
	 * @return string
	 */
	public function getSQL()
	{
		return $this->format('Y-m-d H:i:s');
	}


	/**
	 * Show a human-readable time difference ("10 seconds")
	 *
	 * @param int $diff the difference to caculate
	 * @param int $length the max length of values
	 * @return string
	 */
	public function difference($diff = 'NOW', $length = 1)
	{
		$diff = $this->diff($diff);
		$units = array('y' => 'year', 'm' =>'month', 'd' =>'day', 'h' => 'hour', 'i' => 'minute', 's' =>'second');
		$result = array();
		foreach($units as $k => $name)
		{
			$value = $diff->$k;

			if($diff->$k)
			{
				$result[] = $diff->$k. " $name" . ($diff->$k > 1 ? 's' : '');
			}
			if(count($result) == $length) return implode(', ', $result);
		}
	}

	/**
	 * Returns a the closest human friendly format of the date from right now .
	 *
	 * @param string $format if longer than one day ago
	 * @return string
	 */
	public function humanFriendly($format = 'M j, Y \a\t g:ia')
	{
		$diff = $this->diff();
		$timestamp = $this->getTimestamp();

		if( ! $diff->d)
		{
			$str = $this->difference();
			return $timestamp < time() ? "$str ago" : "in $str";
		}
		return $this->format($format);
	}


	/**
	 * Return an array of time values
	 *
	 * @return array
	 */
	public function getArray()
	{
		$ts = $this->getTimestamp();
		return array(
			'year' => date('Y',$ts),
			'month' => date('m',$ts),
			'day' => date('d',$ts),
			'hour' => date('H',$ts),
			'minute' => date('i',$ts),
			'second' => date('s',$ts)
		);
	}


	/**
	 * Turn an array into a timestamp (used by constructor)
	 *
	 * @param array $data
	 * @return int
	 */
	public static function fromArray(array $data)
	{
		foreach(array('year','month','day','hour','minute','second') as $unit)
		{
			$$unit = isset($data[$unit]) ? $data[$unit] : 0;
		}
		return mktime($hour, $minute, $second, $month, $day, $year);
	}


	/**
	 * Quick way to show a humanfriendly time
	 *
	 * @param mixed $time
	 * @return string
	 */
	public static function show($time)
	{
		$time = new Time($time);
		return $time->humanFriendly();
	}

}

// END
