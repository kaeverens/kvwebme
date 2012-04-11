<?php
/**
	* API for common admin WebME functions
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

// { Core_adminAdminVarsSave

/**
	* save variables of an admin
	*
	* @return status of the save
	*/
function Core_adminAdminVarsSave() {
	$name=$_REQUEST['name'];
	$val=$_REQUEST['val'];
	dbQuery(
		'delete from admin_vars where admin_id='.$_SESSION['userdata']['id']
		.' and varname="'.addslashes($name).'"'
	);
	dbQuery(
		'insert into admin_vars set admin_id='.$_SESSION['userdata']['id']
		.',varname="'.addslashes($name).'",varvalue="'.addslashes($val).'"'
	);
	Core_cacheClear('admin');
	return array('ok'=>1);
}

// }
// { Core_adminCronGet

/**
	* get list of cron jobs
	*
	* @return status
	*/
function Core_adminCronGet() {
	if (@$_REQUEST['name']) {
		return dbRow(
			'select * from cron where name="'.addslashes($_REQUEST['name']).'"'
		);
	}
	return dbAll('select * from cron');
}

// }
// { Core_adminCronSave

/**
	* save cron job
	*
	* @return status
	*/
function Core_adminCronSave() {
	global $DBVARS;
	$id=(int)$_REQUEST['id'];
	$field=$_REQUEST['field'];
	$value=$_REQUEST['value'];
	dbQuery(
		'update cron set `'.addslashes($field).'`="'.addslashes($value)
		.'" where id='.$id
	);
	unset($DBVARS['cron-next']);
	Core_configRewrite();
	return array('ok'=>1);
}

// }
// { Core_adminDirectoriesGet

/**
	* get list of directories (recursive)
	*
	* @return status
	*/
function Core_adminDirectoriesGet() {
	/**
		* return list of contained directories
		*
		* @param string $base base directory of site
		* @param string $dir  directory to list
		*
		* @return array list of contained directories
		*/
	function getSubdirs($base, $dir) {
		$arr=array();
		$D=new DirectoryIterator($base.$dir);
		$ds=array();
		foreach ($D as $dname) {
			if ($dname->isDot() || !$dname->isDir() 
				|| strpos($dname->getFilename(), '.')===0
			) {
				continue;
			}
			$ds[]=$dname->getFilename();
		}
		asort($ds);
		foreach ($ds as $d) {
			$arr[$dir.'/'.$d]=$dir.'/'.$d;
			$arr=array_merge($arr, getSubdirs($base, $dir.'/'.$d));
		}
		return $arr;
	}
	$arr=array_merge(array('/'=>'/'), getSubdirs(USERBASE.'/f', ''));
	return $arr;
}

// }
// { Core_adminFileDelete

/**
	* delete a file
	*
	* @return array status
	*/
function Core_adminFileDelete() {
	$fname=$_REQUEST['fname'];
	if (strpos($fname, '..')!==false) {
		return array('error'=>'no hacking please');
	}
	unlink(USERBASE.'/f/'.$fname);
	return array('ok'=>1);
}

// }
// { Core_adminLanguagesAdd

/**
	* add language
	*
	* @return status
	*/
function Core_adminLanguagesAdd() {
	$name=$_REQUEST['name'];
	$code=$_REQUEST['code'];
	if (!$name || !$code) {
		return array(
			'error'=>'You must fill in Name and Code'
		);
	}
	$isInUse=dbOne(
		'select count(id) as ids from language_names where name="' 
		.addslashes($name).'" or code="'.addslashes($code).'"', 'ids'
	);
	if ($isInUse) {
		return array(
			'error'=>'Either the Name or Code are already in use'
		);
	}
	dbQuery(
		'insert into language_names set name="'.addslashes($name).'"'
		.',code="'.addslashes($code).'",is_default=0'
	);
	Core_cacheClear('core');
	return array('ok'=>1);
}

// }
// { Core_adminLanguagesDelete

/**
	* delete language
	*
	* @return status
	*/
function Core_adminLanguagesDelete() {
	$id=(int)$_REQUEST['id'];
	dbQuery('delete from language_names where id='.$id);
	Core_cacheClear('core');
	return array('ok'=>1);
}

// }
// { Core_adminLanguagesEdit

/**
	* update language
	*
	* @return status
	*/
function Core_adminLanguagesEdit() {
	$id=(int)$_REQUEST['id'];
	$name=$_REQUEST['name'];
	$code=$_REQUEST['code'];
	$is_default=(int)$_REQUEST['is_default'];
	if (!$name || !$code) {
		return array(
			'error'=>'You must fill in Name and Code'
		);
	}
	if ($is_default) {
		dbQuery('update language_names set is_default=0');
	}
	else {
		$r=dbRow('select * from language_names where id='.$id);
		if ($r['is_default']=='1') {
			$is_default=1; // cannot unset is_default. must set on a different lang
		}
	}
	dbQuery(
		'update language_names set name="'.addslashes($name).'"'
		.',code="'.addslashes($code).'",is_default='.$is_default
		.' where id='.$id
	);
	Core_cacheClear('core');
	return array('ok'=>1);
}

// }
// { Core_adminLanguagesEditString

/**
	* so a translation
	*
	* @return status
	*/
