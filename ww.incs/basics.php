<?php
/**
	* functions used in pretty much every section of the CMS
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

$starttime=microtime(true);
$starttimeCount=100;
if (isset($_REQUEST['PHPSESSID'])) {
	@session_id($_REQUEST['PHPSESSID']);
}
@session_start();
if (!defined('START_TIME')) {
	define('START_TIME', microtime(true));
}

// { common variables
$css_urls=array();
$scripts=array();
$scripts_inline=array();
// }

if (!function_exists('__')) {
	/**
		* translate a string
		* note that the function should be called
		* as either __($str, array('params'), 'context')
		* or as __($str, 'context')
		* the context always comes last
		*
		* @param string $str    string to translate
		* @param string $param1 mixed
		* @param array  $param2 mixed
		*
		* @return string the translated string
		*/
	function __($str, $param1='core', $param2=null) {
	
		if (is_string($param1)) {
			$context=$param1;
			$params=array();
		}
		else if (is_array($param1)) {
			$context=$param2;
			$params=$param1;
		}
		if ($context==null) {
			$context='core';
		}
	
		global $_language_cache, $_languages, $_language_notfound;
		if ($context=='') {
			$context='core';
		}
		// { webme is written in en-GB
		if ($_languages[0]=='en' || $_languages[0]=='en-gb'
			|| $_languages[0]=='en-us'
		) {
			for ($i=count($params);$i;--$i) {
				$str=str_replace('%'.$i, $params[$i-1], $str);
			}
			return $str;
		}
		// }
		// { return already-translated strings
		if (isset($_language_cache[$context][$str])) {
			return $_language_cache[$context][$str];
		}
		// }
		if (!isset($_language_cache[$context])) {
			$_language_cache[$context]=array();
		}
		// { load from cache or database
		$rs=Core_cacheLoad('core-translation', md5($str.'|'.$context));
		if (!$rs) {
			$rs=dbAll(
				'select lang,trstr from languages where lang in ("'.join('","', $_languages)
				.'") and context="'.$context.'" and str="'.addslashes($str).'"', 'lang'
			);
			if ($rs) {
				Core_cacheSave('core-translation', md5($str.'|'.$context), $rs);
			}
		}
		// }
		// { find the best-fit translation
		if ($rs && count($rs)) {
			$found='';
			foreach ($_languages as $l) {
				if ($l=='en' || $l=='en-GB') {
					$found=$str;
					break;
				}
				if (isset($rs[$l])) {
					$_language_cache[$context][$str]=$rs[$l]['trstr'];
					$found=$rs[$l]['trstr'];
					break;
				}
			}
			if ($found!='') {
				for ($i=count($params);$i;--$i) {
					$found=str_replace('%'.$i, $params[$i-1], $found);
				}
				return $found;
			}
		}
		// }
	
		// otherwise, log the failure and return the string
		$_language_notfound[]=array($str, $context, $_languages[0]);
		$_language_cache[$context][$str]=$str;
		return $str;
	}
}
if (!function_exists('Core_autoload')) {
	/**
	  * autoloader for classes
	  *
	  * @param string $name the class name
	  *
	  * @return null
	  */
	function Core_autoload($name) {
		@include $name . '.php';
	}
	spl_autoload_register('Core_autoload');
}
// { __FromJson

/**
	* retieve a string from a JSON array of translations
	*
	* @param string  $str           the JSON string
	* @param boolean $first_result  retrieve just the first available string
	* @param mixed   $specific_lang false, or the language to retrieve
	*
	* @return string
	*/
function __FromJson($str, $first_result=false, $specific_lang=false) {
	global $_languages;
	if (substr($str, 0, 1)!=='{') { // }
		return $str;
	}
	$s=json_decode($str, true);
	if ($s===null || !is_array($s)) {
		return $str;
	}
	else {
		if ($specific_lang && $s[$specific_lang]) {
			return $s[$specific_lang];
		}
		if ($first_result) {
			foreach ($s as $l=>$r) {
				return $r;
			}
		}
		foreach ($_languages as $l) {
			if (isset($s[$l]) && $s[$l]) {
				return $s[$l];
			}
		}
	}
	foreach ($s as $l=>$r) { // else just return the first found
		return $r;
	}
}

