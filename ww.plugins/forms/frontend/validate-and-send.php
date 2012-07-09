<?php
/**
  * scripts for validating and sending forms
  *
  * PHP Version 5
  *
  * @category   None
  * @package    None
  * @subpackage None
  * @author     Kae Verens <kae@kvsites.ie>
  * @license    GPL Version 2
  * @link       www.kvweb.me
 */

if (!function_exists('mime_content_type')) {
	/**
		* create function mime_content_type if it's not already there
		*
		* @param string $filename the filename to test
		*
		* @return string the mime type
		*/
	function mime_content_type($filename) {

		$mime_types = array(

			'txt' => 'text/plain',
			'htm' => 'text/html',
			'html' => 'text/html',
			'php' => 'text/html',
			'css' => 'text/css',
			'js' => 'application/javascript',
			'json' => 'application/json',
			'xml' => 'application/xml',
			'swf' => 'application/x-shockwave-flash',
			'flv' => 'video/x-flv',

			// images
			'png' => 'image/png',
			'jpe' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'jpg' => 'image/jpeg',
			'gif' => 'image/gif',
			'bmp' => 'image/bmp',
			'ico' => 'image/vnd.microsoft.icon',
			'tiff' => 'image/tiff',
			'tif' => 'image/tiff',
			'svg' => 'image/svg+xml',
			'svgz' => 'image/svg+xml',

			// archives
			'zip' => 'application/zip',
			'rar' => 'application/x-rar-compressed',
			'exe' => 'application/x-msdownload',
			'msi' => 'application/x-msdownload',
			'cab' => 'application/vnd.ms-cab-compressed',

			// audio/video
			'mp3' => 'audio/mpeg',
			'qt' => 'video/quicktime',
			'mov' => 'video/quicktime',

			// adobe
			'pdf' => 'application/pdf',
			'psd' => 'image/vnd.adobe.photoshop',
			'ai' => 'application/postscript',
			'eps' => 'application/postscript',
			'ps' => 'application/postscript',

			// ms office
			'doc' => 'application/msword',
			'rtf' => 'application/rtf',
			'xls' => 'application/vnd.ms-excel',
			'ppt' => 'application/vnd.ms-powerpoint',

			// open office
			'odt' => 'application/vnd.oasis.opendocument.text',
			'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
		);

		$ext = strtolower(array_pop(explode('.', $filename)));
		if (array_key_exists($ext, $mime_types)) {
			return $mime_types[$ext];
		}
		elseif (function_exists('finfo_open')) {
			$finfo = finfo_open(FILEINFO_MIME);
			$mimetype = finfo_file($finfo, $filename);
			finfo_close($finfo);
			return $mimetype;
		}
		else {
			return 'application/octet-stream';
		}
	}
}

// { Form_readonly

/**
  * get a readonly version of the form (for sending as email)
  *
  * @param array $page_id      page db row
  * @param array &$vars        page meta data
	* @param array &$form_fields array of fields
  *
  * @return HTML of the form
  */
function Form_readonly($page_id, &$vars, &$form_fields) {
	if (!isset($_SESSION['forms'])) {
		$_SESSION['forms']=array();
	}
	$c='';
	// { set up delimiters
	$vals_wrapper_start='<table>';
	$vals_field_start='<tr><th>';
	$vals_field_middle='</th><td>';
	$vals_field_end='</td></tr>';
	$vals_2col_start='<tr><td colspan="2">';
	$vals_2col_end='</td></tr>';
	$vals_wrapper_end='</table>';
	// }
	if (@$vars['forms_template'] && @strpos($vars['forms_template'], '{{')===false) {
		@$vars['forms_template']='';
	} // }}
	if (!@$vars['forms_template']||@$vars['forms_template']=='&nbsp;') {
		$c.='<div>'.$vals_wrapper_start;
	}
	$required=array();
	$cnt=0;
	foreach ($form_fields as $r2) {
		$name=preg_replace('/[^a-zA-Z0-9_]/', '', $r2['name']);
		$class='';
		if ($r2['isrequired']) {
			$required[]=$name.','.$r2['type'];
			$class=' required';
		}
		if (isset($_REQUEST[$name])) {
			$_SESSION['forms'][$name]=$_REQUEST[$name];
		}
		$val=Form_valueDefault($name);
		if (!isset($_REQUEST[$name])) {
			$_REQUEST[$name]='';
		}
		switch ($r2['type']) {
			case 'ccdate': // {
				if ($_REQUEST[$name]=='') {
					$_REQUEST[$name]=date('Y-m');
				}
				$d=preg_replace(
					'#.* ([a-zA-Z]*, [0-9]+)#',
					"$1",
					Core_dateM2H($_REQUEST[$name])
				);
			break; // }
			case 'date': // {
				if ($_REQUEST[$name]=='') {
					$_REQUEST[$name]=date('Y-m-d');
				}
				$d=Core_dateM2H($_REQUEST[$name]);
			break; // }
			case 'file': // {
				$d=__('If there are any files, they are attached to this email');
			break; // }
			case 'hidden': // {
				$d=htmlspecialchars($r2['extra']);
			break; // }
			case 'html-block': 
			case 'page-next': case 'page-previous': case 'page-break': // {
				$d='';
			break; // }
			default: // { # input boxes, and anything which was not handled already
				$d=nl2br(htmlspecialchars($_REQUEST[$name]));
				// }
		}
		if (@$vars['forms_template']&&@$vars['forms_template']!='&nbsp;') {
			@$vars['forms_template']=str_replace(
				'{{$'.$cnt.'}}',
				$d,
				@$vars['forms_template']
			);
			@$vars['forms_template']=str_replace(
				'{{$'.htmlspecialchars($r2['name']).'}}',
				$d,
				$vars['forms_template']
			);
		}
		elseif ($d!='') {
			$c.=$vals_field_start.htmlspecialchars($r2['name']);
			$c.=$vals_field_middle.$d.$vals_field_end;
		}
		$cnt++;
	}
	if (@$vars['forms_template']&&@$vars['forms_template']!='&nbsp;') {
		$c.=$vars['forms_template'];
	}
	else {
		$c.=$vals_2col_start;
	}
	return $c;
}

