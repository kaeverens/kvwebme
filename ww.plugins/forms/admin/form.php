<?php
/**
  * admin page for defining forms
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

if (!$edit && isset($replytoid) && $replytoid) {
	$c.= wInput('replytoid', 'hidden', $replytoid);
}
// { tabs nav
$c.= '<div class="tabs">'
	.'<ul>'
	.'<li><a href="#f-header">Header</a></li>'
	.'<li><a href="#footer">Footer</a></li>'
	.'<li><a href="#main">Main Details</a></li>'
	.'<li><a href="#fields">Form Fields</a></li>'
	.'<li><a href="#success">Success Message</a></li>'
	.'<li><a href="#template">Template</a></li>'
	.'</ul>';
// }
// { header
$c.='<div id="f-header"><p>Text to be shown above the form</p>'
	.ckeditor('body', $page['body'])
	.'</div>';
// }
// { footer
$c.='<div id="footer"><p>Text to appear below the form.</p>';
$c.=ckeditor('page_vars[footer]',(isset($vars['footer'])?$vars['footer']:''));
$c.='</div>';
// }
// { main details
$c.= '<div id="main"><table>';
// { send as email, recipient
if (!isset($vars['forms_send_as_email'])) {
	$vars['forms_send_as_email']=1;
}
if (!isset($vars['forms_recipient'])) {
	$vars['forms_recipient']=$_SESSION['userdata']['email'];
}
$c.= '<tr><th>Send as Email</th><td>'
	.wInput(
		'page_vars[forms_send_as_email]',
		'select',
		array('1'=>'Yes','0'=>'No'),
		$vars['forms_send_as_email']
	)
	.'</td>'
	.'<th>Recipient</th><td>'
	.wInput(
		'page_vars[forms_recipient]',
		'',
		htmlspecialchars($vars['forms_recipient'])
	)
	.'</td></tr>';
// }
// { captcha, reply-to
if (!isset($vars['forms_captcha_required'])) {
	$vars['forms_captcha_required']=1;
}
$c.= '<tr><th>Captcha Required</th><td>'
	.wInput(
		'page_vars[forms_captcha_required]',
		'select',
		array('1'=>'Yes','0'=>'No'),
		$vars['forms_captcha_required']
	)
	.'</td>';
if (!isset($vars['forms_replyto'])) {
	$vars['forms_replyto']='';
}
$c.= '<th>Reply-To</th><td>'
	.'<select name="page_vars[forms_replyto]" id="form-replyto">'
	.'<option>'. htmlspecialchars($vars['forms_replyto']).'</option>'
	.'</select></td></tr>';
// }
// { record in database
$c.= '<tr><th>Record In DB</th><td>'
	.wInput(
		'page_vars[forms_record_in_db]',
		'select',
		array('0'=>'No','1'=>'Yes'),
		$vars['forms_record_in_db']
	)
	.'</td>'
	.'<th>Export<br /><i style="font-size:small">(requires Record In DB)</i></th>'
	.'<td>from: <input id="export_from" class="date" value="'
	.date('Y-m-d', mktime(0, 0, 0, date("m")-1, date("d"), date("Y")))
	.'" />. <a href="javascript:form_export('.$id.')">export</a></td></tr>';
// }
$c.= '</table></div>';
// }
// { form field
$c.= '<div id="fields">';
$c.= '<table id="formfieldsTable" width="100%">'
	.'<tr><th width="30%">Name</th><th width="30%">Type</th>'
	.'<th width="10%">Required</th><th id="extrasColumn">'
	.'<a href="javascript:formfieldsAddRow()">add field</a></th></tr></table>';
$c.='<ul id="form_fields" style="list-style:none">';
$q2=dbAll('select * from forms_fields where formsId="'.$id.'" order by id');
$i=0;
$arr=array(
	'email'=>'email', 'input box'=>'input box', 'textarea'=>'textarea',
	'date'=>'date', 'checkbox'=>'checkbox', 'selectbox'=>'selectbox',
	'hidden'=>'hidden message', 'ccdate'=>'credit card expiry date'
);
foreach ($q2 as $r2) {
	$c.= '<li><table width="100%"><tr><td width="30%">'
		.wInput(
			'formfieldElementsName['.$i.']',
			'',
			htmlspecialchars($r2['name'])
		)
		.'</td><td width="30%">'
		.wInput(
			'formfieldElementsType['.$i.']',
			'select',
			$arr,
			$r2['type']
		)
		.'</td><td width="10%">'
		.wInput(
			'formfieldElementsIsRequired['.($i).']',
			'checkbox',
			$r2['isrequired']
		)
		.'</td><td>';
	switch($r2['type']){
		case 'selectbox':case 'hidden': // {
			$c.= wInput(
				'formfieldElementsExtra['.($i++).']',
				'textarea',
				$r2['extra'],
				'small'
			);
		break;
		// }
		default: // {
			$c.= wInput('formfieldElementsExtra['.($i++).']', 'hidden', $r2['extra']);
			// }
	}
	$c.= '</td></tr></table></li>';
}
$c.= '</ul></div>';
// }
// { success message
if (!isset($vars['forms_successmsg'])) {
	$vars['forms_successmsg']='';
}
$c.= '<div id="success">'
	.'<p>What should be displayed on-screen when the message is sent.</p>'
	.ckeditor('page_vars[forms_successmsg]', $vars['forms_successmsg'])
	.'</div>';
// }
// { template
$c.= '<div id="template">';
$c.= '<p>Leave blank to have an auto-generated template displayed.</p>';
$c.= ckeditor('page_vars[forms_template]', $vars['forms_template']);
$c.= '</div>';
$c.= '</div>';
// }
$c.= '<script>var formfieldElements='.$i.';</script>'
	.'<script src="/ww.plugins/forms/admin/admin.fields.min.js"></script>';
