<?php
/**
 * XML
 *
 * Converts any variable type (arrays, objects, strings) to a SimpleXML object.
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
class XML
{

/**
 * Convert any given variable into a SimpleXML object
 *
 * @param mixed $o variable object to convert
 * @param string $r root element name
 * @param object $x xml object
 * @param string $u unknown element name for numeric keys
 * @param string $d XML doctype
 */
public static function from($o, $r = 'data', $x = NULL, $u = 'element', $d = "<?xml version='1.0' encoding='utf-8'?>")
{
	is_null($x)&&$x=simplexml_load_string("$d<$r/>");foreach((array)$o as$k=>$v){is_numeric($k)&&$k=$u;if(is_scalar($v))$x->addChild($k,h($v));else{$v=(array)$v;$n=array_diff_key($v,array_keys(array_keys($v)))?$x->addChild($k):$x;to_xml($v,$k,$n);}}return$x;
}

}

// END