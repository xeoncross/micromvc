<?php
/**
 * Pagination
 *
 * Provides HTML Pagination links for large datasets
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
class Pagination
{

public $total = NULL;
public $current = 1;
public $uri = NULL;
public $per_page = NULL;
public $links = 2;
public $key = '[[page]]';
public $attributes = array('class' => 'pagination', 'id' => 'pagination');

/**
 * Creates pagination links for the total number of pages
 *
 * @param int $total number of items
 * @param string $uri to place in the links (must include [[page]] )
 * @param int $current page
 * @param int $per_page the number to show per-page (default 10)
 * @return string
 */
public function __construct($total, $uri, $current, $per_page = 10)
{
	$this->per_page=$per_page;$this->total=$t=ceil($total/$per_page);$c=int($current,1);$this->current=$c>$t?$t:$c;$this->uri=$uri;
}


/**
 * Return an HTML pagination string
 * 
 * @return string
 */
public function __toString()
{
	return html::tag('div',$this->previous().$this->first().$this->links().$this->last().$this->next(),$this->attributes);
}


/**
 * Create a "previous page" link if needed
 * 
 * @return string
 */
public function previous()
{
	if($this->current>1)return html::link(str_replace($this->key,$this->current-1,$this->uri),lang('pagination_previous'));
}


/**
 * Create a "first page" link if needed
 * 
 * @return string
 */
public function first()
{
	if($this->current>$this->links+1)return html::link(str_replace($this->key,1,$this->uri),lang('pagination_first'));
}


/**
 * Create a "last page" link if needed
 * 
 * @return string
 */
public function last()
{
	if($this->current+$this->links<$this->total)return html::link(str_replace($this->key,$this->total,$this->uri),lang('pagination_last'));
}


/**
 * Create a "next page" link if needed
 * 
 * @return string
 */
public function next()
{
	if($this->current<$this->total)return html::link(str_replace($this->key,$this->current+1,$this->uri),lang('pagination_next'));
}


/**
 * Create page links for the given object
 * 
 * @return string
 */
public function links()
{
	$c=$this->current;$l=$this->links;$u=$this->uri;$t=$this->total;$s=(($c-$l)>0)?$c-$l:1;$e=(($c+$l)<$t)?$c+$l:$t;$h='';for($i=$s;$i<=$e;++$i){if($c==$i)$h.=html::tag('a',$i,array('class'=>'current'));else$h.=html::link(str_replace($this->key,$i,$u),$i);}return$h;
}

}

// END