// }
// { Form_saveValues

/**
  * save submitted form values
  *
  * @param integer $formid       ID of the form being saved
	* @param array   &$form_fields array of fields
  *
  * @return void
  */
function Form_saveValues($formid, &$form_fields) {
	dbQuery(
		"insert into forms_saved (forms_id,date_created) values($formid,now())"
	);
	$id=dbLastInsertId();
	foreach ($form_fields as $r) {
		$name=preg_replace('/[^a-zA-Z0-9_]/', '', $r['name']);
		if (isset($_REQUEST[$name])) {
			$val=addslashes($_REQUEST[$name]);
		}
		else {
			$val='';
		}
		$key=addslashes($r['name']);
		dbQuery(
			'insert into forms_saved_values (forms_saved_id,name,value)'
			." values($id,'$key','$val')"
		);
	}
}

// }
// { Form_send

/**
  * sends a form, or displays the form instead with errors on top
  *
  * @param array $page        page db row
  * @param array $vars        page meta data
	* @param array $form_fields array of fields
  *
  * @return HTML of either the result, or the form with errors on top
  */
function Form_send($page, $vars, $form_fields) {
	$c='';
	$plaintext='';
	$values=array();
	$email='';
	foreach ($form_fields as $r2) {
		$name=preg_replace('/[^a-zA-Z0-9_]/', '', $r2['name']);
		$separator="\n".str_repeat('-', 80)."\n";
		$val='';
		switch ($r2['type']) {
			case 'checkbox': // {
				$val=@$_REQUEST[$name];
				$values[$r2['name']]=($val=='on')?'yes':'no';
				if ($val=='on') {
					$plaintext.='selected option: '
						.htmlspecialchars($r2['name']).$separator;
				}
			break; // }
			case 'date':case 'ccdate': // {
				$val=Core_dateM2H(@$_REQUEST[$name]);
				if ($r2['type']=='ccdate') {
					$val=preg_replace('#.* ([a-zA-Z]*, [0-9]+)#', "$1", $val);
				}
				$values[$r2['name']]=$val;
				$plaintext.=htmlspecialchars($r2['name'])."\n"
					.htmlspecialchars($val).$separator;
			break; // }
			case 'email': // {
				$val=@$_REQUEST[$name];
				$values[$r2['name']]=$val;
				$plaintext.=htmlspecialchars($r2['name'])."\n"
					.htmlspecialchars($val).$separator;
				$email=$val;
			break; // }
			case 'file': // { build $files array which emulates the $_FILES array
				// { first remove old uploads
				$dir=USERBASE.'/f/.files/forms/';
				if (!is_dir($dir)) {
					break;
				}
				$fs=new DirectoryIterator($dir);
				$time=time();
				foreach ($fs as $f) {
					if ($f->isDot()) {
						continue;
					}
					if ($f->isDir()) {
						$diff=$time-$f->getMTime();
						if ($diff>600) { // file is older than 10 minutes
							CoreDirectory::delete($f->getPathname());
						}
					}
				}
				// }
				$session_id=session_id();
				$dir.=$session_id;
				if (!is_dir($dir)) {
					break;
				}
				$_FILES=array();
				$uploads=new DirectoryIterator($dir);
				foreach ($uploads as $upload) {
					if ($upload->isDot()||$upload->isDir()) {
						continue;
					}
					array_push(
						$_FILES,
						array(
							'name'=>$upload->getFileName(),
							'type'=>mime_content_type($upload->getPathname()),
							'tmp_name'=>$upload->getPathname(),
							'error'=>0,
							'size'=>$upload->getSize()
						)
					);
				}
			break; // }
			case 'html-block': case 'next-page-link': case 'previous-page-link':
			case 'page-break': // { not inputs - don't add them
			break; // }
			default: // {
				$val=@$_REQUEST[$name];
				$values[$r2['name']]=$val;
				$val=nl2br($val);
				$plaintext.=htmlspecialchars($r2['name'])."\n"
					.htmlspecialchars($val).$separator;
				// }
		}
	}
	$from_field=preg_replace('/[^a-zA-Z]/', '', $vars['forms_replyto']);
	$from=isset($_REQUEST[$from_field])?$_REQUEST[$from_field]:'';
	if (@$vars['forms_create_user']) {
		$id=dbOne(
			'select id from user_accounts where email="'.addslashes($email).'"',
			'id'
		);
		if (!$id) {
			dbQuery(
				'insert into user_accounts set email="'.addslashes($email).'",'
				.'extras="'.addslashes(json_encode($values)).'"'
			);
			$id=dbLastInsertId();
			if (isset($_FILES) && count($_FILES)) {
				@mkdir(USERBASE.'/f/user-files');
				@mkdir(USERBASE.'/f/user-files/'.$id);
				foreach ($_FILES as $file) {
					copy(
						$file['tmp_name'],
						USERBASE.'/f/user-files/'.$id.'/'.$file['name']
					);
				}
			}
		}
	}
	if ($vars['forms_send_as_email']) {
		$form=Form_readonly($page['id'], $vars, $form_fields);
		$to=$vars['forms_recipient'];
		$form=str_replace(
			array(
				'<input type="submit" value="'.__('Submit Form').'" />',
				'<form action="'.$_SERVER['REQUEST_URI'].'" method="post" '
					.'class="ww_form" enctype="multipart/form-data">',
				'</form>'
			),
			'',
			$form
		);
		cmsMail(
			$to,
			$from,
			$_SERVER['HTTP_HOST'].' '.$page['name'],
			'<html><head></head><body>'.$form.'</body></html>',
			$_FILES
		);
		if (is_dir(USERBASE.'/f/.files/forms/'.session_id())) { // remove uploaded files
			CoreDirectory::delete(USERBASE.'/f/.files/forms/'.session_id());
		}
	}
	if ($vars['forms_record_in_db']) {
		Form_saveValues($page['id'], $form_fields);
	}
	$c.='<div id="thankyoumessage">'.$vars['forms_successmsg'].'</div>';
	return $c;
}

