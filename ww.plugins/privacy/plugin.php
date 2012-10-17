<?php
/**
	* authentication plugin, for user registration/login
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

// { the plugin config
$plugin=array(
	'name' => 'User Authentication',
	'admin' => array(
		'page_type' => 'UserAuthentication_admin',
		'page_panel' => array(
			'name' => 'Privacy',
			'function' => 'UserAuthentication_showPagePanel'
		),
		'widget' => array(
			'form_url' => '/ww.plugins/privacy/admin/widget.php'
		)
	),
	'description' => 'User authentication, page protection.',
	'frontend' => array(
		'page_type' => 'UserAuthentication_front',
		'page_display_test' => 'UserAuthentication_pageTest',
		'widget' => 'UserAuthentication_showWidget'
	),
	'triggers' => array(
		'page-object-loaded'	=>	'UserAuthentication_redirect'
	),
	'version'=>0
);
// }

// { UserAuthentication_admin

/**
	* show the privacy admin
	*
	* @param object $page      the page object
	* @param array  $page_vars the page's variables
	*
	* @return string HTML of the admin form
	*/
function UserAuthentication_admin($page, $page_vars) {
	require SCRIPTBASE.'ww.plugins/privacy/admin/page_type.php';
	return $html;
}

// }
// { UserAuthentication_front

/**
	* show registration or login page
	*
	* @param object $PAGEDATA the page object
	*
	* @return HTML of the page
	*/
function UserAuthentication_front($PAGEDATA) {
	WW_addCSS('/j/jquery.saorfm/jquery.saorfm.css');		
	WW_addScript('/j/jquery.saorfm/jquery.saorfm.js');
	require SCRIPTBASE.'ww.plugins/privacy/frontend/page_type.php';
	return $PAGEDATA->render().$html;
}

// }
// { UserAuthentication_showPagePanel

/**
	* show page panel
	*
	* @param object $page      the page object
	* @param array  $page_vars the page's variables
	*
	* @return null
	*/
function UserAuthentication_showPagePanel($page, $page_vars) {
	require SCRIPTBASE.'ww.plugins/privacy/admin/privacy_show_page_panel.php';
}

// }
// { UserAuthentication_pageTest

/**
	* is this page private? if so, check that the user is logged in
	*
	* @param object $pagedata the page object
	*
	* @return boolean yes or no
	*/
function UserAuthentication_pageTest($pagedata) {
	require SCRIPTBASE.'ww.plugins/privacy/frontend/privacy_page_test.php';
	return $allowed;
}

// }
// { UserAuthentication_redirect

/**
	* redirect the user if the page requires so
	*
	* @param object $PAGEDATA the page object
	*
	* @return null
	*/
function UserAuthentication_redirect(){	
	global $PAGEDATA;
	if (isset($_SESSION['userdata']['id'])) {
		return;
	}
	if (isset($PAGEDATA->vars['non_logged_in_redirect_to'])
		&& (int)$PAGEDATA->vars['non_logged_in_redirect_to']
	) {
		$p=Page::getInstance($PAGEDATA->vars['non_logged_in_redirect_to']);
		redirect($p->getAbsoluteUrl());
	}
}

// }
// { UserAuthentication_showWidget

/**
	* show the login widget
	*
	* @param array $vars      parameters
	* @param int   $widget_id id of the widget to show
	*
	* @return string html to return
	*/
function UserAuthentication_showWidget($vars=null, $widget_id=0) {
	WW_addCSS('/ww.plugins/privacy/widget.css');
	if (!isset($_SESSION['userdata']) || !$_SESSION['userdata']['id']) {
		require_once SCRIPTBASE.'ww.plugins/privacy/frontend/widget-login.php';
		WW_addScript('privacy/frontend/widget-login.js');
		return $c;
	}
	return '<div id="userauthentication-widget"><ul>'
		.'<li>Hi, <strong>'.$_SESSION['userdata']['name'].'</strong></li>'
		.'<li class="userauthentication-logout">'
		.'<a href="/?logout">'.__('log out', 'core').'</a></li>'
		.'<li class="userauthentication-edit-profile">'
		.'<a href="/_r?type=loginpage">'.__('my account', 'core')
		.'</a></li></ul><br class="clear"/></div>';
}

// }