function Core_adminLanguagesEditString() {
	$str=$_REQUEST['str'];
	$trstr=$_REQUEST['trstr'];
	$lang=$_REQUEST['lang'];
	$context=$_REQUEST['context'];
	dbQuery(
		'delete from languages where str="'.addslashes($str).'" and lang="'
		.addslashes($lang).'" and context="'.addslashes($context).'"'
	);
	dbQuery(
		'insert into languages set str="'.addslashes($str).'", lang="'
		.addslashes($lang).'", context="'.addslashes($context).'", trstr="'
		.addslashes($trstr).'"'
	);
	Core_cacheClear('core');
	return array('ok'=>1);
}

// }
// { Core_adminLanguagesGetStrings

/**
	* get list of translateable strings
	*
	* @return array of strings
	*/
function Core_adminLanguagesGetStrings() {
	return dbAll('select distinct str,context from languages');
}

// }
// { Core_adminLanguagesGetTrStrings

/**
	* get list of translated strings
	*
	* @return array of strings
	*/
function Core_adminLanguagesGetTrStrings() {
	$lang=$_REQUEST['lang'];
	return dbAll(
		'select str,context,trstr from languages where lang="'
		.addslashes($lang).'"'
	);
}

// }
// { Core_adminLoadJSVars

/**
	* load session variables
	*
	* @return array session vars
	*/
function Core_adminLoadJSVars() {
	if (!isset($_SESSION['js'])) {
		$_SESSION['js']=array();
	}
	return $_SESSION['js'];
}

// }
// { Core_adminLocationsAdd

/**
	* add location
	*
	* @return status
	*/
function Core_adminLocationsAdd() {
	$name=$_REQUEST['name'];
	$lat=(float)$_REQUEST['lat'];
	$lng=(float)$_REQUEST['lng'];
	if (!$name) {
		return array(
			'error'=>'You must fill in Name'
		);
	}
	$isInUse=dbOne(
		'select count(id) as ids from locations where name="' 
		.addslashes($name).'"', 'ids'
	);
	if ($isInUse) {
		return array(
			'error'=>'Name already in use'
		);
	}
	dbQuery(
		'insert into locations set name="'.addslashes($name).'"'
		.',lat='.$lat.',lng='.$lng.',is_default=0'
	);
	Core_cacheClear('core');
	return array('ok'=>1);
}

// }
// { Core_adminLocationDelete

/**
	* delete location
	*
	* @return status
	*/
function Core_adminLocationDelete() {
	$id=(int)$_REQUEST['id'];
	dbQuery('delete from locations where id='.$id);
	Core_cacheClear('core');
	return array('ok'=>1);
}

// }
// { Core_adminLocationsEdit

/**
	* update location
	*
	* @return status
	*/
function Core_adminLocationsEdit() {
	$id=(int)$_REQUEST['id'];
	$name=$_REQUEST['name'];
	$lat=(float)$_REQUEST['lat'];
	$lng=(float)$_REQUEST['lng'];
	$is_default=(int)$_REQUEST['is_default'];
	if (!$name) {
		return array(
			'error'=>'You must fill in Name'
		);
	}
	if ($is_default) {
		dbQuery('update locations set is_default=0');
	}
	else {
		$r=dbRow('select * from locations where id='.$id);
		if ($r['is_default']=='1') {
			$is_default=1; // cannot unset is_default. must set on a different lang
		}
	}
	dbQuery(
		'update locations set name="'.addslashes($name).'"'
		.',lat='.$lat.',lng='.$lng.',is_default='.$is_default
		.' where id='.$id
	);
	Core_cacheClear('core');
	return array('ok'=>1);
}

// }
// { Core_adminMenusGet

/**
	* get menus for admin
	*
	* @return menu
	*/
function Core_adminMenusGet() {
	$menus=Core_cacheLoad('admin', 'menus-'.$_SESSION['userdata']['id']);
	if (!$menus) {
		$menus=dbOne(
			'select varvalue from admin_vars where admin_id='
			.$_SESSION['userdata']['id'].' and varname="admin_menu"',
			'varvalue'
		);
		if ($menus) {
			$menus=json_decode($menus);
		}
		else {
			$menus=dbOne(
				'select varvalue from admin_vars where admin_id=0'
				.' and varname="admin_menu"',
				'varvalue'
			);
			if ($menus) {
				$menus=json_decode($menus);
			}
			else {
				global $PLUGINS;
				// { setup standard menu items
				$menus=array(
					'Pages'=>array(
						'_link'=>'pages.php'
					),
					'Site Options'=>array(
						'General'=> array('_link'=>'siteoptions.php'),
						'Languages'=>array(
							'_link'=>'javascript:Core_screen(\'CoreSiteoptions\', \'js:Languages\')'
						),
						'Locations'=>array(
							'_link'=>'javascript:Core_screen(\'CoreSiteoptions\', \'js:Locations\')'
						),
						'Menus' => array(
							'_link'=>'javascript:Core_screen(\'CoreSiteoptions\', \'js:Menus\')'
						),
						'Users' => array('_link'=>'siteoptions.php?page=users'),
						'Plugins'=> array('_link'=>'siteoptions.php?page=plugins'),
						'Themes' => array('_link'=>'siteoptions.php?page=themes'),
						'Timed Events'=>array(
							'_link'=>'javascript:Core_screen(\'CoreSiteoptions\', \'js:Cron\')'
						)
					)
				);
				// }
				// { add custom items (from plugins)
				foreach ($PLUGINS as $pname=>$p) {
					if (!isset($p['admin']) || !isset($p['admin']['menu'])) {
						continue;
					}
					foreach ($p['admin']['menu'] as $name=>$page) {
						if (preg_match('/[^a-zA-Z0-9 >]/', $name)) {
							continue; // illegal characters in name
						}
						$link=strpos($page, 'js:')===false
							?'plugin.php?_plugin='.$pname.'&amp;_page='.$page
							:'javascript:Core_screen(\''.$pname.'\', \''.$page.'\');';
						$json='{"'.str_replace('>', '":{"', $name).'":{"_link":"'.$link.'"}}'
							.str_repeat('}', substr_count($name, '>'));
						$menus=array_merge_recursive($menus, json_decode($json, true));
					}
				}
				// }
				// { add final items
				$menus['Site Options']['Stats']=array('_link'=>'/ww.admin/stats.php');
				$menus['View Site']=array( '_link'=>'/', '_target'=>'_blank');
				$menus['Help']=array( '_link'=>'http://kvweb.me/', '_target'=>'_blank');
				$menus['Log Out']=  array('_link'=>'/?logout=1');
				$menus['Misc']['File Manager']=array(
					'_link'=>'javascript:return window.open(\'/j/kfm/\', \'kfm\', '
					.'\'modal,width=800,height=640\')'
				);
				// }
				dbQuery(
					'insert into admin_vars set admin_id='.$_SESSION['userdata']['id']
					.', varname="admin_menu", varvalue="'
					.addslashes(json_encode($menus)).'"'
				);
			}
		}
		Core_cacheSave('admin', 'menus-'.$_SESSION['userdata']['id'], $menus);
	}
	return $menus;
}

