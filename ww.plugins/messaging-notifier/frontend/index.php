<?php
/**
	* RSS aggregator plugin
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
	* check for new emails
	*
	* @param object $vars config object
	*
	* @return array array of results
	*/
function Aggregator_show($vars) {
	if (!is_array($vars) && isset($vars->id) && $vars->id) {
		$data=Core_cacheLoad('messaging_notifier', 'id'.$vars->id);
		if ($data===false) {
			$data=dbOne(
				'select data from messaging_notifier where id='.$vars->id,
				'data'
			);
			Core_cacheSave('messaging_notifier', 'id'.$vars->id, $data);
		}
		if ($data) {
			return Aggregator_parse(json_decode($data), $vars);
		}
	}
}

/**
	* check for new emails
	*
	* @param array  $data data
	* @param object $vars config object
	*
	* @return array array of results
	*/
function Aggregator_parse($data, $vars) {
	if (!isset($vars->hide_story_title)) {
		$vars->hide_story_title=0;
	}
	if (!isset($vars->characters_shown)) {
		$vars->characters_shown=200;
	}
	if (!isset($vars->scrolling)) {
		$vars->scrolling=0;
	}
	if (!isset($vars->load_in_other_tab)) {
		$vars->load_in_other_tab=1;
	}
	if (!isset($vars->stories_to_show)) {
		$vars->stories_to_show=10;
	}
	$altogether=array();
	foreach ($data as $r) {
		$md5=md5($r->url);
		$f=Core_cacheLoad('messaging-notifier', $md5);
		if ($f===false || (int)@$f['last-check']<time()-($r->refresh*60)) {
			switch ($r->type) {
				case 'WebME News Page': // {
					$f=Aggregator_getWebmeNews($r);
				break; // }
				case 'email': // {
					$f=Aggregator_getEmail($r);
				break; // }
				case 'phpBB3': // {
					$f=Aggregator_getPhpbb3($r);
				break; // }
				case 'RSS': // {
					$f=Aggregator_getRss($r);
				break; // }
				case 'Twitter': // {
					$f=Aggregator_getTwitter($r);
				break; // }
			}
			$f['last-check']=time();
			Core_cacheSave('messaging-notifier', $md5, $f);
		}
		$altogether=array_merge($altogether, $f);
	}
	$html='<div id="messaging-notifier-'.$vars->id.'">'
		.'<ul class="messaging-notifier">';
	$i=0;
	$ordered=array();
	foreach ($altogether as $r) {
		$ordered[$r['unixtime']]=$r;
	}
	krsort($ordered);
	foreach ($ordered as $r) {
		if (++$i > 10 || (!$vars->scrolling && $i>$vars->stories_to_show)) {
			continue;
		}
		$description='';
		if ($vars->characters_shown) {
			$description=preg_replace('/<[^>]*>/', '', $r['description']);
			if (strlen($description)>(int)$vars->characters_shown) {
				$description=substr($description, 0, $vars->characters_shown).'...';
			}
		}
		$target=$vars->load_in_other_tab?' target="_blank"':'';
		$title=$vars->hide_story_title
			?''
			:'<strong>'.htmlspecialchars($r['title']).'</strong><br />';
		$html.='<li class="messaging-notifier-'.$r['type'].'"><a'.$target
			.' href="'.$r['link'].'">'.$title.$description.'</a><br /><i>'
			.date('Y M jS H:i', $r['unixtime']).'</i></li>';
	}
	$html.='</ul></div>';
	WW_addCSS('/ww.plugins/messaging-notifier/c/styles.css');
	if (isset($vars->scrolling) && $vars->scrolling) {
		$n_items=isset($vars->stories_to_show)&&is_numeric($vars->stories_to_show)
			?$vars->stories_to_show
			:2;
		if (isset($vars->scrolling) && $vars->scrolling) {
			WW_addScript('/j/jquery.vticker-min.js');
			WW_addCSS('/ww.plugins/messaging-notifier/c/scroller.css');
			$html.='<script defer="defer">$(function(){
					$("#messaging-notifier-'.$vars->id.'").vTicker({
						speed: 4000,
						pause: 5000,
						showItems: '.$n_items.',
						animation: "",
						mousePause: true
					});
				});</script>';
		}
	}
	$height=isset($vars->height_in_px) && $vars->height_in_px
		?' style="height:'.((int)$vars->height_in_px).'px"'
		:'';
	return $html;
}

/**
	* check for new emails
	*
	* @param object $r config object
	*
	* @return array array of results
	*/
function Aggregator_getRss($r) {
	$f=@file_get_contents($r->url);
	if (!$f) {
		return array();
	}
	$dom=@DOMDocument::loadXML($f);
	$items=$dom->getElementsByTagName('item');
	$arr=array();
	foreach ($items as $item) {
		$i=array();
		$i['type']='RSS';
		$title=$item->getElementsByTagName('title');
		$i['title']=$title->item(0)->nodeValue;
		$link=$item->getElementsByTagName('link');
		$i['link']=$link->item(0)->nodeValue;
		$description=$item->getElementsByTagName('description');
		$i['description']=$description->item(0)->nodeValue;
		$unixtime=$item->getElementsByTagName('pubDate');
		$i['unixtime']=strtotime($unixtime->item(0)->nodeValue);
		$arr[]=$i;
	}
	Core_cacheSave('messaging-notifier', md5($r->url), $arr);
	return $arr;
}

