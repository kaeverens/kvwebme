<?php
/**
	* front controller for stats page
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

require_once 'header.php';
require_once 'stats/lib.php';
echo '<h1>'.__('Website Statistics').'</h1>';

echo Core_adminSideMenu(
	array(
		__('Summary')=>'stats.php?page=summary',
		__('Popular Pages')=>'stats.php?page=popular_pages'
	)
);

echo '<div class="pages_iframe">';
$page=isset($_REQUEST['page'])?$_REQUEST['page']:'';
switch ($page) {
	case 'popular_pages': // {
		require_once 'stats/popular_pages.php';
	break; // }
	default: // {
		require_once 'stats/summary.php';
		// }
}
echo '</div>';
require 'footer.php';
