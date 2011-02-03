<?php
/**
  *
  * Creates a new group, adds the current user to it and sets it as a moderator 
  * group for that forum
  *
  * PHP Version 5
  *
  * @category   None
  * @package    WebworksWebme
  * @subpackage Forum
  * @author     Belinda Hamilton <bhamilton@webworks.ie>
  * @license    GPL Version 2
  * @link       www.webworks.ie
**/
require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!is_admin) {
	die('You do not have permission to create a group');
}
$forum = $_REQUEST['forum'];
if (!is_numeric($_REQUEST['forum'])) {
	exit('Invalid forum id');
}
$name = $_REQUEST['name'];
dbQuery('insert into groups set name = "'.addslashes($name).'"');
$group = dbLastInsertId();
if (!$group) {
	echo '{"status":0, "message": "Error creating group"}';
}
else {
	$user = get_userid();
	dbQuery(
		'insert into users_groups (user_accounts_id, groups_id)'
		.'values('.$user.', '.$group.')'
	);
	$groups 
		= dbOne(
			'select moderator_groups from forums where id = '.$forum,
			'moderator_groups'
		);
	$groups = explode(',', $groups);
	$groups[] = $group;
	$groups = implode(',', $groups);
	dbQuery(
		'update forums set moderator_groups = "'.addslashes($groups).'"'
		.' where id = '.$forum
	);
	echo '{"name":"'.$name.'","forum":'.(int)$forum.'}';
}
