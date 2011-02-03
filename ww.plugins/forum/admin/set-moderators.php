<?php

/**
  * Sets moderator groups for a forum
  *
  * PHP Version 5
  *
  * @category   None
  * @package    Webworks_Webme
  * @subpackage Forum
  * @author     Belinda Hamilton <bhamilton@webworks.ie>
  * @licence    GPL Version 2
  * @link       www.webworks.ie
**/

require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!is_admin()) {
	die('You cannot change moderator groups for this forum');
}
$group = $_REQUEST['group'];
$forum = $_REQUEST['forum'];
$action = $_REQUEST['action'];
$response = array();
if (!(is_numeric($group)&&is_numeric($forum))) {
	exit('Invalid Parameters');
}
$sql = 'select moderator_groups from forums where id = '.$forum;
$moderatorGroups = array();
if (dbOne($sql, 'moderator_groups')) {
	$mods = dbOne($sql, 'moderator_groups');
	$moderatorGroups = explode(',', $mods);
}
if ($action=='true') { //add a group
	$moderatorGroups[] = $group;
}
else { //remove a group
	foreach($moderatorGroups as $k=>$val) {
		if ($val==$group) {
			unset($moderatorGroups[$k]);
		}
	}
	$autoApprove = $_REQUEST['autoApprove'];
	if (!count($moderatorGroups)&&($autoApprove=='true')) { 
		// Approve all posts for forum
		$sql = 'select id from forums_posts where thread_id in '
			.'(select id from forums_threads where forum_id = '.$forum.')'
			.'and moderated=0';
		$results = dbAll($sql);
		$response['posts'] = array();
		foreach ($results as $result) {
			dbQuery(
				'update forums_posts set moderated = 1 '
				.'where id = '.$result['id']
			);
			$response['posts'][] = $result['id'];
		}
	}
}
if (count($moderatorGroups)) {
	$moderatorGroups = implode(',', $moderatorGroups);
}
else {
	$moderatorGroups = null;
}
dbQuery(
	'update forums set moderator_groups = "'.addslashes($moderatorGroups).'"'.
	' where id = '.$forum
);
$response['status'] = 1;
echo json_encode($response);
