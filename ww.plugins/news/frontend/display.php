<?php
/*
	Webme News Plugin v0.1
	File: frontend/display.php
	Developers:
		Conor Mac Aoidh  http://macaoidh.name/
		Kae Verens       http://verens.com/
	Report Bugs:
		Kae Verens       kae@verens.com
		Conor Mac Aoidh  conor@macaoidh.name
*/

function News_displayCalendar(&$PAGEDATA) {
	WW_addScript('/ww.plugins/news/frontend/calendar.js');
	return '<table id="events_wrapper"><tr>'
		.'<td><div id="events_calendar"></div></td>'
		.'<td id="events_list">&nbsp;</td>'
		.'</tr></table>';
}
function News_displayHeadlines() {
	$items_per_page=5;
	$p=isset($_REQUEST['news_page'])?(int)$_REQUEST['news_page']:0;
	if($p<0) $p=0;
	
	$arr=cache_load('pages', 'news-'.$GLOBALS['id'].'-'.$p.'-'.$items_per_page);
	if ($arr===false) {
		$rs=dbAll('select * from pages where parent='.$GLOBALS['id']." order by associated_date desc,cdate desc limit $p,$items_per_page");
		$num_stories=dbOne('select count(id) as num from pages where parent='.$GLOBALS['id'],'num');
		cache_save('pages', 'news-'.$GLOBALS['id'].'-'.$p.'-'.$items_per_page, array($num_stories, $rs));
	}
	else {
		$num_stories=$arr[0];
		$rs=$arr[1];
		unset($arr);
	}
	
	$nextprev=array();
	$nextprev[]='<span class="page_n_of_n">page '.(1+floor($p/$items_per_page)).' of '.(ceil($num_stories/$items_per_page)).'</span>';
	if($p)$nextprev[]='<a class="prev" href="?news_page='.($p-$items_per_page).'">Previous Page</a>';
	if($p+$items_per_page < $num_stories)$nextprev[]='<a class="next" href="?news_page='.($p+$items_per_page).'">Next Page</a>';
	$nextprev='<div class="nextprev">'.join(' | ', $nextprev).'</div>';
	
	$html=$nextprev;
	
	$links=array();
	foreach($rs as $r){
		$page=Page::getInstance($r['id'],$r);
		if(!isset($page->associated_date) || !$page->associated_date)$page->associated_date=$page->cdate;
		$links[]='<h2 class="news-header"><a href="'.$page->getRelativeURL().'">'.htmlspecialchars($page->name).'</a></h2>'
			.'<a class="news-date" href="'.$page->getRelativeURL().'">posted on '.date_m2h($page->associated_date).'</a>'
			.'<p class="news-paragraph">'.substr(preg_replace('/<[^>]*>/','',preg_replace('#<h1>[^<]*</h1>#','',$page->render())),0,600).'...</p>'
		;
	}
	$html.=join('<div class="news-break"></div>',$links);
	
	$html.=$nextprev;
	return $html;
}

if (!isset($PAGEDATA->vars['news_type'])) {
	$PAGEDATA->vars['news_type']=0;
}
switch($PAGEDATA->vars['news_type']) {
	case '1': // { calendar
		$html=News_displayCalendar($PAGEDATA);
	break; // }
	default: // { headlines
		$html=News_displayHeadlines();
	// }
}
