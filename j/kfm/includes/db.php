<?php
class DB{
	var $db=0;
	var $dbtype='';
	function __construct($dsn=array()){
		switch($dsn['type']){
			case 'sqlitepdo':{
				require(KFM_BASE_PATH.'includes/db.sqlite.pdo.php');
				$this->db=new DB_SQLite_PDO($dsn);
				$db_defined=1;
				break;
			}
			default:{
				exit('error: unknown database type "'.$dsn['type'].'"');
			}
		}
	}
	function exec($query){
		$this->db->query($query);
	}
	function fetchAll($query){
		return $this->db->fetchAll($query);
	}
	function fetchRow($query){
		return $this->db->fetchRow($query);
	}
	function lastInsertId($name=''){
		return $this->db->lastInsertId($name);
	}
	function query($query){
		return $this->db->query($query);
	}
}
function db_fetch_all($query){
	if($GLOBALS['kfm_db_type']=='sqlitepdo'){
		return $GLOBALS['kfmdb']->fetchAll($query);
	}
	$q=$GLOBALS['kfmdb']->query($query);
	if(PEAR::isError($q))die('alert("'.$q->getMessage().'\n'.$query.'")');
	return $q->fetchAll();
}
function db_fetch_row($query){
	if($GLOBALS['kfm_db_type']=='sqlitepdo'){
		return $GLOBALS['kfmdb']->fetchRow($query);
	}
	$q=$GLOBALS['kfmdb']->query($query);
	if(PEAR::isError($q))die('alert("'.$q->getMessage().'\n'.$query.'")');
	return $q->fetchRow();
}
