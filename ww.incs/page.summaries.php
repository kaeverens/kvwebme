<?php
/**
	* functions for displaying page summaries (primitive blog-like thing)
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/


/**
  * retrieve list (flat, non-recursive) of pages contained in a specified page
  *
	* @param int   $id             parent page to check
	* @param array $containedpages current list of pages, to be appended to
	*
  * @return array list of page ids
  */
function PageSummaries_getContainedPages($id, $containedpages=array()) {
	$rs=dbAll(
		'select id,type,special,category from pages where parent="'.$id
		.'" and !(special&4)'
	);
	foreach ($rs as $r) {
		switch($r['type']) {
			case 0: {
				$containedpages[]=$r['id'];
				break;
			}
		}
		$containedpages=PageSummaries_getContainedPages($r['id'], $containedpages);
	}
	return $containedpages;
}

/**
  * retrieve HTML summary for a set page
  *
	* @param int $id ID of the page
	*
  * @return string HTML summary for a set page
  */
function PageSummaries_getHtml($id) {
	$PAGEDATA=Page::getInstance($id);
	global $sitedomain;
	$r=dbRow('select * from page_summaries where page_id="'.$PAGEDATA->id.'"');
	if (!count($r)) {
		return '<em>'.__(
			'This page is marked as a page summary, but there is no '
			.'information on how to handle it.'
		)
			.'</em>';
	}
	if ($r['rss']) {
		return PageSummaries_rssToHtml($r['rss']);
	}
	// { build rss
	$title=($PAGEDATA->title=='')
		?$sitedomain
		:htmlspecialchars($PAGEDATA->title);
	$rss='<'.'?xml version="1.0" ?'.'><rss version="2.0"><channel><title>'
		.$title.'</title>';
	$rss.='<link>'.$_SERVER['REQUEST_URI'].'</link><description>RSS for '
		.$PAGEDATA->name.'</description>';
	$category=$PAGEDATA->category?' and category="'.$PAGEDATA->category.'"':'';
	$containedpages=PageSummaries_getContainedPages($r['parent_id']);
	if (count($containedpages)) {
		$q2=dbAll(
			'select edate,name,title,body from pages where id in ('
			.join(',', $containedpages).')'.$category.' order by cdate desc limit 20'
		);
		foreach ($q2 as $r2) {
			$rss.='<item>';
			if (!$r2['title']) {
				$r2['title']=$r2['name'];
			}
			$rss.='<title>'.htmlspecialchars($r2['title']).'</title>';
			$rss.='<pubDate>'.Core_dateM2H($r2['edate']).'</pubDate>';
			// { build body
			if ($r['amount_to_show']==0 || $r['amount_to_show']==1) {
				$length=$r['amount_to_show']==0?300:600;
				$body=substr(
					preg_replace(
						'/<[^>]*>/',
						'',
						str_replace(
							array('&amp;', '&nbsp;', '&lsquo;'),
							array('&',' ','&apos;'),
							$r2['body']
						)
					),
					0,
					$length
				)
				.'...';
			}
			else {
				$body=$r2['body'];
			}
			$body=str_replace('&euro;', '&#8364;', $body);
			// }
			$rss.='<description>'.$body.'</description>';
			$rss.='<link>http://'.$_SERVER['HTTP_HOST'].'/'
				.urlencode(str_replace(' ', '-', $r2['name'])).'</link>';
			$rss.='</item>';
		}
	}
	$rss.='</channel></rss>';
	dbQuery(
		'update page_summaries set rss="'.addslashes($rss)
		.'" where page_id="'.$PAGEDATA->id.'"'
	);
	// }
	return PageSummaries_rssToHtml($rss);
}

/**
  * convert RSS into HTML
  *
	* @param string $rss the RSS to convert
	*
  * @return string HTML version of an RSS file
  */
function PageSummaries_rssToHtml($rss) {
	$rss=str_replace('<'.'?xml version="1.0" ?'.'><rss version="2.0">', '', $rss);
	$rss=preg_replace('/<channel.*?\/description>/', '', $rss);
	$rss=preg_replace('/<pubDate>.*?<\/pubDate>/', '', $rss);
	$rss=str_replace(
		array('<title>', '</title>', '&#8364;'),
		array('<h3>', '</h3>', '&euro;'),
		$rss
	);
	$rss=str_replace('<description>', '<p>', $rss);
	$rss=str_replace('</description>', '</p>', $rss);
	$rss=str_replace('<item>', '<div class="page_summary_item">', $rss);
	$rss=str_replace('</item>', '</div>', $rss);
	$rss=str_replace('<link>', '<a href="', $rss);
	$rss=str_replace('</link>', '">'.__('[more...]').'</a>', $rss);
	$rss=str_replace(array('</rss>', '</channel>'), array('', ''), $rss);
	return $rss==''?'<em>'.__('No articles contained here.').'</em>':$rss;
}
