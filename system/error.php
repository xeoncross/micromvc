<?php
/**
 * Error & Exception
 *
 * Provides global error and exception handling with detailed backtraces.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
class Error
{

public static $found = FALSE;

public static function header()
{
	headers_sent() OR header('HTTP/1.0 500 Internal Server Error');
}

public static function fatal()
{
	if($e=error_get_last())Error::exception(new ErrorException($e['message'],$e['type'],0,$e['file'],$e['line']));
}

public static function handler($c,$e,$f=0,$l=0)
{
	if((error_reporting()&$c)===0)return TRUE;self::$found=1;self::header();$v=new View('error','system');$v->error=$e;$v->title=lang($c);print $v;log_message("[$c] $e [$f] ($l)");return TRUE;
}


public static function exception(Exception $e)
{
	self::$found=1;$m="{$e->getMessage()} [{$e->getFile()}] ({$e->getLine()})";try{log_message($m);self::header();$v=new View('exception','system');$v->exception=$e;print$v;}catch(Exception$e){print$m;}exit(1);
}


/**
 * Fetch and HTML highlight serveral lines of a file.
 *
 * @param string $f the file to open
 * @param integer $n the line number to highlight
 * @param integer $p the number of padding lines on both side
 * @return string
*/
public static function source($f,$n,$p=5)
{
	$l=array_slice(file($f),$n-$p-1,$p*2+1,1);$o='';foreach($l as$i=>$r)$o.='<b>'.sprintf('%'.strlen($n+$p).'d',$i+1).'</b> '.($i+1==$n?'<em>'.h($r).'</em>':h($r));return $o;
}


/**
 * Fetch a backtrace of the code
 *
 * @param int $o offset to start from
 * @param int $l limit of levels to collect
 * @return array
 */
public static function backtrace($o,$l=5)
{
	$t=array_slice(debug_backtrace(),$o,$l);foreach($t as$i=>&$v){if(!isset($v['file'])){unset($t[$i]);continue;}$v['source']=self::source($v['file'],$v['line']);}return$t;
}

}

// END