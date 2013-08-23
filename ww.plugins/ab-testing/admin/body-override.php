<?php
/**
	* kvWebME A/B Testing plugin admin page override script
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

$variants=explode('<div>ABTESTINGDELIMITER</div>', $page['body']);
$body='<div class="tabs"><ul>';
for ($i=0;$i<count($variants)+1;++$i) {
	$body.='<li><a href="#abtesting-'.$i.'">A/B Test '.($i+1).'</a></li>';
}
$body.='<li><a href="#abtesting-details">A/B Test Details</a></li>';
$body.='</ul>';
for ($i=0;$i<count($variants);++$i) {
	$body.='<div id="abtesting-'.$i.'">'
		.ckeditor('abtesting['.$i.']', $variants[$i])
		.'</div>';
}
$body.='<div id="abtesting-'.$i.'">'.ckeditor('abtesting['.$i.']', '').'</div>';
// { details
$body.='<div id="abtesting-details"><table><tr><th>Target page</th><td>';
$body.='<select name="page_vars[abtesting-target]">';
if (@$page_vars['abtesting-target']) {
	$parent=Page::getInstance($page_vars['abtesting-target']);
	$body.='<option value="'.$parent->id.'">'.htmlspecialchars($parent->alias)
		.'</option>';
}
else {
	$body.='<option value="0"> -- none -- </option>';
}
$body.='</select></td></tr></table></div>';
// }
$body.='</div>';
WW_addScript('ab-testing/admin/body-override.js');
