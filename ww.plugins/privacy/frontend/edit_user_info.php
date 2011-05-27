<?php

/**
 * frontend/edit_user_info.php, KV-Webme Privacy Plugin
 *
 * displays editable user info
 *
 * @author     Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @license    GPL 2.0
 * @version    1.0
 */


require '../../../ww.incs/basics.php';

$id = (int)@$_SESSION[ 'userdata' ][ 'id' ];
if( $id == 0 )
	exit;

$user = dbRow( 'select * from user_accounts where id=' . $id );

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
	<tr>
		<th>Address:</th>	
		<td>
			<textarea name="user-address">' . $user[ 'address' ] . '</textarea>
		</td>
	</tr>
</table>';

?>
