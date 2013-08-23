<?php

// { Forms_adminGet

/**
	* retrieve a non-page form
	*
	* @return object
	*/
function Forms_adminGet() {
	$id=(int)$_REQUEST['id'];
	return dbRow('select * from forms_nonpage where id='.$id);
}

// }
// { Forms_adminFormEdit

/**
	* edit a form
	*
	* @return array
	*/
function Forms_adminFormEdit() {
	$id=(int)$_REQUEST['id'];
	$name=$_REQUEST['name'];
	$fields=$_REQUEST['fields'];
	$template=$_REQUEST['template'];
	$sql=$id?'update':'insert into';
	$sql.=' forms_nonpage set name="'.addslashes($name).'"'
		.', fields="'.addslashes(json_encode($fields)).'"'
		.', template="'.addslashes($template).'"';
	$sql.=$id?' where id='.$id:'';
	dbQuery($sql);
	return array(
		'ok'=>true
	);
}

// }
// { Forms_adminFormsList

/**
	* get a list of all non-page forms
	*
	* @return array
	*/
function Forms_adminFormsList() {
	return dbAll('select * from forms_nonpage order by name');
}

// }
