<?php
/**
	* definition file for Protexted Files plugin
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     None
	*/

// { config

$plugin=array(
	'name'=>function() {
		return __('protected files');
	},
	'description' =>function() {
		return __(
			'Protect files by requiring either a login or an email address.'
		);
	},
	'admin'=>array(
		'menu'=>array(
			'Site Options>Protected Files'=>
				'plugin.php?_plugin=protected-files&amp;_page=index'
		)
	),
	'frontend'=>array(
		'file_hook'=>'ProtectedFiles_check'
	),
	'version'=>6
);

// }
// { ProtectedFiles_log

/**
	* log the file request
	*
	* @param string $fname   file name
	* @param int    $success was it successful
	* @param string $email   email address of the requester
	* @param int    $pf_id   ID
	*
	* @return null
	*/
function ProtectedFiles_log($fname, $success, $email='', $pf_id=0) {
	$i=$_SERVER['REMOTE_ADDR'];
	if (!isset($_SESSION['session_md5'])) {
		$_SESSION['session_md5']=md5($i.$_SERVER['REQUEST_TIME']);
	}
	$m=$_SESSION['session_md5'];
	$f=addslashes($fname);
	$e=addslashes($email);
	dbQuery(
		"delete from protected_files_log where session_md5='$m' and file='$f'"
	);
	dbQuery(
		"insert into protected_files_log set ip='$i',file='$f',last_access=now("
		."),success=$success,email='$e',session_md5='$m',pf_id=$pf_id"
	);
}

// }
// { ProtectedFiles_check

/**
	* check that a file can be accessed
	*
	* @param array $vars array
	*
	* @return null
	*/
function ProtectedFiles_check($vars) {
	global $PAGEDATA;
	$fname=$vars['requested_file'];
	$protected_files=Core_cacheLoad('protected_files', 'all');
	if (!$protected_files) {
		$protected_files=dbAll('select * from protected_files');
		Core_cacheSave('protected_files', 'all', $protected_files);
	}
	foreach ($protected_files as $pr) {
		if (strpos($fname, $pr['directory'].'/')===0) {
			if (!isset($pr['details'])) {
				$details=array('type'=>1);
			}
			else {
				$details=json_decode($pr['details'], true);
			}
			switch ((int)$details['type']) {
				case 1: // { email
					$email='';
					if (isset($_SESSION['protected_files_email'])
						&& $_SESSION['protected_files_email']
					) {
						$email=$_SESSION['protected_files_email'];
					}
					elseif(isset($_SESSION['userdata']['email'])
						&& $_SESSION['userdata']['email']
					) {
						$email=$_SESSION['userdata']['email'];
					}
					elseif(isset($_REQUEST['email'])
						&& filter_var($_REQUEST['email'], FILTER_VALIDATE_EMAIL)
					) {
						$email=$_REQUEST['email'];
					}
					if ($email) {
						require_once SCRIPTBASE.'ww.incs/common.php';
						$_SESSION['protected_files_email']=$email;
						if (!isset($_SESSION['protected_files_stage2'])) {
							$_SESSION['protected_files_stage2']=1;
							$PAGEDATA=Page::getInstance(0);
							$PAGEDATA->title='File Download';
							list($smarty, $template)=ProtectedFiles_getTemplate(
								$pr['template']
							);
							$smarty->assign('METADATA', '<title>File Download</title>');
							$smarty->assign(
								'PAGECONTENT',
								'<p>Your download should begin in two seconds. '
								.'If it doesn\'t, please <a href="'
								.$_SERVER['REQUEST_URI']
								.'">click here</a></p>'
								.'<script defer="defer">setTimeout(function(){document.location="'
								.htmlspecialchars($_SERVER['REQUEST_URI'])
								.'";},2000);</script><p>'
								.'<a href="'.$_SESSION['referer']
								.'">Click here</a> to return to the referring page.</p>'
							);
							$smarty->display($template.'.html');
							Core_quit();
						}
						else {
							cmsMail(
								$pr['recipient_email'], 
								'['.$_SERVER['HTTP_HOST'].'] protected file downloaded',
								'protected file "'.addslashes($fname)
								.'" was downloaded by "'.addslashes($email).'"'
							); 
							ProtectedFiles_log($fname, 1, $email, $pr['id']);
							unset($_SESSION['referer']);
						}
					}
					else {
						unset($_SESSION['protected_files_stage2']);
						if (!isset($_SESSION['referer'])) {
							$_SESSION['referer']=isset($_SERVER['HTTP_REFERER'])
								?$_SERVER['HTTP_REFERER']
								:'';
						}
						ProtectedFiles_log($fname, 0, '', $pr['id']);
						$PAGEDATA=Page::getInstance(0);
						$PAGEDATA->title='File Download';
						list($smarty, $template)=ProtectedFiles_getTemplate(
							$pr['template']
						);
						$smarty->assign('METADATA', '<title>File Download</title>');
						$smarty->assign(
							'PAGECONTENT',
							$pr['message'].'<form method="post" action="/f'
							.htmlspecialchars($fname).'">'
							.'<input name="email" /><input type="submit" value="Please en'
							.'ter your email address" /></form>'
						);
						$smarty->display($template.'.html');
						Core_quit();
					}
				break; // }
				case 2: // { groups
					if (isset($_SESSION['userdata']['groups'])) {
						$valid=explode(',', $details['groups']);
						foreach ($valid as $g) {
							if ($g!='' && isset($_SESSION['userdata']['groups'][$g])) {
								return; // ok - this user is a member of a valid group
							}
						}
					}
					$PAGEDATA=Page::getInstance(0);
					$PAGEDATA->title='File Download';
					list($smarty, $template)=ProtectedFiles_getTemplate($pr['template']);
					$smarty->assign('METADATA', '<title>File Download</title>');
					$smarty->assign(
						'PAGECONTENT',
						$pr['message'].'<p>Please <a href="/_r?type=privacy">login</a> '
						.'to view this page</p>'
					);
					$smarty->display($template.'.html');
					Core_quit();
					// }
			}
		}
	}
}

// }
// { ProtectedFiles_getTemplate

/**
	* get template
	*
	* @param string $templateString template
	*
	* @return array
	*/
function ProtectedFiles_getTemplate($templateString) {
	if (file_exists(THEME_DIR.'/'.THEME.'/h/'.$templateString.'.html')) {
		$template=THEME_DIR.'/'.THEME.'/h/'.$templateString.'.html';
	}
	elseif (file_exists(THEME_DIR.'/'.THEME.'/h/_default.html')) {
		$template=THEME_DIR.'/'.THEME.'/h/_default.html';
	}
	else {
		$d=array();
		$dir=new DirectoryIterator(THEME_DIR.'/'.THEME.'/h/');
		foreach ($dir as $f) {
			if ($f->isDot()) {
				continue;
			}
			$n=$f->getFilename();
			if (preg_match('/\.html$/', $n)) {
				$d[]=preg_replace('/\.html$/', '', $n);
			}
		}
		asort($d);
		$template=$d[0];
	}
	if ($template=='') {
		die('no template created. please create a template first');
	}
	require_once SCRIPTBASE.'ww.incs/common.php';
	$smarty=Core_smartySetup(USERBASE.'/ww.cache/pages');
	$smarty->template_dir = THEME_DIR.'/'.THEME.'/h/';
	return array($smarty, str_replace('.html', '', $template));
}

// }