// }
// { Core_cacheClear

/**
  * clear a cache or all caches
  *
  * @param string $type the cache to clear
  *
  * @return null
  */
function Core_cacheClear($type='') {
	if (strpos($type, ',')!==false) {
		$types=explode(',', $type);
		foreach ($types as $t) {
			Core_cacheClear($t);
		}
		return;
	}
	if (!is_dir(USERBASE.'/ww.cache/'.$type)) {
		return;
	}
	$d=new DirectoryIterator(USERBASE.'/ww.cache/'.$type);
	foreach ($d as $f) {
		$f=$f->getFilename();
		if ($f=='.' || $f=='..') {
			continue;
		}
		if (is_dir(USERBASE.'/ww.cache/'.$type.'/'.$f)) {
			Core_cacheClear($type.'/'.$f);
			if (is_dir(USERBASE.'/ww.cache/'.$type.'/'.$f)) {
				@rmdir(USERBASE.'/ww.cache/'.$type.'/'.$f);
			}
		}
		else {
			if (file_exists(USERBASE.'/ww.cache/'.$type.'/'.$f)) {
				@unlink(USERBASE.'/ww.cache/'.$type.'/'.$f);
			}
		}
	}
}

// }
// { Core_cacheLoad

/**
  * retrieve a cached variable if it exists
  *
  * @param string $type type of cache
	* @param string $id   unique identifier of the cache
	* @param mixed  $fail what to return if cache doesn't exist
  *
  * @return mixed the variable if it was cached, or false
  */
function Core_cacheLoad($type, $id, $fail=false) {
	if (strlen($id)>32) {
		$id=md5($id);
	}
	if (file_exists(USERBASE.'/ww.cache/'.$type.'/'.$id)) {
		return json_decode(
			file_get_contents(USERBASE.'/ww.cache/'.$type.'/'.$id),
			true
		);
	}
	return $fail;
}

// }
// { Core_cacheSave

/**
  * cache a variable
  *
  * @param string $type type of cache (page, product, etc)
	* @param string $md5  unique identifier - not necessarily an MD5
	* @param mixed  $vals the variable to encode and cache
  *
  * @return null
  */
function Core_cacheSave($type, $md5, $vals) {
	if (strlen($md5)>32) {
		$md5=md5($md5);
	}
	if (!is_dir(USERBASE.'/ww.cache/'.$type)) {
		mkdir(USERBASE.'/ww.cache/'.$type, 0777, true);
	}
	file_put_contents(
		USERBASE.'/ww.cache/'.$type.'/'.$md5,
		json_encode($vals)
	);
}

// }
// { Core_configRewrite

/**
  * rewrite the config file
  *
  * @return null
  */
function Core_configRewrite() {
	global $DBVARS;
	$tmparr=$DBVARS;
	$tmparr['plugins']=join(',', $DBVARS['plugins']);
	$tmparr2=array();
	foreach ($tmparr as $name=>$val) {
		$tmparr2[]='\''.addslashes($name).'\'=>\''.addslashes($val).'\'';
	}
	$config="<?php\n\$DBVARS=array(\n	".join(",\n	", $tmparr2)."\n);";
	file_put_contents(CONFIG_FILE, $config);
}

// }
// { Core_flushBuffer

/**
  * log the request and send the buffer to the browser
  *
  * @param string $type   type of request
  * @param string $header specific header to use
  *
  * @return null
  */
function Core_flushBuffer($type, $header='') {
	$length=ob_get_length();
	$num_queries=isset($GLOBALS['db'])?$GLOBALS['db']->num_queries:0;
	switch ($type) {
		case 'design_file': case 'file': // {
			$location=$_SERVER['REQUEST_URI'];
		break; // }
		case 'menu': // {
			$location='menu';
		break; // }
		case 'page': // {
			$location=$GLOBALS['PAGEDATA']->id.'|'
				.$GLOBALS['PAGEDATA']->getRelativeUrl();
		break; // }
		default: // {
			$location='unknown_type_'.$type;
			//}
	}
	file_put_contents(
		USERBASE.'/log.txt',
		date('Y-m-d H:i:s').' '.$type.' [info] '
		.$_SERVER['REMOTE_ADDR']
		.'	'.$location
		.'	'.(isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'')
		.'	'.(isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'')
		.'	'.memory_get_peak_usage()
		.'	'.$length
		.'	'.(microtime(true)-START_TIME)
		.'	'.$num_queries."\n",
		FILE_APPEND|LOCK_EX
	);
	if ($header) {
		header($header);
	}
	ob_flush();
}

