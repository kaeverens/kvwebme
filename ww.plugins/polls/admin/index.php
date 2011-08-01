<?php
ww_addScript('/ww.plugins/polls/admin/polls.js');
echo admin_menu(
	array('New'=>$_url.'&action=newPoll', 'View All'=>$_url)
);

echo '<div class="has-left-menu">';
$edit=($action=='editPoll')?1:0;
$dir=dirname(__FILE__);
switch($action){
	case 'deletePoll': // {
		if ($id) {
			require_once $dir.'/actions.delete.php';
		}
		require_once $dir.'/showitems.php';
	break; // }
	case 'Edit Poll': // {
		require_once $dir.'/actions.edit.php';
		require_once $dir.'/forms.php';
	break; // }
	case 'Create Poll': // {
		require_once $dir.'/actions.new.php';
		require_once $dir.'/forms.php';
	break; // }
	case 'newPoll':case 'editPoll': // {
		require_once $dir.'/forms.php';
	break; // }
	default: // {
		require_once $dir.'/showitems.php';
		// }
}
echo '</div>';
