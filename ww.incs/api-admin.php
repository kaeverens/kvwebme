<?php
/**
	* API for common admin CMS functions
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
	if ($name=='') {
		return array('error'=>__('Missing name'));
	}
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
// { Core_adminDBClearAutoincrement

/**
	* resets the auto_increment of a database table to 1
	*
	* @return null
	*/
function Core_adminDBClearAutoincrement() {
	$table=$_REQUEST['table'];
	if (preg_match('/[^a-z_]/', $table)) {
		return false;
	}
	dbQuery('alter table '.$table.' auto_increment=1');
	return true;
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
// { Core_adminEmailsSentDT

/**
	* get list of sent emails in datatable format
	*
	* @return array
	*/
function Core_adminEmailsSentDT() {
	$start=(int)$_REQUEST['iDisplayStart'];
	$length=(int)$_REQUEST['iDisplayLength'];
	$search=$_REQUEST['sSearch'];
	$orderby=(int)$_REQUEST['iSortCol_0'];
	$orderdesc=$_REQUEST['sSortDir_0']=='asc'?'asc':'desc';
	switch ($orderby) {
		case 1:
			$orderby='to_email';
		break;
		case 2:
			$orderby='subject';
		break;
		default:
			$orderby='cdate';
	}
	$filters=array();
	if ($search) {
		$filters[]='to_email like "%'.addslashes($search).'%"'
			.' or subject like "%'.addslashes($search).'%"'
			.' or cdate like "%'.addslashes($search).'%"';
	}
	$filter='';
	if (count($filters)) {
		$filter='where '.join(' and ', $filters);
	}
	$sql='select id,to_email,subject,cdate from emails_sent '.$filter
		.' order by '.$orderby.' '.$orderdesc
		.' limit '.$start.','.$length;
	$rs=dbAll($sql);
	$result=array();
	$result['sEcho']=intval($_GET['sEcho']);
	$result['iTotalRecords']=dbOne(
		'select count(id) as ids from emails_sent', 'ids'
	);
	$result['iTotalDisplayRecords']=dbOne(
		'select count(id) as ids from emails_sent '.$filter,
		'ids'
	);
	$arr=array();
	foreach ($rs as $r) {
		$row=array();
		$row[]=$r['cdate'];
		$row[]=$r['to_email'];
		$row[]=$r['subject'];
		$row[]=$r['id'];
		$arr[]=$row;
	}
	$result['aaData']=$arr;
	return $result;
}

// }
// { Core_adminEmailSentGet

/**
	* get the HTMl of a sent email
	*
	* @return array
	*/
function Core_adminEmailSentGet() {
	$id=(int)$_REQUEST['id'];
	header('Content-type: text/html; charset=utf-8');
	echo dbOne('select body from emails_sent where id='.$id, 'body');
	Core_quit();
}

// }
// { Core_adminEmailTemplateDownload

/**
	* download an email template
	*
	* @return null
	*/
function Core_adminEmailTemplateDownload() {
	$name=$_REQUEST['name'];
	$filename=$name.'.html';
	header('Content-Type: force/download');
	header('Content-Disposition: attachment; filename="'.$filename.'"');
	echo dbOne(
		'select body from email_templates where name="'.addslashes($name).'"',
		'body'
	);
	Core_quit();
}

// }
// { Core_adminEmailTemplateGet

/**
	* get a list of existing email templates
	*
	* @return array list
	*/
function Core_adminEmailTemplateGet() {
	$name=$_REQUEST['name'];
	return dbOne(
		'select body from email_templates where name="'.addslashes($name).'"',
		'body'
	);
}

// }
// { Core_adminEmailTemplateSet

/**
	* save an email template
	*
	* @return array list
	*/
function Core_adminEmailTemplateSet() {
	$name=$_REQUEST['name'];
	$body=$_REQUEST['body'];
	dbQuery('delete from email_templates where name="'.addslashes($name).'"');
	dbQuery(
		'insert into email_templates set name="'.addslashes($name).'"'
		.',body="'.addslashes($body).'"'
	);
	Core_cacheClear('email-templates');
	return true;
}

// }
// { Core_adminEmailTemplatesList

/**
	* get a list of existing email templates
	*
	* @return array list
	*/
function Core_adminEmailTemplatesList() {
	$rs=dbAll('select name from email_templates order by name');
	return $rs;
}

// }
// { Core_adminEmailTemplateUpload

/**
	* handle an uploaded email template
	*
	* @return status
	*/
function Core_adminEmailTemplateUpload() {
	$_REQUEST['name']=str_replace('.html', '', $_FILES['Filedata']['name']);
	$_REQUEST['body']=file_get_contents($_FILES['Filedata']['tmp_name']);
	Core_adminEmailTemplateSet();
	return array('ok'=>1);
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
			'error'=>__('You must fill in Name and Code')
		);
	}
	$isInUse=dbOne(
		'select count(id) as ids from language_names where name="' 
		.addslashes($name).'" or code="'.addslashes($code).'"', 'ids'
	);
	if ($isInUse) {
		return array(
			'error'=>__('Either the Name or Code are already in use')
		);
	}
	dbQuery(
		'insert into language_names set name="'.addslashes($name).'"'
		.',code="'.addslashes($code).'",is_default=0'
	);
	Core_cacheClear('core');
	Core_cacheClear('languages');
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
	Core_cacheClear('languages');
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
			'error'=>__('You must fill in Name and Code')
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
	Core_cacheClear('languages');
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
	Core_cacheClear('languages');
	return array('ok'=>1);
}

