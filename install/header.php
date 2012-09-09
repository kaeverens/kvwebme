<?php
/**
	* installer template header
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/


error_reporting(0);
session_start();
if (file_exists('../.private/config.php')
	&&!isset($_SESSION['config_written'])
) {
	echo '<p>'
		.__(
			'<strong>Config file already exists</strong>. Please remove the '
			.'/install directory.'
		)
		.'</p>';
	Core_quit();
}

$_languages=array(
	'en'
);

set_include_path(
	dirname(dirname(__FILE__)).'/ww.php_classes'
	.PATH_SEPARATOR.get_include_path()
);
// { functions
// { WebME_autoload

/**
  * autoloader for classes
  *
  * @param string $name the class name
  *
  * @return null
  */
function Core_autoload($name) {
	require_once $name.'.php';
}
spl_autoload_register('Core_autoload');
// }
// { __

/**
	* translate a string
	* note that the function should be called
	* as either __($str, array('params'), 'context')
	* or as __($str, 'context')
	* the context always comes last
	*
	* @param string $str     string to translate
	* @param string $context the translation context
	* @param array  $params  array of parameters to insert into the string
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

// }
// }

$home_dir=DistConfig::get('installer-userbase');
$cms_name=DistConfig::get('cms-name');
echo '
<!doctype html>
<html>
<head>
	<title>'.__('%1 Installer', array($cms_name), 'core').'</title>';
echo '<link rel="stylesheet" type="text/css" href="/j/cluetip/jquery.cluetip.css" />
	<link rel="stylesheet" href="/ww.admin/theme/admin.css" type="text/css" />
	<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui'
		.'/1.8.14/themes/base/jquery-ui.css" />

	<!-- Installer specific javascript -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min'
		.'.js"></script>
 	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.14/jquery-'
		.'ui.min.js"></script>
	<script src="/j/validate.jquery.min.js"></script>

	<script defer="defer" type="text/javascript">
        function error_handler( msg ){
                $( "#errors" ).html( msg );
        }
	$( function( ){
		// set the current page in install-menu
	        var link= window.location.href.split( "?" );
		link = link[ 0 ].split( "/" );
	        var path = link[ link.length - 1 ];
		$("#install-menu li a[href=\'"+path+"\']").addClass("current");
		$( "#howto" ).click( function( ){
			$( "#dialog" ).dialog( );
		} );
	} );
	</script>

	<!-- Installer specific CSS -->
	<style type="text/css">
		table{
			border-spacing: 6px;
		}
		table th{
			text-align: left;
		}
		#install-menu{
		        margin: 0;
		        padding: 0 0 20px;    
		}
		#install-menu li{ 
		        margin: 0;
		        padding: 0;
		}
		#install-menu li a{
		        border: 0 none;
		        display: block;
		        text-decoration: none;
		        padding: 3px 0 3px 5px;
		}
		#install-menu li a.current{
		        color: #d36042;
		} 
		#content{
			width: 70%;
			margin-left: 190px;
		}
		#errors{
			color:#D36042
		}
		.error{
			border:1px solid #600;
			background:#D36042;
		}
	</style>

</head>
<body> 
	<div id="header"> 
	</div>

	<div id="wrapper">
		<div id="main">

		<h1>'.__('%1 Installer', array($cms_name), 'core').'</h1>

		<div class="sub-nav">
			<ul id="install-menu">
				<li><a href="index.php">'.__('Installation Requirements').'</a></li>
				<li><a href="step1.php">'.__('Add Database').'</a></li>
				<li><a href="step3.php">'.__('Create User').'</a></li>
				<li><a href="step4.php">'.__('User Files').'</a></li>
				<li><a href="step6.php">'.__('Select Theme').'</a></li>
				<li><a href="step7.php">'.__('Finish').'</a></li>
			</ul>
		</div>

		<div id="pages-wrapper">

			<div id="content">
';
