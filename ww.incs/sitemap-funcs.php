<?php
/**
	* functions for displaying a HTML sitemap
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

require_once SCRIPTBASE . 'ww.incs/menus.php';

/**
  * build a HTML sitemap of the site
  *
  * @return string HTML sitemap
  */
function Sitemap_get() {
	if (@$GLOBALS['DBVARS']['disable-hidden-sitemap']) {
		redirect(preg_replace('/.cmsspecial=sitemap/', '', $_SERVER['REQUEST_URI']));
	}
	global $PAGEDATA;
	$rs=Menu_getChildren(0, $PAGEDATA->id);
	return '<ul>'.Sitemap_getLinks($rs).'</ul>';
}

/**
  * check child pages for their own sub-pages
  *
	* @param array $rs list of child-pages
	*
  * @return string HTML sitemap
  */
function Sitemap_getLinks($rs) {
	global $PAGEDATA;
	$c='';
	foreach ($rs as $r) {
		$d=(strpos($r['classes'], 'hasChildren')!==false)
			?'<ul>'.Sitemap_getLinks(
				Menu_getChildren($r['id'], $PAGEDATA->id)
			).'</ul>'
			:'';
		$c.='<li><a href="'.$r['link'].'" class="'.$r['classes'].'">'
			.htmlspecialchars($r['name']).'</a>'.$d.'</li>';
	}
	return $c;
}
