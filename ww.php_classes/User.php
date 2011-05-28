<?php
class User{
	static $instances = array();
	function __construct($v,$r=false,$enabled=true){
		$v=(int)$v;
		if(!$v)return;
		$filter=$enabled?' and active':'';
		if(!$r)$r=dbRow("select * from user_accounts where id=$v $filter limit 1");
		if(!count($r) || !is_array($r))return false;
		foreach ($r as $k=>$val) $this->{$k}=$val;
		if(!isset($this->id))return false;
		$this->dbVals=$r;
		self::$instances[$this->id] =& $this;
	}
	static function getInstance($id=0, $r=false, $enabled=true){
		if (!is_numeric($id)) {
			return false;
		}
		if (!array_key_exists($id,self::$instances)) {
			new User($id, $r, $enabled);
		}
		if (!isset(self::$instances[$id])) {
			return false;
		}
		return self::$instances[$id];
	}
	function get($name) {
		if (!isset($this->vals)) {
			$this->vals=json_decode($this->dbVals['extras'], true);
			$this->vals['id']=(int)$this->dbVals['id'];
			$this->vals['email']=$this->dbVals['email'];
			$this->vals['name']=$this->dbVals['name'];
			$this->vals['phone']=$this->dbVals['phone'];
			$this->vals['address']=$this->dbVals['address'];
		}
		return @$this->vals[$name];
	}
	function getGroupHighest($name) {
		$groups=$this->getGroups();
		$highest=0;
		foreach ($groups as $gid) {
			$meta=dbOne('select meta from groups where id='.$gid, 'meta');
			if ($meta) {
				if (!$meta) {
					$meta='{}';
				}
				$meta=json_decode($meta, true);
				if (isset($meta[$name]) && $meta[$name]>$highest) {
					$highest=$meta[$name];
				}
			}
		}
		return (float)$highest;
	}
	function getGroups() {
		if (isset($this->groups)) {
			return $this->groups;
		}
		$byid=array();
		$gs
			=dbAll(
				'select groups_id from users_groups '
				.'where user_accounts_id='.$this->id
			);
		foreach($gs as $g){
			$byid[]=$g['groups_id'];
		}
		$this->groups=$byid;
		return $this->groups;
	}
	function isInGroup($group) {
		if (isset($this->groupsByName[$group])) {
			return $this->groupsByName[$group];
		}
		if (!isset($this->groupsByName)) {
			$this->groupsByName=array();
		}
		$gid=dbOne('select id from groups where name="'.addslashes($group).'"', 'id');
		if (!$gid) {
			$this->groupsByName[$group]=0;
			return false;
		}
		$this->groupsByName[$group]=dbOne('select groups_id from users_groups where groups_id='.$gid.' and user_accounts_id='.$this->id, 'groups_id');
		return $this->groupsByName[$group];
	}
	function set($name,$value){
		dbQuery(
			'update user_accounts set '.addslashes($name).'="'.addslashes($value)
			.'" where id='.$this->id
		);
		$this->{$name}=$value;
	}
}
