<?php
/**
	* if only one online store is found, redirects to that page
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     None
	*/

$ids=dbAll('select id from pages where type like "online-store%"');
if (count($ids)>1) {
	/* TODO - translation /CB */
	echo '<p>Please choose the online store you want to administrate.</p><ul>';
	foreach ($ids as $id) {
		$page=Page::getInstance($id['id']);
		echo '<li><a href="/ww.admin/pages.php?id='.$id['id'].'">'
			.$page->getRelativeUrl().'</a></li>';
	}
	echo '</ul>';
}
else if (count($ids)==1) {
	redirect('/ww.admin/pages.php?id='.$ids[0]['id']);
}
else {
	/* TODO - translation /CB */
	echo '<em>No page of type Online Store created. '
		.'Please <a href="/ww.admin/pages.php">create one</a>.</em>';
}
