<?php
/**
	* definition file for RSS plugin
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     None
	*/

// { define $plugin
$plugin=array(
	'name'          => 'RSS',
	'description'   => 'add RSS feeds to forums, news pages, comments, etc.',
	'triggers'      => array(
		'building-metadata'=>'RSS_getFeedURLS'
	),
	'page-override' => 'RSS_pageOverride'
);
// }

/**
	* returns a HTML string to show the FaceBook widget
	*
	* @param object $vars plugin parameters
	*
	* @return string
	*/
function RSS_getFeedURLs($vars=null) {
	if (!isset($GLOBALS['PLUGIN_TRIGGERS']['building-rss-links'])) {
		return;
	}
	$calls=$GLOBALS['PLUGIN_TRIGGERS']['building-rss-links'];
	$feeds='';
	foreach ($calls as $call) {
		$rss=$call($GLOBALS['PAGEDATA']);
		if (is_array($rss)) {
			$feeds.='<link rel="alternate" type="application/rss+xml"'
				.' title="'.htmlspecialchars($rss[0]).'"'
				.' href="'.$rss[1].'" />';
		}
	}
	return $feeds;
}

/**
	* page override function, for displaying the RSS feed
	*
	* @param object $page the page object
	*
	* @return null
	*/
function RSS_pageOverride($page) {
	if (!isset($_REQUEST['p'])) {
		die('no plugin defined in the URL (parameter "p")');
	}
	$p=$_REQUEST['p'];
	$plugins=$GLOBALS['PLUGINS'];
	if (!isset($plugins[$p])) {
		die('the plugin "'.htmlspecialchars($p).'" is not enabled');
	}
	if (!isset($plugins[$p]['rss-handler'])) {
		die('the plugin "'.htmlspecialchars($p).'" has no RSS handler');
	}
	$articles=$plugins[$p]['rss-handler']($page);
	header('Content-type: application/rss+xml');
	echo '<?xml version="1.0" encoding="UTF-8" ?>'."\n"
		.'<rss version="2.0">'
		.'<channel>'
		.'<title>'.htmlspecialchars($articles['title']).'</title>'
		.'<description>'.htmlspecialchars($articles['description']).'</description>'
		.'<link>'.htmlspecialchars($articles['link']).'</link>'
		.'<generator>'.htmlspecialchars($articles['generator']).'</generator>';
	foreach ($articles['items'] as $item) {
		echo '<item>'
			.'<title>'.htmlspecialchars($item['title']).'</title>'
			.'<description>'.htmlspecialchars($item['description']).'</description>'
			.'<link>'.htmlspecialchars($item['link']).'</link>'
			.'<guid>'.htmlspecialchars($item['guid']).'</guid>'
			.'<pubDate>'.htmlspecialchars($item['pubDate']).'</pubDate>'
			.'</item>';
	}
	echo '</channel></rss>';
}