// }
// { Core_isAdmin

/**
  * is the viewer an admin?
  *
  * @return boolean true or false
  */
function Core_isAdmin() {
	if (!isset($_SESSION['userdata'])) { // not logged in
		return false;
	}
	if (!isset($_SESSION['userdata']['groups'])) {
		require_once dirname(__FILE__).'/user-authentication.php';
	}
	return isset($_SESSION['userdata']['groups']['administrators']);
}

// }
// { Core_mail

/**
	* send an email
	*
	* @param string $to                 email address to send to
	* @param string $subject            title of the email
	* @param string $body               HTML of the email body
	* @param string $from               who the email comes from
	* @param string $template           email template to use
	* @param string $additional_headers any additional headers
	*
	* @return null
	*/
function Core_mail(
	$to, $subject, $body, $from, $template='_body', $additional_headers=''
) {
	$dirname=USERBASE.'/ww.cache/email-templates';
	if (!file_exists($dirname.'/'.$template.'.tpl')) {
		$rs=dbAll('select * from email_templates');
		@mkdir($dirname);
		foreach ($rs as $r) {
			file_put_contents($dirname.'/'.$r['name'].'.tpl', $r['body']);
		}
	}
	require_once SCRIPTBASE . '/ww.incs/Smarty-3.1.12/libs/Smarty.class.php';
	$smarty=new Smarty;
	$smarty->left_delimiter = '{{';
	$smarty->right_delimiter = '}}';
	$smarty->template_dir=$dirname;
	$smarty->compile_dir=$dirname;
	$smarty->assign(
		'email_body', $body
	);
	$html=$smarty->fetch($dirname.'/'.$template.'.tpl'); 
	$headers ='MIME-Version: 1.0' . "\r\n";
	$headers.='Reply-to: '.$from."\r\n";
	$headers.='From: '.$from."\r\n";
	$headers.='X-Mailer: PHP/'.phpversion()."\r\n";
	$headers.=$additional_headers;
	dbQuery(
		'insert into emails_sent set to_email="'.addslashes($to).'"'
		.', body="'.addslashes($html).'", headers="'.addslashes($headers).'"'
		.', cdate=now(), subject="'.addslashes($subject).'"'
	);
	$images=array();
	$count=md5(date('r', time()));;
	do {
		$newhtml=$html;
		$itypes=array(
			'png'=>'image/png',
			'gif'=>'image/gif',
			'jpg'=>'image/jpeg'
		);
		foreach ($itypes as $extension=>$mime) {
			if (preg_match('/"http[^"]*'.$extension.'"/i', $html)) {
				preg_match_all('/.*"(http[^"]*'.$extension.')".*/i', $html, $matches);
				$origimg=$matches[1][0];
				$img=str_replace(' ', '%20', $origimg);
				$fname=preg_replace('/.*\//', '', $origimg);
				$f=file_get_contents($img);
				$images[]=array($f, $mime, 'ii_'.$count, $fname);
				$html=str_replace($origimg, 'cid:ii_'.$count, $html);
				$count=md5($count);
			}
		}
	} while ($newhtml!=$html);
	$sep=md5(date('r', time()));
	$sep2=md5($sep);
	$headers.="Content-Type: multipart/related; boundary={$sep}";

	// { body
	$body="--{$sep}\r\n"
		."Content-Type: multipart/alternative; boundary={$sep2}\r\n\r\n"
		."--{$sep2}\r\n"
		."Content-type: text/plain; charset=UTF-8\r\n\r\n"
		.preg_replace('/<[^>]*>/', ' ', $html)."\r\n\r\n"
		."--{$sep2}\r\n"
		."Content-type: text/html; charset=UTF-8\r\n\r\n"
		.$html."\r\n\r\n"
		."--{$sep2}--\r\n";
	// }

	foreach ($images as $v) {
		$body.="--{$sep}\r\n"
			."Content-Type: ".$v[1]."; name=\"".$v[3]."\"\r\n"
			."Content-Transfer-Encoding: base64\r\n"
			."Content-ID: <".$v[2].">\r\n"
			."X-Attachment-Id: ".$v[2]."\r\n"
			."\r\n"
			.chunk_split(base64_encode($v[0]))."\r\n";
	}

	$body.="--{$sep}--";

	mail(
		$to,
		$subject,
		$body,
		$headers,
		"-f$from -ODeliveryMode=d"
	);
}

