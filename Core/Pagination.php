<?php
/**
 * Pagination
 *
 * Provides HTML Pagination links for large datasets
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2011 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
namespace Core;

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
		$this->per_page = $per_page;
		$this->total = ceil($total / $per_page);
		$this->uri = $uri;
		$current = int($current, 1);

		// Current page cannot exceed the total (should we do this check..?)
		$this->current = $current > $this->total ? $this->total : $current;
	}


	/**
	 * Return an HTML pagination string
	 *
	 * @return string
	 */
	public function __toString()
	{
		return html::tag('div', $this->previous() . $this->first() . $this->links() . $this->last() . $this->next(), $this->attributes);
	}


	/**
	 * Create a "previous page" link if needed
	 *
	 * @return string
	 */
	public function previous()
	{
		if($this->current > 1)
		{
			return html::link(str_replace($this->key, $this->current-1, $this->uri), lang('pagination_previous'));
		}
	}


	/**
	 * Create a "first page" link if needed
	 *
	 * @return string
	 */
	public function first()
	{
		if($this->current > $this->links + 1)
		{
			return html::link(str_replace($this->key, 1, $this->uri), lang('pagination_first'));
		}
	}


	/**
	 * Create a "last page" link if needed
	 *
	 * @return string
	 */
	public function last()
	{
		if($this->current + $this->links  < $this->total)
		{
			return html::link(str_replace($this->key, $this->total, $this->uri), lang('pagination_last'));
		}
	}


	/**
	 * Create a "next page" link if needed
	 *
	 * @return string
	 */
	public function next()
	{
		if($this->current < $this->total)
		{
			return html::link(str_replace($this->key, $this->current+1, $this->uri), lang('pagination_next'));
		}
	}


	/**
	 * Create page links for the given object
	 *
	 * @return string
	 */
	public function links()
	{
		// Start and end must be valid integers
		$start = (($this->current - $this->links) > 0) ? $this->current - $this->links : 1;
		$end = (($this->current + $this->links) < $this->total) ? $this->current + $this->links : $this->total;

		$html = '';
		for($i = $start; $i <= $end; ++$i)
		{
			if($this->current == $i)
			{
				$html .= html::tag('a', $i, array('class' => 'current'));
			}
			else
			{
				$html .= html::link(str_replace($this->key, $i, $this->uri), $i);
			}
		}
		return $html;
	}

}

// END
