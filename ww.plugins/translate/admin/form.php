<?php
/**
	* form for Page section of admin area for Translate plugin
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     None
	*/

// { page to translate
$c='<tr><th>Page to translate</th><td>';
$c.='<select id="page_vars_translate_page_id" name="page_vars[translate_page_id]">';
if ($vars['translate_page_id']) {
	$parent=Page::getInstance($vars['translate_page_id']);
	$c.='<option value="'.$parent->id.'">'.htmlspecialchars($parent->name).'</option>';
}
else{
	$vars['translate_page_id']=0;
	$c.='<option value=""> -- choose -- </option>';
}
$c.='</select></td>';
// }
// { what language from
$c.='<th>Language From</th><td>';
$c.='<select id="page_vars_translate_language_from" '
	.'name="page_vars[translate_language_from]">';
if ($vars['translate_language_from']) {
	$c.='<option value="'.htmlspecialchars($vars['translate_language_from']).'">'
		.htmlspecialchars($vars['translate_language_from'])
		.'</option>';
}
else{
	$vars['translate_language_from']=0;
	$c.='<option value="0"> -- choose -- </option>';
}
$c.='</select></td>';
// }
// { what language to
$c.='<th>Language To</th><td>';
$c.='<select id="page_vars_translate_language" '
	.'name="page_vars[translate_language]">';
if ($vars['translate_language']) {
	$c.='<option value="'.htmlspecialchars($vars['translate_language']).'">'
		.htmlspecialchars($vars['translate_language'])
		.'</option>';
}
else{
	$vars['translate_language']=0;
	$c.='<option value="0"> -- choose -- </option>';
}
$c.='</select></td>';
// }
// { body
$c.='<tr><th>Body</th><td colspan="5">';
$c.='<p>To force a re-translation, clear the box below and update.</p>';
$c.=ckeditor('body', $page['body'], false);
$c.='</td></tr>';
// }
WW_addScript('translate/admin/form.js');
