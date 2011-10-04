<?php
$protected_files=dbAll('select id,directory from protected_files order by directory');

$arr=array(
	'New Protected File'=>'/ww.admin/plugin.php?_plugin=protected-files&amp;_page=index'
);
foreach($protected_files as $p){
	$arr[$p['directory']]='/ww.admin/plugin.php?_plugin=protected-files&amp;_page=index&amp;id='.$p['id'];
}
echo Core_adminSideMenu($arr,isset($_REQUEST['id'])?$_url.'&amp;id='.$_REQUEST['id']:$_url);
