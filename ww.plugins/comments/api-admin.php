<?php

function Comments_adminCaptchasSet() {
	$checked=isset($_REQUEST['value']);
	dbQuery('delete from site_vars where name="comments_no_captchas"');
	if ($checked) {
		dbQuery('insert into site_vars set name="comments_no_captchas", value="1"');
	}
}
function Comments_adminModerate() {
	$id = $_REQUEST['id'];
	if (!is_numeric($id)) {
		exit ('Invalid id');
	}
	$val = $_REQUEST['value'];
	if ($val==0||$val==1) {
		dbQuery('update comments set isvalid = '.$val.' where id = '.$id);
		Core_cacheClear('comments');
		return array(
			'status'=>1,
			'id'=>$id,
			'value'=>$val
		);
	}
	else {
		return array(
			'status'=>0,
			'message'=>'Invalid Value'
		);
	}
}
function Comments_adminDelete() {
	$id=(int)$_REQUEST['id'];
	dbQuery('delete from comments where id='.$id);
	return array(
		'status'=>1,
		'id'=>$id
	);
}
