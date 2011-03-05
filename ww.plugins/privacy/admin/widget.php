<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if(!is_admin())die('access denied');

$id=isset($_REQUEST['id'])?(int)$_REQUEST['id']:0;

// { login page
echo '<strong>registration page</strong><br />';
$ua_pages=dbAll('select id,name from pages where type="privacy" order by name');
if(!count($ua_pages)){
	echo 'no User Authentication pages created. please <a href="/ww.admin/pages.php">create one</a> first.';
	exit;
}
echo '<select name="id"><option value=""> -- registration page -- </option>';
foreach($ua_pages as $b){
	echo '<option value="'.$b['id'].'"';
	if($id==$b['id'])echo ' selected="selected"';
	echo '>'.htmlspecialchars($b['name']).'</option>';
}
echo '</select><br />';
// }
// { facebook
echo '<strong>allow logins using facebook</strong>'
	.'<select name="external_login"><option value="0">no</option>'
	.'<option value="1"';
if (isset($_REQUEST['external_login'])
	&& $_REQUEST['external_login']=='1'
) {
	echo ' selected="selected"';
}
echo '>yes</option></select><br />';
if (isset($_REQUEST['external_login'])
	&& $_REQUEST['external_login']=='1'
) {
	// { facebook api key
	echo '<strong>app ID</strong>';
	$fbappid=isset($_REQUEST['fbappid'])?$_REQUEST['fbappid']:'';
	echo '<input name="fbappid" value="'.htmlspecialchars($fbappid).'" /><br />';
	// }
	// { facebook api key
	echo '<strong>app secret</strong>';
	$fbsecret=isset($_REQUEST['fbsecret'])?$_REQUEST['fbsecret']:'';
	echo '<input name="fbsecret" value="'.htmlspecialchars($fbsecret).'" /><br />';
	// }
}
// }
