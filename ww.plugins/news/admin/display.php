<?php
/**
  * News admin
  *
  * PHP Version 5
  *
  * @category   None
  * @package    None
  * @subpackage None
  * @author     Kae Verens <kae@kvsites.ie>
  * @author     Conor MacAoidh <conor@macaoidh.name>
  * @license    GPL Version 2
  * @link       www.kvweb.me
 */

$c = '<div class="tabs">'
	.'<ul>'
	.'<li><a href="#news-main">Main</a></li>'
	.'<li><a href="#news-header">Header</a></li>'
	.'<li><a href="#news-footer">Footer</a></li>'
	.'</ul>';
// { main tab
$c.='<div id="news-main">';
$c.='<p>To create a new news item, right-click on this page\'s name in the left menu, and add a new "Normal" page under it. You can edit the item\'s date and time using "Associated Date" in the misc. tab once it has been created.</p>'
	.'<hr/>'
	.'<p>This page should be displayed in <select name="page_vars[news_type]">'
	.'<option value="0">headline</option><option value="1"';
if (isset($vars['news_type']) && $vars['news_type']=='1') {
	$c.=' selected="selected"';
}
$c.='>calendar</option></select> mode.</p>';
$opts=array('summaries'=>'Summaries','full'=>'Full');
$c.='<p>Display news items in <select name="page_vars[news_display]">';
foreach ($opts as $val=>$name) {
	$c.='<option value="'.$val.'"';
	if (isset($vars['news_display'])&&$vars['news_display']==$val) {
		$c.=' selected="selected"';
	}
	$c.='>'.$name.'</option>';
}
$c.='</select></p>';
$c.='<p>Items to display per page: ';
$c.='<input type="text" class="small" name="page_vars[news_items]" value="';
$c.=(isset($vars['news_items']))?$vars['news_items']:5;
$c.='"/></p>';
$opts=array('yes'=>'Yes','no'=>'No');
$c.='<p>Show title and date for news items: <select name="'
	.'page_vars[news_title]">';
foreach ($opts as $val=>$name) {
	$c.='<option value="'.$val.'"';
	if (isset($vars['news_title'])&&$vars['news_title']==$val) {
		$c.=' selected="selected"';
	}
	$c.='>'.$name.'</option>';
}
$c.='</select></p>';
$opts=array('associated_date desc'=>'Date','ord asc'=>'Position in menu');
$c.='<p>Order items by: <select name="page_vars[news_order]">';
foreach ($opts as $val=>$name) {
	$c.='<option value="'.$val.'"';
	if (isset($vars['news_order'])&&$vars['news_order']==$val) {
		$c.=' selected="selected"';
	}
	$c.='>'.$name.'</option>';
}
$c.='</select></p>';
$c.='</div>';
// }
// { header
$c.='<div id="news-header">'
	.'<p>This text will appear above the news contents.</p>';
$c.=ckeditor('body', $page['body']);
$c.='</div>';
// }
// { footer
$c.='<div id="news-footer">'
	.'<p>This text will appear below the news contents.</p>';
$c.=ckeditor(
	'page_vars[footer]',
	(isset($vars['footer'])?$vars['footer']:'')
);
$c.='</div>';
// }
$c.='</div>';
