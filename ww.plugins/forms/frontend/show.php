<?php
/**
	* scripts for showing and sending forms on front-end
	*
	* PHP Version 5
	*
	* @category   Whatever
	* @package    WebworksWebme
	* @subpackage Form
	* @author     Kae Verens <kae@kvsites.ie>
	* @license    GPL Version 2
	* @link       www.kvweb.me
 */

// { Form_getValidationRules

/**
	* get the validation rules for the form
	*
	* @param array $vars        page meta data
	* @param array $form_fields array of fields
	*
	* @return an array of the errors
	*/
function Form_getValidationRules($vars, $form_fields=array()) {
	global $recipientEmail;
	$rulesCollection=array();
	$from_field=preg_replace('/[^a-zA-Z]/', '', @$vars['forms_replyto']);
	if (is_array($form_fields)) {
		foreach ($form_fields as $r2) {
			$rules=array();
			$name=preg_replace('/[^a-zA-Z0-9_]/', '', $r2['name']);
			if ($r2['isrequired'] || $name==$from_field) {
				$rules['required']=true;
			}
			if ($r2['type']=='email') {
				$rules['email']=true;
			}
			if (count($rules)) {
				$rulesCollection[$name]=$rules;
			}
		}
	}
	// { check the captcha
	if (@$vars['forms_captcha_required']) {
		$rulesCollection['recaptcha_challenge_field']=array('required'=>true);
	}
	// }
	return $rulesCollection;
}

// }
// { Form_show

/**
	* displays or sends a form, depending on whether data's been submitted
	*
	* @param array $page page db row
	* @param array $vars page meta data
	*
	* @return HTML of either the form, or the result of sending
	*/
function Form_show($page, $vars) {
	$errors=array();
	if (!isset($vars['forms_fields'])) {
		$vars['forms_fields']='[]';
	}
	$form_fields=json_decode($vars['forms_fields'], true);
	if (@$_REQUEST['funcFormInput']=='submit') {
		require_once dirname(__FILE__).'/validate-and-send.php';
		$errors=Form_validate($vars, $form_fields);
		if (!count($errors)) {
			return Form_send($page, $vars, $form_fields);
		}
	}
	return Form_showForm($page, $vars, $errors, $form_fields);
}

// }
// { Form_showForm

/**
	* show the form to be submitted
	*
	* @param array $page       page db row
	* @param array $vars       page meta data
	* @param array $errors     any errors that need to be shown
	* @param array form_fields list of fields in the form
	*
	* @return HTML of the form
	*/
