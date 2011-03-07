<?php

echo '<h2>Privacy</h2>';

echo '<table>';
// { restrict access to members of these group
echo '<tr><th>Page is viewable only by logged-in users:</th><td>';
echo '<input type="checkbox" name="page_vars[privacy_require_login]"';
if (isset($page_vars['privacy_require_login'])) {
	echo ' checked="checked"';
}
echo '/></td></tr>';
// }
// { restrict access to members of these group
echo '<tr><th>Page is viewable only by members of these groups:</th><td>';
$rs=dbAll('select * from groups order by name');
$restrict_to=array();
if (isset($page_vars['restrict_to_groups'])
	&& $page_vars['restrict_to_groups']!=''
) {
	$restrict_to=json_decode($page_vars['restrict_to_groups']);
}
foreach ($rs as $r) {
	echo '<input type="checkbox" '
		.'name="page_vars[restrict_to_groups]['.$r['id'].']"';
	if (isset($restrict_to->$r['id'])) {
		echo ' checked="checked"';
	}
	echo ' />'.htmlspecialchars($r['name']).'<br />';
}
echo '</td></tr>';
// }
// { optionally allow non-logged-in readers to view the page if they know a password
if (!isset($page_vars['privacy_password'])) {
	$page_vars['privacy_password']='';
}
echo '<tr><th>Allow non-logged-in readers to view the page if they enter this password:</td>'
	.'<td><input name="page_vars[privacy_password]" value="'
	.htmlspecialchars($page_vars['privacy_password'])
	.'" /></td></tr>';
// }
echo '</table>';
