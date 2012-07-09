<?php
/**
  * scripts for showing and sending "recommend this site" on front-end
  *
  * PHP Version 5
  *
  * @category   Whatever
  * @package    WebworksWebme
  * @subpackage RecommendThisSite
  * @author     Kae Verens <kae@kvsites.ie>
  * @license    GPL Version 2
  * @link       www.kvweb.me
 */

/**
  * displays the recommend-this-site form
  *
  * @param array $page page db row
  * @param array $vars page meta data
  *
  * @return HTML of either the form
  */
function RecommendThisSite_send($page, $vars) {
	$yname=$_REQUEST['rts_yname'];
	$yemail=$_REQUEST['rts_yemail'];
	$fname=$_REQUEST['rts_fname'];
	$femail=$_REQUEST['rts_femail'];
	$tplvars=array(
		'amailbody'    => $vars['recommendthissite_emailtoadmin'],
		'amailsubject' => $vars['recommendthissite_emailtoadmin_subject'],
		'amailemail'   => $vars['recommendthissite_emailtoadmin_email'],
		'ymailbody'    => $vars['recommendthissite_emailtosender'],
		'ymailsubject' => $vars['recommendthissite_emailtosender_subject'],
		'fmailbody'    => $vars['recommendthissite_emailtothefriend'],
		'fmailsubject' => $vars['recommendthissite_emailtothefriend_subject'],
		'success'      => $vars['recommendthissite_successmsg']
	);
	foreach ($tplvars as $k=>$v) {
		$tplvars[$k]=str_replace(
			array(
				'{{$smarty.server.HTTP_HOST}}', '{{$friend_name}}', 
				'{{$friend_email}}', '{{$sender_name}}', '{{$sender_email}}'
			),
			array(
				$_SERVER['HTTP_HOST'], htmlspecialchars($fname),
				htmlspecialchars($femail), htmlspecialchars($yname),
				htmlspecialchars($yemail)
			),
			$v
		);
	}
	cmsMail(
		$yemail, $tplvars['amailemail'],
		$tplvars['ymailsubject'], $tplvars['ymailbody']
	);
	cmsMail(
		$femail, $femail,
		$tplvars['fmailsubject'], $tplvars['fmailbody']
	);
	cmsMail(
		$tplvars['amailemail'], 'noreply@'.str_replace('www.', '', $_SERVER['HTTP_HOST']), 
		$tplvars['amailsubject'], $tplvars['amailbody']
	);
	return $tplvars['success'];
}

/**
  * displays or sends a form, depending on whether data's been submitted
  *
  * @param array $page page db row
  * @param array $vars page meta data
  *
  * @return HTML of either the form, or the result of sending
  */
function RecommendThisSite_show($page, $vars) {
	$html='';
	if (isset($_REQUEST['action']) 
		&& $_REQUEST['action']=='Send Recommendation'
	) {
		$errors=array();
		if (!isset($_REQUEST['rts_yname']) || $_REQUEST['rts_yname']=='') {
			$errors[]='Your name must be entered.';
		}
		if (!isset($_REQUEST['rts_fname']) || $_REQUEST['rts_fname']=='') {
			$errors[]='Friend\'s name must be entered.';
		}
		if (!isset($_REQUEST['rts_yemail']) 
			|| !filter_var($_REQUEST['rts_yemail'], FILTER_VALIDATE_EMAIL)
		) {
			$errors[]='Invalid email address in "Your Email".';
		}
		if (!isset($_REQUEST['rts_femail']) 
			|| !filter_var($_REQUEST['rts_femail'], FILTER_VALIDATE_EMAIL)
		) {
			$errors[]='Invalid email address in "Friend\'s Email".';
		}
		if (count($errors)) {
			$html='<div class="error">Please hit "back" in your browser and '
				.'correct these errors:<ul><li>'
				.join('</li><li>', $errors)
				.'</li></ul>';
		}
		else {
			return RecommendThisSite_send($page, $vars);
		}
	}
	return $html.RecommendThisSite_showForm($page, $vars);
}

/**
  * displays the recommend-this-site form
  *
  * @param array $page page db row
  * @param array $vars page meta data
  *
  * @return HTML of either the form
  */
function RecommendThisSite_showForm($page, $vars) {
	$html='<form method="post"><table>'
		.'<tr><th>Your Name</th><td><input name="rts_yname" /></td></tr>'
		.'<tr><th>Your Email</th><td><input type="email" name="rts_yemail" />'
		.'</td></tr>'
		.'<tr><th>Friend\'s Name</th><td><input name="rts_fname" /></td></tr>'
		.'<tr><th>Friend\'s Email</th><td><input type="email" name="rts_femail" />'
		.'</td></tr>'
		.'<tr><td colspan="2"><input type="submit" name="action" '
		.'value="Send Recommendation" /></td></tr>'
		.'</table></form>';
	return $html;
}