// }
// { Core_quit

/**
	* exit(), but it also calls any shut-down scripts that are installed
	*
	* @param string $str string to echo
	*
	* @return null
	*/
function Core_quit($str='') {
	echo $str;
	$auto_append_file=ini_get('auto_append_file');
	if ($auto_append_file) {
		require_once $auto_append_file;
	}
	exit;
}
// }
// { Core_shutdown

/**
	* shutdown script
	*
	* @return null
	*/
function Core_shutdown() {
	global $_language_notfound;
	return;
	if (!count($_language_notfound)) {
		return;
	}
	foreach ($_language_notfound as $l) {
		$where=' where lang="'.addslashes($l[2]).'" and context="'
			.addslashes($l[1]).'" and str="'.addslashes($l[0]).'"';
		$requests=(int)dbOne(
			'select requests from languages_notfound'.$where,
			'requests'
		);
		if ($requests) {
			dbQuery(
				'update languages_notfound set requests='.($requests+1).$where
			);
		}
		else {
			dbQuery(
				'insert into languages_notfound set requests=1,lang="'
				.addslashes($l[2]).'",context="'.addslashes($l[1]).'",str="'
				.addslashes($l[0]).'"'
			);
		}
	}
}

// }
// { Core_siteVar

/**
	* retrieve a site_var variable
	*
	* @param string $name  the name of the variable
	* @param string $value what to set it to
	*
	* @return string
	*/
function Core_siteVar($name, $value=null) {
	if ($value!==null) { // set
		dbQuery('delete from site_vars where name="'.$name.'"');
		dbQuery(
			'insert into site_vars set name="'.$name.'", value="'
			.addslashes($value).'"'
		);
		Core_cacheSave('site_vars', $name, $value);
		return $value;
	}
	// { or get
	$value=Core_cacheLoad('site_vars', $name, null);
	if ($value!==null) {
		return $value;
	}
	$value=dbOne('select value from site_vars where name="'.$name.'"', 'value');
	if ($value) {
		return $value;
	}
	if (file_exists(dirname(__FILE__).'/site_vars/'.$name.'.php')) {
		require_once dirname(__FILE__).'/site_vars/'.$name.'.php';
		return $value;
	}
	return '';
	// }
}

// }
// { Core_smartySetup

/**
	* set up Smarty with common functions
	*
	* @param string $compile_dir the caching directory to use
	*
	* @return object the Smarty object
	*/
function Core_smartySetup($compile_dir) {
	global $DBVARS, $PLUGINS, $PAGEDATA;
	require_once SCRIPTBASE . '/ww.incs/Smarty-3.1.12/libs/Smarty.class.php';
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
	$smarty->assign('LANGUAGE', @$_SESSION['language']);
	$smarty->assign('LOCATIONNAME', @$_SESSION['location']['name']);
	$smarty->registerPlugin('function', 'BREADCRUMBS', 'Template_breadcrumbs');
	$smarty->registerPlugin('function', 'LANGUAGES', 'Core_languagesGetUi');
	$smarty->registerPlugin('function', 'LOCATIONSELECTOR', 'Core_locationsGetUi');
	$smarty->registerPlugin('function', 'LOGO', 'Template_logoDisplay');
	$smarty->registerPlugin('function', 'MENU', 'menuDisplay');
	$smarty->assign('QRCODE', '/a/f=qrCode/id='.$PAGEDATA->id);
	$smarty->registerPlugin('function', 'nuMENU', 'Core_menuShowFg');
	foreach ($PLUGINS as $pname=>$plugin) {
		if (isset($plugin['frontend']['template_functions'])) {
			foreach ($plugin['frontend']['template_functions'] as $fname=>$vals) {
				$smarty->registerPlugin('function', $fname, $vals['function']);
			}
		}
	}
	$smarty->compile_dir=$compile_dir;
	return $smarty;
}

