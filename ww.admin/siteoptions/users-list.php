<?php
/**
	* User management - list
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

echo '<h3>List Users</h3>';
$groups=array();
// { list all users
$users=dbAll(
	'select active,id,name,email,last_login,last_view from user_accounts '
	.'order by last_view desc,last_login desc,email'
);
echo '<table style="min-width:50%"><tr><th>User</th><th>Groups</th><th>Last'
	.' Login</th><th>Last View</th><th>Actions</th></tr>';
foreach ($users as $user) {
	$name=$user['name']?$user['name']:$user['email'];
	echo '<tr'.($user['active']?'':' class="inactive"').'>'
		.'<th><a href="siteoptions.php?page=users&amp;id='.$user['id'].'">'
		.htmlspecialchars($name).'</a></th>';
	// { groups
	echo '<td>';
	$grs=dbAll("select * from users_groups where user_accounts_id=$user[id]");
	$garr=array();
	foreach ($grs as $gr) {
		if (!isset($groups[$gr['groups_id']])) {
			$groups[$gr['groups_id']]=dbOne(
				"select name from groups where id=$gr[groups_id] limit 1",
				'name'
			);
		}
		$garr[]=$groups[$gr['groups_id']];
	}
	echo join(', ', $garr);
	echo '</td>';
	// }
	// { last login
	if ($user['last_login']=='0000-00-00 00:00:00') {
		echo '<td>never</td>';
	}
	else {
		echo '<td>'.Core_dateM2H($user['last_login']).'</td>';
	}
	// }
	// { last view
	if ($user['last_view']=='0000-00-00 00:00:00') {
		echo '<td>never</td>';
	}
	else {
		echo '<td>'.Core_dateM2H($user['last_view']).'</td>';
	}
	// }
	echo '<td><a href="siteoptions.php?page=users&amp;id='.$user['id'].'">edi'
		.'t</a> <a href="siteoptions.php?page=users&amp;id='.$user['id'].'&amp;'
		
			.'action=delete" onclick="return confirm(\'are you sure you want to del'
		.'ete this user?\')">[x]</a></td></tr>';
}
echo '<tr><td colspan="2"></td><td><a href="siteoptions.php?page=users&amp;'
	.'id=-1">Create User</a></td></tr>';
echo '</table><br style="clear:both"/>';
// }