// }
// { Core_adminMenusGetDefault

/**
	* get default menu set for admin
	*
	* @return menu
	*/
function Core_adminMenusGetDefault() {
	$menus=Core_cacheLoad('admin', 'menus-0');
	if (!$menus) {
		$menus=dbOne(
			'select varvalue from admin_vars where admin_id=0'
			.' and varname="admin_menu"',
			'varvalue'
		);
		if ($menus) {
			$menus=json_decode($menus);
		}
		else {
			global $PLUGINS;
			// { setup standard menu items
			$menus=array(
				'Pages'=>array(
					'_link'=>'pages.php'
				),
				'Site Options'=>array(
					'General'=> array('_link'=>'siteoptions.php'),
					'Languages'=>array(
						'_link'=>'javascript:Core_screen(\'CoreSiteoptions\', \'js:Languages\')'
					),
					'Locations'=>array(
						'_link'=>'javascript:Core_screen(\'CoreSiteoptions\', \'js:Locations\')'
					),
					'Menus' => array(
						'_link'=>'javascript:Core_screen(\'CoreSiteoptions\', \'js:Menus\')'
					),
					'Users' => array('_link'=>'siteoptions.php?page=users'),
					'Plugins'=> array('_link'=>'siteoptions.php?page=plugins'),
					'Themes' => array('_link'=>'siteoptions.php?page=themes'),
					'Timed Events'=>array(
						'_link'=>'javascript:Core_screen(\'CoreSiteoptions\', \'js:Cron\')'
					)
				)
			);
			// }
			// { add custom items (from plugins)
			foreach ($PLUGINS as $pname=>$p) {
				if (!isset($p['admin']) || !isset($p['admin']['menu'])) {
					continue;
				}
				foreach ($p['admin']['menu'] as $name=>$page) {
					if (preg_match('/[^a-zA-Z0-9 >]/', $name)) {
						continue; // illegal characters in name
					}
					$link=strpos($page, 'js:')===false
						?'plugin.php?_plugin='.$pname.'&amp;_page='.$page
						:'javascript:Core_screen(\''.$pname.'\', \''.$page.'\');';
					$json='{"'.str_replace('>', '":{"', $name).'":{"_link":"'.$link.'"}}'
						.str_repeat('}', substr_count($name, '>'));
					$menus=array_merge_recursive($menus, json_decode($json, true));
				}
			}
			// }
			// { add final items
			$menus['Site Options']['Stats']=array('_link'=>'/ww.admin/stats.php');
			$menus['View Site']=array( '_link'=>'/', '_target'=>'_blank');
			$menus['Help']=array( '_link'=>'http://kvweb.me/', '_target'=>'_blank');
			$menus['Log Out']=  array('_link'=>'/?logout=1');
			$menus['Misc']['File Manager']=array(
				'_link'=>'javascript:return window.open(\'/j/kfm/\', \'kfm\', '
				.'\'modal,width=800,height=640\')'
			);
			// }
			dbQuery(
				'insert into admin_vars set admin_id=0'
				.', varname="admin_menu", varvalue="'
				.addslashes(json_encode($menus)).'"'
			);
		}
		Core_cacheSave('admin', 'menus-0', $menus);
	}
	return $menus;
}

// }
// { Core_adminMenusSetMineAsDefault

/**
	* set the current admin's menu as the default admin menu
	*
	* @return menu
	*/
function Core_adminMenuSetMineAsDefault() {
	$menus=dbOne(
		'select varvalue from admin_vars where admin_id='
		.$_SESSION['userdata']['id'].' and varname="admin_menu"',
		'varvalue'
	);
	if (!$menus) {
		return array('{"error":"admin does not have a custom menu"}');
	}
	dbQuery('delete from admin_vars where admin_id=0 and varname="admin_menu"');
	dbQuery(
		'insert into admin_vars set admin_id=0,varname="admin_menu"'
		.',varvalue="'.addslashes($menus).'"'
	);
	Core_cacheClear('admin');
	return array('{"ok":1}');
}

// }
// { Core_adminMenusClearAll

/**
	* clear all menus
	*
	* @return menu
	*/
