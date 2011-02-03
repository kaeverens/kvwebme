<?php
/**
  * post messages to a forum
  *
  * PHP Version 5
  *
  * @category   Whatever
  * @package    WebworksWebme
  * @subpackage Forum
  * @author     Kae Verens <kae@webworks.ie>
  * @license    GPL Version 2
  * @link       www.webworks.ie
 */

require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!isset($_SESSION['userdata']) || !$_SESSION['userdata']['id']) {
	exit;
}

$title=$_REQUEST['title'];
$body=$_REQUEST['body'];
$forum_id=(int)$_REQUEST['forum_id'];
$thread_id=(int)$_REQUEST['thread_id'];

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
	echo json_encode(
		array(
			'errors'=>$errs
		)
	);
	exit;
}

if (!$thread_id) {
	dbQuery(
		'insert into forums_threads values(0,'
		.$forum_id.',0,"'.addslashes($title).'",'
		.$_SESSION['userdata']['id'].',now(),0,now(),0,'
		.$_SESSION['userdata']['id'].')'
	);
	$thread_id=dbLastInsertId();
}
else { // add user to the subscribers list
	$subscribers=dbOne(
		'select subscribers from forums_threads where id='.$thread_id
		,'subscribers'
	);
	$subscribers=explode(',',$subscribers);
	if (!in_array($_SESSION['userdata']['id'],$subscribers)) {
		$subscribers[]=$_SESSION['userdata']['id'];
		dbQuery(
			'update forums_threads set subscribers="'.join(',',$subscribers).'" where id='.$thread_id
		);
	}
}

// { insert the post into the thread
dbQuery(
	'insert into forums_posts values(0,'.$thread_id.','
	.$_SESSION['userdata']['id'].',now(),"'
	.addslashes($body).'", 0)'
);
$post_id=dbLastInsertId();

dbQuery(
	'update forums_threads set num_posts=num_posts+1,'
	.'last_post_date=now(),last_post_by='.$_SESSION['userdata']['id']
	.' where id='.$thread_id
);
// }
// { alert subscribers that a new post is available
$post_author=User::getInstance($_SESSION['userdata']['id']);
$post_author_name=$post_author->dbVals['name'];
$row=dbRow(
	'select subscribers,name from forums_threads where id='.$thread_id
);
$subscribers=explode(',',$row['subscribers']);
$url=Page::getInstance($forum['page_id'])->getRelativeUrl().'?forum-f='.$forum_id
	.'&forum-t='.$thread_id.'&'.$post_id.'#forum-c-'.$post_id;
foreach ($subscribers as $subscriber) {
	$user=User::getInstance($subscriber);
	mail(
		$user->dbVals['email'],
		'['.$_SERVER['HTTP_HOST'].'] '.$row['name'],
		"A new post has been added to this forum thread which you are subscribed to.\n\n"
		.'http://www.'.$_SERVER['HTTP_HOST'].$url."\n\n"
		.$post_author_name." said:\n".str_repeat('=',80)."\n".$body."\n".str_repeat('=',80),
		'From: no-reply@'.$_SERVER['HTTP_HOST']."\nReply-to: no-reply@".$_SERVER['HTTP_HOST']
	);
}
// }

echo json_encode(
	array(
		'forum_id'=>$forum_id,
		'thread_id'=>$thread_id,
		'post_id'=>$post_id
	)
);
