<?php

class UnitTest_Mock_PDO
{

	public function __construct()
	{
		
	}

	public function prepare($sql)
	{
		return new UnitTest_Mock_PDO_Statement($sql); 
	}
	
	//public function 
	public function lastInsertId()
	{
		return 5;
	}
}

class UnitTest_Mock_PDO_Statement
{
	public $sql;
	public $valid;

	public function __construct($sql)
	{
		$this->sql = $sql;
	}
	
	public function query($sql)
	{
		$this->sql = $sql;
		$this->valid = TRUE;
	}
	
	public function execute(array $params)
	{
		$chars = count_chars($this->sql, 0);
		if(count($params) AND count($params) === $chars['?'])
		{
			$this->valid = TRUE;
		}
	}
	
	public function fetch($type)
	{
		return $this->valid ? new stdClass() : NULL;
	}
	
	public function fetchColumn()
	{
		return $this->valid ? 'value' : NULL;
	}
	
	public function fetchAll($type)
	{
		return $this->valid ? ($type === PDO::FETCH_OBJ ? array(new stdClass()) : array('value')) : NULL;
	}
	
	public function rowCount()
	{
		return $this->valid ? 5 : NULL;
	}
	
}