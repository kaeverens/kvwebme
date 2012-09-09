<?php
/**
	* send an sms
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

echo '<table id="sms-send-table">'
	.'<tr><th><select id="sms_send_type"><option selected="selected">'
	.'Phone Number</option><option>Addressbook</option></select></th>'
	.'<td><div id="sms_single"><input id="sms_to" value="phone" />'
	.'<input id="sms_to_name" value="name (optional)" />'
	.'<a href="javascript:sms_choose_from_subscribers()">'
	.'choose from subscribers</a></div>'
	.'<select style="display:none" id="sms_addressbook_id">'
	.'<option value="0"> -- choose -- </option>';
$rs=dbAll('select id,name,subscribers from sms_addressbooks order by name');
foreach ($rs as $r) {
	$subs=json_decode($r['subscribers']);
	echo '<option value="'.$r['id'].'">'
	 .htmlspecialchars($r['name']).' ('.count($subs).')</option>';
}
echo '</select></td><td rowspan="3" id="sms_log"></td></tr>'
	.'<tr><th>Message</th><td><textarea style="width:400px;height:100px;" '
	.'id="sms_msg"></textarea></td></tr>'
	.'<tr><th></th><td><button>send</button></th></tr>'
	.'</table>';
?>
<p>If you use the code <code>[NAME]</code> in the message, it will be
automatically replaced with the recipient's name before sending.</p>
<p>Due to restrictions on SMS length and character encoding, you must
not use more than 160 characters in the message, and can only use the
characters a-zA-Z0-9 !_-.,:\'"</p>
<p>The phone number must be of the form 353861234567. That's the country
code plus the network code (minus the 0) plus the phone number.</p>
<script src="/ww.plugins/sms/admin/send-message.js"></script>
