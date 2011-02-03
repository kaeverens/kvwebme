<?php
/**
	* functions for page type "table of contents"
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@webworks.ie>
	* @license  GPL 2.0
	* @link     http://webworks.ie/
	*/

/**
  * get table of contents for a given page
  *
	* @param object &$PAGEDATA page to retrieve the table of
	*
  * @return string HTML table of contents
  */
function TableOfContents_getContent(&$PAGEDATA) {
	$kids=Pages::getInstancesByParent($PAGEDATA->id);
	$c=$PAGEDATA->render();
	if (!count($kids->pages)) {
		$c.='<em>no sub-pages</em>';
	}
	else{
		$c.='<ul class="subpages">';
		foreach ($kids->pages as $kid) {
			$c.='<li><a href="'.$kid->getRelativeURL().'">'
				.htmlspecialchars($kid->name).'</a></li>';
		}
		$c.='</ul>';
	}
	if (isset($PAGEDATA->vars['footer'])) {
		$c.=$PAGEDATA->vars['footer'];
	}
	return $c;
}
