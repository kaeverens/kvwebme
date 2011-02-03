<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!is_admin()) {
	die('You do not have permission to create a forum');
}
$page = $_REQUEST['page'];
if (!is_numeric($page)) {
	exit('Invalid page id');
}
$name = $_REQUEST['name'];
dbQuery(
	'insert into forums '
	.'set name = "'.addslashes($name).'", page_id='.$page.', moderator_groups=1'
);
$data = array();
$id = dbLastInsertId();
if (!$id) {
	$data['status'] = 0;
	$data['message']= 'Could not create forum';
}
else {
	$data['status'] = 1;
	$groups = array();
	$dbGroups = dbAll('select id, name from groups');
	foreach($dbGroups as $group) {
		$groups[]['id'] = $group['id'];
		$groups[count($groups)-1]['name'] = $group['name'];
	}
	$data['groups'] = $groups;
	$data['name'] = $name;
	$data['id'] = $id;
}
echo json_encode($data);
