<?php
class Poll{
	static $instances = array();
	function __construct($v, $r=false, $values=false, $enabled=true) {
		$v=(int)$v;
		if (!$v) {
			return;
		}
		$filter=$enabled?' and enabled ':'';
		if (!$r) {
			$r=dbRow("select * from poll where id=$v $filter limit 1");
		}
		if (!count($r)) {
			return false;
		}
		foreach ($r as $k=>$val) {
			$this->{$k}=$val;
		}
		$this->dbVals=$r;
		self::$instances[$this->id] =& $this;
	}

	/**
		* get an instance of a Poll by its ID
		*
		* @param int     $id      id of the Poll
		* @param array   $r       database row
		* @param array   $vals    who knows...
		* @param boolean $enabled enabled
		*
		* @return object
		*/
	static function getInstance($id=0, $r=false, $vals=false, $enabled=true) {
		if (!is_numeric($id)) {
			return false;
		}
		if (!array_key_exists($id, self::$instances)) {
			new Poll($id, $r, $vals, $enabled);
		}
		return self::$instances[$id];
	}
}
