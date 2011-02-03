<?php
/*
        Webme News Plugin v0.1
        File: admin/display.php
        Developers:
					Conor Mac Aoidh http://macaoidh.name/
					Kae Verens      http://verens.com/
        Report Bugs:
					conor@macaoidh.name
					kae@verens.com
*/

$html.='<p>Click <a href="javascript:;" onclick="window.parent.pages_new('.$page->id.');">here</a> to create a new news item.</p>'
	.'<p>This page should be displayed in <select name="page_vars[news_type]"><option value="0">headline</option><option value="1"';
if ($vars['news_type']=='1') {
	$html.=' selected="selected"';
}
$html.='>calendar</option></select> mode.</p>.';
