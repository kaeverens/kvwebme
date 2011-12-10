<?php
/**
	* common page admin functions
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

/**
	* build up a selectbox of pages and their child nodes
	*
	* @param int $i  parent of the child nodes to show
	* @param int $n  level of indentation to show
	* @param int $s  currently selected page's ID (if any)
	* @param int $id page NOT to show in the list
	*
	* @return null
	*/
function selectkiddies($i=0, $n=1, $s=0, $id=0) {
	$q=dbAll(
		'select name,id,alias from pages where parent="'.$i.'" and id!="'.$id
		.'" order by ord,name'
	);
	if (count($q)<1) {
		return;
	}
	foreach ($q as $r) {
		if ($r['id']!='') {
			echo '<option value="'.$r['id'].'" title="'
				.htmlspecialchars($r['name']).'"';
			echo ($s==$r['id'])?' selected="selected">':'>';
			echo str_repeat('&raquo; ', $n);
			$name=$r['alias'];
			if (strlen($name)>20) {
				$name=substr($name, 0, 17).'...';
			}
			echo htmlspecialchars($name).'</option>';
			selectkiddies($r['id'], $n+1, $s, $id);
		}
	}
}