function Core_adminMenuClearAll() {
	dbQuery(
		'delete from admin_vars where varname="admin_menu"'
	);
	Core_cacheClear('admin');
	return array('{"ok":1}');
}

// }
// { Core_adminMenusClearAllAdmins

/**
	* clear all admin's menus
	*
	* @return menu
	*/
function Core_adminMenuClearAllAdmins() {
	dbQuery(
		'delete from admin_vars where admin_id and varname="admin_menu"'
	);
	Core_cacheClear('admin');
	return array('{"ok":1}');
}

// }
// { Core_adminMenusClearMine

/**
	* clear the current admin's menu
	*
	* @return menu
	*/
function Core_adminMenuClearMine() {
	dbQuery(
		'delete from admin_vars where admin_id='.$_SESSION['userdata']['id']
		.' and varname="admin_menu"'
	);
	Core_cacheClear('admin');
	return array('{"ok":1}');
}

// }
// { Core_adminPageChildnodes

/**
	* get list of pages and and number of their kids
	*
	* @return array
	*/
function Core_adminPageChildnodes() {
	$pid=(int)preg_replace('/[^0-9]/', '', $_REQUEST['id']);
	$c=Core_cacheLoad('pages', 'adminmenu'.$pid);
	if ($c) {
		return $c;
	}
	$rs=dbAll(
		'select id,id as pid,special&2 as hide,type,name,'
		.'(select count(id) from pages where parent=pid) as children '
		.'from pages where parent='.$pid.' order by ord,name'
	);
	$data=array();
	foreach ($rs as $r) {
		$item=array(
			'data' => __FromJson($r['name'], true),
			'attr' => array(
				'id'   => 'page_'.$r['id']
			),
			'children'=>$r['children']?array():false
		);
		if ($r['type']!=='0') {
			$item['attr']['type']=$r['type'];
		}
		if ($r['hide']=='2') {
			$item['attr']['hide']='yes';
		}
		$data[]=$item;
	}
	Core_cacheSave('pages', 'adminmenu'.$pid, $data);
	return $data;
}

// }
// { Core_adminPageCopy

/**
  * create a copy of a page
  *
  * @return array status of the copy
  */
function Core_adminPageCopy() {
	$id=(int)$_REQUEST['id'];
	if (!$id) {
		return array('error'=>'no ID provided');
	}
	$p=dbRow('select * from pages where id='.$id);
	$name=$p['name'];
	$parts=array();
	foreach ($p as $k=>$v) {
		if ($k=='id') {
			continue;
		}
		$parts[]=$k.'="'.addslashes($v).'"';
	}
	dbQuery('insert into pages set '.join(',', $parts));
	$id=dbLastInsertId();
	dbQuery('update pages set name="'.addslashes($name).'_'.$id.'" where id='.$id);
	Core_cacheClear();
	return array('name'=>$name.'_'.$id, 'id'=>$id, 'pid'=>$p['parent']);
}

// }
// { Core_adminPageDelete

/**
  * delete a page
  *
  * @return array status of the deletion
  */
function Core_adminPageDelete() {
	$id=(int)$_REQUEST['id'];
	if (!$id) {
		return array('error'=>'no ID provided');
	}
	$r=dbRow("SELECT COUNT(id) AS pagecount FROM pages");
	if ($r['pagecount']<2) {
		return array('error'=>'there must always be at least one page.');
	}
	$q=dbQuery('select parent from pages where id="'.$id.'"');
	if ($q->rowCount()) {
		$r=dbRow('select parent from pages where id="'.$id.'"');
		dbQuery('delete from page_vars where page_id="'.$id.'"');
		dbQuery('delete from pages where id="'.$id.'"');
		dbQuery(
			'update pages set parent="'.$r['parent'].'" where parent="'.$id.'"'
		);
		Core_cacheClear();
		dbQuery('update page_summaries set rss=""');
		return array('ok'=>1);
	}
	return array('error'=>'page does not exist');
}

// }
// { Core_adminPageEdit

/**
	* create or edit a page
	*
	* @return array status of the edit
	*/
