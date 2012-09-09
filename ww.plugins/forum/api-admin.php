<?php
/**
  * forum admin api
  *
  * PHP Version 5
  *
  * @category   None
  * @package    Webworks_Webme
  * @subpackage Forum
  * @author     Belinda Hamilton <bhamilton@webworks.ie>
  * @license    GPL Version 2
  * @link       www.kvweb.me
  **/


/**
	* add a forum
	*
	* @return array status
	*/
function Forum_adminForumAdd() {
	$page = $_REQUEST['page'];
	if (!is_numeric($page)) {
		Core_quit('Invalid page id');
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
		foreach ($dbGroups as $group) {
			$groups[]['id'] = $group['id'];
			$groups[count($groups)-1]['name'] = $group['name'];
		}
		$data['groups'] = $groups;
		$data['name'] = $name;
		$data['id'] = $id;
	}
	return $data;
}

/**
	* delete a post
	*
	* @return array status
	*/
function Forum_adminForumDelete() {
	$id = $_REQUEST['id'];
	if (!(is_numeric($id))) {
		Core_quit('Invalid id');
	}
	$data = array();
	$threads = dbAll('select id from forums_threads where forum_id = '.$id);
	$postsToDelete = array();
	foreach ($threads as $thread) {
		$thread_id = $thread['id'];
		$posts = dbAll('select id from forums_posts where thread_id = '.$thread_id);
		foreach ($posts as $post) {
			$postsToDelete[] = $post['id'];
		}
		dbQuery('delete from forums_posts where thread_id = '.$thread_id);
		dbQuery('delete from forums_threads where id = '.$thread_id);
	}
	dbQuery('delete from forums where id = '.$id);
	if (dbOne('select id from forums where id = '.$id, 'id')) {
		$data['status'] = 0;
		$data['message'] = 'Could not delete this forum';
	}
	else {
		$data['status'] = 1;
		$data['id'] = $id;
		$data['posts'] = $postsToDelete;
	}
	return $data;
}

/**
	* approve a post
	*
	* @return array status
	*/
function Forum_adminPostApprove() {
	$id = $_REQUEST['id'];
	$userID =$_SESSION['userdata']['id'];
	if (! ($userID==0 && Core_isAdmin())) { // not a superadmin
		$user = User::getInstance($userID);
		$usersGroups = $user->getGroups();
		$thread=dbOne(
			'select thread_id from forums_posts where id = '.$id, 
			'thread_id'
		);
		$forum=dbOne(
			'select forum_id from forums_threads where id = '.$thread, 
			'forum_id'
		);
		$moderatorGroups=dbOne(
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
			die('You do not have permission to approve posts for this forum');
		}
		if (!is_numeric($id)) {
			Core_quit('Invalid id '.$id);
		}
	}
	dbQuery('update forums_posts set moderated = 1 where id ='.$id);
	if (dbOne('select moderated from forums_posts where id = '.$id, 'moderated')) {
		return array(
			'id'=>$id,
			'action'=>'approved',
			'status'=>1
		);
	}
	return array('status'=>0);
}
/**
  * Delete a post
  *
	* @return array
	*/
function Forum_adminPostDelete() {
	$id = $_REQUEST['id'];
	$userID =$_SESSION['userdata']['id'];
	$user = User::getInstance($userID);
	if ($userID) {
		$usersGroups = $user->getGroups();
	}
	else {
		$usersGroups = array(1);
	}
	$thread=dbOne(
		'select thread_id from forums_posts where id = '.$id,
		'thread_id'
	);
	$forum=dbOne(
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
		Core_quit('Invalid id');
	}
	dbQuery('delete from forums_posts where id = '.$id);
	if (dbOne('select id from forums_posts where id = '.$id, 'id')) {
		return(array('status'=>0));
	}
	return array('status'=>1, 'action'=>'deleted', 'id'=>$id);
}
/**
  * Creates a new group, adds the current user to it and sets it as a moderator 
  * group for that forum
	*
	* @return array
	*/
function Forum_adminGroupNew() {
	$forum = $_REQUEST['forum'];
	if (!is_numeric($_REQUEST['forum'])) {
		Core_quit('Invalid forum id');
	}
	$name = $_REQUEST['name'];
	dbQuery('insert into groups set name = "'.addslashes($name).'"');
	$group = dbLastInsertId();
	if (!$group) {
		return array(
			'status'=>0,
			'message'=>'Error creating group'
		);
	}
	$user =$_SESSION['userdata']['id'];
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
	return array('name'=>$name,'forum'=>(int)$forum);
}
/**
  * Sets moderator groups for a forum
  *
	* @return array
  */
function Forum_adminGroupModeratorSet() {
	$group = $_REQUEST['group'];
	$forum = $_REQUEST['forum'];
	$action = $_REQUEST['action'];
	$response = array();
	if (!(is_numeric($group)&&is_numeric($forum))) {
		Core_quit('Invalid Parameters');
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
		foreach ($moderatorGroups as $k=>$val) {
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
	return $response;
}
