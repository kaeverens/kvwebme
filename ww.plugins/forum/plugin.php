<?php
/**
  * Forum plugin definition file
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

// { plugin config
$plugin=array(
	'name' => 'Forum',
	'admin' => array(
		'page_type' => 'Forum_adminPageForm'
	),
	'description' => 'Add a forum to let your readers talk to each other',
	'frontend' => array(
		'page_type' => 'Forum_frontend'
	),
	'triggers'=>array(
		'building-rss-links'  =>'Forum_getRssLink',
		'privacy_user_profile' => 'forum_user_profile',
	),
	'rss-handler' => 'Forum_rssHandler',
	'version' => 6
);
// }

// { Forum_adminPageForm

/**
  * display the forum-creation tool
  *
  * @param array $page the page's db row
	* @param array $vars any meta data the page has
  *
  * @return string HTML of the forum creation tool
  */
function Forum_adminPageForm($page, $vars) {
	require dirname(__FILE__).'/admin/form.php';
	return $c;
}

// }
// { Forum_frontend

/**
  * display the page's forum
  *
  * @param object $PAGEDATA the page object
  *
  * @return string the forum's HTML
  */
function Forum_frontend($PAGEDATA) {
	require dirname(__FILE__).'/frontend/forum.php';
	return Forum_show($PAGEDATA);
}

// }
// { Forum_rssHandler

/**
  * get a list of posts for an RSS feed
  *
  * @param Object $PAGEDATA The page
  *
  * @return array array of articles
**/

function Forum_rssHandler($PAGEDATA) {
	$items=array();
	$posts=dbAll(
		'select id,thread_id,author_id,created_date,body from forums_posts'
		.' where moderated order by created_date desc limit 10'
	);
	$threads=array();
	$authors=array();
	foreach ($posts as $post) {
		if (!isset($authors[$post['author_id']])) {
			$authors[$post['author_id']]=dbRow(
				'select name from user_accounts where id='.$post['author_id']
			);
		}
		if (!isset($threads[$post['thread_id']])) {
			$threads[$post['thread_id']]=dbRow(
				'select forum_id,name from forums_threads where id='.$post['thread_id']
			);
		}
		$items[]=array(
			'title'=>'post by '.$authors[$post['author_id']]['name']
				.' in "'.$threads[$post['thread_id']]['name'].'"',
			'description'=>$post['body'],
			'link'=>'http://'.$_SERVER['HTTP_HOST'].$PAGEDATA->getRelativeURL()
				.'?forum-f='.$threads[$post['thread_id']]['forum_id']
				.'&amp;forum-t='.$post['thread_id']
				.'#forum-c-'.$post['id'],
			'guid'=>'post-'.$post['id'],
			'pubDate'=>Core_dateM2H($post['created_date'], 'rfc822')
		);
	}
	return array(
		'title'       => 'Posts for '.$PAGEDATA->getRelativeURL(),
		'link'        => 'http://'.$_SERVER['HTTP_HOST'].$PAGEDATA->getRelativeURL(),
		'description' => 'Posts for '.$PAGEDATA->getRelativeURL(),
		'generator'   => 'WebME CMS',
		'items'       => $items
	);
}

// }
// { Forum_getRssLink

/**
  * get RSS link if available
  *
  * @param Object $PAGEDATA The page
  *
  * @return string URL of the RSS feed
**/

function Forum_getRssLink($PAGEDATA) {
	return $PAGEDATA->type=='forum'
		?array(
			'Posts for '.$PAGEDATA->name,
			$PAGEDATA->getRelativeURL().'?_p=rss&amp;p=forum'
		)
		:false;
}

// }
// { forum_user_profile

function forum_user_profile($PAGEDATA, $user) {
	$script = '$(function(){$(".rating").ratings();});
	';

	WW_addScript('ratings/ratings.js');
	WW_addInlineScript($script);

	$threads = dbOne(
		'select count(id) from forums_threads where creator_id=' . $user[ 'id' ],
		'count(id)'
	);

	$posts = dbOne(
		'select count(id) from forums_posts where author_id=' . $user[ 'id' ],
		'count(id)'
	);

	$emailHash = md5(trim(strtolower($user[ 'email' ])));

	$html = '<h1>Forum</h1>
	<table style="border:1px solid #ccc;margin:10px">
		<tr>
			<td rowspan="3">
		    <img class="avatar" data-uid="'.$user['id'].'"/>
			</td>
			<th>Threads Created:</th>
			<td>' . $threads . '</tr>
		</tr>
		<tr>
			<th>Posts:</th>
			<td>' . $posts . '</td>
		</tr>
		<tr>
			<th>Helpfulness Rating:</th>
			<td><p id="forum_user_' . $user[ 'id' ] . '"
			class="rating" type="forum_user">rating</p></td>
		</tr>
	</table>';

	$recent = dbAll(
		'select * from forums_posts where author_id=' . $user[ 'id' ]
		. ' order by created_date desc limit 4'
	);

	$ids = array();
	foreach ($recent as $post) {
		if (!in_array($post[ 'thread_id' ], $ids)) {
			array_push($ids, $post[ 'thread_id' ]);
		}
	}
	$threads = dbAll(
		'select * from forums_threads where id='
		. implode(' or id=', $ids)
	);

	$html .= '<h1>Forum - Your Recent Posts</h1>
	<table style="border:1px solid #ccc;margin:10px">
		<tr>
			<th>Thread</th>
			<th>Date</th>
			<th>Post</th>
		</tr>';

	foreach ($recent as $post) {
		foreach ($threads as $thread) {
			if ($thread[ 'id' ] == $post[ 'thread_id' ]) {
				$thread_id = $thread[ 'id' ];
				$name = $thread[ 'name' ];
				$forum = $thread[ 'forum_id' ];
				break;
			}
		}
		$pagename = dbOne(
			'select name from pages where id=(select page_id from forums where id=1)',
			'name'
		);
		$link = '/_r?type=forum&forum-f=' . $forum . '&forum-t=' . $thread_id;
		$html .= '<tr>
			<td><a href="' . $link . '">' . $name . '</a></td>
			<td>' . Core_dateM2H($post[ 'created_date' ]) . '</td>
			<td>' . substr($post[ 'body' ], 0, 40) . ' [...]</td>
		</tr>';
	}

	$html .= '</table>';

	return $html;
}

// }
