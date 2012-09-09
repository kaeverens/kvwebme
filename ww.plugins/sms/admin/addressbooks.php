<?php
/**
	* SMS addressbooks admin
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

$clink='<a href="javascript:sms_edit(0);">Create Addressbook</a>';
echo $clink;

$rs=dbAll('select * from sms_addressbooks order by name');
if (count($rs)) { // show addressbooks
	echo '<table><tr><th>Name</th><td>Created Date</td><td>Subscribers</td></tr>';
	foreach ($rs as $r) {
		if ($r['subscribers']=='') {
			$r['subscribers']='[]';
		}
		$ss=json_decode($r['subscribers']);
		echo '<tr id="sms_row_'.$r['id'].'">'
			.'<td>'.htmlspecialchars($r['name']).'</td>'
			.'<td>'.Core_dateM2H($r['date_created']).'</td>'
			.'<td>'.count($ss).'</td>'
			.'<td><a href="javascript:sms_edit('.$r['id'].')">edit</a></td>'
			.'<td><a href="javascript:sms_delete('.$r['id'].')">[x]</a></td>'
			.'</tr>';
	}
	echo '</table>';
}
else{
	echo '<em>No addressbooks exist yet. Please create one: '.$clink.'</em>';
}
echo '</div>';

echo '<script src="/ww.plugins/sms/admin/addressbooks.js"></script>';
