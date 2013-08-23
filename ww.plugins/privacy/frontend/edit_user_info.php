<?php
/**
	* displays editable user info
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Conor Mac Aoidh <conormacaoidh@gmail.com>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

require '../../../ww.incs/basics.php';

$id = (int)@$_SESSION[ 'userdata' ][ 'id' ];
if ($id == 0) {
	Core_quit();
}

$user = dbRow('select * from user_accounts where id=' . $id);
$c=json_decode($user['contact'], true);
echo '
<p id="error"></p>
<table>
	<tr>
		<th>'.__('Name').':</th>
		<td>
			<input type="text" value="' . $user['name'] . '" name="user-name"/>
		</td>
	</tr>
	<tr>
		<th>'.__('Phone').':</th>
		<td>
			<input type="text" value="' . $c['phone'] . '" name="user-phone"/>
		</td>
	</tr>
</table>';
