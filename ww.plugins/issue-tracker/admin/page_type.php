<?php
/**
	* admin page for issue tracker
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

$html= '<div class="tabs">';
// { table of contents
$html.= '<ul>'
	.'<li><a href="#issuetracker-main">Main Details</a></li>'
	.'<li><a href="#issuetracker-header">Header</a></li>'
	.'<li><a href="#issuetracker-footer">Footer</a></li>'
	.'</ul>';
// }
// { main details
$html.= '<div id="issuetracker-main">'
	.'<p>Issues should be edited from the front-end of the site.'
	.' This area is for configuration only.</p>';
$html.='<h2>Edit an issue type.</h2>'
	.'<table><tr><th>Issue Type</th>'
	.'<td><select id="issue-type"></select></td>'
	.'</tr>'
	.'<tr><th>Fields</th>'
	.'<td id="issue-fields-wrapper"></td></tr>'
	.'</table>';
$html.= '</div>';
// }
// { header
$html.= '<div id="issuetracker-header">';
$html.= '<p>Text to be shown above the product/product list</p>';
$html.= ckeditor('body', $page['body'], null, 1);
$html.= '</div>';
// }
// { footer
$html.= '<div id="issuetracker-footer">';
$html.= '<p>Text to be shown below the product/product list</p>';
$html.= ckeditor(
	'page_vars[footer]',
	isset($vars['footer'])?$vars['footer']:'',
	null, 1
);
$html.= '</div>';
// }
$html.= '</div>';
WW_addScript('issue-tracker/admin.js');
