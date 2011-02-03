<?php
require 'header.php';
require 'stats/lib.php';
echo '<h1>'.__('Website Statistics').'</h1>';

echo admin_menu(array(
	'Summary'=>'stats.php?page=summary',
	'Popular Pages'=>'stats.php?page=popular_pages'
));

echo '<div class="has-left-menu">';
$page=isset($_REQUEST['page'])?$_REQUEST['page']:'';
switch($page){
	case 'popular_pages': // {
		require 'stats/popular_pages.php';
		break;
	// }
	default: // {
		require 'stats/summary.php';
	// }
}
echo '</div>';
require 'footer.php';