function Core_adminPageEdit() {
	/**
		* function for recursively updating a page (and its children) template
		*
		* @param int    $id       the page id
		* @param string $template the template name
		*
		* @return null
		*/
	function recursivelyUpdatePageTemplates($id, $template) {
		$pages=Pages::getInstancesByParent($id, false);
		$ids=array();
		foreach ($pages->pages as $page) {
			$ids[]=$page->id;
			recursivelyUpdatePageTemplates($page->id, $template);
		}
		if (!count($ids)) {
			return;
		}
		dbQuery(
			'update pages set template="'.addslashes($template).'" where id in ('
			.join(',', $ids).')'
		);
	}
	$id=(int)@$_REQUEST['id'];
	$pid=$id
		?dbOne('select parent from pages where id='.$id, 'parent')
		:(int)$_REQUEST['parent'];
	$special=0;
	if (isset($_REQUEST['special'])) {
		$specials=$_REQUEST['special'];
		if (is_array($specials)) {
			foreach ($specials as $a=>$b) {
				$special+=pow(2, $a);
			}
		}
		$homes=dbOne(
			"SELECT COUNT(id) AS ids FROM pages WHERE (special&1)"
			.($id?" AND id!=$id":""),
			'ids'
		);
		if ($special&1) { // there can be only one homepage
			if ($homes!=0) {
				dbQuery("UPDATE pages SET special=special-1 WHERE special&1");
			}
		}
		else {
			if ($homes==0) {
				$special+=1;
			}
		}
	}
	$keywords=@$_REQUEST['keywords'];
	$title=@$_REQUEST['title'];
	$description=@$_REQUEST['description'];
	$date_publish=isset($_REQUEST['date_publish'])
		?$_REQUEST['date_publish']
		:'0000-00-00 00:00:00';
	$date_unpublish=isset($_REQUEST['date_unpublish'])
		?$_REQUEST['date_unpublish']
		:'0000-00-00 00:00:00';
	$importance=(float)@$_REQUEST['importance'];
	if ($importance<0.1) {
		$importance=0.5;
	}
	if ($importance>1) {
		$importance=1;
	}
	// { name, alias
	$name=trim($_REQUEST['name']);
	if (!$name) {
		$name='no page name provided';
	}
	else { // check to see if name is already in use
		$sql='select id from pages where name="'.addslashes($name)
			.'" and parent='.$pid.' and id!='.$id;
		if (dbOne($sql, 'id')) {
			$i=2;
			while (dbOne(
				'select id from pages where name="'.addslashes($name.$i).'" and parent='
				.$pid.' and id!="'.$id.'"', 'id'
			)) {
				$i++;
			}
			$msgs.='<em>A page named "'.$name.'" already exists. Page name amended'
				.' to "'.$name.$i.'"</em>';
			$name.=$i;
		}
	}
	$alias = transcribe(__FromJson($name, true));
	// }
	// { body
	if (@$_REQUEST['page_vars']['_body']) {
		$_REQUEST['body']=$_REQUEST['page_vars']['_body'];
		unset($_REQUEST['page_vars']['_body']);
	}
	if (!$id) {
		$original_body='<h1>'.htmlspecialchars($name).'</h1><p>&nbsp;</p>';
	}
	else {
		if (is_array($_REQUEST['body'])) {
			$original_body=json_encode($_REQUEST['body']);
		}
		else {
			$original_body=$_REQUEST['body'];
		}
	}
	foreach ($GLOBALS['PLUGINS'] as $plugin) {
		if (isset($plugin['admin']['body_override'])) {
			$original_body=$plugin['admin']['body_override'](false);
		}
	}
	$body=$original_body;
	$body=Core_sanitiseHtml($body);
	// }
	// { template
	$template=@$_REQUEST['template'];
	if ($template=='' && $pid) {
		$template=dbOne('select template from pages where id='.$pid, 'template');
	}
	if (isset($_REQUEST['recursively_update_page_templates'])) {
		recursivelyUpdatePageTemplates($id, $template);
	}
	// }
	$type=$_REQUEST['type'];
	$associated_date=isset($_REQUEST['associated_date'])
		?$_REQUEST['associated_date']
		:date('Y-m-d H:i:s');
	$q='pages set importance='.$importance
		.',template="'.addslashes($template).'",edate=now()'
		.',type="'.addslashes($type).'"'
		.',date_unpublish="'.addslashes($date_unpublish).'"'
		.',date_publish="'.addslashes($date_publish).'"'
		.',associated_date="'.addslashes($associated_date).'"'
		.',keywords="'.addslashes($keywords).'"'
		.',description="'.addslashes($description).'"'
		.',name="'.addslashes($name).'"'
		.',title="'.addslashes($title).'"'
		.',original_body="'
		.addslashes(Core_sanitiseHtmlEssential($original_body))
		.'"'
		.',link="'.addslashes(__FromJson($name, true)).'"'
		.',body="'.addslashes($body).'"'
		.',alias="'.$alias.'",parent='.$pid
		.',special='.$special;
	if (!$id) { // ord
		$ord=dbOne(
			'select ord from pages where parent='.$pid.' order by ord desc limit 1',
			'ord'
		)+1;
		$q.=',ord='.$ord.',cdate=now()';
	}
	// { insert the page
	if ($id) {
		$q='update '.$q.' where id='.$id;
	}
	else {
		$q='insert into '.$q;
	}
	dbQuery($q);
	if (!$id) {
		$id=dbOne('select last_insert_id() as id', 'id');
	}
	// }
	// { page_vars
	dbQuery('delete from page_vars where page_id="'.$id.'"');
	$pagevars=isset($_REQUEST['page_vars'])?$_REQUEST['page_vars']:array();
	if (@$_REQUEST['short_url']) {
		dbQuery(
			'insert into short_urls set cdate=now(),page_id='.$id.',short_url="'
			.addslashes($_REQUEST['short_url']).'"'
		);
		$pagevars['_short_url']=1;
	}
	else {
		dbQuery('delete from short_urls where page_id='.$id);
		unset($pagevars['_short_url']);
	}
	if (is_array($pagevars)) {
		if (isset($pagevars['google-site-verification'])) {
			$pagevars['google-site-verification']=preg_replace(
				'#.*content="([^"]*)".*#',
				'\1',
				$pagevars['google-site-verification']
			);
		}
		foreach ($pagevars as $k=>$v) {
			if (is_array($v)) {
				$v=json_encode($v);
			}
			dbQuery(
				'insert into page_vars (name,value,page_id) values("'.addslashes($k)
				.'","'.addslashes($v).'",'.$id.')'
			);
		}
	}
	// }
	if ($_POST['type']==4) {
		$r2=dbRow('select * from page_summaries where page_id="'.$id.'"');
		$do=1;
		if ($r2) {
			if (isset($_POST['page_summary_parent'])
				&& $r2['parent_id']!=$_POST['page_summary_parent']
			) {
				dbQuery('delete from page_summaries where page_id="'.$_POST['id'].'"');
			}
			else {
				$do=0;
			}
		}
		if ($do) {
			dbQuery(
				'insert into page_summaries set page_id="'.$id.'",parent_id="'
				.$_POST['page_summary_parent'].'",rss=""'
			);
		}
		require_once SCRIPTBASE.'/ww.incs/page.summaries.php';
		PageSummaries_getHtml($_POST['id']);
	}
	// { clean up and return
	dbQuery('update page_summaries set rss=""');
	if (@$GLOBALS['DBVARS']['cron-next']) {
		unset($GLOBALS['DBVARS']['cron-next']);	
	}
	Core_cacheClear();
	Core_configRewrite();
	return array(
		'id'   =>$id,
		'pid'  =>$pid,
		'alias'=>$alias
	);
	// }
}

