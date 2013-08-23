<?php
/**
	* panels admin API
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

// { Panels_adminSave

/**
	* save a panel
	*
	* @return null
	*/
function Panels_adminSave() {
	$id=(int)$_REQUEST['id'];
	$widgets=addslashes($_REQUEST['data']);
	dbQuery("update panels set body='$widgets' where id=$id");
	Core_cacheClear('panels');
	Core_cacheClear('pages');
}

// }
// { Panels_adminVisibilityGet

/**
	* get visibility of panel
	*
	* @return array
	*/
function Panels_adminVisibilityGet() {
	$visible=array();
	$hidden=array();
	if (isset($_REQUEST['id'])) {
		$id=(int)$_REQUEST['id'];
		$r=dbRow("select visibility,hidden from panels where id=$id");
		if (is_array($r) && count($r)) {
			if ($r['visibility']) {
				$visible=json_decode($r['visibility']);
			}
			if ($r['hidden']) {
				$hidden=json_decode($r['hidden']);
			}
		}
	}
	if (isset($_REQUEST['visibility']) && $_REQUEST['visibility']) {
		$visible=explode(',', $_REQUEST['visibility']);
	}
	if (isset($_REQUEST['hidden']) && $_REQUEST['hidden']) {
		$hidden=explode(',', $_REQUEST['hidden']);
	}
	return array(
		'visible'=>Panels_selectChildPages(0, 1, $visible, 0),
		'hidden'=>Panels_selectChildPages(0, 1, $hidden, 0)
	);
}

// }
// { Panels_selectChildPages

/**
	* select list of sub-pages as select options
	*
	* @param int   $i      parent ID
	* @param int   $n      depth of child
	* @param array $s      don't remember
	* @param int   $id     already selected page
	* @param int   $prefix something or other
	*
	* @return html
	*/
function Panels_selectChildPages($i=0, $n=1, $s=array(), $id=0, $prefix='') {
	$q=dbAll(
		'select name,id from pages where parent="'.$i.'" and id!="'.$id
		.'" order by ord,name'
	);
	if (count($q)<1) {
		return;
	}
	$html='';
	foreach ($q as $r) {
		if ($r['id']!='') {
			$html.='<option value="'.$r['id'].'" title="'
				.htmlspecialchars($r['name']).'"';
			$html.=(in_array($r['id'], $s))?' selected="selected">':'>';
			$name=strtolower(str_replace(' ', '-', $r['name']));
			$html.= htmlspecialchars($prefix.$name).'</option>';
			$html.=Panels_selectChildPages($r['id'], $n+1, $s, $id, $name.'/');
		}
	}
	return $html;
}

// }
