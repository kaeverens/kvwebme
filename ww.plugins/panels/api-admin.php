<?php
function Panels_adminSave() {
	$id=(int)$_REQUEST['id'];
	$widgets=addslashes($_REQUEST['data']);
	dbQuery("update panels set body='$widgets' where id=$id");
	Core_cacheClear('panels');
	Core_cacheClear('pages');
}
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
		'visible'=>panel_selectkiddies(0, 1, $visible, 0),
		'hidden'=>panel_selectkiddies(0, 1, $hidden, 0)
	);
}
function panel_selectkiddies($i=0, $n=1, $s=array(), $id=0, $prefix='') {
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
			$html.=panel_selectkiddies($r['id'], $n+1, $s, $id, $name.'/');
		}
	}
	return $html;
}