// }
// { Core_adminPageMove

/**
  * move a page
  *
  * @return array status of the move
  */
function Core_adminPageMove() {
	$id=(int)$_REQUEST['id'];
	$to=(int)$_REQUEST['parent_id'];
	$order=$_REQUEST['order'];
	dbQuery("update pages set parent=$to where id=$id");
	for ($i=0;$i<count($order);++$i) {
		$pid=(int)$order[$i];
		dbQuery("update pages set ord=$i where id=$pid");
	}
	Core_cacheClear();
	dbQuery('update page_summaries set rss=""');
	return array('ok'=>1);
}

// }
// { Core_adminPageParentsList

/**
	* get array of pages
	*
	* @return array
	*/
function Core_adminPageParentsList() {
	$id=isset($_REQUEST['other_GET_params'])?(int)$_REQUEST['other_GET_params']:-1;
	/**
		* get list of contained directories
		*
		* @param int $i  ID of the parent page
		* @param int $n  indentation level
		* @param int $id ID of a page /not/ to show
		*
		* @return array
		*/
	function selectkiddies($i=0, $n=1, $id=0) {
		$arr=array();
		$q=dbAll(
			'select name,id,alias from pages where parent="'.$i.'" and id!="'.$id
			.'" order by ord,name'
		);
		if (count($q)<1) {
			return $arr;
		}
		foreach ($q as $r) {
			if ($r['id']!='') {
				$arr[' '.$r['id']]=str_repeat('» ', $n).__FromJson($r['name']);
				$arr=array_merge($arr, selectkiddies($r['id'], $n+1, $id));
			}
		}
		return $arr;
	}
	return array_merge(
		array(' 0'=>' -- none -- '),
		selectkiddies(0, 0, $id)
	);
}

// }
// { Core_adminPageTypesList

/**
	* get an array of page types
	*
	* @return array
	*/
function Core_adminPageTypesList() {
	$arr=array();
	global $pagetypes,$PLUGINS;
	foreach ($pagetypes as $a) {
		$arr[$a[0]]=$a[1];
	}
	foreach ($PLUGINS as $n=>$p) {
		if (isset($p['admin']['page_type'])) {
			if (is_array($p['admin']['page_type'])) {
				foreach ($p['admin']['page_type'] as $name=>$type) {
					$arr[$n.'|'.$name]=$name;
				}
			}
			else {
				$arr[$n.'|'.$n]=$n;
			}
		}
	}
	return $arr;
}

// }
// { Core_adminPluginsDependenciesGet

/**
	* get an array of dependent plugins
	*
	* @param array $plugins array of plugins to check
	*
	* @return array array of dependencies
	*/
function Core_adminPluginsDependenciesGet($plugins) {
	$new_plugs=array();
	foreach ($plugins as $plug) {
		if (!is_dir(SCRIPTBASE.'ww.plugins/'.$plug)
			||!file_exists(SCRIPTBASE.'ww.plugins/'.$plug.'/plugin.php')
		) {
			// plugin doesn't exist
			return $plug;
		}
		global $PLUGINS;
		if (isset($PLUGINS[$plug])) { // if installed load from memory
			$plugin=$PLUGINS[$plug];
		}
		else { // else include plugin file
			// if already included then it must be
			// already on the list
			// I think there's a logic problem here. Kae
			require_once SCRIPTBASE.'ww.plugins/'.$plug.'/plugin.php';
		}
		if (isset($plugin['dependencies'])) {
			$dependencies=(strpos($plugin['dependencies'], ',')===false)
				?array($plugin['dependencies'])
				:explode(',', $plugin['dependencies']);
			foreach ($dependencies as $dependency) {
				if (!in_array($dependency, $plugins)
					&&!in_array($dependency, $new_plugs)
				) {
					array_push($new_plugs, $dependency);
				}
			}
		}
		array_push($new_plugs, $plug);
		$plugin=array();
	}
	$diff=array_diff($new_plugs, $plugins);
	$new_plugs=array_merge($plugins, $new_plugs);
	if (is_array($diff)&&count($diff)!=0) {
		$check=Core_adminPluginsDependenciesGet($diff);
		if (!is_array($check)) {
			return $check;
		}
		$new_plugs=array_merge($new_plugs, $check);
	}
	return array_unique($new_plugs);
}

// }
// { Core_adminPluginsGetAvailable

/**
	* build array of available (not installed) plugins
	*
	* @return array of available plugins
	*/
