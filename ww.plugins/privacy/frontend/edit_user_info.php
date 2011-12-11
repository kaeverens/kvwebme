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
	exit;
}

$user = dbRow('select * from user_accounts where id=' . $id);

echo '
<p id="error"></p>
<table>
	<tr>
		<th>Name:</th>
		<td>
			<input type="text" value="' . $user[ 'name' ] . '" name="user-name"/>
		</td>
	</tr>
	<tr>
		<th>Phone:</th>	
		<td>
			<input type="text" value="' . $user[ 'phone' ] . '" name="user-phone"/>
		</td>
	</tr>
</table>';

?>
