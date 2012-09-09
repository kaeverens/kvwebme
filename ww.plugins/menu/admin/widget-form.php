<?php
/**
	* admin for Menu widget
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!Core_isAdmin()) {
	die(__('access denied'));
}

if (isset($_REQUEST['get_menu'])) {
	$r=dbRow('select * from menus where id='.(int)$_REQUEST['get_menu']);
	if ($r===false) {
		$r=array(
			'parent'=>0,
			'direction'=>0,
			'state'=>0
		);
	}
	if ($r['parent']>0) {
		$r['parent_name']=__FromJson(Page::getInstance($r['parent'])->name);
	}
	else {
		if ($r['parent']==0) {
			$r['parent_name']=' -- '.__('none').' -- ';
		}
		else {
			$r['parent_name']=' -- '.__('current page').' -- ';
		}
	}
	echo json_encode($r);
	Core_quit();
}
if (isset($_REQUEST['action']) && $_REQUEST['action']=='save') {
	$id=(int)$_REQUEST['id'];
	$id_was=$id;
	$parent=(int)$_REQUEST['parent'];
	$direction=(int)$_REQUEST['direction'];
	$type=(int)$_REQUEST['type'];
	$background=addslashes($_REQUEST['background']);
	$opacity=(float)$_REQUEST['opacity'];
	$columns=(int)$_REQUEST['columns'];
	$style_from=(int)$_REQUEST['style_from'];
	$state=(int)$_REQUEST['state'];
	$sql="menus set type='$type',parent='$parent',direction='$direction',"
		."background='$background',opacity=$opacity,columns=$columns,"
		."style_from=$style_from,state=$state";
	if ($id) {
		$sql="update $sql where id=$id";
		dbQuery($sql);
	}
	else{
		$sql="insert into $sql";
		dbQuery($sql);
		$id=dbOne('select last_insert_id() as id', 'id');
	}
	Core_cacheClear('menus');
	$ret=array('id'=>$id,'id_was'=>$id_was);
	echo json_encode($ret);
	Core_quit();
}

if (isset($_REQUEST['id'])) {
	$id=(int)$_REQUEST['id'];
}
else {
	$id=0;
}
echo '<a href="javascript:;" id="menu_editlink_'.$id.'" '
	.'class="menu_editlink">'.__('view or edit menu').'</a>';
