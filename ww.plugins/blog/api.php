<?php

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
	$numPerPage=(int)$_REQUEST['iDisplayLength'];
	$startAt=(int)$_REQUEST['iDisplayStart'];
	$userfilter=Core_isAdmin()
		?'':' and user_id='.$_SESSION['userdata']['id'];
	$totalRecords=dbOne(
		'select count(id) as ids from blog_entry where 1'.$userfilter,
		'ids'
	);
	$totalDisplayRecords=$totalRecords;
	$sql=
		'select id, title, cdate, pdate, udate, user_id, published, comments'
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
		$dates='<span class="__" lang-context="blog" title="created: '
			.$row['cdate'].'">c: '.preg_replace('/ .*/', '', $row['cdate'])
			.'</span>';
		if ($row['pdate']) {
			$dates.='<br/><span class="__" lang-context="blog" title="published: '
				.$row['pdate'].'">p: '.preg_replace('/ .*/', '', $row['pdate'])
				.'</span>';
		}
		if ($row['udate'] && $row['udate']!=$row['cdate']
			&& $row['udate']!=$row['pdate']
		) {
			$dates.='<br/><span class="__" lang-context="blog" title="updated: '
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
	$excerpt_image=@$_REQUEST['blog_excerpt_image'];
	$tags=@$_REQUEST['blog_tags'];
	$pdate=@$_REQUEST['blog_pdate'];
	$user_id=(int)@$_REQUEST['blog_user_id'];
	$id=(int)@$_REQUEST['blog_id'];
	$status=(int)@$_REQUEST['blog_status'];

	$sql='title="'.addslashes($title).'",'
		.'body="'.addslashes($body).'",'
		.'excerpt="'.addslashes($excerpt).'",'
		.'excerpt_image="'.addslashes($excerpt_image).'",'
		.'tags="'.addslashes($tags).'",'
		.'pdate="'.addslashes($pdate).'",'
		.'status="'.$status.'",'
		.'udate=now()';
	if ($id) {
		$sql='update blog_entry set '.$sql.' where id='.$id;
		dbQuery($sql);
		return array('ok'=>$id);
	}
	else {
		$sql='insert into blog_entry set '.$sql.',cdate=now(),user_id='
			.((int)$_SESSION['userdata']['id']);
		dbQuery($sql);
		return array('ok'=>dbLastInsertId());
	}
}

// }
// { Blog_postGet

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