// }
// { Core_trigger

/**
  * trigger an event
  *
  * @param string $trigger_name the event to trigger
	* @param array  $params       parameters to pass to the event
  *
  * @return string results of the event
  */
function Core_trigger($trigger_name, $params = null) {
	global $PLUGIN_TRIGGERS, $PAGEDATA;
	if (!isset($PLUGIN_TRIGGERS[$trigger_name])) {
		return;
	}
	$c='';
	foreach ($PLUGIN_TRIGGERS[$trigger_name] as $fn) {
		if ($params == null) {
			$c.=$fn($PAGEDATA);
		}
		else {
			if (is_array($params)) {
				$args=$params;
				// push PAGEDATA to beginning of array
				array_unshift($args, $PAGEDATA);
				$res=call_user_func_array($fn, $args);
			}
			else {
				$res=$fn($PAGEDATA, $params);
			}
			if (is_array($res)) {
				return $res;
			}
			$c.=$res;
		}
	}
	return $c;
}

// }
// { dbAll

/**
  * run a database query and return all resulting rows
  *
  * @param string $query the query to run
	* @param string $key   if supplied, use this field for the row keys
  *
  * @return array the results
  */
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
		if (!isset($r[$key])) {
			return false;
		}
		$arr[$r[$key]]=$r;
	}
	return $arr;
}

// }
// { dbInit

/**
  * initialise the database
  *
  * @return object the database object
  */
function dbInit() {
	if (isset($GLOBALS['db'])) {
		return $GLOBALS['db'];
	}
	global $DBVARS;
	try {
		$db=new PDO(
			'mysql:host='.$DBVARS['hostname'].';dbname='.$DBVARS['db_name'],
			$DBVARS['username'],
			$DBVARS['password'],
			array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
		);
	}
	catch (Exception $e) {
		die($e->getMessage());
	}
	$db->num_queries=0;
	$GLOBALS['db']=$db;
	return $db;
}

// }
// { dbLastInsertId

/**
  * get the id from the last database insert query
  *
  * @return int last insert id
  */
function dbLastInsertId() {
	return (int)dbOne('select last_insert_id() as id', 'id');
}

// }
// { dbOne

/**
  * run a database query and return a single field
  *
  * @param string $query the query to run
  * @param string $field the field to return
  *
  * @return mixed false if it failed, or the requested field if successful
  */
function dbOne($query, $field='') {
	$r = dbRow($query);
	if ($r === false) {
		return false;
	}
	return $r[$field];
}

// }
// { dbQuery

/**
  * run a database query
  *
  * @param string $query the query to run
  *
  * @return mixed false if it failed, or the database resource if successful
  */
function dbQuery($query) {
	$db=dbInit();
	$q=$db->query($query);
	if ($q === false) { // failed
		return false;
	}
	$db->num_queries++;
	return $q;
}

// }
// { dbRow

/**
  * run a database query and return a single row
  *
  * @param string $query the query to run
  *
  * @return array the returned row
  */
function dbRow($query) {
	$q = dbQuery($query);
	if ($q === false) {
		return false;
	}
	return $q->fetch(PDO::FETCH_ASSOC);
}

// }
// { transcribe

/**
 * transcribe
 *
 * replaces accented characters with their
 * non-accented equivellants
 *
 * @param string $string the string to transcribe
 *
 * @return string the transcribed string
 */
