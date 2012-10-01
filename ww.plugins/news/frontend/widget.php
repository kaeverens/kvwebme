<?php
/**
  * News widget
  *
  * PHP Version 5
  *
  * @category   None
  * @package    None
  * @subpackage None
  * @author     Kae Verens <kae@kvsites.ie>
  * @author     Conol MacAoidh <conor@macaoidh.name>
  * @license    GPL Version 2
  * @link       www.kvweb.me
 */

$html='';
if (!isset($vars->id)) {
	$html='<em>No news page selected.</em>';
	return;
}
if (!$vars->stories_to_show) {
	$vars->stories_to_show=10;
}
$rs=Core_cacheLoad('pages', 'news|'.$vars->id.'|'.$vars->stories_to_show);
if ($rs===false) {
	$rs=dbAll(
		'select id from pages where parent='.$vars->id
		.' and date_publish<now() and date_unpublish>now()'
		.' order by associated_date desc,cdate desc limit '.$vars->stories_to_show
	);
	if ($rs!==false) {
		Core_cacheSave('pages', 'news|'.$vars->id.'|'.$vars->stories_to_show, $rs);
	}
}
if (!count($rs)) {
	$html='<em>No news items to display.</em>';
	return;
}
$links=array();
foreach ($rs as $r) {
	$page=Page::getInstance($r['id']);
	$thumb='';
	if ((isset($vars->thumbnail) && $vars->thumbnail)
		|| $vars->characters_shown
	) {
		$pagerendered=$page->render();
	}
	if (isset($vars->thumbnail) && $vars->thumbnail) {
		$img=preg_replace('/.*<img/', '<img', str_replace(array("\n", "\r"), ' ', $pagerendered));
		if (strpos($img, '<img')===0) {
			$img=preg_replace('/>.*/', '', $img);
			$img=preg_replace('/.*src="([^"]*)".*/', '\1', $img);
			$img=preg_replace('#^/f/#', '', $img);
			$thumb='<img src="/a/f=getImg/w='.$vars->thumbnailw
				.'/h='.$vars->thumbnailh.'/'.$img.'" style="float:left;"/>';
		}
	}
	$body='';
	if ($vars->characters_shown) {
		$body=preg_replace('#<h1[^<]*</h1>#', '', $pagerendered);
		$body=str_replace(array("\n", "\r"), ' ', $body);
		$body=preg_replace('/<script defer="defer"[^>]*>.*?<\/script>/', '', $body);
		$body=preg_replace('/<[^>]*>/', '', $body);
		$body='<br /><i>'.substr($body, 0, $vars->characters_shown).'...</i>';
	}
	$links[]='<a href="'.$page->getRelativeURL().'"><strong>'
		.htmlspecialchars(__FromJson($page->name)).'</strong><div class="date">'
		.Core_dateM2H($page->associated_date).'</div><span class="news-body">'
		.$thumb.$body.'</span></a>';
}
$html.='<div id="news-wrapper-'.$vars->id
	.'" class="news_excerpts_wrapper"><ul class="news_excerpts"><li>'
	.join('</li><li>', $links).'</li></ul></div>';
if (isset($vars->scrolling) && $vars->scrolling) {
	$n_items=isset($vars->stories_to_show) && is_numeric($vars->stories_to_show)
		?$vars->stories_to_show
		:2;
	if (isset($vars->scrolling) && $vars->scrolling) {
		WW_addScript('/j/jquery.vticker-min.js');
		WW_addCSS('/ww.plugins/news/c/scroller.css');
		$html.='<script defer="defer">$(function(){
			$("#news-wrapper-'.$vars->id.'").vTicker({
				speed: 15000,
				pause: 5000,
				showItems: '.$n_items.',
				animation: "",
				mousePause: true
			});
		});</script>';
	}
}
