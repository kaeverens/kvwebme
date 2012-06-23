<?php
/**
  * News page
  *
  * PHP Version 5
  *
  * @category   None
  * @package    None
  * @subpackage None
  * @author     Kae Verens <kae@kvsites.ie>
  * @author     Conor MacAoidh <conor@macaoidh.name>
  * @license    GPL Version 2
  * @link       www.kvweb.me
 */

/**
  * show the news in Calendar mode
  *
  * @param array $PAGEDATA the page object
  *
  * @return string HTML of the news
  */
function News_displayCalendar($PAGEDATA) {
	WW_addScript('news/frontend/calendar.js');
	return '<table id="events_wrapper"><tr>'
		.'<td><div id="events_calendar"></div></td>'
		.'<td id="events_list">&nbsp;</td>'
		.'</tr></table>';
}

/**
  * show the news in Headline mode
  *
  * @param array $PAGEDATA the page object
  *
  * @return string HTML of the news
  */
function News_displayHeadlines($PAGEDATA) {
	$items_per_page=(isset($PAGEDATA->vars['news_items']))?
		$PAGEDATA->vars['news_items']:
		5;
	$p=isset($_REQUEST['news_page'])?(int)$_REQUEST['news_page']:0;
	if ($p<0) {
		$p=0;
	}
	
	$arr=Core_cacheLoad('pages', 'news-'.$GLOBALS['id'].'-'.$p.'-'.$items_per_page);
	if ($arr===false) {
		$order_by=(isset($PAGEDATA->vars['news_order']))?
			addslashes($PAGEDATA->vars['news_order']):
			'associated_date desc';
		$rs=dbAll(
			'select * from pages where parent='.$GLOBALS['id'].' order by '
			.$order_by.',cdate desc limit '.$p.','.$items_per_page
		);
		$num_stories=dbOne(
			'select count(id) as num from pages where parent='.$GLOBALS['id'],
			'num'
		);
		Core_cacheSave(
			'pages',
			'news-'.$GLOBALS['id'].'-'.$p.'-'.$items_per_page,
			array($num_stories, $rs)
		);
	}
	else {
		$num_stories=$arr[0];
		$rs=$arr[1];
		unset($arr);
	}
	
	$nextprev=array();
	$nextprev[]='<span class="page_n_of_n">'
		.__(
			'page %1 of %2',
			array(1+floor($p/$items_per_page), ceil($num_stories/$items_per_page)),
			'core'
		)
		.'</span>';
	if ($p) {
		$nextprev[]='<a class="prev" href="?news_page='.($p-$items_per_page)
		.'">'.__('Previous Page').'</a>';
	}
	if ($p+$items_per_page < $num_stories) {
		$nextprev[]='<a class="next" href="?news_page='.($p+$items_per_page)
		.'">'.__('Next Page').'</a>';
	}
	$nextprev='<div class="nextprev">'.join(' | ', $nextprev).'</div>';
	
	$html=$nextprev;
	
	$links=array();
	foreach ($rs as $r) {
		$page=Page::getInstance($r['id'], $r);
		$content=(isset($PAGEDATA->vars['news_display'])
			&&$PAGEDATA->vars['news_display']=='full'
		)
			?$page->render()
			:substr(
				preg_replace(
					'/<[^>]*>/',
					'',
					preg_replace('#<h1>[^<]*</h1>#', '', $page->render())
				),
				0,
				600
			);
		$date=(isset($PAGEDATA->vars['news_title'])
			&&$PAGEDATA->vars['news_title']=='yes'
		)
			?'<h2 class="news-header"><a href="'.$page->getRelativeURL().'">'
			.htmlspecialchars($page->name).'</a></h2>'.'<a class="news-date" href="'
			.$page->getRelativeURL().'">'
			.__('posted on %1', array(Core_dateM2H($page->associated_date)), 'core')
			.'</a>'
			:'';
		if (!isset($page->associated_date)
			|| !$page->associated_date
		) {
			$page->associated_date=$page->cdate;
		}
		$links[]=$date
			.'<p class="news-paragraph">'.$content.'...</p>'
		;
	}
	$html.=join('<div class="news-break"></div>', $links);
	$html.=$nextprev;
	return $html;
}

if (!isset($PAGEDATA->vars['news_type'])) {
	$PAGEDATA->vars['news_type']=0;
}
switch ($PAGEDATA->vars['news_type']) {
	case '1': // { calendar
		$html=News_displayCalendar($PAGEDATA);
	break; // }
	default: // { headlines
		$html=News_displayHeadlines($PAGEDATA);
		// }
}
