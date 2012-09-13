<?php
/**
	* mailing list admin section
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Conor Mac Aoidh <conor@macaoidh.name>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

ww_addScript('/ww.plugins/mailing-list/files/admin.js');
echo Core_adminSideMenu(
	array(
		'View List'=>$_url,
		'New Email'=>$_url.'&mailing_list=send_email',
		'Form Options'=>$_url.'&mailing_list=form_options'
	)
);

echo '<em>This plugin is deprecated.'
	.' Please use the Mailinglist (no hyphen) plugin instead.</em>';
echo '<div class="pages_iframe">';

$page=isset($_GET['mailing_list'])?$_GET['mailing_list']:'';
$dir=dirname(__FILE__);
switch($page){
	case 'form_options':
		require_once $dir.'/form_options.php';
	break;
	case 'delete':
		require_once $dir.'/actions.delete.php';
		require_once $dir.'/view_list.php';
	break;
	case 'send_email':
		require_once $dir.'/actions.email.php';
	break;
	default:
		require_once $dir.'/view_list.php';
}
echo '</div>';
