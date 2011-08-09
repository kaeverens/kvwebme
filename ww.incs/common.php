<?php
require_once dirname(__FILE__).'/basics.php';
require_once SCRIPTBASE . 'ww.incs/Smarty-2.6.26/libs/Smarty.class.php';
function Core_getJQueryScripts() {
	global $DBVARS;
	$jquery_versions=array('1.6', '1.8');
	if (@$DBVARS['offline']) {
		require SCRIPTBASE.'/ww.incs/get-offline-files.php';
		$jurls=Core_getOfflineJQueryScripts($jquery_versions);
	}
	else {
		$h='https://ajax.googleapis.com/ajax/libs/jquery';
		$jurls=array(
			$h.'/'.$jquery_versions[0].'/jquery.min.js',
			$h.'ui/'.$jquery_versions[1].'/jquery-ui.min.js',
			$h.'ui/'.$jquery_versions[1].'/themes/base/jquery-ui.css'
		);
	}
	return '<script src="'.$jurls[0].'"></script>'
		.'<script src="'.$jurls[1].'"></script>'
		.'<link href="'.$jurls[2].'" rel="stylesheet" />';
}
function date_m2h($d, $type = 'date') {
	$date = preg_replace('/[- :]/', ' ', $d);
	$date = explode(' ', $date);
	if (count($date)<4) {
		$date[3]='00';
		$date[4]='00';
		$date[5]='00';
	}
	$utime=@mktime($date[3], $date[4], $date[5], $date[1], $date[2], $date[0]);
	if ($type == 'date') {
		return date('l jS F, Y', $utime);
	}
	if ($type == 'datetime') {
		return date('D jS M, Y h:iA', $utime);
	}
	return date(DATE_RFC822, $utime);
}
function redirect($addr) {
	header('HTTP/1.1 301 Moved Permanently');
	header('Location: '.$addr);
	echo '<html><head><script type="text/javascript">setTimeout(function(){do'
		.'cument.location="'.$addr.'";},10);</script></head><body></body></html>';
	exit;
}
function webmeMail($from, $to, $subject, $message, $files = false) {
	require_once dirname(__FILE__).'/mail.php';
	send_mail($from, $to, $subject, $message, $files);
}
$Core_isAdmin = 0;
$sitedomain=str_replace('www.', '', $_SERVER['HTTP_HOST']);
if (strpos($_SERVER['REQUEST_URI'],'ww.admin/')!==false) {
	$kfm_do_not_save_session=true;
	require_once SCRIPTBASE . 'j/kfm/api/api.php';
	require_once SCRIPTBASE . 'j/kfm/initialise.php';
}
function menuDisplay($a=0) {
	require_once SCRIPTBASE . 'ww.incs/menus.php';
	return Menu_show($a);
}
function smarty_setup($compile_dir) {
	global $DBVARS, $PLUGINS;
	$smarty = new Smarty;
	$smarty->left_delimiter = '{{';
	$smarty->right_delimiter = '}}';
	$smarty->assign(
		'WEBSITE_TITLE',
		htmlspecialchars($DBVARS['site_title'])
	);
	$smarty->assign(
		'WEBSITE_SUBTITLE',
		htmlspecialchars($DBVARS['site_subtitle'])
	);
	$smarty->assign('GLOBALS', $GLOBALS);
	$smarty->register_function('BREADCRUMBS', 'Template_breadcrumbs');
	$smarty->register_function('LOGO', 'Template_logoDisplay');
	$smarty->register_function('MENU', 'menuDisplay');
	$smarty->register_function('nuMENU', 'menu_show_fg');
	foreach ($PLUGINS as $pname=>$plugin) {
		if (isset($plugin['frontend']['template_functions'])) {
			foreach ($plugin['frontend']['template_functions'] as $fname=>$vals) {
				$smarty->register_function($fname, $vals['function']);
			}
		}
	}
	$smarty->compile_dir=$compile_dir;
	return $smarty;
}

