<?php
/**
	* common functions
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

require_once dirname(__FILE__).'/basics.php';

// { Core_getJQueryScripts

/**
	* link to current jQuery/jQuery-UI scripts
	*
	* @return string HTML of the links
	*/
function Core_getJQueryScripts() {
	global $DBVARS;
	$jquery_versions=array('1.8.2', '1.9.0');
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
	$uicssbits=(int)@$DBVARS['disable-jqueryui-css'];
	$uicss=defined('IN_ADMIN')
		?($uicssbits&2 ? '':'<link href="'.$jurls[2].'" rel="stylesheet" />')
		:($uicssbits&1 ? '':'<link href="'.$jurls[2].'" rel="stylesheet" />');
	return $uicss
		.'<script src="'.$jurls[0].'"></script>'
		.'<script src="'.$jurls[1].'"></script>';
}

// }
// { Core_languagesGetUi

/**
	* show list of languages
	*
	* @param array $params array of parameters
	*
	* @return string HTML of <ul> list of languages
	*/
function Core_languagesGetUi($params=null) {
	require_once dirname(__FILE__).'/api-funcs.php';
	$languages=Core_languagesGet();
	switch (@$params['type']) {
		case 'selectbox': // {
			$ui='<select id="core-language">';
			foreach ($languages as $language) {
				$ui.='<option value="'.$language['code'].'"';
				if ($language['code']==@$_SESSION['language']) {
					$ui.=' selected="selected"';
				}
				$ui.='>'.htmlspecialchars($language['name']).'</option>';
			}
			$ui.='</select>';
		break; // }
		default: // {
			$ui='<h2>'.__('Languages', 'core').'</h2><ul class="languages">';
			$url=preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']);
			foreach ($languages as $language) {
				$ui.='<li><a href="#'.$language['code'].'"';
				if ($language['code']==@$_SESSION['language']) {
					$ui.=' class="selected"';
				}
				$ui.='>'.htmlspecialchars($language['name']).'</a></li>';
			}
			$ui.='</ul>';
			// }
	}
	return $ui;
}

// }
// { Core_dateM2H

/**
	* convert a MySQL date to a human-readable one
	*
	* @param string $d    the date to convert
	* @param string $type the type of date to return
	*
	* @return string the transformed date
	*/
function Core_dateM2H($d, $type = 'date') {
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

// }
// { Core_locationsGetUi

/**
	* show list of locations
	*
	* @param array $params array of parameters
	*
	* @return string HTML of <select> list of locations
	*/
function Core_locationsGetUi($params=array()) {
	require_once dirname(__FILE__).'/api-funcs.php';
	$locations=Core_locationsGetFull();
	$ui='<select id="core-location">';
	foreach ($locations as $k=>$v) {
		$ui.='<option value="'.$v.'"';
		if ($v==@$_SESSION['location']['id']) {
			$ui.=' selected="selected"';
			WW_addInlineScript('pagedata.locationName="'.addslashes($k).'";');
		}
		$ui.='>'.htmlspecialchars($k).'</option>';
	}
	$ui.='</select>';
	WW_addScript('/j/locations-selector.js');
	WW_addInlineScript('var locationSelectorOpts='.json_encode($params).';');
	return $ui;
}

// }
// { Core_menuShowFg

/**
	* get HTML for building a hierarchical menu
	*
	* @param array $opts options
	*
	* @return string the html
	*/
function Core_menuShowFg ($opts=array()) {
	if (!function_exists('menuBuildFg')) { // menuBuildFg
		/**
		 * get recursive details of pages to build a menu
		 *
		 * @param int   $parentid the parent's ID
		 * @param int   $depth    current menu depth
		 * @param array $options  any further options
		 *
		 * @return string HTML of the sub-menu
		 */
		function menuBuildFg($parentid, $depth, $options) {
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
					break; // }
				}
			}
			// }
			$sql="select id,name,type from pages where parent='".$parentid
				."' and !(special&2) order by $order";
			$md5=md5($sql);
			$rs=Core_cacheLoad('pages', $md5, -1);
			if ($rs===-1) {
				$rs=dbAll($sql);
				Core_cacheSave('pages', $md5, $rs);
			}
			if ($rs===false || !count($rs)) {
				return '';
			}
			$items=array();
			foreach ($rs as $r) {
				$item='<li>';
				$page=Page::getInstance($r['id'])->initValues();
				$item.='<a class="menu-fg menu-pid-'.$r['id'].'" href="'
					.$page->getRelativeUrl().'">'
					.htmlspecialchars(__FromJson($page->name)).'</a>';
				// { override menu if a trigger causes the override
				$submenus=Core_trigger(
					'menu-subpages-html',
					array($page, $depth+1, $options)
				);
				if ($submenus) {
					$item.=$submenus;
				}
				// }
				// { otherwise load sub-menus as usual
				else {
					$item.=menuBuildFg($r['id'], $depth+1, $options);
				}
				// }
				$item.='</li>';
				$items[]=$item;
			}
			$options['columns']=(int)$options['columns'];
			// { return top-level menu
			if (!$depth) {
							return '<ul>'.join('', $items).'</ul>';
			}
			// }
			$s='';
			if ($options['style_from']=='1') {
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
			// { return 1-column sub-menu
			if ($options['columns']<2) {
				return '<ul'.$s.'>'.join('', $items).'</ul>';
			}
			// }
			// { return multi-column submenu
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
			// }
		}
	}
	global $_languages;
	$c='';
	$md5_1=md5('menu_fg|'.print_r($opts, true).'|'.join(', ', $_languages));
	$options=array(
		'direction' => 0,  // 0: horizontal, 1: vertical
		'parent'    => 0,  // top-level
		'background'=> '', // sub-menu background colour
		'columns'   => 1,  // for wide drop-down sub-menus
		'opacity'   => 0,  // opacity of the sub-menu
		'type'      => 0,  // 0=drop-down, 1=accordion, 3=tree list
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
	$md5=md5(
		$options['parent'].'|0|'.json_encode($options).'|'.join(', ', $_languages)
	);
	$html=Core_cacheLoad('pages', 'fgmenu-'.$md5);
	if (1 || $html===false) {
		$html=menuBuildFg($options['parent'], 0, $options);
		Core_cacheSave('pages', 'fgmenu-'.$md5, $html);
	}
	switch ($options['type']) {
		case 2: // { tree
			$c.='<div class="menu-tree">'.$html.'</div>';
		break; // }
		case 1: // { accordion
			WW_addScript('/j/menu-accordion/menu.js');
			WW_addCSS('/j/menu-accordion/menu.css');
			$class = ( $options[ 'state' ] == 0 )
				? ' contracted'
				: (( $options[ 'state' ] == 1 ) ? ' expanded' : ' expand-selected') ;
			$c.= '<div class="menu-accordion'.$class.'">'.$html.'</div>';
		break; // }
		default: // { fly-out
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
		break; // }
	}
	return $c;
}

// }
// { menuDisplay

/**
	* smarty function for setting up a menu
	*
	* @param int $a parent ID
	*
	* @return string HTML of the menu
	*/
function menuDisplay($a=0) {
	require_once SCRIPTBASE . 'ww.incs/menus.php';
	return Menu_show($a);
}

// }
// { redirect

/**
	* redirect the browser to a different URL using a 301 redirect
	*
	* @param string $addr the address to redirect to
	*
	* @return null
	*/
function redirect($addr) {
	header('HTTP/1.1 301 Moved Permanently');
	header('Location: '.$addr);
	echo '<html><head><script defer="defer" type="text/javascript">'
		.'setTimeout(function(){document.location="'.$addr.'";},10);</script>'
		.'</head><body></body></html>';
	Core_quit();
}

// }
// { Template_breadcrumbs

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
	if ($top) {
		$pre='<div class="breadcrumbs">';
		$suf='</div>';
		$bcfn=@$GLOBALS['PLUGINS'][$page->plugin]['frontend']['breadcrumbs'];
		$suf=$bcfn
			?$bcfn($page->getRelativeURL()).'</div>'
			:'</div>';
	}
	else {
		$pre='';
		$suf='';
	}
	return $pre.$c.'<a href="' . $page->getRelativeURL() . '">' 
		. htmlspecialchars(__fromJSON($page->name)) . '</a>'.$suf;
}

