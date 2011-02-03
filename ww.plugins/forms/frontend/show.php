<?php
/**
  * scripts for showing and sending forms on front-end
  *
  * PHP Version 5
  *
  * @category   Whatever
  * @package    WebworksWebme
  * @subpackage Form
  * @author     Kae Verens <kae@webworks.ie>
  * @license    GPL Version 2
  * @link       www.webworks.ie
 */

/**
  * displays or sends a form, depending on whether data's been submitted
  *
  * @param array $page page db row
  * @param array $vars page meta data
  *
  * @return HTML of either the form, or the result of sending
  */
function Form_show($page, $vars) {
	return getVar('funcFormInput')=='submit'
		?Form_send($page, $vars)
		:Form_showForm($page, $vars);
}

/**
  * sends a form, or displays the form instead with errors on top
  *
  * @param array $page page db row
  * @param array $vars page meta data
  *
  * @return HTML of either the result, or the form with errors on top
  */
function Form_send($page, $vars) {
	global $recipientEmail;
	$c='';
	$err='';
	$msg='';
	$plaintext='';
	$values=array();
	$q2=dbAll(
		'select * from forms_fields where formsId="'.$page['id'].'" order by id'
	);
	foreach ($q2 as $r2) {
		$name=preg_replace('/[^a-zA-Z0-9_]/', '', $r2['name']);
		$separator="\n".str_repeat('-', 80)."\n";
		$val='';
		switch ($r2['type']) {
			case 'checkbox': // {
				$val=getVar($name);
				$values[$r2['name']]=($val=='on')?'yes':'no';
				if ($val=='on') {
					$plaintext.='selected option: '
						.htmlspecialchars($r2['name']).$separator;
				}
			break;
			// }
			case 'date':case 'ccdate': // {
				$val=date_m2h($_REQUEST[$name]);
				if ($r2['type']=='ccdate') {
					$val=preg_replace('#.* ([a-zA-Z]*, [0-9]+)#', "$1", $val);
				}
				$values[$r2['name']]=$val;
				$plaintext.=htmlspecialchars($r2['name'])."\n"
					.htmlspecialchars($val).$separator;
			break;
			// }
			default: // {
				$val=getVar($name);
				$values[$r2['name']]=$val;
				$val=nl2br($val);
				$plaintext.=htmlspecialchars($r2['name'])."\n"
					.htmlspecialchars($val).$separator;
				// }
		}
		if ($r2['isrequired']&&$val=='') {
			$err.='<em>'.__(
				'You must fill in the <strong>%1</strong> field.',
				$r2['name']
			)
			.'</em><br />';
		}
		if ($r2['type']=='email' && !filter_var($val, FILTER_VALIDATE_EMAIL)) {
			$err.='<em>'.__(
				'You must provide a valid email in the <strong>%1</strong> field.',
				$r2['name']
			).'</em><br />';
		}
	}
	if ($vars['forms_captcha_required']) {
		require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/recaptcha.php';
		if (!isset($_REQUEST['recaptcha_challenge_field'])) {
			$err.='<em>You must fill in the captcha (image text).</em>';
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
				$err.='<em>Invalid captcha. Please try again.</em>';
			}
		}
	}
	$form=Form_showForm($page, $vars, $err);
	$from_field=preg_replace('/[^a-zA-Z]/', '', $vars['forms_replyto']);
	$from=isset($_REQUEST[$from_field])?$_REQUEST[$from_field]:'';
	if ($from == '') {
		$err='please fill in the "'.$vars['forms_replyto'].'" field.';
	}
	if ($err!='') {
		$c.=Form_showForm($page, $vars, $err);
	}
	else {
		if ($vars['forms_send_as_email']) {
			$form=Form_showForm($page, $vars, $err, true);
			$to=$vars['forms_recipient'];
			$form=str_replace(
				array(
					'<input type="submit" value="Submit Form" />',
					'<form action="'.$_SERVER['REQUEST_URI'].'" method="post" '
						.'class="ww_form" enctype="multipart/form-data">',
					'</form>'
				),
				'',
				$form
			);
			webmeMail(
				$to,
				$from,
				$page['name'],
				'<html><head></head><body>'.$form.'</body></html>',
				$_FILES
			);
		}
		if ($vars['forms_record_in_db']) {
			Form_saveValues($page['id']);
		}
		$c.='<div id="thankyoumessage">'.$vars['forms_successmsg'].'</div>';
	}
	return $c;
}

