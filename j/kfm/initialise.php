<?php
/**
 * KFM - Kae's File Manager - initialisation
 *
 * @category None
 * @package  None
 * @author   Kae Verens <kae@verens.com>
 * @author   Benjamin ter Kuile <bterkuile@gmail.com>
 * @license  docs/license.txt for licensing
 * @link     http://kfm.verens.com/
 */
if(!defined('KFM_BASE_PATH'))define('KFM_BASE_PATH', dirname(__FILE__).'/');
if(function_exists("date_default_timezone_set") && function_exists("date_default_timezone_get"))date_default_timezone_set(date_default_timezone_get());

// { load classes and helper functions
spl_autoload_register('KFM_autoload');
function KFM_autoload($name) {
	require_once KFM_BASE_PATH . 'classes/' . $name . '.php';
}
require KFM_BASE_PATH.'includes/lang.php';
require KFM_BASE_PATH.'includes/db.php';
$GLOBALS['kfm']=new kfmBase();
require_once(KFM_BASE_PATH.'templating.php');
function kfm_dieOnError($error) {
    if (!PEAR::isError($error))return;
    echo '<strong>Error</strong><br />'.$error->getMessage().'<br />'.$error->userinfo.'<hr />';
    exit;
}
function setting_array($str){
    $str=trim($str,' ,');
    if($str=='')return array();
    return preg_split('/\s*,\s*/',$str);
}
function sql_escape($sql) {
	$sql=addslashes($sql);
    if ($GLOBALS['kfm_db_type']=='sqlite'||$GLOBALS['kfm_db_type']=='sqlitepdo')$sql = str_replace("\\'", "''", $sql);
    return $sql;
}
function file_join(){
  $path= (strtoupper (substr(PHP_OS, 0,3)) == 'WIN')
    ?''
    :'/';
	$args=func_get_args();
  $path.=join('/',$args);
  $path = str_replace(array('///','//'), '/', $path);
  return $path;
}
// }
if (get_magic_quotes_gpc()) require 'includes/remove_magic_quotes.php';
// { check for fatal errors
if (ini_get('safe_mode')){
    echo '<html><body><p>KFM does not work if you have <code>safe_mode</code> enabled. This is not a bug - please see <a href="http://ie.php.net/features.safe-mode">PHP.net\'s safe_mode page</a> for details</p></body></html>';
    exit;
}
if (!file_exists(KFM_BASE_PATH.'configuration.php')) {
    echo '<em>Missing <code>configuration.php</code>!</em><p>If this is a fresh installation of KFM, then please <strong>copy</strong> <code>configuration.dist.php</code> to <code>configuration.php</code>, remove the settings you don\'t want to change, and edit the rest to your needs.</p><p>For examples of configuration, please visit http://kfm.verens.com/configuration</p>';
    exit;
}
// }
// { load the default config first, then load the custome config over it
if(file_exists(KFM_BASE_PATH.'configuration.dist.php'))include KFM_BASE_PATH.'configuration.dist.php';
require_once KFM_BASE_PATH.'configuration.php';
if(!function_exists('kfm_admin_check')){
	function kfm_admin_check(){
		return true;
	}
}
// }
// { defines
define('KFM_DB_PREFIX', $GLOBALS['kfm_db_prefix']);
// }
// { variables
// structure
$GLOBALS['kfm']->defaultSetting('kfm_url','');
$GLOBALS['kfm']->defaultSetting('file_url','url'); # Unsecure, but better for people setting the userfiles_output
$GLOBALS['kfm']->defaultSetting('user_root_folder','');
$GLOBALS['kfm']->defaultSetting('startup_folder','');
$GLOBALS['kfm']->defaultSetting('force_startup_folder',false);
$GLOBALS['kfm']->defaultSetting('hidden_panels',array('logs','file_details','directory_properties'));
$GLOBALS['kfm']->defaultSetting('log_level', 0);
$GLOBALS['kfm']->defaultSetting('allow_user_file_associations',false);
//display
$GLOBALS['kfm']->defaultSetting('theme', 'default'); // must be overwritten
$GLOBALS['kfm']->defaultSetting('show_admin_link', file_exists(KFM_BASE_PATH.'admin'));
$GLOBALS['kfm']->defaultSetting('time_format', '%T');
$GLOBALS['kfm']->defaultSetting('date_format', '%x');
$GLOBALS['kfm']->defaultSetting('listview',0);
$GLOBALS['kfm']->defaultSetting('preferred_languages',array('en','de','da','es','fr','nl','ga'));
//contextmenu
$GLOBALS['kfm']->defaultSetting('subcontext_categories',array());
$GLOBALS['kfm']->defaultSetting('subcontext_size',4);
// directory
$GLOBALS['kfm']->defaultSetting('root_folder_name','root');
$GLOBALS['kfm']->defaultSetting('allow_files_in_root',1);
$GLOBALS['kfm']->defaultSetting('allow_directory_create',1);
$GLOBALS['kfm']->defaultSetting('allow_directory_delete',1);
$GLOBALS['kfm']->defaultSetting('allow_directory_edit',1);
$GLOBALS['kfm']->defaultSetting('allow_directory_move',1);
$GLOBALS['kfm']->defaultSetting('folder_drag_action',3);
$GLOBALS['kfm']->defaultSetting('default_directories',array());
$GLOBALS['kfm']->defaultSetting('default_directory_permission',755);
$GLOBALS['kfm']->defaultSetting('banned_folders',array('/^\./'));
$GLOBALS['kfm']->defaultSetting('allowed_folders',array());
//files
$GLOBALS['kfm']->defaultSetting('allow_file_create',1);
$GLOBALS['kfm']->defaultSetting('allow_file_delete',1);
$GLOBALS['kfm']->defaultSetting('allow_file_edit',1);
$GLOBALS['kfm']->defaultSetting('allow_file_move',1);
$GLOBALS['kfm']->defaultSetting('show_files_in_groups_of', 10);
$GLOBALS['kfm']->defaultSetting('files_name_length_displayed',20);
$GLOBALS['kfm']->defaultSetting('files_name_length_in_list', 0);
$GLOBALS['kfm']->defaultSetting('banned_extensions',array('asp','cfm','cgi','php','php3','php4','php5','phtm','pl','sh','shtm','shtml'));
$GLOBALS['kfm']->defaultSetting('banned_files',array('thumbs.db','/^\./'));
$GLOBALS['kfm']->defaultSetting('allowed_files',array());

