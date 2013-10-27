<?php

class AdminVars{
	static function get($name) {
		return self::getByAdminId($name, $_SESSION['userdata']['id']);
	}
	static function getByAdminId($name, $id) {
		return dbOne(
			'select varvalue from admin_vars'
			.' where admin_id='.(int)$id
			.' and varname="'.addslashes($name).'"',
			'varvalue', 'admin_vars'
		);
	}
	static function getAll($name=false) {
		$name=$name?' varname="'.addslashes($name).'"':'';
		return dbAll(
			'select * from admin_vars'
			.' where admin_id='.$_SESSION['userdata']['id'].$name,
			false, 'admin_vars'
		);
	}
	static function getAllStartsWith($str) {
		$arr=array();
		$rs=self::getAll();
		foreach ($rs as $r) {
			if (strpos($r['varname'], $str)===0) {
				$arr[$r['varname']]=$r;
			}
		}
		return $arr;
	}
	static function set($name, $val) {
		self::setByAdminId($name, $val, $_SESSION['userdata']['id']);
	}
	static function setByAdminId($name, $val, $adminId) {
		dbQuery(
			'delete from admin_vars where admin_id='.(int)$adminId
			.' and varname="'.addslashes($name).'"'
		);
		dbQuery(
			'insert into admin_vars set admin_id='.(int)$adminId
			.',varname="'.addslashes($name).'",varvalue="'.addslashes($val).'"'
		);
		Core_cacheClear('admin_vars');
	}
	static function delete($name) {
		self::deleteByAdminId($name, $_SESSION['userdata']['id']);
	}
	static function deleteByAdminId($name, $id=false) {
		$adminId='';
		if ($id!==false) {
			if ($id<0) {
				$adminId=' and admin_id>0';
			}
			else {
				$adminId=' and admin_id='.$id;
			}
		}
		dbQuery(
			'delete from admin_vars where varname="'.addslashes($name).'"'.$adminId
		);
		Core_cacheClear('admin_vars');
	}
}