// }
// { Core_adminLanguagesExportPo

/**
	* export po file
	*
	* @return status
	*/
function Core_adminLanguagesExportPo() {
	$lang=$_REQUEST['lang'];
	$strings=array();
	$rs=dbAll('select distinct str from languages');
	foreach ($rs as $r) {
		$strings[$r['str']]=1;
	}
	$rs=dbAll(
		'select str,trstr from languages where lang="'.addslashes($lang).'"'
		.' order by str'
	);
	header('Content-Type: force/download');
	header('Content-Disposition: attachment; filename="'.$lang.'.po"');
	echo "msgid \"\"\nmsgstr \"\"\n\"MIME-Version: 1.0\\n\"\n"
		."\"Content-Type: text/plain; charset=utf-8\\n\"\n"
		."\"Content-Transfer-Encoding: 8bit\\n\"\n"
		."\n";
	foreach ($rs as $r) {
		echo 'msgid "'.$r['str']."\"\n";
		echo 'msgstr "'.$r['trstr']."\"\n\n";
		unset($strings[$r['str']]);
	}
	foreach ($strings as $r=>$v) {
		echo 'msgid "'.$r."\"\n";
		echo "msgstr \"\"\n\n";
	}
	Core_quit();
}

// }
// { Core_adminLanguagesGetContexts

/**
	* get list of available contexts
	*
	* @return array of strings
	*/
