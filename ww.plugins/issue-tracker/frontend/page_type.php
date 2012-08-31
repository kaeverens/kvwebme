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
WW_addScript('/j/jquery.dataTables-1.7.5/jquery.dataTables.min.js');
WW_addCSS('/j/jquery.dataTables-1.7.5/jquery.dataTables.css');
WW_addScript('/j/uploader.js');
