<?php
class Polls{
	static $instancesByFilter = array();
	static $instancesBySearch = array();
	static $instancesOrderedByCategory= array();
	static $instancesOrderedByPollValue=array();
	static $instances = array();
	function __construct(){
	}
	static function getAll($enabled=true){
		if(count(self::$instances))return self::$instances;
		$filter=$enabled?' WHERE enabled ':'';
		$rs=dbAll("select * from poll $filter order by name");
		foreach($rs as $r)self::$instances[]=Poll::getInstance($r['id'],$r);
		return self::$instances;
	}
	static function getByFilter($filter=''){
		if(array_key_exists($filter,self::$instancesByFilter))return self::$instancesByFilter[$filter];
		$rs=dbAll("select * from poll $filter");
		self::$instancesByFilter[$filter]=array();
		foreach($rs as $r)self::$instancesByFilter[$filter][]=Poll::getInstance($r['id'],$r);
		return self::$instancesByFilter[$filter];
	}
	static function getByIds($ids=array()){
		$ids=addslashes(join(',',$ids));
		if(array_key_exists($ids,self::$instancesByIds))return self::$instancesByIds[$ids];
		self::$instancesByFilter[$ids]=array();
		$rs=dbAll("select * from poll where id in ($ids)");
		foreach($rs as $r)self::$instancesByIds[$ids][]=Poll::getInstance($r['id'],$r);
		return self::$instancesByIds[$ids];
	}
}
