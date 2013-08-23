<?php
/**
  * upgrade script for Forum plugin
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

if ($version==0) { // forums table
	dbQuery(
		'CREATE TABLE IF NOT EXISTS `forums` (
			`id` int NOT NULL auto_increment primary key,
			`page_id` int,
			`parent_id` int default 0,
			`name` text
		) ENGINE=MyISAM DEFAULT CHARSET=utf8'
	);
	dbQuery(
		'CREATE TABLE IF NOT EXISTS `forums_threads` (
			`id` int NOT NULL auto_increment primary key,
			`forum_id` int,
			`sticky` tinyint default 0,
			`name` text,
			`creator_id` int,
			`created_date` datetime,
			`num_posts` int,
			`last_post_date` datetime,
			`last_post_by` int
		) ENGINE=MyISAM DEFAULT CHARSET=utf8'
	);
	dbQuery(
		'CREATE TABLE IF NOT EXISTS `forums_posts` (
			`id` int NOT NULL auto_increment primary key,
			`thread_id` int,
			`author_id` int,
			`created_date` datetime,
			`body` text
		) ENGINE=MyISAM DEFAULT CHARSET=utf8'
	);
	$version=1;
}
if ($version==1) { // subscribers
	dbQuery(
		'alter table forums_threads add subscribers text'
	);
	$threads=dbAll('select id from forums_threads');
	foreach ($threads as $thread) {
		$subscribers=array();
		$posts=dbAll('select author_id from forums_posts where thread_id='.$thread['id']);
		foreach ($posts as $post) {
			if (!in_array($post['author_id'], $subscribers)) {
				$subscribers[]=$post['author_id'];
			}
		}
		dbQuery('update forums_threads set subscribers="'.join(',',$subscribers).'" where id='.$thread['id']);
	}
	$version=2;
}
if ($version==2) { // moderation of posts
	dbQuery('alter table forums_posts add moderated smallint default 0');
	$version=3;
}
if ($version==3) { // moderator groups
	dbQuery('alter table forums add moderator_groups text');
	$version=4;
}
if ($version==4) { // fix older threads
	dbQuery('update forums_posts set moderated=1');
	$version=5;
}
if ($version==5) { // make moderation optional defaulting to no
	dbQuery('alter table forums add is_moderated tinyint(1) default 0');
	$version=6;
}