function Form_showForm($page, $vars, $errors, $form_fields) {
	if (!isset($_SESSION['forms'])) {
		$_SESSION['forms']=array();
	}
	$c='<form action="'.$_SERVER['REQUEST_URI'].'" method="post" '
		.'class="ww_form" enctype="multipart/form-data">';
	if (count($errors)) {
		$c.='<div class="errorbox">'.join('<br />', $errors).'</div>';
	}
	switch(@$vars['forms_htmltype']) {
		case 'div': // {
			$vals_wrapper_start='';
			$vals_field_start='<div><span class="__" lang-context="core">';
			$vals_field_middle='</span>';
			$vals_field_end='</div>';
			$vals_2col_start='<div>';
			$vals_2col_end='</div>';
			$vals_wrapper_end='';
		break; // }
		default: // {
			$vals_wrapper_start='<table class="forms-table">';
			$vals_field_start='<tr><th class="__" lang-context="core">';
			$vals_field_middle='</th><td>';
			$vals_field_end='</td></tr>';
			$vals_2col_start='<tr><td colspan="2">';
			$vals_2col_end='</td></tr>';
			$vals_wrapper_end='</table>';
			// }
	}
	if (@$vars['forms_template'] && strpos($vars['forms_template'], '{{')===false) {
		$vars['forms_template']='';
	} // }}
	if (!(@$vars['forms_template'])||$vars['forms_template']=='&nbsp;') {
		$c.='<div>'.$vals_wrapper_start;
	}
	$required=array();
	$cnt=0;
	$has_date=false;
	$has_ccdate=false;
	foreach ($form_fields as $r2) {
		if ($r2['type']=='hidden') {
			continue;
		}
		$name=preg_replace('/[^a-zA-Z0-9_]/', '', $r2['name']);
		$help=@$r2['help'];
		if ($help!='') {
			$help=' title="'.htmlspecialchars($help, ENT_QUOTES).'"';
		}
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
		$table_break=0;
		switch ($r2['type']) {
			case 'checkbox': // {
				$d='<input type="checkbox" id="'.$name.'" name="'.$name.'"'.$help;
				if ($_REQUEST[$name]) {
					$d.=' checked="'.$_REQUEST[$name].'"';
				}
				$d.=' class="'.$class.' checkbox" />';
			break; // }
			case 'ccdate': // {
				if ($_REQUEST[$name]=='') {
					$_REQUEST[$name]=date('Y-m');
				}
				$d='<input name="'.$name.'" value="'
					.$_REQUEST[$name].'" class="ccdate"'.$help.'/>';
				$has_ccdate=true;
			break; // }
			case 'date': // {
				if ($_REQUEST[$name]=='') {
					$_REQUEST[$name]=date('Y-m-d');
				}
				$d='<input name="'.$name.'" value="'.$_REQUEST[$name].'"'.$help
					.' class="date" type="date" placeholder="yyyy-mm-dd" '
					.'metadata="'.addslashes($r2['extra']).'"/>';
				$has_date=true;
			break; // }
			case 'email': // {
				if ($r2['extra']) {
					$class.=' verify';
					$verify='<input style="display:none" class="email-verification" '
						.'name="'.$name.'_verify" value="" placeholder="verification code"'
						.$help.'/>';
					$_SESSION['form_input_email_verify_'.$name]=rand(10000, 99999);
				}
				else {
					$verify='';
				}
				$d='<input type="email" id="'.$name.'" name="'.$name.'" value="'.$val
					.'" class="email'.$class.' text"'.$help.'/>'.$verify;
			break; // }
			case 'file': // {
				WW_addScript('forms/j/swfobject.js');
				WW_addScript('forms/j/uploadify.jquery.min.js');
				WW_addCSS('/ww.plugins/forms/j/uploadify.css');
				$opts=isset($r2['extra'])?explode(':', $r2['extra']):array();
				if (!isset($opts[0])||!isset($opts[1])) {
					$opts=array(
						'off',
						'*;'
					);
				}
				$multi=($opts[0]=='on')?'true':'false';
				$script='
				$(function(){
					$("#'.$name.'").uploadify({
						"uploader":"/ww.plugins/forms/j/uploadify.swf",
						"script":"/ww.plugins/forms/frontend/file-upload.php",
						"cancelImg":"/ww.plugins/forms/j/cancel.png",
						"multi":'.$multi.',
						"removeCompleted":false,
						"fileDataName":"file-upload",
						"scriptData":{
							"PHPSESSID":"'.session_id().'"
						},
						"onComplete":function(event,ID,fileObj,response,data){
							if(response=="deleted"){
								alert("You have uploaded too many large files. These files'
								.' have been deleted to conserve space. Please reload the '
								.'page and try again with less or smaller files.");
							}
						},
						"onAllComplete":function(){
							$("input[type=submit]").attr("disabled",false);
						},
						"onSelect":function(){
							$("input[type=submit]").attr("disabled","disabled");
						},
						"fileExt":"'.$opts[1].'",
						"fileDesc":" ",
						"auto":true
					});
				});';
				WW_addInlineScript($script);
				$d='<div id="upload">';
				$d.='<input type="file" id="'.$name.'" name="file-upload"'.$help.'/>';
				$d.='</div>';
				// { add existing files
				$dir=USERBASE.'/f/.files/forms/'.session_id();
				if (is_dir($dir)) {
					$files=array();
					$uploads=new DirectoryIterator($dir);
					foreach ($uploads as $upload) {
						if ($upload->isDot()||$upload->isDir()) {
							continue;
						}
						$bytes=$upload->getSize();
						$kb=round(($bytes/1024), 2);
						$d.='<div class="uploadifyQueueItem completed">'
							.'<div class="cancel"><a class="download-delete-item" '
							.'href="javascript:;" id="'.$upload->getFileName().'">'
							.'<img border="0" src="/ww.plugins/forms/j/cancel.png"></a>'
							.'</div>'
							.'<span class="fileName">'.$upload->getFileName()
							.' ('.$kb.' KB)</span>'
							.'<span class="percentage"> - Completed</span>'
						.'</div>';
					}
				}
				// }
			break; // }
			case 'hidden': // {
				$d='<textarea id="'.$name.'" name="'.$name.'" class="'.$class
						.' hidden"'.$help.'>'.htmlspecialchars($r2['extra']).'</textarea>';
			break; // }
			case 'html-block': // {
				$d=$r2['extra'];
				$table_break=true;
			break; // }
			case 'page-next': // {
				$d='<a href="javascript:;" class="form-page-next">Next</a>';
				$table_break=true;
			break; // }
			case 'page-previous': // {
				$d='<a href="javascript:;" class="form-page-previous">Previous</a>';
				$table_break=true;
			break; // }
			case 'page-break': // {
				$d='</div><div style="display:none">';
				$table_break=true;
			break; // }
			case 'selectbox': // {
				$d='<select id="'.$name.'" name="'.$name.'"'.$help.'>';
				$arr=explode("\n", htmlspecialchars($r2['extra']));
				foreach ($arr as $li) {
					if ($_REQUEST[$name]==$li) {
						$d.='<option selected="selected">'.rtrim($li).'</option>';
					}
					else {
						$d.='<option>'.rtrim($li).'</option>';
					}
				}
				$d.='</select>';
			break; // }
			case 'signature': // {
				$d='<div class="signature-wrapper">'
					.'<canvas class="signature-pad" width="300" height="150">'
					.'</canvas>'
					.'<a href="#" class="signature-clear">clear</a>'
					.'<input type="hidden" name="'.$name.'"/>'
					.'</div>';
				WW_addScript('forms/j/jquery.signaturepad.js');
				WW_addScript('forms/j/field-type-signature.js');
			break; // }
			case 'textarea': // {
				if (!$r2['extra']) {
					$r2['extra']='0,0';
				}
				list($max, $softmax)=explode(',', $r2['extra']);
				$maxlength=$max?'maxlength="'.$max.'" ':'';
				$d='<textarea '.$maxlength.' softmaxlength="'.$softmax.'"'.$help
						.' id="'.$name.'" name="'.$name.'" class="'.$class.'">'
						.$_REQUEST[$name].'</textarea>';
			break; // }
			default: // { # input boxes, and anything which was not handled already
				$d='<input id="'.$name.'" name="'.$name.'" value="'.$val
						.'" class="'.$class.' text"'.$help.'/>';
				// }
		}
		if (@$vars['forms_template']&&$vars['forms_template']!='&nbsp;') {
			$vars['forms_template']=str_replace(
				'{{$'.$cnt.'}}',
				$d,
				$vars['forms_template']
			);
			$vars['forms_template']=str_replace(
				'{{$'.htmlspecialchars($r2['name']).'}}',
				$d,
				$vars['forms_template']
			);
		}
		else {
			if ($table_break) {
				$c.=$vals_wrapper_end.$d.$vals_wrapper_start;
			}
			else {
				$c.=$vals_field_start.$r2['name'];
				if ($r2['isrequired']) {
					$c.='<sup>*</sup>';
				}
				$c.=$vals_field_middle.$d.$vals_field_end;
			}
		}
		$cnt++;
	}
	if (@$vars['forms_captcha_required']) {
		require_once SCRIPTBASE.'ww.incs/recaptcha.php';
		$row=$vals_2col_start.Recaptcha_getHTML().$vals_2col_end;
		if (isset($vars['forms_template']) && $vars['forms_template']) {
			$vars['forms_template'].=$vals_wrapper_start.$row.$vals_wrapper_end;
		}
		else {
			$c.=$row;
		}
	}
	if (@$vars['forms_template']&&$vars['forms_template']!='&nbsp;') {
		$c.=$vars['forms_template'];
	}
	else {
		$c.=$vals_2col_start;
	}
	$c.='<button class="submit __" lang-context="core">Submit Form</button>'
		.'<input type="hidden" name="funcFormInput" value="submit" />'
		.'<input type="hidden" name="requiredFields" value="'
		.join(',', $required).'" />';
	if (count($required)) {
		$c.='<br /><span>'.__('* indicates required fields', 'core').'</span>';
	}
	if (!@$vars['forms_template']||@$vars['forms_template']=='&nbsp;') {
		$c.=$vals_2col_end.$vals_wrapper_end.'</div>';
		$c=str_replace('<table></table>', '', $c);
		WW_addInlineScript(
			'var form_rules='
			.json_encode(Form_getValidationRules($vars, $form_fields)).';'
		);
		WW_addScript('forms/frontend/show.js');
		$c.='<script src="http://ajax.aspnetcdn.com/ajax/jquery.validate/1.9/'
			.'jquery.validate.min.js"></script>';
	}
	$helpType=(int)@$vars['forms_helpType'];
	$helpSelector=@$vars['forms_helpSelector'];
	$verifiedEmails=isset($_SESSION['forms_verified_emails'])
		?json_encode($_SESSION['forms_verified_emails']):
		'[]';
	$c.='<script defer="defer">var forms_helpType='.$helpType.',forms_helpSelector="'
		.$helpSelector.'",forms_verifiedEmails='.$verifiedEmails
		.';</script></form>';
	if ($has_ccdate) {
		WW_addInlineScript('$("input.ccdate").datepicker({"dateFormat":"yy-mm"});');
	}
	WW_addCSS('/ww.plugins/forms/forms.css');
	return $c;
}

// }
// { Form_valueDefault

/**
	* get the default value for a form field
	*
	* @return array details
	*/
function Form_valueDefault($name) {
	$val=@$_REQUEST[$name];
	if (!$val && isset($_SESSION['userdata']) && $_SESSION['userdata']) {
		switch($name){
			case 'Email': case '__ezine_subscribe': // {
				if (isset($_SESSION['userdata']['email'])) {
					$val=$_SESSION['userdata']['email'];
				}
			break;
			// }
			case 'FirstName': // {
				$val=preg_replace('/ .*/', '', $_SESSION['userdata']['name']);
			break;
			// }
			case 'Street': // {
				$val=$_SESSION['userdata']['address1'];
			break;
			// }
			case 'Street2': // {
				$val=$_SESSION['userdata']['address2'];
			break;
			// }
			case 'Surname': // {
				$val=preg_replace('/.* /', '', $_SESSION['userdata']['name']);
			break;
			// }
			case 'Town': // {
				$val=$_SESSION['userdata']['address3'];
			break;
			// }
		}
	}
	return $val;
}

// }