$GLOBALS['kfm']->defaultSetting('startup_selected_files', array()); // maybe should just be a get setting
//image
$GLOBALS['kfm']->defaultSetting('use_imagemagick',1);
//upload
$GLOBALS['kfm']->defaultSetting('allow_file_upload',1);
$GLOBALS['kfm']->defaultSetting('only_allow_image_upload',0);
$GLOBALS['kfm']->defaultSetting('use_multiple_file_upload',1);
$GLOBALS['kfm']->defaultSetting('default_upload_permission',644);
$GLOBALS['kfm']->defaultSetting('banned_upload_extensions',array());
$GLOBALS['kfm']->defaultSetting('max_image_upload_width', 5000);
$GLOBALS['kfm']->defaultSetting('max_image_upload_height', 5000);
// plugins
$GLOBALS['kfm']->defaultSetting('disabled_plugins',array());
// depricated
$GLOBALS['kfm']->defaultSetting('allow_image_manipulation',1); // this is plugin management

if(!$GLOBALS['kfm_use_servers_pear'])set_include_path(KFM_BASE_PATH.'includes/pear'.PATH_SEPARATOR.get_include_path());
if(!substr($GLOBALS['kfm_userfiles_output'],-1,1)=='/')$GLOBALS['kfm_userfiles_output'] .= '/'; // Just convention, end with slash
$GLOBALS['kfm']->defaultSetting('files_url',$GLOBALS['kfm_userfiles_output']);
// { KFM versions
$versions=file(KFM_BASE_PATH.'docs/version.txt');
define('KFM_VERSION', (int)trim($versions[0]));
define('KFM_VERSION_DB', (int)trim($versions[1]));
// }
if (!isset($_SERVER['DOCUMENT_ROOT'])) { // fix for IIS
    $_SERVER['DOCUMENT_ROOT'] = preg_replace('/\/[^\/]*$/', '', str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']));
}
// { API - for programmers only
if (file_exists(KFM_BASE_PATH.'api/config.php')) require KFM_BASE_PATH.'api/config.php';
if (file_exists(KFM_BASE_PATH.'api/cms_hooks.php')) require KFM_BASE_PATH.'api/cms_hooks.php';
else require KFM_BASE_PATH.'api/cms_hooks.php.dist';
// }
$rootdir = (strpos($GLOBALS['kfm_userfiles_address'], './')===0 || strpos($GLOBALS['kfm_userfiles_address'], '../')===0) ?KFM_BASE_PATH.$GLOBALS['kfm_userfiles_address']:$GLOBALS['kfm_userfiles_address'].'/';

if (!is_dir($rootdir))mkdir($rootdir, 0755, true);
$rootdir = realpath($rootdir);
if (!is_dir($rootdir)) {
    echo 'error: "'.htmlspecialchars($rootdir).'" could not be created';
    exit;
}
$rootdir = realpath($rootdir).'/';
$GLOBALS['kfm']->defaultSetting('files_root_path',$rootdir); // This may be a security problem, don't use this property!!!
$GLOBALS['kfm']->files_root_path = $rootdir; // Use this in stead
define('KFM_DIR', dirname(__FILE__));
if (!defined('GET_PARAMS')) define('GET_PARAMS', '');
define('IMAGEMAGICK_PATH', isset($GLOBALS['kfm_imagemagick_path'])?$GLOBALS['kfm_imagemagick_path']:'/usr/bin/convert');
$cache_directories = array();
$GLOBALS['kfm_errors']        = array();
$GLOBALS['kfm_messages']      = array();
// }
// { work directory
if ($GLOBALS['kfm_workdirectory'][0]=='/') {
    $workpath = $GLOBALS['kfm_workdirectory'];
} else {
    $workpath = $rootdir.$GLOBALS['kfm_workdirectory'];
}
$workurl = $GLOBALS['kfm_userfiles_output'].$GLOBALS['kfm_workdirectory'];
$workdir = true;
if (substr($workpath, -1)!='/') $workpath.='/';
if (substr($workurl, -1)!='/') $workurl.='/';
define('WORKPATH', $workpath);
define('WORKURL', $workurl);
if (is_dir($workpath)) {
    if (!is_writable($workpath)) {
        echo 'error: "'.htmlspecialchars($workpath).'" is not writable';
        exit;
    }
} else {
    // Support for creating the directory
    $workpath_tmp = substr($workpath, 0, -1);
    if (is_writable(dirname($workpath_tmp)))mkdir($workpath_tmp, 0755);
    else{
        echo 'error: could not create directory <code>"'.htmlspecialchars($workpath_tmp).'"</code>. please make sure that <code>'.htmlspecialchars(preg_replace('#/[^/]*$#', '', $workpath_tmp)).'</code> is writable by the server';
        exit;
    }
}
// }
// { database
$db_defined            = 0;
$GLOBALS['kfm_db_prefix_escaped'] = str_replace('_', '\\\\_', KFM_DB_PREFIX);
$port                  = ($GLOBALS['kfm_db_port']=='')?'':':'.$GLOBALS['kfm_db_port'];
switch($GLOBALS['kfm_db_type']) {
case 'mysql': // {
    include_once 'MDB2.php';
    $dsn   = 'mysql://'.$GLOBALS['kfm_db_username'].':'.$GLOBALS['kfm_db_password'].'@'.$GLOBALS['kfm_db_host'].$port.'/'.$GLOBALS['kfm_db_name'];
    $GLOBALS['kfmdb'] = &MDB2::connect($dsn);
    if (PEAR::isError($GLOBALS['kfmdb'])) {
        $dsn   = 'mysql://'.$GLOBALS['kfm_db_username'].':'.$GLOBALS['kfm_db_password'].'@'.$GLOBALS['kfm_db_host'];
        $GLOBALS['kfmdb'] = &MDB2::connect($dsn);
        kfm_dieOnError($GLOBALS['kfmdb']);
        $GLOBALS['kfmdb']->query('CREATE DATABASE '.$GLOBALS['kfm_db_name'].' CHARACTER SET UTF8');
        $GLOBALS['kfmdb']->disconnect();
        $dsn   = 'mysql://'.$GLOBALS['kfm_db_username'].':'.$GLOBALS['kfm_db_password'].'@'.$GLOBALS['kfm_db_host'].'/'.$GLOBALS['kfm_db_name'];
        $GLOBALS['kfmdb'] = &MDB2::connect($dsn);
        kfm_dieOnError($GLOBALS['kfmdb']);
    }
    $GLOBALS['kfmdb']->setFetchMode(MDB2_FETCHMODE_ASSOC);
     $GLOBALS['kfmdb']->setOption('portability',MDB2_PORTABILITY_ALL ^ MDB2_PORTABILITY_EMPTY_TO_NULL);
    if (!$db_defined) {
        $res = &$GLOBALS['kfmdb']->query("show tables like '".$GLOBALS['kfm_db_prefix_escaped']."%'");
        kfm_dieOnError($res);
        if (!$res->numRows())include KFM_BASE_PATH.'scripts/db.mysql.create.php';
        else $db_defined = 1;
    }
    break;
 // must be overwritten// }
case 'pgsql': // {
    include_once 'MDB2.php';
    $dsn   = 'pgsql://'.$GLOBALS['kfm_db_username'].':'.$GLOBALS['kfm_db_password'].'@'.$GLOBALS['kfm_db_host'].$port.'/'.$GLOBALS['kfm_db_name'];
    $GLOBALS['kfmdb'] = &MDB2::connect($dsn);
    if (PEAR::isError($GLOBALS['kfmdb'])) {
        $dsn   = 'pgsql://'.$GLOBALS['kfm_db_username'].':'.$GLOBALS['kfm_db_password'].'@'.$GLOBALS['kfm_db_host'];
        $GLOBALS['kfmdb'] = &MDB2::connect($dsn);
        kfm_dieOnError($GLOBALS['kfmdb']);
        $GLOBALS['kfmdb']->query('CREATE DATABASE '.$GLOBALS['kfm_db_name']);
        $GLOBALS['kfmdb']->disconnect();
        $dsn   = 'pgsql://'.$GLOBALS['kfm_db_username'].':'.$GLOBALS['kfm_db_password'].'@'.$GLOBALS['kfm_db_host'].'/'.$GLOBALS['kfm_db_name'];
        $GLOBALS['kfmdb'] = &MDB2::connect($dsn);
        kfm_dieOnError($GLOBALS['kfmdb']);
    }
    $GLOBALS['kfmdb']->setFetchMode(MDB2_FETCHMODE_ASSOC);
     $GLOBALS['kfmdb']->setOption('portability',MDB2_PORTABILITY_ALL ^ MDB2_PORTABILITY_EMPTY_TO_NULL);
    if (!$db_defined) {
        $res = &$GLOBALS['kfmdb']->query("SELECT tablename from pg_tables where tableowner = current_user AND tablename NOT LIKE E'pg\\\\_%' AND tablename NOT LIKE E'sql\\\\_%' AND tablename LIKE E'".$GLOBALS['kfm_db_prefix_escaped']."%'");
        kfm_dieOnError($res);
        if ($res->numRows()<1)include KFM_BASE_PATH.'scripts/db.pgsql.create.php';
        else $db_defined = 1;
    }
    break;
// }
case 'sqlite': // {
	die('<p>The embedded SQLite in PHP is version 2, but that is no longer supported by <a href="http://www.sqlite.org/download.html">SQLite</a>, <a href="http://php.net/manual/en/book.sqlite.php">PHP</a>, or <a href="http://kfm.verens.com/">KFM</a>. Please use the PDO SQLite driver instead.</p>');
// }
case 'sqlitepdo': // {
    $create = false;
    define('DBNAME', $GLOBALS['kfm_db_name']);
    if (!file_exists(WORKPATH.DBNAME)) $create = true;
    $dsn   = array('type'=>'sqlitepdo', 'database'=>WORKPATH.DBNAME, 'mode'=>'0644');
    $GLOBALS['kfmdb'] = new DB($dsn);
    if ($create)include KFM_BASE_PATH.'scripts/db.sqlite.create.php';
    $db_defined = 1;
    break;
// }
}
if (!$db_defined) {
    echo 'failed to connect to database';
    exit;
}
$GLOBALS['kfm']->db = &$GLOBALS['kfmdb']; // Add database as reference to the kfm object
// }
// { get kfm parameters and check for updates
$GLOBALS['kfm_parameters'] = array();
$rs = db_fetch_all("select * from ".KFM_DB_PREFIX."parameters");
foreach($rs as $r)$GLOBALS['kfm_parameters'][$r['name']] = $r['value'];
if ($GLOBALS['kfm_parameters']['version']!=KFM_VERSION && file_exists(KFM_BASE_PATH.'scripts/update.'.KFM_VERSION.'.php'))
    require KFM_BASE_PATH.'scripts/update.'.KFM_VERSION.'.php'; // deprecated. remove for 1.5 and clean up the /scripts/ directory
if (!isset($GLOBALS['kfm_parameters']['version_db']) || $GLOBALS['kfm_parameters']['version_db']!=KFM_VERSION_DB) require KFM_BASE_PATH.'scripts/dbupdates.php';
// }
// { JSON
if (!function_exists('json_encode')) { // php-json is not installed
    include_once 'JSON.php';
    include_once KFM_BASE_PATH.'includes/json.php';
}
// }
// { start session
$session_id  = (isset($_REQUEST['kfm_session']))?$_REQUEST['kfm_session']:'';
$GLOBALS['kfm_session'] = new kfmSession($session_id);
if (isset($_GET['logout'])||isset($_GET['log_out'])) $GLOBALS['kfm_session']->set('loggedin',0);
$GLOBALS['kfm']->defaultSetting('kfm_session_id', $GLOBALS['kfm_session']->key);
// }
// { check authentication
if (isset($use_kfm_security) && !$use_kfm_security)$GLOBALS['kfm_session']->setMultiple(array('loggedin'=>1,'user_id'=>1,'username'=>'CMS user','user_status'=>1),false);
if (!$GLOBALS['kfm_session']->get('loggedin') && (!isset($GLOBALS['kfm_api_auth_override'])||!$GLOBALS['kfm_api_auth_override'])) {
    $err = '';
    if (isset($_POST['username'])&&isset($_POST['password'])) {
        $res=db_fetch_row('SELECT id, username, password, status FROM '.KFM_DB_PREFIX.'users WHERE username="'.sql_escape($_POST['username']).'" AND password="'.sha1($_POST['password']).'"');
        if($res && count($res)){
            $GLOBALS['kfm_session']->setMultiple(array('user_id'=>$res['id'],'username'=>$_POST['username'], 'password'=>$_POST['password'],'user_status'=>$res['status'], 'loggedin'=>1));
        }else $err = '<em>Incorrect Password. Please try again, or check your <code>configuration.php</code>.</em>';
    }
   if (!$GLOBALS['kfm_session']->get('loggedin')) $GLOBALS['kfm']->show_login_form($err);
}
$uid=$GLOBALS['kfm_session']->get('user_id');
$GLOBALS['kfm']->user_id=$uid;
$GLOBALS['kfm']->defaultSetting('user_id',$uid);
$GLOBALS['kfm']->user_status=$GLOBALS['kfm_session']->get('user_status');
$GLOBALS['kfm']->defaultSetting('user_status',$GLOBALS['kfm']->user_status);
$GLOBALS['kfm']->username=$GLOBALS['kfm_session']->get('username');
$GLOBALS['kfm']->user_name=&$GLOBALS['kfm']->username;
$GLOBALS['kfm']->defaultSetting('username',$GLOBALS['kfm']->username);
$GLOBALS['kfm']->defaultSetting('user_name',$GLOBALS['kfm']->username);
$GLOBALS['kfm']->session= &$GLOBALS['kfm_session'];
// }
// { Read settings
function get_settings($uid){
  $settings=array();
  $usersettings = array();
  $admin_settings=db_fetch_all('SELECT name, value, usersetting FROM '.KFM_DB_PREFIX.'settings WHERE user_id=1');
  if(is_array($admin_settings)){
    foreach($admin_settings as $setting){
        $settings[$setting['name']]=$setting['value'];
        if($setting['usersetting']) $usersettings[] = $setting['name'];
    }
  }
  if($uid!=1){
    $user_settings=db_fetch_all('SELECT name, value, usersetting FROM '.KFM_DB_PREFIX.'settings WHERE user_id='.$uid);
    if(is_array($user_settings)){
			foreach($user_settings as $setting){
        $settings[$setting['name']]=$setting['value'];
        if($setting['usersetting']) $usersettings[] = $setting['name'];
      }
    }
  }
  return array($settings, array_unique($usersettings));
}
list($settings, $usersettings) = get_settings($uid); // $settings as database values
foreach($usersettings as $usersetting) $GLOBALS['kfm']->addUserSetting($usersetting);
if(!isset($settings['kfm_url'])){
 $GLOBALS['kfm_url'] = str_replace($_SERVER['DOCUMENT_ROOT'],'',str_replace('\\','/',getcwd()));
 if(!isset($GLOBALS['kfm_url'][0]) || !$GLOBALS['kfm_url'][0] == '/') $GLOBALS['kfm_url'] = '/'.$GLOBALS['kfm_url']; // Make the url absolute
 $GLOBALS['kfm']->db->query('INSERT INTO '.KFM_DB_PREFIX.'settings (name, value, user_id) VALUES ("kfm_url", "'.sql_escape($GLOBALS['kfm_url']).'",1)');
}
if(isset($settings['disabled_plugins'])){
    $GLOBALS['kfm']->setting('disabled_plugins',setting_array($settings['disabled_plugins']));
    unset($settings['disabled_plugins']); // it does not have to be set again
}
// }
// { Setting plugins
$h=opendir(KFM_BASE_PATH.'plugins');
while(false!==($file=readdir($h))){
    if(!is_dir(KFM_BASE_PATH.'plugins/'.$file))continue;
    if($file[0]!='.' && substr($file,0,9)!='disabled_'){
        if(file_exists(KFM_BASE_PATH.'plugins/'.$file.'/plugin.php')) include(KFM_BASE_PATH.'plugins/'.$file.'/plugin.php');
    }
}
closedir($h);
foreach($GLOBALS['kfm']->plugins as $key=>$plugin){
    $GLOBALS['kfm']->sdef['disabled_plugins']['options'][]=$plugin->name;
    if(in_array($plugin->name,$GLOBALS['kfm']->setting('disabled_plugins'))){
        $GLOBALS['kfm']->plugins[$key]->disabled=true;
        continue;
    }
    if(count($plugin->settings)){
        $GLOBALS['kfm']->addSdef($plugin->name, array('type'=>'group_header'));
        foreach($plugin->settings as $psetting){
            $GLOBALS['kfm']->addSdef($psetting['name'], $psetting['definition'],$psetting['default']);
        }
    }
}
// }
// { Apply settings
foreach($GLOBALS['kfm']->sdef as $sname=>$sdef){
    if(isset($settings[$sname])){
        switch($sdef['type']){
            case 'array':
            case 'select_list':
                $value=setting_array($settings[$sname]);
                break;
            default:
                $value=$settings[$sname];
                break;
        }
        $GLOBALS['kfm']->setting($sname, $value);
    }
}
// }
// { (user) root folder
$GLOBALS['kfm_root_dir'] = kfmDirectory::getInstance(1);
if ($GLOBALS['kfm']->user_id!=1 && $GLOBALS['kfm']->setting('user_root_folder')){
    $GLOBALS['kfm']->setting('user_root_folder',str_replace('username',$GLOBALS['kfm']->username,$GLOBALS['kfm']->setting('user_root_folder')));
    $dirs   = explode(DIRECTORY_SEPARATOR, trim($GLOBALS['kfm']->setting('user_root_folder'), ' '.DIRECTORY_SEPARATOR));
    $subdir = $GLOBALS['kfm_root_dir'];
    foreach ($dirs as $dirname) {
        $subdir = $subdir->getSubdir($dirname);
        if(!$subdir) die ('Error: Root directory cannot be found.');
        $GLOBALS['kfm_root_folder_id'] = $subdir->id;
    }
    $user_root_dir = $subdir;
} else {
    $user_root_dir = $GLOBALS['kfm_root_dir'];
}
$GLOBALS['kfm_root_folder_id'] = $user_root_dir->id;
$GLOBALS['kfm']->setting('root_folder_id',$user_root_dir->id);
// }
// { Setting themes
$h=opendir(KFM_BASE_PATH.'themes');
while(false!==($file=readdir($h))){
    if($file[0]!='.' || substr($file,0,9)=='disabled_'){
        $GLOBALS['kfm']->themes[]=$file;
        $GLOBALS['kfm']->sdef['theme']['options'][$file]=$file;
    }
}
closedir($h);
// }
// { Setting the theme
if(isset($_GET['theme']))$GLOBALS['kfm_session']->set('theme',$_GET['theme']);
if($GLOBALS['kfm_session']->get('theme'))$GLOBALS['kfm']->setting('theme',$GLOBALS['kfm_session']->get('theme'));
else if($GLOBALS['kfm']->setting('theme')) $GLOBALS['kfm_session']->set('theme',$GLOBALS['kfm']->setting('theme'));
else{
    if(in_array('default',$GLOBALS['kfm']->themes)){
        $GLOBALS['kfm']->defaultSetting('theme','default');
        $GLOBALS['kfm_session']->set('theme','default');
    }else{
        if(!count($GLOBALS['kfm']->themes)) kfm_error('No themes available');
        else{
            $GLOBALS['kfm']->defaultSetting('theme',$GLOBALS['kfm']->themes[0]);
            $GLOBALS['kfm_session']->set('theme',$GLOBALS['kfm']->themes[0]);
        }
    }
}
// }
// { languages
$GLOBALS['kfm_language'] = '';
// {  find available languages
if ($handle = opendir(KFM_BASE_PATH.'lang')) {
    $files = array();
    while(false!==($file = readdir($handle)))if (is_file(KFM_BASE_PATH.'lang/'.$file))$files[] = $file;
    closedir($handle);
    sort($files);
    $GLOBALS['kfm_available_languages'] = array();
    foreach($files as $f)$GLOBALS['kfm_available_languages'][] = str_replace('.js', '', $f);
} else {
    echo 'error: missing language files';
    exit;
}
// }
// {  check for URL parameter "lang"
if (isset($_REQUEST['langCode'])) $_REQUEST['lang']=$_REQUEST['langCode'];
if (isset($_REQUEST['lang'])&&$_REQUEST['lang']&&in_array($_REQUEST['lang'], $GLOBALS['kfm_available_languages'])) {
    $GLOBALS['kfm_language'] = $_REQUEST['lang'];
    $GLOBALS['kfm_session']->set('language', $GLOBALS['kfm_language']);
}
// }
// {  check session for language selected earlier
    if (
        $GLOBALS['kfm_language']==''&&
        !is_null($GLOBALS['kfm_session']->get('language'))&&
        $GLOBALS['kfm_session']->get('language')!=''&&
        in_array($GLOBALS['kfm_session']->get('language'), $GLOBALS['kfm_available_languages'])
    )$GLOBALS['kfm_language'] = $GLOBALS['kfm_session']->get('language');
// }
// {  check the browser's http headers for preferred languages
if ($GLOBALS['kfm_language']=='') {
    if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))$_SERVER['HTTP_ACCEPT_LANGUAGE'] = '';
    $langs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
    foreach($langs as $lang)if (in_array(preg_replace('/[-;].*/','',trim($lang)), $GLOBALS['kfm_available_languages'])) {
        $GLOBALS['kfm_language'] = preg_replace('/[-;].*/','',trim($lang));
        break;
    }
}
// }
// {  check the kfm_preferred_languages
if ($GLOBALS['kfm_language']=='')foreach($GLOBALS['kfm']->setting('preferred_languages') as $lang)if (in_array($lang, $GLOBALS['kfm_available_languages'])) {
    $GLOBALS['kfm_language'] = $lang;
    break;
}
// }
// {  still no language chosen? use the first available one then
    if ($GLOBALS['kfm_language']=='')$GLOBALS['kfm_language'] = $GLOBALS['kfm_available_languages'][0];
