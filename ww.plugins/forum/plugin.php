<?php
/**
  * Forum plugin definition file
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
		'building-rss-links'  =>'Forum_getRssLink'
	),
	'rss-handler' => 'Forum_rssHandler',
	'version' => 6
);

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
			'pubDate'=>date_m2h($post['created_date'], 'rfc822')
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
			$PAGEDATA->getRelativeURL().'?_p=rss&p=forum'
		)
		:false;
}
