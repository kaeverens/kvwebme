<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';

if (!is_admin()) {
	die('access denied');
}

$users=explode(',', $_REQUEST['users']);
$usersDB=dbAll('select id,name,email from user_accounts order by name,email');
foreach ($usersDB as $user) {
	echo '<li><input type="checkbox" name="user_ids['.$user['id'].']"';
	if (in_array($user['id'], $users)) {
		echo ' checked="checked"';
	}
	echo '/>'.htmlspecialchars($user['name']).' ('
		.htmlspecialchars($user['email'])
		.')</li>';
}
