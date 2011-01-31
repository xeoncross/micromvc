<?php
/**
 * Cipher
 *
 * Provides encryption/decription functionality including URL shortening. By 
 * default the encrypt/decrypt functions use AES to encrypt a string using 
 * Cipher-block chaining (CBC) and then sign it using HMAC-SHA256 to prevent 
 * initialization vector (IV) man-in-the-middle attacks against the first block.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
class Cipher
{

// Base 65 character set for ID/KEY conversion
public static $base = 'abcdefghijklmnopqrstuvwxzyABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_~'; 

/**
 * Encrypt a string
 * 
 * @param string $text to encrypt
 * @param string $key a cryptographically random string
 * @param int $algo the encryption algorithm
 * @param int $mode the block cipher mode
 * @return string
 */
public static function encrypt($text, $key, $algo = MCRYPT_RIJNDAEL_256, $mode = MCRYPT_MODE_CBC)
{
	$text=mcrypt_encrypt($algo,hash('sha256',$key,TRUE),$text,$mode,$iv=mcrypt_create_iv(mcrypt_get_iv_size($algo,$mode),MCRYPT_RAND)).$iv;return hash('sha256',$key.$text).$text;
}


/**
 * Decrypt an encrypted string
 * 
 * @param string $text to encrypt
 * @param string $key a cryptographically random string
 * @param int $algo the encryption algorithm
 * @param int $mode the block cipher mode
 * @return string
 */
public static function decrypt($text, $key, $algo = MCRYPT_RIJNDAEL_256, $mode = MCRYPT_MODE_CBC)
{
	$h=substr($text,0,64);$t=substr($text,64);if(hash('sha256',$key.$t)!=$h)return;$iv=substr($t,-mcrypt_get_iv_size($algo,$mode));return rtrim(mcrypt_decrypt($algo,hash('sha256',$key,TRUE),substr($t,0,-strlen($iv)),$mode,$iv),"\x0"); 
}


/**
 * Convert a numeric ID to base 65 encoded key (URL shortening)
 * 
 * @param integer $id
 * @return string
 */
public static function key_from_id($id)
{
	$k='';while($id>64){$k=self::$base[fmod($id,65)].$k;$id=floor($id/65);}return self::$base{$id}.$k;
}


/**
 * Convert a base 65 encoded key to a numeric ID (Reverse URL shortening)
 * 
 * @param string $key
 * @return integer
 */
public static function id_from_key($key)
{
	$id=0;$key=str_split($key);$c=count($key);foreach($key as$k=>$v)$id+=pow(65,($c-$k-1))*strpos(self::$base,$v);return$id;
}

}

// END