/**
	*  return a HTML string with "breadcrumb" links to the current page
	*
	* @param int $id  ID of the root page to draw breadcrumbs from
	* @param int $top should this breadcrumb be wrapped?
	*
	* @return string
	*/
function Template_breadcrumbs($id=0, $top=1) {
	if ($id) {
		$page=Page::getInstance($id);
	}
	else {
		$page=$GLOBALS['PAGEDATA'];
	}
	$c=$page->parent
		? Template_breadcrumbs($page->parent, 0) . ' &raquo; '
		: '';
	$pre=$top?'<div class="breadcrumbs">':'';
	$suf=$top?'</div>':'';
	return $pre.$c.'<a href="' . $page->getRelativeURL() . '" title="' 
		. htmlspecialchars($page->title) . '">' 
		. htmlspecialchars($page->name) . '</a>'.$suf;
}

/**
	* return a logo HTML string if the admin uploaded one
	*
	* @param array $vars array of logo parameters (width, height)
	*
	* @return string
	*/
function Template_logoDisplay($vars) {
	$vars=array_merge(array('width'=>64, 'height'=>64), $vars);
	if (!file_exists(USERBASE.'/f/skin_files/logo.png')) {
		return '';
	}
	$x=(int)$vars['width'];
	$y=(int)$vars['height'];
	$geometry=$x.'x'.$y;
	$image_file=USERBASE.'/f/skin_files/logo-'.$geometry.'.png';
	if (!file_exists($image_file)) {
		$from=addslashes(USERBASE.'/f/skin_files/logo.png');
		$to=addslashes($image_file);
		`convert $from -geometry $geometry $to`;
	}
	$size=getimagesize($image_file);
	return '<img class="logo" src="/i/blank.gif" style="'
		.'background:url(/f/skin_files/logo-'.$geometry.'.png) no-repeat;'
		.'width:'.$size[0].'px;height:'.$size[1].'px;" />';
}
// { user authentication
if (@$_REQUEST['action']=='login'
	|| isset($_SESSION['userdata']['id'])
	|| isset($_REQUEST['logout'])
) {
	require_once dirname(__FILE__).'/user-authentication.php';
}
// }
function menu_build_fg($parentid, $depth, $options) {
	$PARENTDATA=Page::getInstance($parentid)->initValues();
	// { menu order
	$order='ord,name';
	if (isset($PARENTDATA->vars['order_of_sub_pages'])) {
		switch ($PARENTDATA->vars['order_of_sub_pages']) {
			case 1: // { alphabetical
				$order='name';
				if ($PARENTDATA->vars['order_of_sub_pages_dir']) {
					$order.=' desc';
				}
			break; // }
			case 2: // { associated_date
				$order='associated_date';
				if ($PARENTDATA->vars['order_of_sub_pages_dir']) {
					$order.=' desc';
				}
				$order.=',name';
			break; // }
			default: // { by admin order
				$order='ord';
				if ($PARENTDATA->vars['order_of_sub_pages_dir']) {
					$order.=' desc';
				}
				$order.=',name';
				// }
		}
	}
	// }
	$rs=dbAll(
		"select id,name,type from pages where parent='".$parentid
		."' and !(special&2) order by $order"
	);
	if ($rs===false || !count($rs)) {
		return '';
	}

	$items=array();
	foreach ($rs as $r) {
		$item='<li>';
		$page=Page::getInstance($r['id'])->initValues();
		$item.='<a class="menu-fg menu-pid-'.$r['id'].'" href="'
			.$page->getRelativeUrl().'">'.htmlspecialchars($page->alias).'</a>';
		$item.=menu_build_fg($r['id'], $depth+1, $options);
		$item.='</li>';
		$items[]=$item;
	}
	$options['columns']=(int)$options['columns'];

	// return top-level menu
	if (!$depth) {
		return '<ul>'.join('', $items).'</ul>';
	}

	if ($options['style_from']=='1') {
		$s='';
		if ($options['background']) {
			$s.='background:'.$options['background'].';';
		}
		if ($options['opacity']) {
			$s.='opacity:'.$options['opacity'].';';
		}
		if ($s) {
			$s=' style="'.$s.'"';
		}
	}

	// return 1-column sub-menu
	if ($options['columns']<2) {
		return '<ul'.$s.'>'.join('', $items).'</ul>';
	}

	// return multi-column submenu
	$items_count=count($items);
	$items_per_column=ceil($items_count/$options['columns']);
	$c='<table'.$s.'><tr><td><ul>';
	for ($i=1;$i<$items_count+1;++$i) {
		$c.=$items[$i-1];
		if ($i!=$items_count && !($i%$items_per_column)) {
			$c.='</ul></td><td><ul>';
		}
	}
	$c.='</ul></td></tr></table>';
	return $c;
}
function menu_show_fg ($opts) {
	$md5=md5('menu_fg|'.print_r($opts, true));
	$cache=Core_cacheLoad('menus', $md5);
	if ($cache) {
		return $cache;
	}
	$options=array(
		'direction' => 0,  // 0: horizontal, 1: vertical
		'parent'    => 0,  // top-level
		'background'=> '', // sub-menu background colour
		'columns'   => 1,  // for wide drop-down sub-menus
		'opacity'   => 0,  // opacity of the sub-menu
		'type'      => 0,  // 0=drop-down, 1=accordion
		'style_from'=> 1,   // inherit sub-menu style from CSS (0) or options (1)
		'state'	    => 0,  // 2=expand current page,1=expand all,0=contract all
	);
	foreach ($opts as $k=>$v) {
		if (isset($options[$k])) {
			$options[$k]=$v;
		}
	}
	if (!is_numeric($options['parent'])) {
		$r=Page::getInstanceByName($options['parent']);
		if ($r) {
			$options['parent']=$r->id;
		}
	}
	if (is_numeric($options['direction'])) {
		if ($options['direction']=='0') {
			$options['direction']='horizontal';
		}
		else {
			$options['direction']='vertical';
		}
	}
	$options['type']=(int)$options['type'];
	$items=array();
	$menuid=$GLOBALS['fg_menus']++;
	$md5=md5($options['parent'].'|0|'.json_encode($options));
	$html=Core_cacheLoad('pages', 'fgmenu-'.$md5);
	if ($html===false) {
		$html=menu_build_fg($options['parent'], 0, $options);
		Core_cacheSave('pages', 'fgmenu-'.$md5, $html);
	}
	if ($options['type']) {
		WW_addScript('/j/menu-accordion/menu.js');
		WW_addCSS('/j/menu-accordion/menu.css');
		$class = ( $options[ 'state' ] == 0 )
			? ' contracted'
			: (( $options[ 'state' ] == 1 ) ? ' expanded' : ' expand-selected') ;
		$c.= '<div class="menu-accordion'.$class.'">'.$html.'</div>';
	}
	else {
		WW_addScript('/j/fg.menu/fg.menu.js');
		WW_addCSS('/j/fg.menu/fg.menu.css');
		$c.='<div class="menu-fg menu-fg-'.$options['direction'].'" id="menu-fg-'
			.$menuid.'">'.$html.'</div>';
		if ($options['direction']=='vertical') {
			$posopts="positionOpts: { posX: 'left', posY: 'top',"
				."offsetX: 40, offsetY: 10, directionH: 'right', directionV: 'down',"
				."detectH: true, detectV: true, linkToFront: false },";
		}
		else {
			$posopts='';
		}
		WW_addInlineScript(
			"$(function(){ $('#menu-fg-$menuid>ul>li>a').each(function(){ $(this)"
			.".fgmenu({ content:$(this).next().outerHTML(), choose:function(ev,ui"
			."){ document.location=ui.item[0].childNodes(0).href; }, $posopts fly"
			."Out:true }); }); $('.menu-fg>ul>li').addClass('fg-menu-top-level');"
			."});"
		);
	}
	return $c;
}
$fg_menus=0;
