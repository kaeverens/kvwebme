<?php
/*
        Webme News Plugin v0.1
        File: admin/display.php
        Developers:
					Conor Mac Aoidh http://conormacaoidh.com/
					Kae Verens      http://verens.com/
        Report Bugs:
					conor@kvsites.ie
					kae@verens.com
*/

$html='<p>Click <a href="javascript:;" onclick="window.parent.pages_new('
	.$page['id'].');">here</a> to create a new news item.</p>'
	.'<p>This page should be displayed in <select name="page_vars[news_type]">'
	.'<option value="0">headline</option><option value="1"';
if (isset($vars['news_type']) && $vars['news_type']=='1') {
	$html.=' selected="selected"';
}
$html.='>calendar</option></select> mode.</p>.';
$opts=array('summaries'=>'Summaries','full'=>'Full');
$html.='<p>Display news items in <select name="page_vars[news_display]">';
foreach($opts as $val=>$name){
	$html.='<option value="'.$val.'"';
	if(isset($vars['news_display'])&&$vars['news_display']==$val)
		$html.=' selected="selected"';
	$html.='>'.$name.'</option>';
}
$html.='</select></p>';
$html.='<p>Items to display per page: ';
$html.='<input type="text" class="small" name="page_vars[news_items]" value="';
$html.=(isset($vars['news_items']))?$vars['news_items']:5;
$html.='"/></p>';
$opts=array('yes'=>'Yes','no'=>'No');
$html.='<p>Show title and date for news items: <select name="page_vars[news_title]">';
foreach($opts as $val=>$name){
	$html.='<option value="'.$val.'"';
	if(isset($vars['news_title'])&&$vars['news_title']==$val)
		$html.=' selected="selected"';
	$html.='>'.$name.'</option>';
}
$html.='</select></p>';
$opts=array('associated_date desc'=>'Date','ord asc'=>'Position in menu');
$html.='<p>Order items by: <select name="page_vars[news_order]">';
foreach($opts as $val=>$name){
	$html.='<option value="'.$val.'"';
	if(isset($vars['news_order'])&&$vars['news_order']==$val)
		$html.=' selected="selected"';
	$html.='>'.$name.'</option>';
}
$html.='</select></p>';
