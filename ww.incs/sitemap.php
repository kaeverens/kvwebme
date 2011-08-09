<?php
/**
	* generate a sitemap, as defined here: http://sitemaps.org/
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

require_once 'basics.php';
header('Content-type: text/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
$rs=dbAll(
	'select id,edate,importance,name from pages where importance>0 '
	.'&& !(special&2) order by importance desc'
);
foreach ($rs as $r) {
	$page=Page::getInstance($r['id'])->initValues();
	$https=isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on'?'https':'http';
	echo '<url><loc>'.$https.'://'.$_SERVER['HTTP_HOST'].$page->getRelativeUrl()
		.'</loc>'
		.'<lastmod>'.preg_replace('/ .*/', '', $r['edate']).'</lastmod>'
		.'<priority>'.$r['importance'].'</priority>'
		.'</url>';
}
echo '</urlset>';
