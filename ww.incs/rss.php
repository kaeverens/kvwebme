<?php
/**
	* display page summaries
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

require_once '../ww.incs/common.php';
header('Content-type: text/xml; charset=utf-8');
$pagename=preg_replace('#^/|.rss$#', '', urldecode($_SERVER['REQUEST_URI']));
$page=Page::getInstanceByName($pagename);
if ($page) {
	$r2=dbRow('select rss from page_summaries where page_id='.$page->id);
	if (count($r2)) {
		if ($r2['rss']=='') {
			require_once SCRIPTBASE.'/ww.incs/page.summaries.php';
			PageSummaries_getHtml($page->id);
			$r2=dbRow('select rss from page_summaries where page_id='.$page->id);
		}
		$rss=str_replace('&rsquo;', '&apos;', $r2['rss']);
		$rss=str_replace('&sbquo;', '&apos;', $rss);
		echo $rss;
	}
}
else {
	echo __('page "%1" not found', array($pagename), 'core');
}
