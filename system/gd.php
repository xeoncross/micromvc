<?php
/**
 * GD Image
 *
 * Class for intelligently cropping and resizing images keeping the subject in 
 * focus and preserving image transparency. Works with GIF, JPEG, and PNG. 
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
class GD
{

/**
 * Create a JPEG thumbnail for the given png/gif/jpeg image and return the path to the new image.
 * 
 * @param string $f the file path to the image
 * @param int $w the width
 * @param int $h the height
 * @param int $q the image quality
 * @return string
 */
public static function thumbnail($f, $w = 80, $h = 80, $q = 80)
{
	$d=SP."uploads/thumbnails/$w-x-$h/";$n=basename($f).'.jpg';if(is_file($d.$n)OR!dir::usable($d)OR!is_file($f)OR!($i=self::open($f)))return;if(imagejpeg(self::resize($i,$w,$h),$d.$n,$q))return$d.$n;
}


/**
 * Open a resource handle to a (png/gif/jpeg) image file for processing.
 * 
 * @param string $f the file path to the image
 * @return resource
 */
public static function open($f)
{
	if(is_file($f)&&($e=pathinfo($f,PATHINFO_EXTENSION))&&($x='imagecreatefrom'.($e=='jpg'?'jpeg':$e))&&($i=$x($f))&&is_resource($i))return$i;
}


/**
 * Resize and crop the image to fix proportinally in the given dimensions.
 * 
 * @param resource $i the image resource handle
 * @param int $w the width
 * @param int $h the height
 * @return resource
 */
public static function resize($i,$w,$h)
{
	$x=imagesx($i);$y=imagesy($i);$s=min($x/$w,$y/$h);$n=imagecreatetruecolor($w,$h);self::alpha($n);imagecopyresampled($n,$i,0,0,0,($y/4-($h/4)),$w,$h,$x-($x-($s*$w)),$y-($y-($s*$h)));return$n;
}


/**
 * Preserve the alpha channel transparency in PNG images
 * 
 * @param resource $i the image resource handle
 */
public static function alpha($i)
{
	imagecolortransparent($i,imagecolorallocate($i,0,0,0));imagealphablending($i,false);imagesavealpha($i,true);
}


/**
 * Send the correct HTTP header to display the image
 * 
 * @param string $ext type of png, gif, or jpeg
 */
public static function header($ext)
{
	headers_sent()||header('Content-type: image/'.$ext);
}

}

// END