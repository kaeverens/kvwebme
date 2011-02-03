<?php
/*
	Webme Mailing List Plugin v0.2
	File: admin/index.php
	Developer: Conor Mac Aoidh <http://macaoidh.name>
	Report Bugs: <conor@macaoidh.name>
*/

ww_addScript('/ww.plugins/mailing-list/files/admin.js');
echo admin_menu(array(
	'View List'=>$_url,
	'New Email'=>$_url.'&mailing_list=send_email',
	'Form Options'=>$_url.'&mailing_list=form_options'
));

echo '<div class="has-left-menu">';

$page=isset($_GET['mailing_list'])?$_GET['mailing_list']:'';
$dir=dirname(__FILE__);
switch($page){
	case 'form_options':
		include($dir.'/form_options.php');
	break;
	case 'delete':
		include($dir.'/actions.delete.php');
		include($dir.'/view_list.php');		
	break;
	case 'send_email':
		include($dir.'/actions.email.php');
	break;
	default:
		include($dir.'/view_list.php');
}
echo '</div>';