/**
	* check for new emails
	*
	* @param object $r config object
	*
	* @return array array of results
	*/
function Aggregator_getWebmeNews($r) {
	if (!is_numeric($r->url)) {
		return array();
	}
	$items=dbAll(
		'select id,name,associated_date,body from pages where parent='.$r->url
		.' order by associated_date desc limit 20'
	);
	$arr=array();
	foreach ($items as $item) {
		$p=Page::getInstance($item['id']);
		$i=array();
		$i['type']='WebME-News-Page';
		$i['title']=$item['name'];
		$i['link']='/?pageid='.$item['id'];
		$i['unixtime']=strtotime($item['associated_date']);
		$i['description']=$item['body'];
		$arr[]=$i;
	}
	Core_cacheSave('messaging-notifier', md5($r->url), $arr);
	return $arr;
}

/**
	* check for new emails
	*
	* @param object $r config object
	*
	* @return array array of results
	*/
function Aggregator_getTwitter($r) {
	$f=@file_get_contents($r->url);
	if (!$f) {
		return array();
	}
	$dom=DOMDocument::loadXML($f);
	$items=$dom->getElementsByTagName('item');
	$arr=array();
	foreach ($items as $item) {
		$i=array();
		$i['type']='Twitter';
		$title=$item->getElementsByTagName('title');
		$i['title']=$title->item(0)->nodeValue;
		$link=$item->getElementsByTagName('link');
		$i['link']=$link->item(0)->nodeValue;
		$unixtime=$item->getElementsByTagName('pubDate');
		$i['unixtime']=strtotime($unixtime->item(0)->nodeValue);
		$arr[]=$i;
	}
	Core_cacheSave('messaging-notifier', md5($r->url), $arr);
	return $arr;
}

/**
	* check for new emails
	*
	* @param object $r config object
	*
	* @return array array of results
	*/
function Aggregator_getPhpbb3($r) {
	$f=@file_get_contents($r->url);
	if (!$f) {
		return array();
	}
	$urlbase=preg_replace('#/[^/]*$#', '/', $r->url);
	$dom=@DOMDocument::loadHTML($f);
	$lists=$dom->getElementsByTagName('ul');
	$arr=array();
	foreach ($lists as $list) {
		$class=$list->getAttribute('class');
		if ($class!='topiclist topics') {
			continue;
		}
		$items=$list->getElementsByTagName('li');
		foreach ($items as $item) {
			$i=array();
			$i['type']='phpBB3';
			$str=$item->getElementsByTagName('dt');
			$tmp_doc=new DOMDocument();
			$tmp_doc->appendChild($tmp_doc->importNode($str->item(0), true));
			$str=preg_replace(
				'/[ 	]+/',
				' ',
				str_replace(array("\n", "\r"), ' ', $tmp_doc->saveHTML())
			);
			$i['title']
				=preg_replace('#^.*<a href="./memb[^>]*>([^<]*)<.*#', '\1', $str)
				.' wrote a post in: '
				.preg_replace('#^<dt[^>]*> <a href=[^>]*>([^<]*)<.*#', '\1', $str);
			$i['link']=$urlbase.preg_replace(
				'#^<dt[^>]*> <a href="([^"]*)".*#',
				'\1',
				$str
			);
			if (strpos($i['link'], '&amp;sid=')!==false) { // strip session id
				$i['link']=preg_replace('/&amp;sid=.*/', '', $i['link']);
			}
			$i['unixtime']=strtotime(
				preg_replace('#.*raquo; (.*) </dt>#', '\1', $str)
			);
			$arr[]=$i;
		}
	}
	Core_cacheSave('messaging-notifier', md5($r->url), $arr);
	return $arr;
}

/**
	* check for new emails
	*
	* @param object $r config object
	*
	* @return array array of results
	*/
function Aggregator_getEmail($r) {
	$bs=explode('|', $r->url);
	$username=$bs[0];
	$password=$bs[1];
	$hostname=$bs[2];
	$link_url=isset($bs[3])?$bs[3]:'';
	$mbox=imap_open(
		'{'.$hostname.':143/novalidate-cert}INBOX',
		$username,
		$password
	);
	$emails=imap_search($mbox, 'ALL');
	$arr=array();
	if ($emails && is_array($emails)) {
		foreach ($emails as $email_number) {
			$overview=imap_fetch_overview($mbox, $email_number, 0);
			$subject=$overview[0]->subject;
			$from=trim(preg_replace('/<[^>]*>/', '', $overview[0]->from));
			$arr[]=array(
				'type'  => 'email',
				'title' => $from.' wrote an email: '.$subject,
				'link' => $link_url,
				'unixtime'=>strtotime($overview[0]->date)
			);
			imap_delete($mbox, $email_number);
		}
	}
	imap_expunge($mbox);
	imap_close($mbox);
	$md5=md5($r->url);
	$c=Core_cacheLoad('messaging-notifier', $md5);
	if ($c===false) {
		$c=array();
	}
	$arr=array_merge($arr, $c);
	krsort($arr);
	$arr=array_slice($arr, 0, 10);
	Core_cacheSave('messaging-notifier', $md5, $arr);
	return $arr;
}