// }
// { Form_validate

/**
  * validate the inputs for a form
  *
  * @param array &$vars        page meta data
	* @param array &$form_fields array of fields
  *
  * @return an array of the errors
  */
function Form_validate(&$vars, &$form_fields) {
	$errors=array();
	foreach ($form_fields as $r2) {
		$name=preg_replace('/[^a-zA-Z0-9_]/', '', $r2['name']);
		if ($r2['type']=='email' && $r2['extra']) {
			if (!isset($_SESSION['emails'])
				|| $_SESSION['emails'][@$_REQUEST[$name]]!==true
			) {
				$errors[]=__('Email validation code was not correct.');
			}
		}
		if ($r2['isrequired'] && @$_REQUEST[$name]=='') {
			$n=$r2['name'];
			$errors[]=__(
				'You must fill in the <strong>%1</strong> field.',
				array($n),
				'core'
			);
		}
		if ($r2['type']=='email'
			&& !filter_var(@$_REQUEST[$name], FILTER_VALIDATE_EMAIL)
		) {
			$n=$r2['name'];
			$errors[]=__(
				'You must provide a valid email in the <strong>%1</strong> field.',
				array($n),
				'core'
			);
		}
	}
	// { check the captcha
	if (@$vars['forms_captcha_required']) {
		require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/recaptcha.php';
		if (!isset($_REQUEST['recaptcha_challenge_field'])) {
			$errors[]=__('You must fill in the captcha (image text).');
		}
		else {
			$result 
				= recaptcha_check_answer(
					RECAPTCHA_PRIVATE,
					$_SERVER['REMOTE_ADDR'],
					$_REQUEST['recaptcha_challenge_field'],
					$_REQUEST['recaptcha_response_field']
				);
			if (!$result->is_valid) {
				$errors[]=__('Invalid captcha. Please try again.');
			}
		}
	}
	// }
	// { check the From field
	$from_field=preg_replace('/[^a-zA-Z]/', '', @$vars['forms_replyto']);
	$from=isset($_REQUEST[$from_field])?$_REQUEST[$from_field]:'';
	if ($from == '') {
		if (!(@$vars['forms_replyto'])) {
			$errors[]=__('No replyto field has been set up by the admin!');
		}
		else {
			$errors[]='please fill in the "'.$vars['forms_replyto'].'" field.';
		}
	}
	// }
	return $errors;
}

// }
