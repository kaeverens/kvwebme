<?php

/**
	* The plugin.php file for Webme's comments plugin
	*
	* PHP Version 5
	*
	* @category   WebmeCommentsPlugin
	* @package    WebworksWembe
	* @subpackage Comments
	* @author     Belinda Hamilton <bhamilton@webworks.ie>
	* @author     Kae Verens <kae@kvsites.ie>
	* @license    GPL Version 2
	* @link       www.kvweb.me
	**/

// { configure
$plugin = array(
	'name'=>'Comments',
	'description' =>'Allow visitors to comment on pages on your site',
	'version'=>3,
	'admin'=>array(
		'menu'=>array(
			'Communication>Comments'=>'plugin.php?_plugin=comments&amp;_page=comments'
		),
		'page_panel'=>array(
			'name'=>'Comments', 
			'function'=>'Comments_pageTab'
		)
	),
	'triggers'=>array(
		'page-content-created'=>'Comments_getCommentsHTML',
		'building-rss-links'  =>'Comments_getRssLink'
	),
	'rss-handler'=>'Comments_rssHandler'
);
// }

/**
	* A stub function to display the contents of the comments tab
	*
	* @param Object $page     The page
	* @param Object $pagevars Page related variables
	*
	* @return void
	*
	* @see admin/comments-tab.php
	*
	**/
function Comments_pageTab ($page, $pagevars) {
	require_once SCRIPTBASE.'ww.plugins/comments/admin/comments-tab.php';
}

/**
	* A stub function to show comments
	*
	* @param Object $PAGEDATA The page
	*
	* @return string The comment html
	*
	* @see frontend/comments-show.php
	*
	**/
function Comments_getCommentsHTML($PAGEDATA) {
	require_once dirname(__FILE__).'/frontend/show-comments.php';
	return Comments_displayComments($PAGEDATA);
}

/**
	* get RSS link if available
	*
	* @param Object $PAGEDATA The page
	*
	* @return string URL of the RSS feed
	**/
function Comments_getRssLink($PAGEDATA) {
	$hideComments=isset($PAGEDATA->vars['hide_comments'])
		&& $PAGEDATA->vars['hide_comments'];
	return $hideComments
		?false
		:array(
			'Comments for '.$PAGEDATA->name,
			$PAGEDATA->getRelativeURL().'?_p=rss&amp;p=comments'
		);
}

/**
	* get a list of comments for an RSS feed
	*
	* @param Object $PAGEDATA The page
	*
	* @return array array of articles
	**/
function Comments_rssHandler($PAGEDATA) {
	$hideComments=isset($PAGEDATA->vars['hide_comments'])
		&& $PAGEDATA->vars['hide_comments'];
	if ($hideComments) {
		die('comments are hidden');
	}
	require_once dirname(__FILE__).'/frontend/libs.php';
	$comments=Comments_getListOfComments($PAGEDATA, 'desc', 10);
	$items=array();
	foreach ($comments as $comment) {
		$items[]=array(
			'title'=>'comment by '.$comment['name'],
			'description'=>$comment['comment'],
			'link'=>'//'.$_SERVER['HTTP_HOST'].$PAGEDATA->getRelativeURL()
				.'#comments-'.$comment['id'],
			'guid'=>'comment-'.$comment['id'],
			'pubDate'=>Core_dateM2H($comment['cdate'], 'rfc822')
		);
	}
	return array(
		'title'       => 'Comments for '.$PAGEDATA->getRelativeURL(),
		'link'        => '//'.$_SERVER['HTTP_HOST'].$PAGEDATA->getRelativeURL()
			.'#comments',
		'description' => 'Comments for '.$PAGEDATA->getRelativeURL(),
		'generator'   => 'WebME CMS',
		'items'       => $items
	);
}
