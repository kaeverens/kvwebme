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


/**
	* get list of cron jobs
	*
	* @return status
	*/
function Core_adminCronGet() {
	return dbAll('select * from cron');
}

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

/**
	* install/de-install plugins
	*
	* @return array status
	*/
function Core_adminPluginsSetInstalled() {
	// { get hidden plugins (those the admin installs manually)
	$tmp_hidden=array();
	foreach ($GLOBALS['PLUGINS'] as $name=>$plugin) {
		if (isset($plugin['hide_from_admin']) && $plugin['hide_from_admin']) {
			$tmp_hidden[]=$name;
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
		return array('ok'=>1);
	}
	return array('ok'=>0);
}

/**
	* save a session variable
	*
	* @return array status of save
	*/
function Core_adminLoadJSVars() {
	if (!isset($_SESSION['js'])) {
		$_SESSION['js']=array();
	}
	return $_SESSION['js'];
}

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
//	$name = transcribe($name);
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
	if(@$GLOBALS['DBVARS']['cron-next']) {
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

/**
	* get stats
	*
	* @return array details
	*/
function Core_adminStatsGetVisits() {
	$from=isset($_REQUEST['from'])
		?$_REQUEST['from']
		:date('Y-m-d',time()-3600*24*7);
	$to=isset($_REQUEST['to'])
		?$_REQUEST['to']
		:date('Y-m-d', time()+3600*24);
}

/**
	* get an array of names and emails of users
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
