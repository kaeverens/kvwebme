<?php
/**
  * Deletes a post
  *
  * PHP Version 5
  *
  * @category   None
  * @package    Webworks_Webme
  * @subpackage Forum
  * @author     Belinda Hamilton <bhamilton@webworks.ie>
  * @license    GPL Version 2
  * @link       www.webworks.ie
**/
require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/ww.php_classes/User.php';
$id = $_REQUEST['id'];
$userID = get_userid();
$user = User::getInstance($userID);
$usersGroups = $user->getGroups();
$thread 
    = dbOne(
		'select thread_id from forums_posts where id = '.$id,
		'thread_id'
	);  
$forum
	= dbOne(
		'select forum_id from forums_threads where id = '.$thread,
		'forum_id'
	); 
$moderatorGroups
	= dbOne(
		'select moderator_groups from forums where id = '.$forum,
		'moderator_groups'
	);  
$moderatorGroups = explode(',', $moderatorGroups);
$isModerator = false;
foreach ($usersGroups as $group) {
	if (in_array($group, $moderatorGroups)) {
		$isModerator = true;
		break;
	}   
}   
if (!$isModerator) {
	die('You do not have permission to delete posts for this forum');
}
if (!is_numeric($id)) {
	exit('Invalid id');
}
dbQuery('delete from forums_posts where id = '.$id);
if (dbOne('select id from forums_posts where id = '.$id, 'id')) {
	echo '{"status":0}';
}
else {
	echo '{"status":1, "action":"deleted", "id":'.$id.'}';
}