// }
// }
// { common functions
function kfm_error($message,$level=3){
    $GLOBALS['kfm_errors'][]=array('message'=>$message,'level'=>$level);
    return false;
}
function kfm_isError($level=3){
    foreach($GLOBALS['kfm_errors'] as $error) if($error->level<=$level)return true;
    return false;
}
function kfm_getErrors($level=3){
    return $GLOBALS['kfm_errors'];
}
function kfm_addMessage($message){
    $GLOBALS['kfm_messages'][]=array('message'=>$message);
}
function kfm_getMessages(){
    return $GLOBALS['kfm_messages'];
}
// }
// { directory functions
function kfm_add_directory_to_db($name, $parent) {
    include_once KFM_BASE_PATH.'includes/directories.php';
    return _add_directory_to_db($name, $parent);
}
function kfm_createDirectory($parent, $name) {
    include_once KFM_BASE_PATH.'includes/directories.php';
    return _createDirectory($parent, $name);
}
function kfm_deleteDirectory($id, $recursive = 0) {
    include_once KFM_BASE_PATH.'includes/directories.php';
    return _deleteDirectory($id, $recursive);
}
function kfm_getCssSprites($id=0) {
	$id=(int)$id;
	if($id<1)$id=1;
	$dir=kfmDirectory::getInstance($id);
	return $dir->getCssSprites();
}
function kfm_getDirectoryDbInfo($id) {
    include_once KFM_BASE_PATH.'includes/directories.php';
    return _getDirectoryDbInfo($id);
}
function kfm_getDirectoryParents($pid, $type = 1) {
    include_once KFM_BASE_PATH.'includes/directories.php';
    return _getDirectoryParents($pid, $type);
}
function kfm_getDirectoryParentsArr($pid, $path = array()) {
    include_once KFM_BASE_PATH.'includes/directories.php';
    return _getDirectoryParentsArr($pid, $path);
}
function kfm_loadDirectories($root, $oldpid = 0) {
    include_once KFM_BASE_PATH.'includes/directories.php';
    return _loadDirectories($root, $oldpid);
}
function kfm_moveDirectory($from, $to) {
    include_once KFM_BASE_PATH.'includes/directories.php';
    return _moveDirectory($from, $to);
}
function kfm_renameDirectory($dir, $newname) {
    include_once KFM_BASE_PATH.'includes/directories.php';
    return _renameDirectory($dir, $newname);
}
function kfm_rmdir($dir) {
    include_once KFM_BASE_PATH.'includes/directories.php';
    return _rmdir($dir);
}
function kfm_setDirectoryMaxSizeImage($dir,$width,$height){
    include_once KFM_BASE_PATH.'includes/directories.php';
    return _setDirectoryMaxSizeImage($dir,$width,$height);
}
// }
// { file functions
function kfm_copyFiles($files, $dir_id) {
    include_once KFM_BASE_PATH.'includes/files.php';
    return _copyFiles($files, $dir_id);
}
function kfm_createEmptyFile($cwd, $filename) {
    include_once KFM_BASE_PATH.'includes/files.php';
    return _createEmptyFile($cwd, $filename);
}
function kfm_downloadFileFromUrl($url, $filename) {
    include_once KFM_BASE_PATH.'includes/files.php';
    return _downloadFileFromUrl($url, $filename);
}
function kfm_extractZippedFile($id) {
    include_once KFM_BASE_PATH.'includes/files.php';
    return _extractZippedFile($id);
}
function kfm_getFileAsArray($filename) {
    include_once KFM_BASE_PATH.'includes/files.php';
    return _getFileAsArray($filename);
}
function kfm_getFileDetails($filename) {
    include_once KFM_BASE_PATH.'includes/files.php';
    return _getFileDetails($filename);
}
function kfm_getFileUrl($fid, $x = 0, $y = 0) {
    include_once KFM_BASE_PATH.'includes/files.php';
    return _getFileUrl($fid, $x, $y);
}
function kfm_getFileUrls($farr) {
    include_once KFM_BASE_PATH.'includes/files.php';
    return _getFileUrls($farr);
}
function kfm_getTagName($id) {
    include_once KFM_BASE_PATH.'includes/files.php';
    return _getTagName($id);
}
function kfm_getTextFile($filename) {
    include_once KFM_BASE_PATH.'includes/files.php';
    return _getTextFile($filename);
}
function kfm_moveFiles($files, $dir_id) {
    include_once KFM_BASE_PATH.'includes/files.php';
    return _moveFiles($files, $dir_id);
}
function kfm_loadFiles($rootid = 1, $setParent = false) {
    include_once KFM_BASE_PATH.'includes/files.php';
    return _loadFiles($rootid, $setParent);
}
function kfm_renameFile($filename, $newfilename) {
    include_once KFM_BASE_PATH.'includes/files.php';
    return _renameFile($filename, $newfilename);
}
function kfm_renameFiles($files, $template) {
    include_once KFM_BASE_PATH.'includes/files.php';
    return _renameFiles($files, $template);
}
function kfm_resize_bytes($size) {
    include_once KFM_BASE_PATH.'includes/files.php';
    return _resize_bytes($size);
}
function kfm_rm($files, $no_dir = 0) {
    include_once KFM_BASE_PATH.'includes/files.php';
    return _rm($files, $no_dir);
}
function kfm_saveTextFile($filename, $text) {
    include_once KFM_BASE_PATH.'includes/files.php';
    return _saveTextFile($filename, $text);
}
function kfm_search($keywords, $tags) {
    include_once KFM_BASE_PATH.'includes/files.php';
    return _search($keywords, $tags);
}
function kfm_tagAdd($recipients, $tagList) {
    include_once KFM_BASE_PATH.'includes/files.php';
    return _tagAdd($recipients, $tagList);
}
function kfm_tagRemove($recipients, $tagList) {
    include_once KFM_BASE_PATH.'includes/files.php';
    return _tagRemove($recipients, $tagList);
}
function kfm_zip($name, $files) {
    include_once KFM_BASE_PATH.'includes/files.php';
    return _zip($name, $files);
}
// }
// { image functions
function kfm_changeCaption($filename, $newCaption) {
    include_once KFM_BASE_PATH.'includes/images.php';
    return _changeCaption($filename, $newCaption);
}
function kfm_getThumbnail($fileid, $width, $height) {
    include_once KFM_BASE_PATH.'includes/images.php';
    return _getThumbnail($fileid, $width, $height);
}
function kfm_resizeImage($filename, $width, $height) {
    include_once KFM_BASE_PATH.'includes/images.php';
    return _resizeImage($filename, $width, $height);
}
function kfm_resizeImages($files, $width, $height) {
    include_once KFM_BASE_PATH.'includes/images.php';
    return _resizeImages($files, $width, $height);
}
function kfm_rotateImage($filename, $direction) {
    include_once KFM_BASE_PATH.'includes/images.php';
    return _rotateImage($filename, $direction);
}
function kfm_cropToOriginal($fid, $x1, $y1, $width, $height) {
    include_once KFM_BASE_PATH.'includes/images.php';
    return _cropToOriginal($fid, $x1, $y1, $width, $height);
}
function kfm_cropToNew($fid, $x1, $y1, $width, $height, $newname) {
    include_once KFM_BASE_PATH.'includes/images.php';
    return _cropToNew($fid, $x1, $y1, $width, $height, $newname);
}
// }
