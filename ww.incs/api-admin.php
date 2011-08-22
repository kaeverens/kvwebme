<?php
/**
	* API for common admin WebME functions
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

function Core_adminDirectoriesGet() {
	function get_subdirs($base, $dir) {
		$arr=array();
		$D=new DirectoryIterator($base.$dir);
		$ds=array();
		foreach ($D as $dname) {
			if ($dname->isDot() || !$dname->isDir()) {
				continue;
			}
			$ds[]=$dname->getFilename();
		}
		asort($ds);
		foreach ($ds as $d) {
			$arr[$dir.'/'.$d]=$dir.'/'.$d;
			$arr=array_merge($arr, get_subdirs($base, $dir.'/'.$d));
		}
		return $arr;
	}
	$arr=array_merge(array('/'=>'/'), get_subdirs(USERBASE.'f', ''));
	return $arr;
}
function Core_adminPageParentsList() {
	$id=isset($_REQUEST['other_GET_params'])?(int)$_REQUEST['other_GET_params']:-1;
	function selectkiddies($i=0, $n=1, $id=0) {
		$arr=array();
		$q=dbAll(
			'select name,id,alias from pages where parent="'.$i.'" and id!="'.$id
			.'" order by ord,name'
		);
		if (count($q)<1) {
			return $arr;
		}
		foreach ($q as $r) {
			if ($r['id']!='') {
				$arr[$r['id']]=str_repeat('» ', $n).$r['name'];
				$arr=array_merge($arr, selectkiddies($r['id'], $n+1, $id));
			}
		}
		return $arr;
	}
	return array_merge(
		array('0'=>' -- none -- '),
		selectkiddies(0, 0, $id)
	);
}
function Core_adminPageTypesList() {
	$arr=array();
	global $pagetypes,$PLUGINS;
	foreach ($pagetypes as $a) {
		$arr[$a[0]]=$a[1];
	}
	foreach ($PLUGINS as $n=>$p) {
		if (isset($p['admin']['page_type'])) {
			if (is_array($p['admin']['page_type'])) {
				foreach ($p['admin']['page_type'] as $name=>$type) {
					$arr[$n.'|'.$name]=$name;
				}
			}
			else {
				$arr[$n.'|'.$n]=$n;
			}
		}
	}
	return $arr;
}
/**
  * create a copy of a page
  *
  * @return array status of the copy
  */
function Core_adminPageCopy() {
	$id=(int)$_REQUEST['id'];
	if (!$id) {
		return array('error'=>'no ID provided');
	}
	$p=dbRow('select * from pages where id='.$id);
	$name=$p['name'];
	$parts=array();
	foreach ($p as $k=>$v) {
		if ($k=='id') {
			continue;
		}
		$parts[]=$k.'="'.addslashes($v).'"';
	}
	dbQuery('insert into pages set '.join(',', $parts));
	$id=dbLastInsertId();
	dbQuery('update pages set name="'.addslashes($name).'_'.$id.'" where id='.$id);
	Core_cacheClear();
	return array('name'=>$name.'_'.$id, 'id'=>$id, 'pid'=>$p['parent']);
}

/**
  * delete a page
  *
  * @return array status of the deletion
  */
function Core_adminPageDelete() {
	$id=(int)$_REQUEST['id'];
	if (!$id) {
		return array('error'=>'no ID provided');
	}
	$r=dbRow("SELECT COUNT(id) AS pagecount FROM pages");
	if ($r['pagecount']<2) {
		return array('error'=>'there must always be at least one page.');
	}
	$q=dbQuery('select parent from pages where id="'.$id.'"');
	if ($q->rowCount()) {
		$r=dbRow('select parent from pages where id="'.$id.'"');
		dbQuery('delete from page_vars where page_id="'.$id.'"');
		dbQuery('delete from pages where id="'.$id.'"');
		dbQuery(
			'update pages set parent="'.$r['parent'].'" where parent="'.$id.'"'
		);
		Core_cacheClear();
		dbQuery('update page_summaries set rss=""');
		return array('ok'=>1);
	}
	return array('error'=>'page does not exist');
}

/**
  * move a page
  *
  * @return array status of the move
  */
function Core_adminPageMove() {
	$id=(int)$_REQUEST['id'];
	$to=(int)$_REQUEST['parent_id'];
	$order=$_REQUEST['order'];
	dbQuery("update pages set parent=$to where id=$id");
	for ($i=0;$i<count($order);++$i) {
		$pid=(int)$order[$i];
		dbQuery("update pages set ord=$i where id=$pid");
	}
	Core_cacheClear();
	dbQuery('update page_summaries set rss=""');
	return array('ok'=>1);
}
