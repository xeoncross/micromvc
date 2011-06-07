<?php
/**
 * Example ORM Class adds timestamps to all models on Create/Update.
 */
class Example_Model_ORM extends ORM
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
