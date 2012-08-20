<?php
/**
	* User class
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

class User{
	static $instances=array();

	// { __construct

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

	// }
	// { addToGroup

	/**
		* add the user to a specified group
		*
		* @param string $group the group name
		*
		* @return null
		*/
	function addToGroup($group) {
		$gid=dbOne(
			'select id from groups where name="'.addslashes($group).'"',
			'id'
		);
		if (!$gid) {
			dbQuery('insert into groups set name="'.addslashes($group).'"');
			$gid=dbLastInsertId();
		}
		dbQuery(
			'insert into users_groups set groups_id='.$gid
			.',user_accounts_id='.$this->id
		);
	}

	// }
	// { get

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
			// { contact details
			$contact=json_decode($this->dbVals['contact']);
			if (is_array($contact)) {
				foreach ($contact as $k=>$v) {
					$this->vals[$k]=$v;
				}
			}
			// }
			// { address
			$address=$this->dbVals['address'];
			if ($address=='') {
				$address=array(array());
			}
			else {
				$address=json_decode($address, true);
			}
			foreach ($address as $ad) {
				$main=$ad;
				if (@$add['default']=='yes') {
					break;
				}
			}
			$address=@$main['street'].'|'.@$main['street2'].'|'.@$main['town']
				.'|'.@$main['county'].'|'.@$main['country'];
			$address=preg_replace('/\|+/', '<br/>', $address);
			$this->vals['address']=$address;
			// }
			$this->vals['location_lat']=(float)$this->dbVals['location_lat'];
			$this->vals['location_lng']=(float)$this->dbVals['location_lng'];
		}
		if (!isset($this->vals[$name])) {
			$this->vals[$name]='';
		}
		return @$this->vals[$name];
	}

	// }
	// { getAsScript

	/**
		* get user details for page usage
		*
		* @return string
		*/
	public static function getAsScript() {
		$tmp='userdata={isAdmin:'.(Core_isAdmin()?1:0)
			.',id:'.$_SESSION['userdata']['id']
			.(isset($_SESSION['wasAdmin'])?',wasAdmin:1':'')
			.',name:"'.addslashes($_SESSION['userdata']['name']).'"'
			.',lat:'.((float)@$_SESSION['userdata']['location_lat'])
			.',lng:'.((float)@$_SESSION['userdata']['location_lng']);
		if (isset($_SESSION['userdata']['discount'])) {
			$tmp.=',discount:'.(int)$_SESSION['userdata']['discount'];
		}
		if (isset($_SESSION['userdata']['address'])) {
			$tmp.=',address:1';
		}
		if (isset($_SESSION['userdata']['id']) && $_SESSION['userdata']['id']) {
			$tmp.=',groups:['
				.join(
					',',
					User::getInstance($_SESSION['userdata']['id'])->getGroups()
				)
				.']';
		}
		return $tmp.'};';
	}

	// }
	// { getGroupHighest

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

	// }
	// { getGroups

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

	// }
	// { getInstance

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

	// }
	// { isInGroup

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

	// }
	// { set

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

	// }
}
