<?php
/**
 * Upload
 *
 * Uploads and parses files ensuring they are safely placed on the server.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
class Upload
{

//List of allowed file extensions separated by "|"
public $allowed_files = 'gif|jpg|jpeg|png|txt|zip|rar|tar|gz|mov|flv|mpg|mpeg|mp4|wmv|avi|mp3|wav|ogg';

/**
 * Try to Upload the given file returning the filename on success
 * 
 * @param array $f $_FILES array element
 * @param string $d destination directory
 * @param boolean $o overwrite existing files of the same name?
 * @param integer $s maximum size allowed (can also be set in php.ini or server config)
 */
static function file($f,$d,$o=FALSE,$s=FALSE)
{
	if(self::error($f)OR!extract(self::parse_filename($f['name']))OR!$name||($s&&$f['size']>$s))return 0;dir::usable($d);$n=$o?"$name.$ext":self::unique_filename($d,$name,$ext);if(self::move($f,$d.$n))return$n;
}


static function error($f)
{
	if(!isset($f['tmp_name'],$f['name'],$f['error'],$f['size'])OR$f['error']!=UPLOAD_ERR_OK)return TRUE;
}


static function parse_filename($f)
{
	$p=pathinfo($f);return((isset($p['filename'],$p['extension'])&&$n=string::sanitize_filename($p['filename']))?array('name'=>$n,'ext'=>strtolower($p['extension'])):array('name'=>'','ext'=>''));
}


static function allowed_file($ext)
{
	return in_array($ext,explode('|',self::$allowed_files));
}


static function unique_filename($d,$f,$e)
{
	$x=null;while(file_exists("$d$f$x.$e")){$x++;}return"$f$x.$e";
}


static function move($f,$d)
{
	return move_uploaded_file($f['tmp_name'],$d);
}

}

// END