// }
// { Template_logoDisplay

/**
	* return a logo HTML string if the admin uploaded one
	*
	* @param array $vars array of logo parameters (width, height)
	*
	* @return string
	*/
function Template_logoDisplay($vars) {
	$vars=array_merge(array('width'=>64, 'height'=>64), $vars);
	$image_file_orig=USERBASE.'/f/skin_files/logo.png';
	if (!file_exists($image_file_orig)) {
		return '';
	}
	$x=(int)$vars['width'];
	$y=(int)$vars['height'];
	$geometry=$x.'x'.$y;
	$image_file=USERBASE.'/f/skin_files/logo-'.$geometry.'.png';
	if (!file_exists($image_file)
		|| filectime($image_file)<filectime($image_file_orig)
	) {
		CoreGraphics::resize($image_file_orig, $image_file, $x, $y);
	}
	$size=getimagesize($image_file);
	return '<img class="logo" src="/i/blank.gif" style="'
		.'background:url(/f/skin_files/logo-'.$geometry.'.png) no-repeat;'
		.'width:'.$size[0].'px;height:'.$size[1].'px;" />';
}

// }
// { cmsMail

/**
	* send an email in HTML and text format. optionally with attached files
	*
	* @param string $from    email address of the sender
	* @param string $to      the recipient
	* @param string $subject subject line of the email
	* @param string $message the body of the email
	* @param array  $files   list of files to attach
	*
	* @return null
	*/
function cmsMail($from, $to, $subject, $message, $files = false) {
	require_once dirname(__FILE__).'/mail.php';
	send_mail($from, $to, $subject, $message, $files);
}

// }

$Core_isAdmin = 0;
$sitedomain=str_replace('www.', '', $_SERVER['HTTP_HOST']);
if (strpos($_SERVER['REQUEST_URI'], 'ww.admin/')!==false) {
	$kfm_do_not_save_session=true;
	require_once SCRIPTBASE . 'j/kfm/api/api.php';
	require_once SCRIPTBASE . 'j/kfm/initialise.php';
}
// { user authentication
if (@$_REQUEST['action']=='login'
	|| isset($_SESSION['userdata']['id'])
	|| isset($_REQUEST['logout'])
) {
	require_once dirname(__FILE__).'/user-authentication.php';
}
// }
$fg_menus=0;
