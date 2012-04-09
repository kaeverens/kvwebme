<?php
/**
	* User management
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

echo '<h2>Users</h2>';
echo '<a href="./siteoptions.php?page=users">List Users</a>'
	.' | <a href="./siteoptions.php?page=users&amp;tab=options">User Options</a>'
	.' | <a href="./siteoptions.php?page=users&amp;action=clear-groups">'
	.'Remove unused groups</a>';
if (isset($_REQUEST['action']) && $_REQUEST['action']=='clear-groups') {
	$removed=array();
	$rs=dbAll(
		'select id,name from (select groups.id,groups.name,groups_id from groups'
		.' left join users_groups on groups.id=groups_id) as derived'
		.' where groups_id is null'
	);
	foreach ($rs as $r) {
		if ($r['id']!='1') {
			$removed[]=$r['name'];
			dbRow('delete from groups where id='.$r['id']);
		}
	}
	if (count($removed)) {
		echo '<em>Removed the following unused groups: "'
			.join('", "', $removed).'".</em>';
	}
	else {
		echo '<em>No unused groups were found.</em>';
	}
}
$groups=array();
if (@$_REQUEST['tab']=='options') {
	require_once 'siteoptions/users-options.php';
}
else if (@$_REQUEST['id']) {
	require_once 'siteoptions/users-edit.php';
}
else {
	require_once 'siteoptions/users-list.php';
}
