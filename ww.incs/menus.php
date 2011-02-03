<?php
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
function Menu_getChildren(
	$parentid,
	$currentpage=0,
	$isadmin=0,
	$topParent=0,
	$search_options=0
) {
	$md5=md5(
		$parentid.'|'.$currentpage.'|'.$isadmin.'|'.$topParent.'|'.$search_options
	);
	$cache=cache_load('menus', $md5);
	if ($cache) {
		return $cache;
	}
	$pageParentFound=0;
	$PARENTDATA=Page::getInstance($parentid);
	$PARENTDATA->initValues();
	$filter=$isadmin?'':'&& !(special&2)';
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
	$rs=dbAll(
		'select id as subid,id,name,type,(select count(id) from pages where '
		."parent=subid $filter) as numchildren from pages where parent='"
		.$parentid."' $filter order by $order"
	);
	$menuitems=array();
	// { optimise db retrieval of pages
	$ids=array();
	foreach ($rs as $r) {
		if (!isset(Page::$instances[$r['id']])) {
			$ids[]=$r['id'];
		}
	}
	Pages::precache($ids);
	// }
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
		$rs[$k]['name']=$PAGEDATA->name;
		$rs[$k]['parent']=$parentid;
		$menuitems[]=$rs[$k];
	}
	cache_save('menus', $md5, $menuitems);
	return $menuitems;
}
function Menu_show($b) {
	global $PAGEDATA;
	if (!$PAGEDATA->id) {
		return '';
	}
	$md5=md5('ww_menudisplay|'.print_r($b, true));
	$cache=cache_load('menus', $md5);
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
	$search_options=0;
	$ajaxmenu=$vals['nodropdowns']?'':' ajaxmenu ';
	$c='<div id="ajaxmenu'.$parent.'" class="menuBar'.$align.$ajaxmenu
		.$classes.' parent'.$parent.'">';
	$rs=Menu_getChildren($parent, $PAGEDATA->id, 0, $parent, $search_options);
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
				.$page->getRelativeURL().'">'.$spanl.htmlspecialchars($page->name)
				.$spanr.'</a>';
			$links++;
		}
	}
	$c.='<a class="menuItemTop nojs" href="'.$PAGEDATA->getRelativeURL()
		.'?webmespecial=sitemap">'.__('Site Map').'</a>';
	$c.='</div>';
	if ($vals['mode']=='two-tier') {
		$pid=$PAGEDATA->getTopParentId();
		if ($pid!=2 && $pid!=3 && $pid!=17 && $pid!=32 && $pid!=33 && $pid!=34) {
			$pid=2;
		}
		$rs=Menu_getChildren($pid, $PAGEDATA->id, 0, $parent, $search_options);
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
	cache_save('menus', $md5, $c);
	return $c;
}