function Core_adminPluginsGetAvailable() {
	global $PLUGINS;
	$available = array( );
	$dir = new DirectoryIterator(SCRIPTBASE . 'ww.plugins');
	foreach ($dir as $p) {
		if ($p->isDot()) {
			continue;
		}
		$name = $p->getFilename();
		if (!is_dir(SCRIPTBASE.'ww.plugins/'.$name)||isset($PLUGINS[$name])) {
		  continue;
		}
		if (!file_exists(SCRIPTBASE . 'ww.plugins/' . $name .'/plugin.php')) {
			continue;
		}
		require SCRIPTBASE . 'ww.plugins/' . $name .'/plugin.php';
		if (isset( $plugin[ 'hide_from_admin' ] ) && $plugin[ 'hide_from_admin' ]) {
		  continue;
		}
		$available[ $name ] = array( 
			'name' => $plugin[ 'name' ],
			'description' => @$plugin[ 'description' ],
			'version' => ( @$plugin[ 'version' ] == 0 ) ? '0' : $plugin[ 'version' ]
		);
	}	
	return $available;
}

// }
// { Core_adminPluginsGetInstalled

/**
	* build array of installed plugins
	*
	* @return array of plugins
	*/
function Core_adminPluginsGetInstalled() {
	global $PLUGINS;
	$installed = array();
	foreach ($PLUGINS as $name => $plugin) {
		// exclude hidden plugins
		if (isset($plugin[ 'hide_from_admin' ]) && $plugin['hide_from_admin']) {
			continue;
		}
		$installed[ $name ] = array(
			'name' => $plugin[ 'name' ],
			'description' => $plugin[ 'description' ],
			'version' => ( @$plugin[ 'version' ] == 0 ) ? '0' : $plugin[ 'version' ]
		);
	}
	return $installed;
}

// }
// { Core_adminPluginsSetInstalled

/**
	* install/de-install plugins
	*
	* @return array status
	*/
function Core_adminPluginsSetInstalled() {
	global $PLUGINS;
	// { get hidden plugins (those the admin installs manually)
	$tmp_hidden=array();
	foreach ($PLUGINS as $name=>$plugin) {
		if (isset($plugin['hide_from_admin']) && $plugin['hide_from_admin']) {
			$tmp_hidden[]=$name;
		}
	}
	// }
	// { see what was added or removed
	$added=array();
	foreach ($_REQUEST['plugins'] as $name=>$var) {
		if (!isset($PLUGINS[$name])) {
			$added[]=$name;
		}
	}
	$removed=array();
	foreach ($PLUGINS as $name=>$var) {
		if (!isset($_REQUEST['plugins'][$name])) {
			$removed[]=$name;
		}
	}
	// }
	// { get changes from form
	$tmp=array();
	foreach ($_REQUEST['plugins'] as $name=>$var) {
		if (file_exists(SCRIPTBASE . 'ww.plugins/' . $name .'/plugin.php')) {
			$tmp[]=$name;
		}
	}
	// }
	$plugins=array_merge($tmp, $tmp_hidden);
	$plugins=Core_adminPluginsDependenciesGet($plugins);
	if (is_array($plugins)) {
	  $GLOBALS['DBVARS']['plugins']=$plugins;
	  Core_configRewrite();
		return array('ok'=>1, 'added'=>$added, 'removed'=>$removed);
	}
	return array('ok'=>0);
}

// }
// { Core_adminPluginsInstallOne

/**
	* install one plugin
	*
	* @return array status
	*/
function Core_adminPluginsInstallOne() {
	$to_install=$_REQUEST['name'];
	// { is it already installed?
	$installed=Core_adminPluginsGetInstalled();
	foreach ($installed as $key=>$p) {
		if ($key==$to_install) {
			return array('ok'=>1, 'message'=>'already installed');
		}
	}
	// }
	// { does it exist?
	$available=Core_adminPluginsGetAvailable();
	$found=0;
	foreach ($available as $key=>$p) {
		if ($key==$to_install) {
			$found=1;
		}
	}
	if ($found==0) {
		return array('ok'=>0, 'message'=>'plugin not found');
	}
	// }
	// { install it
	$plugins=array();
	foreach ($installed as $key=>$p) {
		$plugins[$key]=1;
	}
	$plugins[$to_install]=1;
	$_REQUEST['plugins']=$plugins;
	return Core_adminPluginsSetInstalled();
	// }
}

// }
// { Core_adminPluginsRemoveOne

/**
	* remove one plugin
	*
	* @return array status
	*/
function Core_adminPluginsRemoveOne() {
	$to_remove=$_REQUEST['name'];
	// { is it already removed?
	$installed=Core_adminPluginsGetInstalled();
	$found=0;
	foreach ($installed as $key=>$p) {
		if ($key==$to_remove) {
			$found=1;
		}
	}
	if ($found==0) {
		return array('ok'=>1, 'message'=>'already removed');
	}
	// }
	// { remove it
	$plugins=array();
	foreach ($installed as $key=>$p) {
		if ($key==$to_remove) {
			continue;
		}
		$plugins[$key]=1;
	}
	$_REQUEST['plugins']=$plugins;
	return Core_adminPluginsSetInstalled();
	// }
}

// }
// { Core_adminSaveJSVar

/**
	* save a session variable
	*
	* @return array status of save
	*/
function Core_adminSaveJSVar() {
	if (!isset($_SESSION['js'])) {
		$_SESSION['js']=array();
	}
	foreach ($_REQUEST as $k=>$v) {
		if (in_array($k, array('a', 'p', 'f', '_remainder'))) {
			continue;
		}
		$_SESSION['js'][$k]=$v;
	}
	return array('ok'=>1);
}

// }
// { Core_adminStatsGetVisits

