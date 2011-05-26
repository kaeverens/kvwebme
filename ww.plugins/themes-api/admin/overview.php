<?php

/**
 * admin/overview.php, KV-Webme Themes Repository
 *
 * gives a brief overview of the repository
 *
 * @author     Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @license    GPL 2.0
 * @version    1.0
 */

/**
 * get the count of moderated and awaiting
 * moderation themes
 */
$approved = 0;
$moderate = 0;
for( $i = 0; $i < count( $themes ); ++$i ){
	if( $themes[ $i ][ 'moderated' ] == 'yes' )
		++$approved;
	else
		++$moderate;
}

/**
 * figure out who is in the moderation team
 */
$id = dbOne( 'select id from groups where name="moderators"', 'id' );
$users = dbAll( 'select name from user_accounts, users_groups where groups_id=' . $id . ' and user_accounts_id=id' );
$list = array( );
for( $i = 0; $i < count( $users ); array_push( $list, $users[ $i ][ 'name' ] ), ++$i );

echo '
<h2>Overview</h2>
<table>
	<tr>
		<th>Total Themes:</th>
		<td>' . count( $themes ) . '</td>
	</tr>
	<tr>
		<th>Approved Themes:</th>
		<td>' . $approved . '</td>
	</tr>
	<tr>
		<th>Awaiting Moderation:</th>
		<td>' . $moderate . '</td>
	</tr>
	<tr>
		<th>Theme Moderators:</th>
		<td>' . implode( ',', $list ) . '</td>
	</tr>
</table>';


?>
