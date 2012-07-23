<?php

$filters=array();
if (isset($_REQUEST['from_date'])) {
	$filters[]='meeting_date>"'.addslashes($_REQUEST['from_date']).'"'
		.' and meeting_date<"'.addslashes($_REQUEST['from_date']).' 24"';
}

$sql='select * from meetings';
if (count($filters)) {
	$sql.=' where ('.join(') and (', $filters).')';
}
$sql.=' order by meeting_time';
$meetings=dbAll($sql);

echo '<table id="meetings">'
	.'<thead><tr><th>Meeting Time</th><th>Who</th><th>Is Meeting Who</th>'
	.'<th>Question List</th><th>&nbsp;</th></tr></thead>';
echo '<tbody>';
foreach ($meetings as $meeting) {
	$user=User::getInstance($meeting['user_id'], false, false);
	$customer=User::getInstance($meeting['customer_id'], false, false);
	$formname=dbOne(
		'select name from forms_nonpage where id='.$meeting['form_id'], 'name'
	);
	$username=$user?$user->get('name'):'no such user';
	$customername=$customer?$customer->get('name'):'no such user';
	echo '<tr id="meeting-'.$meeting['id'].'">'
		.'<td>'.Core_dateM2H($meeting['meeting_time'], 'datetime').'</td>'
		.'<td>'.htmlspecialchars($username).'</td>'
		.'<td>'.htmlspecialchars($customername).'</td>'
		.'<td>'.$formname.'</td>'
		.'<td><a href="#" class="edit">'.__('Edit').'</a>'
		.' | <a href="#" class="delete">'.__('[x]').'</a></td>'
		.'</tr>';
}
echo '</tbody></table>';
echo '<button id="meetings-create">'.__('Create').'</button>';

WW_addScript('/ww.plugins/meetings/admin.js');
