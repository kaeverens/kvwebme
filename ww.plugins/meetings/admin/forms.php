<?php

echo '<table id="meeting-forms"><thead><tr><th>Name</th><th>Data</th>'
	.'<th>&nbsp;</th></tr></thead>';

$sql='select forms_nonpage.id id, name, count(meetings.id) as amt'
	.' from forms_nonpage, meetings where forms_nonpage.id=meetings.form_id';
$rs=dbAll($sql);
foreach ($rs as $r) {
	echo '<tr data-meeting-id="'.$r['id'].'">'
		.'<td>'.htmlspecialchars($r['name']).'</td>'
		.'<td><a href="#" class="meetings-view">View recorded data</a></td>'
		.'<td><a href="#" class="edit">Edit</a>'
		.' | <a href="#" class="delete">[x]</a></td></tr>';
}
echo '</table>';
WW_addScript('/ww.plugins/meetings/admin.js');
