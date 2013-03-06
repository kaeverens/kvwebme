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
echo '<h2>'.__('Users').'</h2>';
// { links at top of page
$url='./siteoptions.php?page=users&amp;';
echo '<a href="./siteoptions.php?page=users">'.__('List Users').'</a>'
	.' | <a href="'.$url.'tab=options">'
	.__('User Options').'</a>'
	.' | <a href="'.$url.'action=clear-groups">'
	.__('Remove unused groups').'</a>'
	.' | <a href="'.$url.'action=remove-unused-accounts">'
	.__('Remove users that never logged in').'</a>';
// }
if (isset($_REQUEST['action'])) {
	switch($_REQUEST['action']) {
		case 'clear-groups': // {
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
				echo '<em>'.__('Removed the following unused groups').': "'
					.join('", "', $removed).'".</em>';
			}
			else {
				echo '<em>'.__('No unused groups were found.').'</em>';
			}
		break; // }
		case 'remove-unused-accounts': // {
			$users=dbAll(
				'select id from user_accounts where last_login="0000-00-00 00:00:00"'
			);
			$usersCount=count($users);
			$ids=array();
			foreach ($users as $user) {
				$ids[]=$user['id'];
			}
			if ($usersCount) {
				dbQuery(
					'delete from user_accounts where last_login="0000-00-00 00:00:00"'
				);
				dbQuery(
					'delete from users_groups where user_accounts_id in ('
					.join(',', $ids).')'
				);
			}
			echo '<em>'.__('%1 users deleted.', array($usersCount)).'</em>';
		break; // }
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
