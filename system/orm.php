<?php
/**
 * ORM (Object-relational mapping)
 *
 * Allows the application to work directly with data in the database by modeling
 * it as native PHP objects. In other words, no more SQL queries. This ORM class
 * uses Index-Only SQL to make the most of object cacheing. It is advised you 
 * use APC, Memcached, or another RAM cache along with this class.
 * 
 * When creating your models you must use the following public variables to
 * define relations among your objects.
 *
 * $t = table
 * $k = primary key
 * $f = foreign key
 * $o = orderby
 * $b = belongs to
 * $h = has one/many
 * $hmt = has many through
 * 
 * @package		MicroMVC
 * @author		David Pennington
 * @copyright	(c) 2010 MicroMVC Framework
 * @license		http://micromvc.com/license
 ********************************** 80 Columns *********************************
 */
class ORM
{

// object data, related, changed, loaded, saved
public $d=array(), $r=array(), $c=array(), $l, $s;

// db object, table, key, foreign key, belongs to, has one/many, has many through, order by, cachelife
public static $db, $t, $k='id', $f, $b=array(), $h=array(), $hmt=array(), $o=array(), $cache=0;

/**
 * Create a new database entity object
 *
 * @param int|mixed $id of the row or row object
 */
public function __construct($id=0)
{
	if(!$id)return;if(is_numeric($id))$this->d[static::$k]=$id;else{$this->d=(array)$id;$this->l=1;}$this->s=1;
}


/**
 * Get this object's primary key
 *
 * @return int
 */
public function k()
{
	return isset($this->d[static::$k])?$this->d[static::$k]:NULL;
}


/**
 * Return object data as array
 *
 * @return array
 */
public function to_array()
{
	if($this->load())return$this->d;
}


/**
 * Set an array of values on this object
 *
 * @param string $a
 * @return self
 */
public function set($a)
{
	foreach($a as$c=>$v)$this->__set($c,$v);return$this;
}


/**
 * Set a propery of this object
 *
 * @param string $k name
 * @param mixed $v value
 */
public function __set($k,$v)
{
	if(!array_key_exists($k,$this->d)OR$this->d[$k]!==$v){$this->d[$k]=$v;$this->c[$k]=$k;$this->s=0;}
}


/**
 * Retive a property or 1-to-1 object relation
 *
 * @param string $k the column or relation name
 * @return mixed
 */
public function __get($k)
{
	$this->load();return array_key_exists($k,$this->d)?$this->d[$k]:$this->r($k);
}


/**
 * @see isset()
 */
public function __isset($k)
{
	if($this->load())return(array_key_exists($k,$this->d)||isset($this->r[$k]));
}


/**
 * @see unset()
 */
public function __unset($k)
{
	$this->load();unset($this->d[$k],$this->c[$k],$this->r[$k]);
}


/**
 * Reload the current object from the database
 *
 * @return boolean
 */
public function reload()
{
	$k=$this->k();$this->d=$this->c=$this->r=array();$this->l=0;$this->d[static::$k]=$k;return$this->load();
}


/**
 * Clear the current object
 */
public function clear()
{
	$t=$this;$t->d=$t->r=$t->c=array();$t->l=$t->s=0;
}


/**
 * Attempt to load the object record from the database
 *
 * @return boolean
 */
public function load()
{
	$t=$this;if($t->l)return 1;$k=static::$k;if(empty($t->d[$k]))return 0;$id=$t->d[$k];if(!($r=static::cache_get($t::$t.$id)))if($r=self::select('row','*',$t,array($k=>$id)))static::cache_set($t::$t.$id,$r);if($r){$t->d=(array)$r;return$t->s=$t->l=1;}else$t->clear();
}


/**
 * Load a related 1-to-1 object
 *
 * @param string $a relation alias
 * @return object
 */
public function r($a)
{
	$m=isset(static::$b[$a])?static::$b[$a]:static::$h[$a];$t=$this;if(isset($t->r[$a]))return$t->r[$a];return$t->r[$a]=new$m(isset(static::$b[$a])?$t->d[$m::$f]:self::select('column',$m::$k,$m,array(static::$f=>$t->k())));
}


/**
 * Load a has_many relation set from another model using the filtering options of fetch()
 *
 * @param string $m alias name
 * @param mixed $a arguments to pass
 * @return array
 */
public function __call($m,$a)
{
	$f='fetch';if(substr($m,0,6)==='count_'){$f='count';$m=substr($m,6);}$a=$a+array(array(),0,0,array());$a[0][static::$f]=$this->k();if(isset(static::$h[$m])){$c=static::$h[$m];return$c::$f($a[0],$a[1],$a[2],$a[3]);}else return$this->hmt($m,$a);
}


/**
 * Load a has_many_through relation set from another model using the filtering options of fetch(). Called by __call()
 *
 * @param string $m relation alias
 * @param array $a arguments
 * @return array
 */
public function hmt($m,$a)
{
	$c=static::$hmt[$m];$t=key($c);$m=current($c);return self::objects($m::$f,$m,$t,array($this::$f=>$this->k())+$a[0],$a[1],$a[2],$a[3]);
}


/**
 * Load an array of objects from the database
 *
 * @param string $k column to load
 * @param object $c class to load into
 * @param object $m model to search
 * @param array $w where conditions
 * @param int $l limit
 * @param int $o offset
 * @param array $ord order by conditions
 * @return array
 */
public static function objects($k=0,$c=0,$m=0,$w=0,$l=0,$o=0,$ord=array())
{
	if($r=self::select('fetch',$k,$m,$w,$l,$o,$ord)){$c=$c?:get_called_class();foreach($r as$k=>$v)$r[$k]=new$c($v);}return$r;
}


/**
 * Load a SELECT query result set
 *
 * @param string $f function name (column/row/fetch)
 * @param string $c column(s) to fetch
 * @param object $m model to search
 * @param array $w where conditions
 * @param int $l limit
 * @param int $o offset
 * @param array $ord order by conditions
 * @return mixed
 */
public static function select($f,$c,$m=0,$w=array(),$l=0,$o=0,$ord=array())
{
	$m=$m?:get_called_class();$ord=$ord+static::$o;if($f!='fetch'){$l=$o=0;$ord=array();}$v=DB::select(($c?$c:'COUNT(*)'),$m::$t,$w,$l,$o,$ord);return static::$db->$f($v[0],$v[1],($c=='*'?NULL:0));
}


/**
 * Fetch an array of objects from this table
 *
 * @param array $where conditions
 * @param int $limit filter
 * @param int $offset filter
 * @param array $order_by conditions
 */
public static function fetch(array $where = NULL, $limit = 0, $offset = 0, array $order_by = array())
{
	return self::objects(static::$k,0,0,$where,$limit,$offset,$order_by);
}


/**
 * Count all database rows matching the conditions
 *
 * @param array $where conditions
 * @return int
 */
public static function count(array $where = NULL)
{
	return self::select('column',0,0,$where);
}

/**
 * Save the current object to the database
 */
public function save()
{
	$t=$this;if(!$t->c)return$t;$d=array();foreach($t->c as$c)$d[$c]=$t->d[$c];
	if(v($t->d[$t::$k]))$t->update($d);else$t->insert($d);$t->c=array();return$t;
}


/**
 * Insert the current object into the database table
 *
 * @param array $data to insert
 * @return int
 */
protected function insert(array$data)
{
	$t=$this;$id=static::$db->insert($t::$t,$data);$t->d[$t::$k]=$id;$t->l=$t->s=1;return$id;
}


/**
 * Update the current object in the database table
 *
 * @param array $d data
 * @return boolean
 */
protected function update(array$data)
{
	$t=$this;$t->s=1;$r=static::$db->update($t::$t,$data,array($t::$k=>$t->d[$t::$k]));static::cache_delete($t::$t.$t->d[$t::$k]);return$r;
}


/**
 * Delete the current object (and all related objects) from the database
 *
 * @param int $id to delete
 * @return int
 */
public function delete($id=0)
{
	$id=$id?:$this->k();$c=$this->delete_relations();$c+=self::$db->delete('DELETE FROM '.$this::$t.' WHERE '.static::$k.'=?',array($id));static::cache_delete($this::$t.$id);$this->clear();return$c;
}


/**
 * Delete all the related objects that belong to the current object
 *
 * @return int
 */
public function delete_relations()
{
	$c=0;foreach(static::$h as$a=>$m)foreach($this->$a()as$o)$c+=$o->delete();return$c;
}


/**
 * Store a value in the cache
 *
 * @param string $k name
 * @param mixed $v value
 */
public static function cache_set($k,$v){}


/**
 * Fetch a value from the cache
 *
 * @param string $k name
 * @return mixed
 */
public static function cache_get($k){}


/**
 * Delete a value from the cache
 *
 * @param string $k name
 * @return boolean
 */
public static function cache_delete($k){}


/**
 * Check that a value exists in the cache
 *
 * @param string $k name
 * @return boolean
 */
public static function cache_exists($k){}

}

// END