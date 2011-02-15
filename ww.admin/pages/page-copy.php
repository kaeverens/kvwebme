<?php
$id=(int)$_REQUEST['id'];
if (!$id) {
	exit;
}
require '../../ww.incs/basics.php';
if (!is_admin()) {
	exit;
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
cache_clear('menus');
cache_clear('pages');
echo '{"name":"'.addslashes($name.'_'.$id).'","id":'.$id.',"pid":'
	.$p['parent'].'}';