function Core_adminLanguagesGetContexts() {
	return dbAll('select distinct context from languages');
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
// { Core_adminLanguagesImportPo

/**
	* import po file
	*
	* @return status
	*/
function Core_adminLanguagesImportPo() {
	$lang=@$_REQUEST['lang'];
	$context=@$_REQUEST['context'];
	if (!$lang || !$context) {
		return array(
			'error'=>__('"Language" and "Context" parameters both required')
		);
	}
	$file=file($_FILES['Filedata']['tmp_name']);
	$msgid='';
	$msgstr='';
	dbQuery(
		'delete from languages where lang="'.addslashes($lang).'"'
		.' and context="'.addslashes($context).'"'
	);
	foreach ($file as $line) {
		$line=trim($line);
		if ($line=='') {
			$msgid='';
			$msgstr='';
			continue;
		}
		if (preg_match('/^msgid "/', $line)) {
			$msgid=preg_replace('/^msgid "|"$/', '', $line);
			continue;
		}
		if (preg_match('/^msgstr "/', $line)) {
			$msgstr=preg_replace('/^msgstr "|"$/', '', $line);
			if (!$msgid) {
				continue;
			}
			$sql='insert into languages set str="'.addslashes($msgid).'", lang="'
				.addslashes($lang).'", context="'.addslashes($context).'"'
				.', trstr="'.addslashes($msgstr).'"';
			dbQuery($sql);
		}
	}
	Core_cacheClear('languages');
	return true;
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
			'error'=>__('You must fill in Name')
		);
	}
	$isInUse=dbOne(
		'select count(id) as ids from locations where name="' 
		.addslashes($name).'"', 'ids'
	);
	if ($isInUse) {
		return array(
			'error'=>__('Name already in use')
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
	$sub=dbOne(
		'select count(id) as ids from locations where parent_id='.$id,
		'ids'
	);
	if ($sub) {
		return array(
			'error'=>__('Can not delete this location as it contains other locations')
		);
	}
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
	$parent_id=(int)$_REQUEST['parent_id'];
	$is_default=(int)$_REQUEST['is_default'];
	if (!$name) {
		return array(
			'error'=>__('You must fill in Name')
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
		'update locations set name="'.addslashes($name).'"'.',parent_id='.$parent_id
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
			$menus=Core_adminMenusGetDefault();
			dbQuery(
				'insert into admin_vars set admin_id='.$_SESSION['userdata']['id']
				.', varname="admin_menu", varvalue="'
				.addslashes(json_encode($menus)).'"'
			);
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
				'Pages'=>array( // __('Pages')
					'_link'=>'pages.php'
				),
				'Site Options'=>array( // { __('Site Options')
					'General'=> array('_link'=>'siteoptions.php'), // __('General')
					'Languages'=>array( // { __('Languages')
						'_link'=>
							'javascript:Core_screen(\'CoreSiteoptions\', \'js:Languages\')'
					), // }
					'Locations'=>array( // { __('Locations')
						'_link'=>
							'javascript:Core_screen(\'CoreSiteoptions\', \'js:Locations\')'
					), // }
					'Menus' => array( // { __('Menus')
						'_link'=>'javascript:Core_screen(\'CoreSiteoptions\', \'js:Menus\')'
					), // }
					'Emails' => array( // { __('Emails')
						'_link'=>
							'javascript:Core_screen(\'CoreSiteoptions\', \'js:Emails\')'
					), // }
					 // __('Users')
					'Users' => array('_link'=>'siteoptions.php?page=users'),
					// __('Plugins')
					'Plugins'=> array('_link'=>'siteoptions.php?page=plugins'),
					// __('Themes')
					'Themes' => array('_link'=>'siteoptions.php?page=themes'),
					'Timed Events'=>array( // { __('Timed Events')
						'_link'=>'javascript:Core_screen(\'CoreSiteoptions\', \'js:Cron\')'
					) // }
				) // }
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
					if (strpos($page, 'javascript:')===0) {
						$link=$page;
					}
					else {
						$link=strpos($page, 'js:')===false
							?$page
							:'javascript:Core_screen(\''.$pname.'\', \''.$page.'\');';
					}
					$json='{"'.str_replace('>', '":{"', $name).'":{"_link":"'.$link.'"}}'
						.str_repeat('}', substr_count($name, '>'));
					$menus=array_merge_recursive($menus, json_decode($json, true));
				}
			}
			// }
			// { add final items
			// __('View Site')
			$menus['View Site']=array( '_link'=>'/', '_target'=>'_blank');
			// __('Log Out')
			$menus['Log Out']=  array('_link'=>'/?logout=1');
			// __('Misc') __('File Manager')
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
// { Core_adminMenusAdd

/**
	* add an item to a menu
	*
	* @param string $name path to the item
	* @param string $link the link of the menu item
	*
	* @return null
	*/
function Core_adminMenusAdd($name, $link) {
	$json='{"'.str_replace('>', '":{"', $name)
		.'":{"_link":"'.$link.'"}}'
		.str_repeat('}', substr_count($name, '>'));
	$newlink=json_decode($json, true);
	$rs=dbAll('select * from admin_vars where varname="admin_menu"');
	foreach ($rs as $r) {
		$menus=json_decode($r['varvalue'], true);
		$menus=array_merge_recursive($menus, $newlink);
		$sql='update admin_vars set varvalue="'
			.addslashes(json_encode($menus))
			.'" where admin_id='.$r['admin_id'].' and varname="admin_menu"';
		dbQuery($sql);
	}
}

// }
// { Core_adminMenusRemove

/**
	* remove a menu item
	*
	* @param string $path the item to remove
	*
	* @return null
	*/
function Core_adminMenusRemove($path) {
	$bits=explode('>', $path);
	$name=array_shift($bits);
	$rs=dbAll('select * from admin_vars where varname="admin_menu"');
	foreach ($rs as $r) {
		$menus=json_decode($r['varvalue'], true);
		$menus=Core_adminMenusRemoveRecurse($menus, $bits, $name);
		$sql='update admin_vars set varvalue="'
			.addslashes(json_encode($menus))
			.'" where admin_id='.$r['admin_id'].' and varname="admin_menu"';
		dbQuery($sql);
	}
}

// }
// { Core_adminMenusRemoveRecurse

/**
	* helper function for Core_adminMenusRemove
	*
	* @param array $menus list of menu items
	* @param array $bits  path to item to remove
	* @param array $name  name of the item to remove
	*
	* @return modified menu
	*/
function Core_adminMenusRemoveRecurse($menus, $bits, $name) {
	if (!isset($menus[$name])) {
		return $menus;
	}
	$thismenu=$menus[$name];
	$submenus=0;
	foreach ($menus as $key=>$val) {
		if (!preg_match('/^_/', $key)) {
			$submenus++;
		}
	}
	if (!$submenus) {
		return false;
	}
	if (count($bits)) {
		$newname=array_shift($bits);
		$thismenu=Core_adminMenusRemoveRecurse($thismenu, $bits, $newname);
		$menus[$name]=$thismenu;
		if ($thismenu==false) {
			unset($menus[$name]);
		}
	}
	else {
		unset($menus[$name]);
	}
	return $menus;
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
		return array('error'=>__('No ID provided'));
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
	global $PLUGINS;
	$id=(int)$_REQUEST['id'];
	$page=Page::getInstance($id);
	$page->initValues();
	if (isset($page->plugin)) {
		$type=$page->plugin;
	}
	if ((isset($type) && !isset($PLUGINS[$type]['do-not-delete']))
		|| !isset($type)
	) {	
		if (!$id) {
			return array('error'=>__('No ID provided'));
		}
		$r=dbRow("SELECT COUNT(id) AS pagecount FROM pages");
		if ($r['pagecount']<2) {
			return array('error'=>__('There must always be at least one page.'));
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
		return array('error'=>__('Page does not exist'));
	}
	return array('error'=>__('Page could not be deleted'));	
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
	if (!isset($_REQUEST['body'])) {
		$_REQUEST['body']='';
	}
	if ($importance<0.1) {
		$importance=0.5;
	}
	if ($importance>1) {
		$importance=1;
	}
	// { name, alias
	$name=trim($_REQUEST['name']);
	if (!$name) {
		$name=__('No page name provided');
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
			$msgs.='<em>'
				.__(
					'A page named "%1" already exists. Page name amended to "%2"',
					$name, $name.$i
				)
				.'</em>';
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
		$lim=(int)@$GLOBALS['DBVARS']['site_page_length_limit'];
		if (is_array($_REQUEST['body'])) {
			if ($lim) {
				foreach ($_REQUEST['body'] as $k=>$v) {
					if (strlen($v)>$lim) {
						$_REQUEST['body'][$k]=preg_replace(
							'/<[^>]*$/', '', substr($v, 0, $lim)
						);
					}
				}
			}
			$original_body=json_encode($_REQUEST['body']);
		}
		else {
			$original_body=$_REQUEST['body'];
			if ($lim && strlen($original_body)>$lim) {
				$original_body=preg_replace(
					'/<[^>]*$/', '', substr($original_body, 0, $lim)
				);
			}
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
	
	if ($id!=0) {				//if we don't create a page
						//i.e. we edit it	
		$page=Page::getInstance($id);
		$page->initValues();
	
		if (isset($page->plugin)) { //if this page it's a plugin
			$type=$page->plugin;		//we find the plugin's name(plugin type)
		}
	
		if (@$GLOBALS['PLUGINS'][$type]['do-not-delete']) { // don't modify type
			$type=dbOne('select type from pages where id='.$id, 'type');
			if ($type!=$_REQUEST['type']) {
				echo '<script>alert("'
					.addslashes(__("The type of the page couldn't be changed"))
					.'")</script>';
			}
		}
		else { //We can change the type
			$type=$_REQUEST['type'];
		}
	}
	else {
		//if we create the page
		$type=$_REQUEST['type'];
	}

	$destType=preg_replace('/\|.*/', '', $_REQUEST['type']);
	if (@$GLOBALS['PLUGINS'][$destType]['only-one-page-instance'] == true) {
		
		//we count how many pages of this type
		//we have
		$howMany = dbOne(
			'select COUNT(type) FROM pages WHERE type="'. $_REQUEST['type'] .'"'
			.' and id!='.$id,
			'COUNT(type)'
		);
			
		if ($howMany>=1) {		//If we already have a page
			echo "<script>alert('"
				.addslashes(__('You already have one page of that type'))
				."');</script>";
			return array('error' =>__('You can have only one page of this type'));
		}	
	}
	

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
		$q='UPDATE '.$q.' where id='.$id;
	}
	else {
		$onlyOnePageInstance = false;
		$pluginType = preg_replace('/\|.*/', '', $_REQUEST['type']);
		if (isset($GLOBALS['PLUGINS'][$pluginType]['only-one-page-instance'])) {
			$onlyOnePageInstance
				=$GLOBALS['PLUGINS'][$pluginType]['only-one-page-instance'];
		}
		$alreadyAtInstancesLimit=$onlyOnePageInstance
			?dbOne(
				'select COUNT(type) FROM pages WHERE type="'.$_REQUEST['type'].'"',
				'COUNT(type)'
			)
			:0;
		$q='INSERT into '.$q;		
		if ($onlyOnePageInstance == true) {
			if ($howMany>=1) {
				return array('error'=>__('You can have only one page of this type'));
			}
		}
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
		array(' 0'=>' -- '.__('None').' -- '),
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
		if (isset($plugin['hide_from_admin']) && $plugin['hide_from_admin']) {
		  continue;
		}
		$available[$name] = array( 
			'name' => is_string($plugin['name'])?$plugin['name']:$plugin['name'](),
			'description'=>is_string($plugin['description'])
				?$plugin['description']:$plugin['description'](),
			'version'=>(@$plugin['version']==0)?'0':$plugin['version']
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
			'name' => is_string($plugin['name'])?$plugin['name']:$plugin['name'](),
			'description'=>is_string($plugin['description'])
				?$plugin['description']:$plugin['description'](),
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
			return array('ok'=>1, 'message'=>__('Plugin already installed'));
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
		return array('ok'=>0, 'message'=>__('Plugin not found'));
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
		return array('ok'=>1, 'message'=>__('Plugin already removed'));
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
// { Core_adminReportsPopularPages

/**
	* get a list of popular pages from today, last week, last month
	*
	* @return array report data
	*/
function Core_adminReportsPopularPages() {
	Core_statsUpdate();
	return array(
		'day'=>dbAll(
			'select page,sum(amt) as amt from logs_pages'
			.' where cdate=date(now())'
			.' group by page'
			.' order by amt desc'
			.' limit 50'
		),
		'week'=>dbAll(
			'select page,sum(amt) as amt from logs_pages'
			.' where cdate>date_add(date(now()), interval -7 day)'
			.' group by page'
			.' order by amt desc'
			.' limit 50'
		),
		'month'=>dbAll(
			'select page,sum(amt) as amt from logs_pages'
			.' where cdate>date_add(date(now()), interval -31 day)'
			.' group by page'
			.' order by amt desc'
			.' limit 50'
		)
	);
}

// }
// { Core_adminReportsVisitorStats

/**
	* get number of recent visitors
	*
	* @return array report data
	*/
function Core_adminReportsVisitorStats() {
	Core_statsUpdate();
	$from=$_REQUEST['from'];
	$to=$_REQUEST['to'];
	$rs=dbAll(
		'select cdate,unique_visitors from logs_archive where cdate>="'
		.addslashes($from).'" and cdate<"'.addslashes($to).' 25"'
	);
	$days=array();
	$repeats=array();
	foreach ($rs as $r) {
		if (!isset($days[$r['cdate']])) {
			$days[$r['cdate']]=(int)$r['unique_visitors'];
		}
		else {
			$repeats[$r['cdate']]=1;
			$days[$r['cdate']]+=$r['unique_visitors'];
		}
	}
	foreach ($repeats as $key=>$val) {
		$r=dbRow(
			'select sum(unique_visitors) unique_visitors,sum(page_loads) page_loads'
			.', sum(ram_used) ram_used, sum(bandwidth_used) bandwidth_used'
			.', sum(render_time) render_time,sum(db_calls) db_calls'
			.' from logs_archive where cdate="'.$key.'"'
		);
		dbQuery('delete from logs_archive where cdate="'.$key.'"');
		dbQuery(
			'insert into logs_archive set cdate="'.$key.'"'
			.',unique_visitors='.$r['unique_visitors']
			.',page_loads='.$r['page_loads']
			.',ram_used='.$r['ram_used']
			.',bandwidth_used='.$r['bandwidth_used']
			.',render_time='.$r['render_time']
			.',db_calls='.$r['db_calls']
		);
	}
	return $days;
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
	$contact_fields=array(
		'contact_name', 'business_phone', 'business_email', 'phone', 'website',
		'mobile', 'skype', 'facebook', 'twitter', 'linkedin', 'blog'
	);
	if (in_array($name, array('name', 'email'))) {
		dbQuery(
			'update user_accounts set '.$name.'="'.addslashes($value).'" where id='.$id
		);
	}
	elseif (in_array($name, $contact_fields)) {
		$c=json_decode(
			dbOne('select contact from user_accounts where id='.$id, 'contact'),
			true
		);
		$c[$name]=$value;
		dbQuery(
			'update user_accounts set contact="'.addslashes(json_encode($c)).'"'
			.' where id='.$id
		);
	}
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
	$sql='select id,name,email,contact,date_created from user_accounts '.$filter
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
		$c=json_decode($r['contact'], true);
		$row[]=$c['phone'];
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
// { Core_statsUpdate

/**
	* reads the site log file and updates the database with its contents.
	* then clears the log file.
	*
	* @return void
	*/
function Core_statsUpdate() {
	$time=time()+30;
	$f=file(USERBASE.'/log.txt');
	foreach ($f as $l) {
		list(
			$tmp,$type_data,$user_agent,$referer,
			$ram_used,$bandwidth,$time_to_render,$db_calls
		)=explode("	", $l);
		$ram_used=(int)$ram_used;
		$bandwidth=(int)$bandwidth;
		$time_to_render=(float)$time_to_render;
		$db_calls=(int)$db_calls;
		$bits=explode(' ', $tmp);
		list($log_date,$log_type,$ip_address)=array(
			$bits[0].' '.$bits[1],$bits[2],$bits[4]
		);
		$sql="insert into logs values('$log_date','$log_type','$ip_address','"
			.addslashes($type_data)."','".addslashes($user_agent)."','"
			.addslashes($referer)
			."',$ram_used,$bandwidth,$time_to_render,$db_calls)";
		dbQuery($sql);
	}
	file_put_contents(USERBASE.'/log.txt', '');
	do {
		$cdate=dbOne(
			'select date(log_date) as cdate from logs limit 1',
			'cdate'
		);
		if ($cdate) {
			// { logs archive
			$unique_visitors=dbOne(
				'select count(*) as visitors from (select distinct ip_address from'
				.' logs where log_date>"'.$cdate.'" and log_date<"'.$cdate.' 25") as s1',
				'visitors'
			);
			$page_views=dbOne(
				'select count(type_data) as page_views from logs where log_type="page" and '
				.'log_date>"'.$cdate.'" and log_date<"'.$cdate.' 25"',
				'page_views'
			);
			$other=dbRow(
				'select sum(ram_used) as ram,sum(bandwidth) as bandwidth,'
				.'sum(time_to_render) as rendertime,sum(db_calls) as dbcalls '
				.'from logs where log_type="page" and log_date>"'.$cdate.'" '
				.'and log_date<"'.$cdate.' 25"'
			);
			$sql='insert into logs_archive set cdate="'.$cdate.'",'
				.'unique_visitors='.$unique_visitors.',page_loads='.$page_views
				.',ram_used='.$other['ram'].',bandwidth_used='.$other['bandwidth']
				.',render_time='.$other['rendertime'].',db_calls='.$other['dbcalls'];
			dbQuery($sql);
			// }
			// { popular pages
			$pages=dbAll(
				'select count(type_data) as amt,type_data from logs where '
				.'log_type="page" and log_date>"'.$cdate.'" and log_date<"'
				.$cdate.' 25" group by type_data order by amt desc limit 50'
			);
			foreach ($pages as $page) {
				$url=preg_replace('/.*\|/', '', $page['type_data']);
				$sql='insert into logs_pages set cdate="'.$cdate.'"'
					.',page="'.addslashes($url).'",amt="'.$page['amt'].'"';
				dbQuery($sql);
			}
			// }
			// { referers
			$notin='referer not like "%'
				.str_replace('www.', '', $_SERVER['HTTP_HOST']).'%"';
			$referers=dbAll(
				'select count(referer) as amt,referer,type_data from logs where '
				.'referer!="" and log_type="page" and log_date>"'.$cdate.'" and '
				.'log_date<"'.$cdate.' 25" and '.$notin.' group by referer'
				.',type_data order by amt desc limit 50'
			);
			foreach ($referers as $referer) {
				$url=preg_replace('/.*\|/', '', $referer['type_data']);
				$sql='insert into logs_referers set cdate="'.$cdate.'",referer="'
					.addslashes($referer['referer']).'",page="'.addslashes($url).'"'
					.',amt='.$referer['amt'];
				dbQuery($sql);
			}
			// }
			dbQuery('delete from logs where log_date like "'.$cdate.'%"');
		}
	} while ($cdate && time()<$time);
}

// }
