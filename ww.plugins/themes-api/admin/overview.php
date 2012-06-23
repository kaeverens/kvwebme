<?php
/**
	* gives a brief overview of the repository
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Conor Mac Aoidh <conormacaoidh@gmail.com>
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

/**
 * get the count of moderated and awaiting
 * moderation themes
 */
$approved = 0;
$moderate = 0;
for ($i = 0; $i < count($themes); ++$i) {
	if ($themes[ $i ][ 'moderated' ] == 'yes') {
		++$approved;
	}
	else {
		++$moderate;
	}
}

/**
 * figure out who is in the moderation team
 */
$id = dbOne('select id from groups where name="moderators"', 'id');
$users = dbAll(
	'select name from user_accounts, users_groups where groups_id=' 
	. $id . ' and user_accounts_id=id'
);
$list = array( );
for ( $i=0; $i<count($users); ++$i) {
	array_push($list, $users[$i]['name'])
}

echo '
<h2>'.__('Overview').'</h2>
<table>
	<tr>
		<th>'.__('Total Themes:').'</th>
		<td>' . count($themes) . '</td>
	</tr>
	<tr>
		<th>'.__('Approved Themes:').'</th>
		<td>' . $approved . '</td>
	</tr>
	<tr>
		<th>'.__('Awaiting Moderation:').'</th>
		<td>' . $moderate . '</td>
	</tr>
	<tr>
		<th>'.__('Theme Moderators:').'</th>
		<td>' . implode(',', $list) . '</td>
	</tr>
</table>';
