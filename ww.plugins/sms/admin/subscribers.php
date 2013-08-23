<?php
/**
	* front controller for WebME files
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

if (!Core_isAdmin()) {
	Core_quit();
}
require SCRIPTBASE.'ww.plugins/sms/admin/libs.php';

echo '<div id="sms_wrapper">';

$clink='<a href="javascript:sms_edit(0);">Add Subscriber</a>';
echo $clink;

$rs=dbAll('select * from sms_subscribers order by name');
if (count($rs)) { // show subscribers
	echo '<table class="datatable"><thead><tr>'
		.'<th>Name</th><th>Phone</th><th>Created Date</th><th></th><th></th>'
		.'</tr></thead><tbody>';
	foreach ($rs as $r) {
		if (!isset($r['subscribers']) || $r['subscribers']=='') {
			$r['subscribers']='[]';
		}
		$ss=json_decode($r['subscribers']);
		echo '<tr id="sms_row_'.$r['id'].'">'
			.'<td>'.htmlspecialchars($r['name']).'</td>'
			.'<td>'.htmlspecialchars($r['phone']).'</td>'
			.'<td>'.Core_dateM2H($r['date_created']).'</td>'
			.'<td><a href="javascript:sms_edit('.$r['id'].')">edit</a></td>'
			.'<td><a href="javascript:sms_delete('.$r['id'].')">[x]</a></td>'
			.'</tr>';
	}
	echo '</tbody></table>';
}
else {
	echo '<em>No subscribers exist yet. Please create one: '.$clink.'</em>';
}
echo '</div>';

echo '<script src="/ww.plugins/sms/admin/subscribers.js"></script>';
