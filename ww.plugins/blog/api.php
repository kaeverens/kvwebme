<?php
/**
	* blog API
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

// { Blog_commentAdd

/**
	* add a comment to a blog entry
	*
	* @return array entry status
	*/
function Blog_commentAdd() {
	$ret=array();
	$bid=(int)$_REQUEST['blog_entry_id'];
	$pid=(int)$_REQUEST['page_id'];
	$page=Page::getInstance($pid);
	if (!$page->name) {
		$ret['error']='Invalid page id.';
		return $ret;
	}
	$entry=dbRow(
		'select * from blog_entry where id='.$bid.' and status>0 and allow_comments'
	);
	if (!$entry) {
		$ret['error']='Entry does not exist, is not yet public,'
			.' or does not allow comments.';
		return $ret;
	}
	$name=$_REQUEST['name'];
	$email=$_REQUEST['email'];
	$url=$_REQUEST['url'];
	$comment=$_REQUEST['comment'];
	$status=0;
	$uid=0;
	if (isset($_SESSION['userdata']['id'])) {
		$name=$_SESSION['userdata']['name'];
		$email=$_SESSION['userdata']['email'];
		$status=1;
		$uid=$_SESSION['userdata']['id'];
	}
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$ret['error']='Invalid email address';
		return $ret;
	}
	if ($url && !filter_var($url, FILTER_VALIDATE_URL)) {
		$ret['error']='Invalid URL';
		return $ret;
	}
	$verification='';
	if (!$status && $entry['allow_comments']==1) {
		$verification=md5(time().rand());
	}
	dbQuery(
		'insert into blog_comment set user_id='.$uid
		.', name="'.addslashes($name).'"'
		.', url="'.addslashes($url).'"'
		.', email="'.addslashes($email).'"'
		.', comment="'.addslashes($comment).'"'
		.', cdate=now(), blog_entry_id='.$bid
		.', status='.$status.', verification="'.$verification.'"'
	);
	if (!$status && $entry['allow_comments']==1) {
		Core_mail(
			$email,
			'['.$_SERVER['HTTP_HOST'].'] comment verification',
			'A comment was posted on our website claiming to be from your email'
			." address.\n\nIf it was not you, then please ignore this email.\n\n"
			."To verify the comment, please click the following link:\n"
			.'http://'.$_SERVER['HTP_HOST'].'/a/p=blog/f=commentVerify/md5='
			.$verification
		);
		$ret['message']='Please check your email for a verification code';
	}
	return $ret;
}

// }
// { Blog_getPostsList

/**
	* get a list of posts
	*
	* @return array
	*/

function Blog_getPostsList() {
	if (!isset($_SESSION['userdata'])) {
		return array('error'=>'cannot retrieve list of posts');
	}
	$numPerPage=isset($_REQUEST['iDisplayLength'])
		?(int)$_REQUEST['iDisplayLength']:25;
	$startAt=isset($_REQUEST['iDisplayStart'])?(int)$_REQUEST['iDisplayStart']:0;
	$userfilter=Core_isAdmin()
		?'':' and user_id='.$_SESSION['userdata']['id'];
	$totalRecords=dbOne(
		'select count(id) as ids from blog_entry where 1'.$userfilter,
		'ids'
	);
	$totalDisplayRecords=$totalRecords;
	$sql='select id, title, cdate, pdate, udate, user_id, status, comments'
		.' from blog_entry where 1'
		.$userfilter
		.' order by cdate desc limit '.$startAt.','.$numPerPage;
	$rows=dbAll($sql);
	$posts=array();
	foreach ($rows as $row) {
		$post=array(
			$row['id'],
			$row['title'],
			$row['comments']
		);
		// { dates
		$dates='<span title="'.__('Date created', 'core').': '
			.$row['cdate'].'">c: '.preg_replace('/ .*/', '', $row['cdate'])
			.'</span>';
		if ($row['pdate']) {
			$dates.='<br/><span title="'.__('Date published', 'core').': '
				.$row['pdate'].'">p: '.preg_replace('/ .*/', '', $row['pdate'])
				.'</span>';
		}
		if ($row['udate'] && $row['udate']!=$row['cdate']
			&& $row['udate']!=$row['pdate']
		) {
			$dates.='<br/><span title="'.__('Date updated', 'core').': '
				.$row['udate'].'">u: '.preg_replace('/ .*/', '', $row['udate'])
				.'</span>';
		}
		$post[]=$dates;
		// }
		$post[]=$row['user_id'];
		$post[]='';
		$posts[]=$post;
	}
	return array(
		'sEcho'=>intval(@$_REQUEST['sEcho']),
		'iTotalRecords'=>$totalRecords,
		'iTotalDisplayRecords'=>$totalDisplayRecords,
		'aaData'=>$posts
	);
}