/**
  * show the form to be submitted
  *
  * @param array   $page               page db row
  * @param array   $vars               page meta data
  * @param string  $err                any errors that need to be shown
  * @param boolean $only_show_contents whether to show inputs or text in HTML
  * @param boolean $show_submit        whether to show the submit button
  *
  * @return HTML of the form
  */
function Form_showForm(
	$page, $vars, $err='', $only_show_contents=false, $show_submit=true
) {
	if (!isset($_SESSION['forms'])) {
		$_SESSION['forms']=array();
	}
	$c='';
	if (!$only_show_contents && $show_submit) {
		$c.='<form action="'.$_SERVER['REQUEST_URI'].'" method="post" '
			.'class="ww_form" enctype="multipart/form-data">';
	}
	$c.='<fieldset>';
	if ($err) {
		$c.='<div class="errorbox">'.$err.'</div>';
	}
	if ($vars['forms_template'] && strpos($vars['forms_template'], '%')===false) {
		$vars['forms_template']='';
	}
	if (!$vars['forms_template']||$vars['forms_template']=='&nbsp;') {
		$c.='<table>';
	}
	$required=array();
	$q2=dbAll(
		'select * from forms_fields where formsId="'.$page['id'].'" order by id'
	);
	$cnt=0;
	$has_date=false;
	$has_ccdate=false;
	foreach ($q2 as $r2) {
		if ($r2['type']=='hidden' && !$only_show_contents) {
			continue;
		}
		$name=preg_replace('/[^a-zA-Z0-9_]/', '', $r2['name']);
		$class='';
		if ($r2['isrequired']) {
			$required[]=$name.','.$r2['type'];
			$class=' required';
		}
		if (isset($_REQUEST[$name])) {
			$_SESSION['forms'][$name]=$_REQUEST[$name];
		}
		$val=getVar($name);
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
		if (!isset($_REQUEST[$name])) {
			$_REQUEST[$name]='';
		}
		switch ($r2['type']) {
			case 'checkbox': // {
				if ($only_show_contents) {
					$d=$_REQUEST[$name];
				}
				else {
					$d='<input type="checkbox" id="'.$name.'" name="'.$name.'"';
					if ($_REQUEST[$name]) {
						$d.=' checked="'.$_REQUEST[$name].'"';
					}
					$d.=' class="'.$class.' checkbox" />';
				}
			break;
			// }
			case 'ccdate': // {
				if ($_REQUEST[$name]=='') {
					$_REQUEST[$name]=date('Y-m');
				}
				if ($only_show_contents) {
					$d=preg_replace(
						'#.* ([a-zA-Z]*, [0-9]+)#',
						"$1",
						date_m2h($_REQUEST[$name])
					);
				}
				else {
					$d='<input name="'.$name.'" value="'
						.$_REQUEST[$name].'" class="ccdate" />';
				}
				$has_ccdate=true;
			break;
			// }
			case 'date': // {
				if ($_REQUEST[$name]=='') {
					$_REQUEST[$name]=date('Y-m-d');
				}
				$d=$only_show_contents?
					date_m2h($_REQUEST[$name]):
					'<input name="'.$name.'" value="'
						.$_REQUEST[$name].'" class="date" />';
				$has_date=true;
			break;
			// }
			case 'email': // {
				$d=$only_show_contents
					?$_REQUEST[$name]
					:'<input id="'.$name.'" name="'.$name.'" value="'.$val
						.'" class="email'.$class.' text" />';
			break;
			// }
			case 'file': // {
				$d=$only_show_contents
					?'<i>files attached</i>'
					:'<input id="'.$name.'" name="'.$name.'" type="file" />';
			break;
			// }
			case 'hidden': // {
				$d=$only_show_contents
					?htmlspecialchars($r2['extra'])
					:'<textarea id="'.$name.'" name="'.$name.'" class="'.$class
						.' hidden">'.htmlspecialchars($r2['extra']).'</textarea>';
			break;
			// }
			case 'selectbox': // {
				if ($only_show_contents) {
					$d=$_REQUEST[$name];
				}
				else {
					$d='<select id="'.$name.'" name="'.$name.'">';
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
				}
			break;
			// }
			case 'textarea': // {
				$d=$only_show_contents
					?$_REQUEST[$name]
					:'<textarea id="'.$name.'" name="'.$name.'" class="'.$class.'">'
						.$_REQUEST[$name].'</textarea>';
			break;
			// }
			default: // { # input boxes, and anything which was not handled already
				$d=$only_show_contents
					?$_REQUEST[$name]
					:'<input id="'.$name.'" name="'.$name.'" value="'.$val
						.'" class="'.$class.' text" />';
				// }
		}
		if ($vars['forms_template']&&$vars['forms_template']!='&nbsp;') {
			$vars['forms_template']=str_replace(
				'%'.$cnt.'%',
				$d,
				$vars['forms_template']
			);
			$vars['forms_template']=str_replace(
				'%'.htmlspecialchars($r2['name']).'%',
				$d,
				$vars['forms_template']
			);
		}
		else{
			$c.='<tr><th>'.htmlspecialchars(__($r2['name']));
			if ($r2['isrequired']) {
				$c.='<sup>*</sup>';
			}
			$c.="</th>\n\t<td>".$d."</td></tr>\n\n";
		}
		$cnt++;
	}
	if ($vars['forms_captcha_required'] && !$only_show_contents) {
		require_once SCRIPTBASE.'ww.incs/recaptcha.php';
		$row='<tr><td colspan="2">'.Recaptcha_getHTML().'</td></tr>';
		if ($vars['forms_template']) {
			$vars['forms_template'].='<table>'.$row.'</table>';
		}
		else {
			$c.=$row;
		}
	}
	if ($vars['forms_template']&&$vars['forms_template']!='&nbsp;') {
		$c.=$vars['forms_template'];
	}
	else {
		$c.='<tr><th colspan="2" class="submitrow">';
	}
	if ($only_show_contents) {
		return $c.'</fieldset>';
	}
	if ($show_submit) {
		$c.='<input type="submit" />'
			.'<input type="hidden" name="funcFormInput" value="submit" />'
			.'<input type="hidden" name="requiredFields" value="'
			.join(',', $required).'" />';
	}
	if (count($required)) {
		$c.='<br />'.__('* indicates required fields');
	}
	if (!$vars['forms_template']||$vars['forms_template']=='&nbsp;') {
		$c.='</th></tr></table>';
	}
	$c.='</fieldset>';
	if (!$only_show_contents && $show_submit) {
		$c.='</form>';
	}
	$script='';
	if ($has_date) {
		$script.='$("input.date").datepicker({"dateFormat":"yy-mm-dd"});';
	}
	if ($has_ccdate) {
		$script.='$("input.ccdate").datepicker({"dateFormat":"yy-mm"});';
	}
	if ($script) {
		$c.='<script>'.$script.'</script>';
	}
	return $c;
}

/**
  * save submitted form values
  *
  * @param integer $formid ID of the form being saved
  *
  * @return void
  */
function Form_saveValues($formid) {
	dbQuery(
		"insert into forms_saved (forms_id,date_created) values($formid,now())"
	);
	$id=dbLastInsertId();
	$q2=dbAll(
		'select name from forms_fields where formsId="'.$formid.'" order by id'
	);
	foreach ($q2 as $r) {
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
