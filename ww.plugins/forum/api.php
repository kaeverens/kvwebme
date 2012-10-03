<?php
/**
	* forum api
	*
	* PHP Version 5
	*
	* @category   Whatever
	* @package    WebworksWebme
	* @subpackage Forum
	* @author     Kae Verens <kae@kvsites.ie>
	* @license    GPL Version 2
	* @link       www.kvweb.me
	*/

// { Forum_post

/**
	* submit a post to a forum
	*
	* @return status of the forum
	*/
function Forum_post() {
	if (!isset($_SESSION['userdata']) || !$_SESSION['userdata']['id']) {
		Core_quit();
	}
	$title=$_REQUEST['title'];
	$body=$_REQUEST['body'];
	$forum_id=(int)@$_REQUEST['forum_id'];
	$thread_id=(int)@$_REQUEST['thread_id'];
	$errs=array();
	if (!$body) {
		$errs[]='no post body supplied';
	}
	if (!$forum_id) {
		$errs[]='no forum selected';
	}
	else {
		$forum=dbRow('select * from forums where id='.$forum_id);
		if (!$forum || !count($forum)) {
			$errs[]='forum does not exist';
		}
		else {
			if ($thread_id) {
				$title='';
				$thread=dbRow(
					'select * from forums_threads where id='
					.$thread_id.' and forum_id='.$forum_id
				);
				if (!$thread || !count($thread)) {
					$errs[]='thread does not exist or doesn\'t belong to that forum';
				}
			}
			else {
				if (!$title) {
					$errs[]='no thread title supplied';
				}
			}
		}
	}
	if (count($errs)) {
		return array('errors'=>$errs);
	}
	if (!$thread_id) {
		$sql='insert into forums_threads set forum_id='.$forum_id.','
			.'name="'.addslashes($title).'",creator_id='.$_SESSION['userdata']['id']
			.',created_date=now(),num_posts=0,last_post_date=now(),last_post_by=0,'
			.'subscribers="'.$_SESSION['userdata']['id'].'"';
		dbQuery($sql);
		$thread_id=dbLastInsertId();
	}
	else { // add user to the subscribers list
		$subscribers=dbOne(
			'select subscribers from forums_threads where id='.$thread_id,
			'subscribers'
		);
		$subscribers=explode(',', $subscribers);
		if (!in_array($_SESSION['userdata']['id'], $subscribers)) {
			$subscribers[]=$_SESSION['userdata']['id'];
			dbQuery(
				'update forums_threads set subscribers="'.join(',', $subscribers)
				.'" where id='.$thread_id
			);
		}
	}
	// { insert the post into the thread
	$moderated=1-$forum['is_moderated'];
	dbQuery(
		'insert into forums_posts set thread_id='.$thread_id
		.',author_id='.$_SESSION['userdata']['id'].',created_date=now()'
		.',body="'.addslashes($body).'",moderated='.$moderated
	);
	$post_id=(int)dbLastInsertId();
	
	dbQuery(
		'update forums_threads set num_posts=num_posts+1,'
		.'last_post_date=now(),last_post_by='.$_SESSION['userdata']['id']
		.' where id='.$thread_id
	);
	// }
	// { alert subscribers that a new post is available
	$post_author=User::getInstance($_SESSION['userdata']['id']);
	$row=dbRow(
		'select subscribers,name from forums_threads where id='.$thread_id
	);
	$subscribers=explode(',', $row['subscribers']);
	$url=Page::getInstance($forum['page_id'])->getRelativeUrl()
		.'?forum-f='.$forum_id
		.'&forum-t='.$thread_id.'&'.$post_id.'#forum-c-'.$post_id;
	foreach ($subscribers as $subscriber) {
		if ($subscriber==$_SESSION['userdata']['id']) {
			continue;
		}
		$user=User::getInstance($subscriber);
		if (!$user) {
			continue;
		}
		Core_mail(
			$user->get('email'),
			'['.$_SERVER['HTTP_HOST'].'] '.$row['name'],
			"A new post has been added to this forum thread which you are subscribed"
			." to.<br/>\n<br/>\n"
			.'http://www.'.$_SERVER['HTTP_HOST'].$url."<br/>\n<br/>\n"
			.$post_author->get('name')." said:<hr/>".$body.'<hr/>',
			'no-reply@'.$_SERVER['HTTP_HOST']
		);
	}
	// }
	return array(
		'forum_id'=>$forum_id,
		'thread_id'=>$thread_id,
		'post_id'=>$post_id
	);
}

// }
// { Forum_delete

/**
  * delete a message from a forum
  *
	* @return array
	*/
function Forum_delete() {
	if (!isset($_SESSION['userdata']) || !$_SESSION['userdata']['id']) {
		Core_quit();
	}
	$post_id=(int)$_REQUEST['id'];
	$errs=array();
	if (!$post_id) {
		$errs[]='no post selected';
	}
	$post=dbRow(
		'select author_id,thread_id from forums_posts where id='.$post_id
	);
	if (!Core_isAdmin()
		&& $post['author_id'] != $_SESSION['userdata']['id']
	) {
		$errs[]='this is not your post, or post does not exist';
	}
	if (count($errs)) {
		return array('errors'=>$errs);
	}
	dbQuery('delete from forums_posts where id='.$post_id);
	$sql='select count(id) from forums_posts where thread_id='.$post['author_id'];
	if ((int)dbOne($sql, 'count(id)')<1) {
		dbQuery('delete from forums_threads where id='.$post['thread_id']);
	}
	dbQuery(
		'update forums_threads set num_posts='
		.'(select count(id) as ids from forums_posts '
		.'where thread_id=forums_threads.id)'
	);
	dbQuery('select from forums_threads where num_posts=0');
	return array(
		'ok'=>1
	);
}

// }