// }
// { Blog_getUserName

/**
	* given an id of a user, it will return the user's name
	*
	* @return array usernames
	*/
function Blog_getUserName() {
	$id=(int)@$_REQUEST['id'];
	$name=dbOne('select name from user_accounts where id='.$id, 'name');
	if (!$name) {
		$name='unknown';
	}
	return array('name'=>$name);
}

// }
// { Blog_postDelete

/**
	* delete a post (must be an admin or the article's owner)
	*
	* @return status
	*/
function Blog_postDelete() {
	$id=(int)@$_REQUEST['blog_id'];

	$constraints=' where id='.$id;
	if (!Core_isAdmin()) {
		$constraints.=' and user_id='.((int)$_SESSION['userdata']['id']);
	}

	$sql='delete from blog_entry'.$constraints;
	dbQuery($sql);
	return true;
}

// }
// { Blog_postEdit

/**
	* edit a post (must be an admin or the article's owner)
	*
	* @return status
	*/
function Blog_postEdit() {
	$title=@$_REQUEST['blog_title'];
	$body=@$_REQUEST['blog_body'];
	$excerpt=@$_REQUEST['blog_excerpt'];
	$excerpt_image=@$_REQUEST['blog_excerpt-image'];
	$tags=@$_REQUEST['blog_tags'];
	$pdate=@$_REQUEST['blog_pdate'];
	$user_id=(int)@$_REQUEST['blog_user_id'];
	$id=(int)@$_REQUEST['blog_id'];
	$status=(int)@$_REQUEST['blog_status'];
	$allow_comments=(int)@$_REQUEST['blog_allow_comments'];
	$featured_post=isset($_REQUEST['blog_featured-post'])?1:0;

	// TODO: make sure only verified users are allowed edit or create an entry

	$sql='title="'.addslashes($title).'"'
		.', body="'.addslashes($body).'"'
		.', featured="'.$featured_post.'"'
		.', excerpt="'.addslashes($excerpt).'"'
		.', excerpt_image="'.addslashes($excerpt_image).'"'
		.', tags="'.addslashes($tags).'"'
		.', pdate="'.addslashes($pdate).'"'
		.', status="'.$status.'"'
		.', allow_comments="'.$allow_comments.'"'
		.', udate=now()';
	if ($id) {
		$sql='update blog_entry set '.$sql.' where id='.$id;
		dbQuery($sql);
	}
	else {
		$sql='insert into blog_entry set '.$sql.',cdate=now(),user_id='
			.((int)$_SESSION['userdata']['id']);
		dbQuery($sql);
		$id=dbLastInsertId();
	}
	dbQuery('delete from blog_tags where entry_id='.$id);
	$tags=explode('|', $tags);
	foreach ($tags as $tag) {
		dbQuery(
			'insert into blog_tags set entry_id='.$id.', tag="'.addslashes($tag).'"'
		);
	}
	Core_cacheClear('blog');
	return array('ok'=>$id);
}

// }
// { Blog_postGet

/**
	* get a post
	*
	* @return array the post
	*/
function Blog_postGet() {
	$id=(int)$_REQUEST['id'];
	if (Core_isAdmin()) {
		return dbRow('select * from blog_entry where id='.$id);
	}
	if (isset($_SESSION['userdata']['id'])) {
		return dbRow(
			'select * from blog_entry where id='.$id
			.' and userid='.$_SESSION['userdata']['id']
		);
	}
}

// }