function transcribe($string) {
    $a = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞ
ßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ“”’';
    $b = 'aaaaaaaceeeeiiiidnoooooouuuuy
bsaaaaaaaceeeeiiiidnoooooouuuyybyRr""\'';
    $string = utf8_decode($string);    
    $string = strtr($string, utf8_decode($a), $b);
    $string = strtolower($string);
    return utf8_encode($string);
} 

// }
// { WW_addCSS

/**
  * add a CSS file to be shown in the page
  *
  * @param string $url URL of the sheet
  *
  * @return null
  */
function WW_addCSS($url) {
	global $css_urls;
	if (in_array($url, $css_urls)) {
		return;
	}
	$css_urls[]=$url;
}

// }
// { WW_addInlineScript

/**
  * add a JS script to be shown inline at the bottom of the page
  *
	* @param string $script the JS script
  *
  * @return null
  */
function WW_addInlineScript($script) {
	global $scripts_inline;
	$script=preg_replace(
		'/\s+/',
		' ',
		str_replace(array("\n","\r"), ' ', $script)
	);
	if (in_array($script, $scripts_inline)) {
		return;
	}
	$scripts_inline[]=$script;
}

// }
// { WW_addScript

/**
  * add a JS script to be externally linked and shrunk
  *
	* @param string $url the URL of the external JS script
  *
  * @return null
  */
function WW_addScript($url) {
	global $scripts;
	if (in_array($url, $scripts)) {
		return;
	}
	$scripts[]=$url;
}

// }
// { set up language
$_languages=array();
if (isset($_REQUEST['__LANG'])) {
	$_SESSION['language']=$_REQUEST['__LANG'];
}
if (isset($_SESSION['language'])) {
	$_languages[]=$_SESSION['language'];
}
if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
	$_languages[]='en';
}
else {
	foreach (explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']) as $l) {
		$lang=preg_replace('/;.*/', '', $l);
		$_languages[]=$lang;
	}
}
$_language_cache=array();
$_language_notfound=array(); // for recording missing language strings
// }
// { set up constants
define('SCRIPTBASE', $_SERVER['DOCUMENT_ROOT'] . '/');
if (!file_exists(SCRIPTBASE . '.private/config.php')) {
	echo '<html><body><p>'.__('No configuration file found').'</p>';
	if (file_exists('install/index.php')) {
		echo '<p><a href="/install/index.php">'.__('Click here to install').'</a></p>';
	}
	else {
		echo '<p><strong>'.__('Installation script also missing...')
			.'</strong></p>';
	}
	echo '</body></html>';
	Core_quit();
}
require SCRIPTBASE . '.private/config.php';
if (isset($DBVARS['userbase'])) {
	define('USERBASE', $DBVARS['userbase']);
}
else {
	define('USERBASE', SCRIPTBASE);
}
$DBVARS['plugins']=(isset($DBVARS['plugins']) && $DBVARS['plugins']!='')
	?explode(',', $DBVARS['plugins'])
	:array();
