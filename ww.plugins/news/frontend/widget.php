<?php
$html='';
if(!isset($vars->id)){
	$html='<em>No news page selected.</em>';
	return;
}
if(!$vars->stories_to_show)$vars->stories_to_show=10;
$rs=cache_load('pages','news'.$vars->id);
if($rs===false){
	$rs=dbAll('select id from pages where parent='.$vars->id.' order by associated_date desc,cdate desc limit 20');
	if ($rs!==false) {
		cache_save('pages', 'news'.$vars->id, $rs);
	}
}
if(!count($rs)){
	$html='<em>No news items to display.</em>';
	return;
}
$links=array();
foreach($rs as $r){
	$page=Page::getInstance($r['id']);
	$body='';
	if ($vars->characters_shown) {
		$body=preg_replace('#<h1[^<]*</h1>#', '', $page->render());
		$body=str_replace(array("\n","\r"),' ',$body);
		$body=preg_replace('/<script[^>]*>.*?<\/script>/', '', $body);
		$body=preg_replace('/<[^>]*>/', '', $body);
		$body='<br /><i>'.substr($body,0,$vars->characters_shown).'...</i>';
	}
	$links[]='<a href="'.$page->getRelativeURL().'"><strong>'.htmlspecialchars($page->name).'</strong><div class="date">'.date_m2h($page->associated_date).'</div>'.$body.'</a>';
}
$html.='<div id="news-wrapper-'.$vars->id.'" class="news_excerpts_wrapper"><ul class="news_excerpts"><li>'.join('</li><li>',$links).'</li></ul></div>';
if(isset($vars->scrolling) && $vars->scrolling){
	$n_items=isset($vars->stories_to_show) && is_numeric($vars->stories_to_show)?$vars->stories_to_show:2;
	if(isset($vars->scrolling) && $vars->scrolling){
		WW_addScript('/j/jquery.vticker-min.js');
		WW_addCSS('/ww.plugins/news/c/scroller.css');
		$html.='<script>$(function(){
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