/**
	* get stats
	*
	* @return array details
	*/
function Core_adminStatsGetVisits() {
	$from=isset($_REQUEST['from'])
		?$_REQUEST['from']
		:date('Y-m-d', time()-3600*24*7);
	$to=isset($_REQUEST['to'])
		?$_REQUEST['to']
		:date('Y-m-d', time()+3600*24);
}

// }
// { Core_adminUserEditVal

/**
	* edit a single value of a user
	*
	* @return array status
	*/
function Core_adminUserEditVal() {
	$id=(int)$_REQUEST['id'];
	$name=$_REQUEST['name'];
	$value=$_REQUEST['val'];
	if (!in_array($name, array('name', 'email', 'phone'))) {
		return array('error'=>'field not allowed');
	}
	dbQuery(
		'update user_accounts set '.$name.'="'.addslashes($value).'" where id='.$id
	);
	Core_cacheClear();
	return array('ok'=>1);
}

// }
// { Core_adminUserGroupsCreate

/**
	* create user groups if they don't exist
	*
	* @return status
	*/
function Core_adminUserGroupsCreate() {
	$groups=$_REQUEST['groups'];
	foreach ($groups as $group) {
		if ($group=='') {
			continue;
		}
		$id=dbOne(
			'select id from groups where name="'.addslashes($group).'"',
			'id'
		);
		if (!$id) {
			dbQuery('insert into groups set name="'.addslashes($group).'"');
		}
	}
	return array('ok'=>1);
}

// }
// { Core_adminUserGroupsGet

/**
	* get an array of user groups
	*
	* @return array
	*/
function Core_adminUserGroupsGet() {
	return dbAll('select id,name from groups order by name');
}

// }
// { Core_adminUserNamesEmailsGet

/**
	* get an array of names and emails of users
	*
	* @return array
	*/
function Core_adminUserNamesEmailsGet() {
	$names=array();
	foreach (
		dbAll('select id,name,email from user_accounts order by name') as $r
	) {
		if (!$r['name']) {
			$r['name']=$r['email'];
		}
		$names[]=array(
			'id'=>$r['id'],
			'name'=>$r['name'],
			'email'=>$r['email']
		);
	}
	return $names;
}

// }
// { Core_adminUserNamesGet

/**
	* get an array of names OR emails of users
	*
	* @return array
	*/
function Core_adminUserNamesGet() {
	$names=array();
	foreach (
		dbAll('select id,name,email from user_accounts order by name') as $r
	) {
		if (!$r['name']) {
			$r['name']=$r['email'];
		}
		$names[$r['id']]=$r['name'];
	}
	return $names;
}

// }
// { Core_adminUsersGetDT

/**
	* get overview data of a list of users in datatable format
	*
	* @return array
	*/
function Core_adminUsersGetDT() {
	$start=(int)$_REQUEST['iDisplayStart'];
	$length=(int)$_REQUEST['iDisplayLength'];
	$search=$_REQUEST['sSearch'];
	$orderby=(int)$_REQUEST['iSortCol_0'];
	$orderdesc=$_REQUEST['sSortDir_0']=='desc'?'desc':'asc';
	switch ($orderby) {
		case 2:
			$orderby='name';
		break;
		case 3:
			$orderby='email';
		break;
		case 4:
			$orderby='phone';
		break;
		case 5:
			$orderby='date_created';
		break;
		default:
			$orderby='name';
	}
	$filters=array();
	if ($search) {
		$filters[]='name like "%'.addslashes($search).'%"'
			.' or email like "%'.addslashes($search).'%"'
			.' or phone like "%'.addslashes($search).'%"'
			.' or date_created like "%'.addslashes($search).'%"';
	}
	if (isset($_REQUEST['filter-groups'])) {
		$gids=array();
		$bits=explode(',', $_REQUEST['filter-groups']);
		foreach ($bits as $bit) {
			$gids[]=(int)$bit;
		}
		$rs=dbAll(
			'select distinct user_accounts_id from users_groups'
			.' where groups_id in ('.join(', ', $gids).')'
		);
		$uids=array(0);
		foreach ($rs as $r) {
			$uids[]=$r['user_accounts_id'];
		}
		$filters[]='id in ('.join(',', $uids).')';
	}
	$filter='';
	if (count($filters)) {
		$filter='where '.join(' and ', $filters);
	}
	$sql='select id,name,email,phone,date_created from user_accounts '.$filter
		.' order by '.$orderby.' '.$orderdesc
		.' limit '.$start.','.$length;
	$rs=dbAll($sql);
	$result=array();
	$result['sEcho']=intval($_GET['sEcho']);
	$result['iTotalRecords']=dbOne(
		'select count(id) as ids from user_accounts', 'ids'
	);
	$result['iTotalDisplayRecords']=dbOne(
		'select count(id) as ids from user_accounts '.$filter,
		'ids'
	);
	$arr=array();
	foreach ($rs as $r) {
		$row=array();
		$row[]=$r['id'];
		$row[]=$r['name'];
		$row[]=$r['email'];
		$row[]=$r['phone'];
		$row[]=$r['date_created'];
		$rs2=dbAll(
			'select name from groups,users_groups where user_accounts_id='.$r['id']
			.' and groups_id=groups.id'
		);
		$groups=array();
		foreach ($rs2 as $r2) {
			$groups[]=$r2['name'];
		}
		$row[]=join(', ', $groups);
		$row[]='';
		$arr[]=$row;
	}
	$result['aaData']=$arr;
	return $result;
}

// }
