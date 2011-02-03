<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!is_admin()) {
	die('access denied');
}

if(isset($_REQUEST['get_menu'])){
	$r=dbRow('select * from menus where id='.(int)$_REQUEST['get_menu']);
	if($r===false)$r=array(
		'parent'=>0,
		'direction'=>0
	);
	if($r['parent'])$r['parent_name']=Page::getInstance($r['parent'])->name;
	else $r['parent_name']=' -- none -- ';
	echo json_encode($r);
	exit;
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
	$sql="menus set type='$type',parent='$parent',direction='$direction',"
		."background='$background',opacity=$opacity,columns=$columns,"
		."style_from=$style_from";
	if($id){
		$sql="update $sql where id=$id";
		dbQuery($sql);
	}
	else{
		$sql="insert into $sql";
		dbQuery($sql);
		$id=dbOne('select last_insert_id() as id','id');
	}
	$ret=array('id'=>$id,'id_was'=>$id_was);
	echo json_encode($ret);
	exit;
}

if(isset($_REQUEST['id']))$id=(int)$_REQUEST['id'];
else $id=0;
echo '<a href="javascript:;" id="menu_editlink_'.$id.'" class="menu_editlink">view or edit menu</a>';
