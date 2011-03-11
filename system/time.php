<?php
/**
 * Time
 *
 * Extends the DateTime class to make it easier to caculate time differences and
 * display human-readable representations.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
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
	if(is_int($time))$time="@$time";if(is_array($time))$time=self::fromArray($time);if($timezone)parent::__construct($time, $timezone);else parent::__construct($time);
}


/**
 * Return difference between $this and $now
 *
 * @param Datetime|String $now
 * @return DateInterval
 */
public function diff($now = 'NOW', $absolute = FALSE)
{
	if(!($now instanceOf DateTime))$now=new Time($now);return parent::diff($now, $absolute);
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
 * @param int $d the difference to caculate
 * @param int $l the max length of values
 * @return string
 */
public function difference($d = 'NOW', $l = 1)
{
	$d=$this->diff($d);$u=array('y'=>'year','m'=>'month','d'=>'day','h'=>'hour','i'=>'minute','s'=>'second');$r=array();foreach($u as$k=>$n){$v=$d->$k;if($v)$r[]="$v $n".($v>1?'s':'');if(count($r)==$l)return implode(', ',$r);}
}

/**
 * Returns a the closest human friendly format of the date from right now.
 *
 * @param string $format if longer than one day ago
 * @return string
 */
public function humanFriendly($format = 'M j, Y \a\t g:ia')
{
	$diff=$this->diff();$t=$this->getTimestamp();if(!$diff->d){$s=$this->difference();return$t<time()?"$s ago":"in $s";}return$this->format($format);
}


/**
 * Return an array of time values
 *
 * @return array
 */
public function getArray()
{
	$ts=$this->getTimestamp();return array('year'=>date('Y',$ts),'month'=>date('m',$ts),'day'=>date('d',$ts),'hour'=>date('H',$ts),'minute'=>date('i',$ts),'second'=>date('s',$ts));
}


/**
 * Turn an array into a timestamp (used by constructor)
 *
 * @param array $data
 * @return int
 */
public static function fromArray( array $data)
{
	foreach(array('year','month','day','hour','minute','second')as$k)$$k=isset($data[$k])?$data[$k]:0;return mktime($hour,$minute,$second,$month,$day,$year);
}


/**
 * Quick way to show a humanfriendly time
 * 
 * @param mixed $time
 * @return string
 */
public static function show($time)
{
	$t=new Time($time);return$t->humanFriendly();
}

}

// END