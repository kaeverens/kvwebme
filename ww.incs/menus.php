<?php
/**
	* menu functions
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

// { Menu_containsPage

function Menu_containsPage($needle, $haystack) {
	$r=Page::getInstance($needle);
	if (!isset($r->parent) || $r->parent==0) {
		return 0;
	}
	if ($r->parent==$haystack) {
		return 1;
	}
	return Menu_containsPage($r->parent, $haystack);
}

// }
// { Menu_getChildren

function Menu_getChildren(
	$parentid,
	$currentpage=0,
	$isadmin=0,
	$topParent=0
) {
	global $_languages;
	$md5=md5(
		$parentid.'|'.$currentpage.'|'.$isadmin.'|'.$topParent
		.'|'.join(',', $_languages).'|'.@$_SESSION['language']
	);
	$pageParentFound=0;
	// { first, check to see if this is a menu that should be handled by a plugin
	if (preg_match('/[a-z]/', $parentid)) {
		$bits=explode('_', $parentid);
		return $GLOBALS['PLUGINS'][$bits[0]]['triggers']['menu-subpages'](
			null,
			$bits[1]
		);
	}
	// }
	if ($parentid) {
		$PARENTDATA=Page::getInstance($parentid);
		$PARENTDATA->initValues();
	}
	else {
		$PARENTDATA=(object)array(
			'id'=>'0',
			'order_of_sub_pages'=>'ord',
			'order_of_sub_pages_dir'=>'asc'
		);
	}
	$filter=$isadmin?'':'&& !(special&2)';
	// { override menu if a trigger causes the override
	$trigger=Core_trigger('menu-subpages', $PARENTDATA);
	if ($trigger) {
		return $trigger;
	}
	// }
	// { menu order
	$order='ord,name';
	if (isset($PARENTDATA->vars['order_of_sub_pages'])) {
		switch($PARENTDATA->vars['order_of_sub_pages']) {
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
				$order.=',name'; // }
		}
	}
	// }
	$sql='select id as subid,id,name,alias,type,(select count(id) from pages where '
		."parent=subid $filter) as numchildren from pages where parent='"
		.$parentid."' $filter order by $order";
	$rs=Core_cacheLoad('pages', md5($sql), -1);
	if ($rs==-1) {
		$rs=dbAll($sql);
		Core_cacheSave('pages', md5($sql), $rs);
	}
	$menuitems=array();
	$i=0;
	foreach ($rs as $k=>$r) {
		$PAGEDATA=Page::getInstance($r['id']);
		if (isset($PAGEDATA->banned) && $PAGEDATA->banned) {
			continue;
		}
		$c=array();
		$c[]=($parentid==$topParent)?'menuItemTop':'menuItem';
		if (!$i++) {
			$c[]='first';
		}
		$c[]='c'.$i;
		// { see if the menu item has sub-pages according to a trigger
		$trigger=is_array(Core_trigger('menu-subpages', $PAGEDATA));
		if ($trigger) {
			$r['numchildren']=1;
			$rs[$k]['numchildren']=1;
		}
		// }
		if ($r['numchildren']) {
			$c[]='ajaxmenu_hasChildren';
		}
		if ($r['id']==$currentpage) {
			$c[]='ajaxmenu_currentPage';
			$pageParentFound=1;
		}
		else {
			if ($r['numchildren']
				&&!$pageParentFound
				&&Menu_containsPage($currentpage, $r['id'])
			) {
				$c[]='ajaxmenu_containsCurrentPage';
				$pageParentFound=1;
			}
		}
		$rs[$k]['classes']=join(' ', $c);
		$rs[$k]['link']=$PAGEDATA->getRelativeURL();
		$rs[$k]['name']=__FromJson($PAGEDATA->name);
		$rs[$k]['parent']=$parentid;
		$menuitems[]=$rs[$k];
	}
	return $menuitems;
}

// }
// { Menu_show

function Menu_show($b) {
	WW_addScript('/j/menu.js');
	global $PAGEDATA, $_languages;
	if (!$PAGEDATA->id) {
		return '';
	}
	$md5=md5(
		'ww_menudisplay|'.print_r($b, true).'|'.join(',', $_languages)
		.'|'.@$_SESSION['language']
	);
	$cache=Core_cacheLoad('menus', $md5);
	if ($cache) {
		return $cache;
	}
	if (is_array($b)) {
		$align=(isset($b['direction']) && $b['direction']=='vertical')?'Left':'Top';
		$vals=$b;
	}
	else{
		$arr=explode('|', $b);
		$b=$arr[0];
		$vals=array();
		if (count($arr)>1) {
			$d=split(',', $arr[1]);
		}
		else {
			$d=array();
		}
		foreach ($d as $e) {
			$f=split('=', $e);
			if (count($f)>1) {
				$vals[$f[0]]=$f[1];
			}
			else {
				$vals[$f[0]]=1;
			}
		}
		$c='';
		$align=($b=='vertical')?'Left':'Top';
	}
	$parent=0;
	$classes='';
	if (isset($vals['mode'])) {
		if ($vals['mode']=='accordian' || $vals['mode']=='accordion') {
			$classes.=' click_required accordion';
		}
		else if ($vals['mode']=='two-tier') {
			$classes.=' two-tier';
		}
	}
	else {
		$vals['mode']='default';
	}
	if (isset($vals['preopen_menu'])) {
		$classes.=' preopen_menu';
	}
	if (isset($vals['close']) && $vals['close']=='no') {
		$classes.=' noclose';
	}
	if (isset($vals['parent'])) {
		$r=Page::getInstanceByName($vals['parent']);
		if ($r) {
			$parent=$r->id;
		}
	}
	if (isset($vals['spans'])) {
		$vals['spans']=(int)$vals['spans'];
	}
	else {
		$vals['spans']=1;
	}
	$ajaxmenu=isset($vals['nodropdowns']) && $vals['nodropdowns']?'':' ajaxmenu ';
	$c='<div id="ajaxmenu'.$parent.'" class="menuBar'.$align.$ajaxmenu
		.$classes.' parent'.$parent.'">';
	$rs=Menu_getChildren($parent, $PAGEDATA->id, 0, $parent);
	$links=0;
	if ($vals['spans']) {
		$spanl='<span class="l"></span>';
		$spanr='<span class="r"></span>';
	}
	else {
		$spanl='';
		$spanr='';
	}
	if (count($rs)) {
		foreach ($rs as $r) {
			$page=Page::getInstance($r['id']);
			if (!$links) {
				$r['classes'].=' first';
			}
			$c.='<a id="ajaxmenu_link'.$r['id'].'" class="'.$r['classes'].'" href="'
				.$page->getRelativeURL().'">'.$spanl
				.htmlspecialchars(__FromJson($page->name))
				.$spanr.'</a>';
			$links++;
		}
	}
	if (!@$GLOBALS['DBVARS']['disable-hidden-sitemap']) {
		$c.='<a class="menuItemTop" style="display:none" href="'
			.$PAGEDATA->getRelativeURL().'?cmsspecial=sitemap">'
			.__('Site Map').'</a>';
	}
	$c.='</div>';
	if ($vals['mode']=='two-tier') {
		$pid=$PAGEDATA->getTopParentId();
		if ($pid!=2 && $pid!=3 && $pid!=17 && $pid!=32 && $pid!=33 && $pid!=34) {
			$pid=2;
		}
		$rs=Menu_getChildren($pid, $PAGEDATA->id, 0, $parent);
		$c.='<div id="ajaxmenu'.$pid.'" class="menu tier-two">';
		if (count($rs)) {
			foreach ($rs as $r) {
				$page=Page::getInstance($r['id']);
				$c.='<a id="ajaxmenu_link'.$r['id'].'" class="'.$r['classes']
					.'" href="'.$page->getRelativeURL().'">'.$spanl
					.htmlspecialchars($page->name).$spanr.'</a>';
			}
		}
		else {
			$c.='<a><span class="l"></span>&nbsp;<span class="r"></span></a>';
		}
		$c.='</div>';
	}
	Core_cacheSave('menus', $md5, $c);
	return $c;
}

// }
