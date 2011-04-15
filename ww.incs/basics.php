<?php
session_start();
require 'Log.php';
if(!defined('START_TIME'))define('START_TIME',microtime(true));
function __() {
	$str = gettext(func_get_arg(0));
	for($i = func_num_args()-1 ; $i ; --$i){
		$s=func_get_arg($i);
		$str=str_replace('%'.$i,$s,$str);
	}
	return $str;  
}
spl_autoload_register('WebME_autoload');
function WebME_autoload($name) {
	require $name . '.php';
}
function cache_clear($type){
	if(!is_dir(USERBASE.'/ww.cache/'.$type))return;
	$d=new DirectoryIterator(USERBASE.'/ww.cache/'.$type);
	foreach($d as $f){
		$f=$f->getFilename();
		if($f=='.' || $f=='..')continue;
		if (!is_dir(USERBASE.'/ww.cache/'.$type.'/'.$f)) {
			unlink(USERBASE.'/ww.cache/'.$type.'/'.$f);
		}
	}
}
function cache_load($type,$md5){
	if(file_exists(USERBASE.'/ww.cache/'.$type.'/'.$md5)){
		return json_decode(file_get_contents(USERBASE.'/ww.cache/'.$type.'/'.$md5), true);
	}
	return false;
}
function cache_save($type,$md5,$vals){
	if (!is_dir(USERBASE.'/ww.cache/'.$type)) {
		mkdir(USERBASE.'/ww.cache/'.$type, 0777, true);
	}
	file_put_contents(USERBASE.'/ww.cache/'.$type.'/'.$md5, json_encode($vals));
}
function config_rewrite(){
	global $DBVARS;
	$tmparr=$DBVARS;
	$tmparr['plugins']=join(',',$DBVARS['plugins']);
	$tmparr2=array();
	foreach($tmparr as $name=>$val)$tmparr2[]='\''.addslashes($name).'\'=>\''.addslashes($val).'\'';
	$config="<?php\n\$DBVARS=array(\n	".join(",\n	",$tmparr2)."\n);";
	file_put_contents(CONFIG_FILE,$config);
}
function dbAll($query, $key='') {
	$q = dbQuery($query);
	if ($q === false) {
		return false;
	}
	$results=array();
	while ($r=$q->fetch(PDO::FETCH_ASSOC)) {
		$results[]=$r;
	}
	if (!$key) {
		return $results;
	}
	$arr=array();
	foreach ($results as $r) {
		$arr[$r[$key]]=$r;
	}
	return $arr;
}
function dbInit(){
	if(isset($GLOBALS['db']))return $GLOBALS['db'];
	global $DBVARS;
	$db=new PDO('mysql:host='.$DBVARS['hostname'].';dbname='.$DBVARS['db_name'],$DBVARS['username'],$DBVARS['password']);
	$db->query('SET NAMES utf8');
	$db->num_queries=0;
	$GLOBALS['db']=$db;
	return $db;
}
function dbLastInsertId(){
	return dbOne('select last_insert_id() as id','id');
}
function dbOne($query, $field='') {
	$r = dbRow($query);
	if ($r === false) {
		return false;
	}
	return $r[$field];
}
function dbQuery($query){
	$db=dbInit();
	$q=$db->query($query);
	if ($q === false) { // failed
		return false;
	}
	$db->num_queries++;
	return $q;
}
function dbRow($query) {
	$q = dbQuery($query);
	if ($q === false) {
		return false;
	}
	return $q->fetch(PDO::FETCH_ASSOC);
}
function ob_show_and_log($type,$header=''){
	$log = Log::singleton('file',USERBASE.'/log.txt',$type,array('locking'=>true,'timeFormat'=>'%Y-%m-%d %H:%M:%S'));
	$length=ob_get_length();
	$num_queries=isset($GLOBALS['db'])?$GLOBALS['db']->num_queries:0;
	switch($type){
		case 'design_file': // {
			$location=$_SERVER['REQUEST_URI'];
			break;
		// }
		case 'file': // {
			$location=$_SERVER['REQUEST_URI'];
			break;
		// }
		case 'menu': // {
			$location='menu';
			break;
		// }
		case 'page': // {
			$location=$GLOBALS['PAGEDATA']->id.'|'.$GLOBALS['PAGEDATA']->getRelativeUrl();
			break;
		// }
		default: // {
			$location='unknown_type_'.$type;
		//}
	}
	$log->log(
		$_SERVER['REMOTE_ADDR']
		.'	'.$location
		.'	'.(isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'')
		.'	'.(isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'')
		.'	'.memory_get_peak_usage()
		.'	'.$length
		.'	'.(microtime(true)-START_TIME)
		.'	'.$num_queries
	);
	if($header)header($header);
	ob_flush();
}
function admin_can_create_top_pages(){
	return has_page_permissions(1024);
}
function is_admin(){
	return (isset($_SESSION['userdata']) && isset($_SESSION['userdata']['groups']['administrators']));
}
function is_logged_in(){
	return isset($_SESSION['userdata']);
}
function get_userid(){
	return $_SESSION['userdata']['id'];
}
function has_page_permissions($val){
	return true;
}
function has_access_permissions($val){
	return true;
}
function plugin_trigger($trigger_name){
	global $PLUGIN_TRIGGERS,$PAGEDATA;
	if(!isset($PLUGIN_TRIGGERS[$trigger_name]))return;
	$c='';
	foreach($PLUGIN_TRIGGERS[$trigger_name] as $fn) {
		$c.=$fn($PAGEDATA);
	}
	return $c;
}
define('SCRIPTBASE', $_SERVER['DOCUMENT_ROOT'] . '/');
if (!file_exists(SCRIPTBASE . '.private/config.php')) {
	echo '<html><body><p>No configuration file found</p>';
	if(file_exists('install/index.php'))echo '<p><a href="/install/index.php">Click here to install</a></p>';
	else echo '<p><strong>Installation script also missing...</strong> please contact kae@webworks.ie if you think there\'s a problem.</p>';
	echo '</body></html>';
	exit;
}
require SCRIPTBASE . '.private/config.php';
if(isset($DBVARS['userbase']))define('USERBASE', $DBVARS['userbase']);
else define('USERBASE', SCRIPTBASE);
// { built-in page types
$pagetypes=array(
	array(0,'normal',0),
	array(4,'page summaries',0),
	array(5,'search results',0),
	array(9,'table of contents',0)
);
// }
$admin_top_menu=array(
	array('id'=>'am_pages','name'=>'pages','link'=>'pages.php'),
	array('id'=>'am_siteoptions','name'=>_('site options'),'link'=>'siteoptions.php'),
	array('id'=>'am_stats','name'=>_('stats'),'link'=>'stats.php')
);
$DBVARS['plugins']=(isset($DBVARS['plugins']) && $DBVARS['plugins']!='')?explode(',',$DBVARS['plugins']):array();
if(!defined('CONFIG_FILE'))define('CONFIG_FILE',SCRIPTBASE.'.private/config.php');
define('WORKDIR_IMAGERESIZES', USERBASE.'/f/.files/image_resizes/');
define('WORKURL_IMAGERESIZES', '/f/.files/image_resizes/');
define('CKEDITOR','ckeditor');
if(!defined('KFM_BASE_PATH'))define('KFM_BASE_PATH', SCRIPTBASE.'j/kfm/');
set_include_path(SCRIPTBASE.'ww.php_classes'.PATH_SEPARATOR.KFM_BASE_PATH.'classes'.PATH_SEPARATOR.get_include_path());
// { theme variables
if (isset($DBVARS['theme_dir_personal']) && $DBVARS['theme_dir_personal']) {
	define('THEME_DIR',$DBVARS['theme_dir_personal']);
}
else if (isset($DBVARS['theme_dir']) && $DBVARS['theme_dir']) {
	define('THEME_DIR',$DBVARS['theme_dir']);
}
else {
	define('THEME_DIR',SCRIPTBASE.'ww.skins');
}
if (@$DBVARS['theme']) {
	define('THEME',$DBVARS['theme']);
}
else{
	if (!file_exists(THEME_DIR)) {
		die(
			'error: theme directory '.THEME_DIR.' does not exist. please '
			.'create it and make sure it is writable by the web server.'
		);
	}
	$dir=new DirectoryIterator(THEME_DIR);
	$themes_found=0;
	$DBVARS['theme']='.default';
	foreach($dir as $file){
		if($file->isDot())continue;
		if(!$file->isDir())continue;
		$DBVARS['theme']=$file->getFileName();
		break;
	}
	define('THEME',$DBVARS['theme']);
}
// }
// { plugin
$PLUGINS=array();
$PLUGIN_TRIGGERS=array();
if(!isset($ignore_webme_plugins)){
	foreach($DBVARS['plugins'] as $pname){
		if(strpos('/',$pname)!==false)continue;
		require SCRIPTBASE . 'ww.plugins/'.$pname.'/plugin.php';
		if (isset($plugin['version']) && $plugin['version'] && (!isset($DBVARS[$pname.'|version']) || $DBVARS[$pname.'|version']!=$plugin['version'])){
			$version=isset($DBVARS[$pname.'|version'])
				?(int)$DBVARS[$pname.'|version']
				:0;
			require SCRIPTBASE . 'ww.plugins/'.$pname.'/upgrade.php';
			header('Location: '.$_SERVER['REQUEST_URI']);
			exit;
		}
		$PLUGINS[$pname]=$plugin;
		if(isset($plugin['triggers'])){
			foreach($plugin['triggers'] as $name=>$fn){
				if(!isset($PLUGIN_TRIGGERS[$name]))$PLUGIN_TRIGGERS[$name]=array();
				$PLUGIN_TRIGGERS[$name][]=$fn;
			}
		}
	}
}
// }
plugin_trigger('initialisation-completed');
