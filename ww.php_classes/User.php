<?php
class User{
	static $instances=array();
	/**
		* instantiate a User object
		*
		* @param int     $id      the user id
		* @param array   $r       a pre-defined array to fill in the values
		* @param boolean $enabled whether to only instantiate users that are enabled
		*
		* @return null
		*/
	function __construct($id, $r=false, $enabled=true) {
		$id=(int)$id;
		if (!$id) {
			return;
		}
		$filter=$enabled?' and active':'';
		if (!$r) {
			$r=dbRow("select * from user_accounts where id=$id $filter limit 1");
		}
		if (!count($r) || !is_array($r)) {
			return false;
		}
		foreach ($r as $k=>$val) {
			$this->{$k}=$val;
		}
		if (!isset($this->id)) {
			return false;
		}
		$this->dbVals=$r;
		self::$instances[$this->id] =& $this;
	}
	/**
		* get a user instance by ID
		*
		* @param int     $id      the user id
		* @param array   $r       a pre-defined array to fill in the values
		* @param boolean $enabled whether to only instantiate users that are enabled
		*
		* @return object the User instance
		*/
	static function getInstance($id=0, $r=false, $enabled=true) {
		if (!is_numeric($id)) {
			return false;
		}
		if (!array_key_exists($id, self::$instances)) {
			new User($id, $r, $enabled);
		}
		if (!isset(self::$instances[$id])) {
			return false;
		}
		return self::$instances[$id];
	}
	/**
		* retrieve a value
		*
		* @param string $name the variable to retrieve
		*
		* @return mixed value
		*/
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
	/**
		* return the highest meta value of a specified name
		* for example, a number of different groups may have a "discount" value
		*
		* @param string $name name of the value to find the highest of
		*
		* @return float highest value found
		*/
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
	/**
		* get list of groups this user is in
		*
		* @return array groups
		*/
	function getGroups() {
		if (isset($this->groups)) {
			return $this->groups;
		}
		$byid=array();
		$gs=dbAll(
			'select groups_id from users_groups '
			.'where user_accounts_id='.$this->id
		);
		foreach ($gs as $g) {
			$byid[]=$g['groups_id'];
		}
		$this->groups=$byid;
		return $this->groups;
	}
	/**
		* is this user in a specified group
		*
		* @param string $group the group name
		*
		* @return boolean
		*/
	function isInGroup($group) {
		if (isset($this->groupsByName[$group])) {
			return $this->groupsByName[$group];
		}
		if (!isset($this->groupsByName)) {
			$this->groupsByName=array();
		}
		$gid=dbOne(
			'select id from groups where name="'.addslashes($group).'"',
			'id'
		);
		if (!$gid) {
			$this->groupsByName[$group]=0;
			return false;
		}
		$this->groupsByName[$group]=dbOne(
			'select groups_id from users_groups where groups_id='.$gid
			.' and user_accounts_id='.$this->id,
			'groups_id'
		);
		return $this->groupsByName[$group];
	}
	/**
		* set a variable, save it to the database
		*
		* @param string $name  the value name
		* @param mixed  $value what to set the variable to
		*
		* @return null
		*/
	function set($name, $value) {
		dbQuery(
			'update user_accounts set '.addslashes($name).'="'.addslashes($value)
			.'" where id='.$this->id
		);
		$this->{$name}=$value;
	}
}
