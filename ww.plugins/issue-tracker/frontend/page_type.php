<?php

$html='<div id="issuetracker-wrapper"></div>';
if (isset($PAGEDATA->vars['issue_tracker_see_all'])) {
	WW_addInlineScript(
		'var it_see_all='.$PAGEDATA->vars['issue_tracker_see_all'].';'
	);
}
if (isset($PAGEDATA->vars['issue_tracker_edit_all'])) {
	WW_addInlineScript(
		'var it_edit_all='.$PAGEDATA->vars['issue_tracker_edit_all'].';'
	);
}
WW_addScript('issue-tracker/str.js');
WW_addScript('issue-tracker/js.js');
WW_addScript('/j/uploader.js');
// { datatables
WW_addScript(
	'http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/'
	.'jquery.dataTables.min.js'
);
WW_addCSS(
	'http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/'
	.'jquery.dataTables.css'
);
WW_addCSS(
	'http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/'
	.'jquery.dataTables_themeroller.css'
);
// }