if (!defined('CONFIG_FILE')) {
	define('CONFIG_FILE', SCRIPTBASE.'.private/config.php');
}
define('WORKDIR_IMAGERESIZES', USERBASE.'/f/.files/image_resizes/');
define('WORKURL_IMAGERESIZES', '/f/.files/image_resizes/');
define('CKEDITOR', 'ckeditor');
if (!defined('KFM_BASE_PATH')) {
	define('KFM_BASE_PATH', SCRIPTBASE.'j/kfm/');
}
// }
// { include path, for classes, etc
set_include_path(
	SCRIPTBASE.'ww.php_classes'.PATH_SEPARATOR.KFM_BASE_PATH.'classes'
	.PATH_SEPARATOR.get_include_path()
);
// }
// { theme variables
if (isset($DBVARS['theme_dir_personal']) && $DBVARS['theme_dir_personal']) {
	define('THEME_DIR', $DBVARS['theme_dir_personal']);
}
else if (isset($DBVARS['theme_dir']) && $DBVARS['theme_dir']) {
	define('THEME_DIR', $DBVARS['theme_dir']);
}
else {
	define('THEME_DIR', SCRIPTBASE.'ww.skins');
}
if (@$_REQUEST['__theme'] && strpos($_REQUEST['__theme'], '/')===false
	&& file_exists(THEME_DIR.'/'.$_REQUEST['__theme'])
) {
	$_SESSION['theme_override']=array(
		$_REQUEST['__theme'],
		@$_REQUEST['__theme_variant'],
		time()
	);
	define('THEME', $_REQUEST['__theme']);
	$DBVARS['theme_variant']=@$_REQUEST['__theme_variant'];
}
elseif (isset($_SESSION['theme_override'])
	&& $_SESSION['theme_override'][2]>(time()-5)
) {
	define('THEME', $_SESSION['theme_override'][0]);
	$DBVARS['theme_variant']=$_SESSION['theme_override'][1];
}
elseif (@$DBVARS['theme']) {
	define('THEME', $DBVARS['theme']);
}
else {
	if (!file_exists(THEME_DIR)) {
		@mkdir(THEME_DIR);
		if (!file_exists(THEME_DIR)) {
			die(
				__(
					'error: theme directory %1 does not exist. please'
					.' create it and make sure it is writable by the web server.',
					array(THEME_DIR), 'core'
				)
			);
		}
	}
	$dir=new DirectoryIterator(THEME_DIR);
	$themes_found=0;
	$DBVARS['theme']='.default';
	foreach ($dir as $file) {
		if ($file->isDot() || !$file->isDir()) {
			continue;
		}
		$DBVARS['theme']=$file->getFileName();
		break;
	}
	define('THEME', $DBVARS['theme']);
}
// }
// { set up location
if (!isset($_SESSION['location'])) {
	require_once dirname(__FILE__).'/api-funcs.php';
	$locations=Core_locationsGet();
	$_SESSION['location']=false;
	if (count($locations)) {
		$_SESSION['location']=array(
			'lat'=>$locations[0]['lat'],
			'lng'=>$locations[0]['lng'],
			'locid'=>$locations[0]['id'],
			'locname'=>$locations[0]['name']
		);
	}
}
if (isset($_REQUEST['__LOCATION'])) {
	$lid=(int)$_REQUEST['__LOCATION'];
	$loc=Core_cacheLoad('core', 'location-'.$lid, -1);
	if ($loc==-1) {
		$loc=dbRow('select * from locations where id='.$lid);
		Core_cacheSave('core', 'location-'.$lid, $loc);
	}
	if ($loc) {
		$_SESSION['location']=array(
			'lat'=>$loc['lat'],
			'lng'=>$loc['lng'],
			'id'=>$loc['id'],
			'name'=>$loc['name']
		);
	}
	else {
		$_SESSION['location']['id']=0;
		$_SESSION['location']['name']='Nationwide';
	}
}
// }
// { plugins
$PLUGINS=array();
$PLUGIN_TRIGGERS=array();
if (!isset($ignore_cms_plugins)) {
	foreach ($DBVARS['plugins'] as $pname) {
		if (strpos('/', $pname)!==false) {
			continue;
		}
		require_once SCRIPTBASE . 'ww.plugins/'.$pname.'/plugin.php';
		if (isset($plugin['version']) && $plugin['version']
			&& (!isset($DBVARS[$pname.'|version'])
			|| $DBVARS[$pname.'|version']!=$plugin['version'] )
		) {
			$version=isset($DBVARS[$pname.'|version'])
				?(int)$DBVARS[$pname.'|version']
				:0;
			require SCRIPTBASE . 'ww.plugins/'.$pname.'/upgrade.php';
			$DBVARS[$pname.'|version']=$version;
			Core_configRewrite();
			Core_cacheClear();
			header('Location: '.$_SERVER['REQUEST_URI']);
			Core_quit();
		}
		$PLUGINS[$pname]=$plugin;
		if (isset($plugin['triggers'])) {
			foreach ($plugin['triggers'] as $name=>$fn) {
				if (!isset($PLUGIN_TRIGGERS[$name])) {
					$PLUGIN_TRIGGERS[$name]=array();
				}
				$PLUGIN_TRIGGERS[$name][]=$fn;
			}
		}
	}
}
// }
register_shutdown_function('Core_shutdown');
Core_trigger('initialisation-completed');
