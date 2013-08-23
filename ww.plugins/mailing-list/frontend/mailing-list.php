<?php
/**
	* Webme Mailing List Plugin v0.2
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Conor MacAoidh <conor@macaoidh.name>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

// { Mailinglist_addPersonToDatabase

/**
	* add a person to the database
	*
	* @param string $email  person's email address
	* @param string $name   name of the person
	* @param string $mobile person's mobile number
	*
	* @return null
	*/
function Mailinglist_addPersonToDatabase($email, $name, $mobile) {
	if ($name=='__empty__') {
		$name='not collected';
	}
	if ($mobile=='__empty__') {
		$mobile='not collected';
	}
	$hash=mt_rand().mt_rand().mt_rand();
	dbQuery(
		'insert into mailing_list set email="'.addslashes($email).'", name="'
		.addslashes($name).'", status="Pending", hash="'.$hash.'", mobile="'
		.addslashes($mobile).'"'
	);
	return $hash;
}

// }
// { Mailinglist_checkNameAndEmail

/**
	* check person's email and name
	*
	* @param string $email email address to check format of
	* @param string $name  name to check
	*
	* @return null
	*/
function Mailinglist_checkNameAndEmail($email, $name) {
	if ($name=='') {
		return false;
	}
	return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// }
// { Mailinglist_createForm

/**
	* function for creating the form for the mailinglist
	*
	* @return null
	*/
function Mailinglist_createForm() {
	$f='';
	$fields=dbAll('select name,value from mailing_list_options');
	foreach ($fields as $field) {
		$FIELD[$field['name']]=$field['value'];
	}
	$f='<form id="mailing_list" method="post">'
		.'<input onfocus="if(this.value==\''.$FIELD['inp_em']
		.'\')this.value=\'\'" onblur="if(this.value==\'\')this.value=\''
		.$FIELD['inp_em'].'\'" value="'.$FIELD['inp_em']
		.'" id="email" type="text" name="mailing_email"/>';
	if ($FIELD['col_name']==1) {
		$f.='<input type="text" name="name" value="'.$FIELD['inp_nm']
			.'" id="mailing_name" onfocus="if(this.value==\''.$FIELD['inp_nm']
			.'\')this.value=\'\'" onblur="if(this.value==\'\')this.value=\''
			.$FIELD['inp_nm'].'\'"/>';
	}
	if ($FIELD['col_mobile']==1) {
		$f.='<input type="text" name="mobile" value="'.$FIELD['inp_mb']
			.'" id="mailing_mobile" onfocus="if(this.value==\''.$FIELD['inp_mb']
			.'\')this.value=\'\'" onblur="if(this.value==\'\')this.value=\''
			.$FIELD['inp_mb'].'\'"/>';
	}
	if ($FIELD['dis_sub']==1) {
		$f.='<input type="submit" name="submit" value="'.$FIELD['inp_sub']
			.'" id="mailing_submit"/>';
	}
	$f.='</form>';
	return $f;
}

// }
// { Mailinglist_sendConfirmation

/**
	* send a confirmation email
	*
	* @param string $email email address to send the confirmation to
	* @param string $hash  hash key for verification
	*
	* @return null
	*/
function Mailinglist_sendConfirmation($email, $hash) {
	$data=dbAll('select name,value from mailing_list_options');
	foreach ($data as $d) {
		$EMAIL[$d['name']]=$d['value'];
	}
	if ($_SERVER['HTTPS']=='on') {
		$http='https';
	}
	else {
		$http='http';
	}
	$url = $http.'://'.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	$EMAIL['body']=str_replace(
		'%link%',
		$url.'?mailing_list_hash='.$hash,
		$EMAIL['body']
	);
	Core_mail(
		$email,
		$EMAIL['subject'],
		$EMAIL['body'],
		$EMAIL['from']
	);
}

// }
// { Mailinglist_showAlert

/**
	* show alert
	*
	* @param string $text text to alert
	*
	* @return null
	*/
function Mailinglist_showAlert($text) {
	return '<script defer="defer">fAlert(\''.$text.'\');</script>';
}

// }
// { Mailinglist_showForm2

/**
	* function for showing the form for the mailinglist
	*
	* @return null
	*/
function Mailinglist_showForm2() {
	WW_addScript('mailing-list/files/impromptu.jquery.min.js');
	WW_addScript('mailing-list/files/general.js');
	WW_addCSS('/ww.plugins/mailing-list/files/mailing-list.css');
	$html=Mailinglist_createForm();
	if (isset($_GET['mailing_list_hash'])) {
		$hash=$_GET['mailing_list_hash'];
		$email=dbQuery('select email from mailing_list where hash="'.$hash.'"');
		if (count($email)!=1) {
			$html.=Mailinglist_showAlert('Error. Invalid link provided');
		}
		else {
			dbQuery(
				'update mailing_list set status="Activated" where hash="'.$hash.'"'
			);
			$html.=Mailinglist_showAlert('Thank You, Email added to the list.');
		}
	}
	elseif (isset($_POST['submit'])) {
		$email.=$_POST['mailing_email'];
		if (isset($_POST['name'])) {
			$name=$_POST['name'];
		}
		else {
			$name='__empty__';
		}
		if (isset($_POST['mobile'])) {
			$mobile=$_POST['mobile'];
		}
		else {
			$mobile='__empty__';
		}
		$valid=Mailinglist_checkNameAndEmail($email, $name);
		if ($valid==true) {
			$hash=Mailinglist_addPersonToDatabase($email, $name, $mobile);
			Mailinglist_sendConfirmation($email, $hash);
			$html.=Mailinglist_showAlert(
				'Thank You! A confirmation email has been sent to '.$email
			);
		}
		else {
			$html.=Mailinglist_showAlert('Error. Invalid details.');
		}
	}
	return $html;
}

// }
