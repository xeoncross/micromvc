<?php
/**
 * Example ORM Class
 *
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2011 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
namespace Example\Model;

class ORM extends \Core\ORM
{
	protected function insert(array $data)
	{
		$data['created'] = sql_date();
		parent::insert($data);
	}

	protected function update(array $data)
	{
		$data['modified'] = sql_date();
		parent::update($data);
	}
}
