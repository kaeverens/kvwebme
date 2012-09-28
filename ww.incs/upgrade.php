<?php
/**
	* upgrade script for WebME
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
  * recursively copy a directory
  *
	* @param string $src source directory
	* @param string $dst destination directory
	*
  * @return null
  */
function Copy_recursive($src, $dst) {
	$dir = opendir($src);
	if (!file_exists($dst)) {
		mkdir($dst);
	}
	while (false !== ( $file = readdir($dir)) ) {
		if (( $file != '.' ) && ( $file != '..' )) {
			if ( is_dir($src . '/' . $file) ) {
				Copy_recursive($src . '/' . $file, $dst . '/' . $file);
			}
			else {
				copy($src . '/' . $file, $dst . '/' . $file);
			}
		}
	}
	closedir($dir);
} 

$version=0;
require_once '../ww.incs/common.php';
if (isset($DBVARS['version'])) {
	$version=(int)$DBVARS['version'];
}
echo '<strong>upgrades detected. running upgrade script.</strong>';
// {
if (isset($DBVARS['userbase'])) {
	$userbase=$DBVARS['userbase'];
}
else {
	$userbase=SCRIPTBASE;
}
// }
if ($version==0) { // missing user accounts and groups
	// { user_accounts
	dbQuery(
		'create table user_accounts( id int auto_increment not null primary key,'
		.'email text default "", name text default "", password varchar(32), '
		.'phone text default "", active smallint default 0, address text, '
		.'parent int default 0)default charset=utf8'
	);
	// }
	// { groups
	dbQuery(
		'create table groups( id int auto_increment not null primary key,'
		.'name text, parent int default 0)default charset=utf8'
	);
	// }
	// { users_groups
	dbQuery(
		'create table users_groups( user_accounts_id int default 0, '
		.'groups_id int default 0)default charset=utf8'
	);
	// }
	echo '<p>Database upgraded - you will need to create an admin by insertin'
		.'g appropriate values into the tables user_accounts, groups and users_'
		.'groups (use 1 as the primary key for each). Future upgrades should no'
		.'t require any manual action at all.</p>';
	$version=1;
}
if ($version==1) { // add .private/.htaccess
	$str="order allow,deny\ndeny from all";
	if ( file_exists('../.private/.htaccess')
		|| file_put_contents('../.private/.htaccess', $str)
	) {
		$version=2;
	}
	else {
		echo '<p>Error: could not create <code>.private/.htaccess</code>. '
			.'Please make sure the <code>.private</code> directory is writable '
			.'by the server.</p>';
	}
}
if ($version==2) { // admin vars
	dbQuery(
		'CREATE TABLE `admin_vars` ( `admin_id` int(11) default 0, `varname` text,'
		.'`varvalue` text) ENGINE=MyISAM DEFAULT CHARSET=utf8'
	);
	$version=3;
}
if ($version==3) { // pages
	dbQuery(
		'CREATE TABLE `pages` ( `id` int(11) NOT NULL auto_increment, `name` te'
		.'xt, `body` mediumtext, `parent` int(11) default 0, `ord` int(11) NOT '
		.'NULL default 0, `cdate` datetime NOT NULL default "0000-00-00 00:00:0'
		.'0", `special` bigint(20) default NULL, `edate` datetime default NULL,'
		.' `title` text, `htmlheader` text, `template` text, `type` smallint(6)'
		.' default 0, `keywords` text, `description` text, `category` text NOT '
		.'NULL, `importance` float default 0.5, PRIMARY KEY  (`id`)) ENGINE=MyI'
		.'SAM DEFAULT CHARSET=utf8'
	);
	dbQuery(
		"INSERT INTO `pages` VALUES (1,'Home','<h1>Welcome</h1>\\r\\n<p>This is"
		." your new website. To administer it, please go to <a href=\\\"/ww.adm"
		."in/\\\">/ww.admin</a> and log in using your email address and passwor"
		."d. If you have forgotten your password, please use the reminder form "
		."to have a new password sent to you.</p>\\r\\n<p>If you don\\'t like t"
		."he default theme, please choose a different one in the Site Options p"
		."age.</p>',0,1,now(),1,now(),'Welcome','','',0,'','','',0.5)"
	);
	$version=4;
}
if ($version==4) { // page_vars
	dbQuery(
		'CREATE TABLE `page_vars` (`page_id` int(11) default NULL,`name` text,'
		.'`value` text) ENGINE=MyISAM DEFAULT CHARSET=utf8'
	);
	$version=5;
}
if ($version==5) { // site_vars
	dbQuery(
		'CREATE TABLE `site_vars` ( `name` text, `value` text)'
		.'ENGINE=MyISAM DEFAULT CHARSET=utf8'
	);
	$version=7;
}
if ($version<8) { // blog indexes
	dbQuery(
		'CREATE TABLE `blog_indexes` ( `pageid` int(11) default NULL,'
		.'`parent` int(11) default NULL, `rss` text,'
		.'`amount_to_show` int(11) default 0) ENGINE=MyISAM DEFAULT CHARSET=utf8'
	);
	$version=8;
}
if ($version==8) { // remove blog indexes
	dbQuery('DROP TABLE `blog_indexes`');
	$version=9;
}
if ($version==9) { // comments
	dbQuery(
		'CREATE TABLE `comments` ( `id` int(11) NOT NULL auto_increment,'
		.'`objectid` int(11) default 0, `name` text, `email` text, `homepage` text,'
		.'`comment` text, `cdate` datetime default NULL,'
		.'`isvalid` smallint(6) default 0,'
		.'`verificationhash` char(28) default NULL, PRIMARY KEY  (`id`))'
		.'ENGINE=MyISAM DEFAULT CHARSET=utf8'
	);
	$version=10;
}
if ($version==10) { // set default theme
	if (!isset($DBVARS['theme'])) {
		$DBVARS['theme']='.default';
	}
	$version=11;
}
if ($version==11) { // smarty template_c directory
	$dir=$userbase. 'templates_c';
	if (!is_dir($dir)) {
		mkdir($dir);
		if (!is_dir($dir)) {
			echo '<p>Error: could not create directory <code>'.$dir
				.'</code>. Please make sure that <code>'.$userbase
				.'</code> is writable by the server.</p>';
		}
	}
	if (is_dir($dir)) {
		touch($dir.'/test.txt');
		if (!file_exists($dir.'/test.txt')) {
			echo '<p>Error: could not create test file <code>'.$dir
				.'/test.txt</code>. Please make sure that <code>'.$dir
				.'</code> is writable by the server.</p>';
		}
		else {
			unlink($dir.'/test.txt');
			$version=12;
		}
	}
}
if ($version==12) { // tmp files directory
	$dir=$userbase . 'f';
	if (!is_dir($dir)) {
		mkdir($dir);
		if (!is_dir($dir)) {
			echo '<p>Error: could not create directory <code>'.$dir
				.'</code>. Please make sure that <code>'.$userbase
				.'</code> is writable by the server.</p>';
		}
	}
	if (is_dir($dir)) {
		$dir=$dir.'/.files';
		if (!is_dir($dir)) {
			mkdir($dir);
			if (!is_dir($dir)) {
				echo '<p>Error: could not create directory <code>'.$dir
					.'</code>. Please make sure that <code>'.$userbase
					.'f/</code> is writable by the server.</p>';
			}
		}
		if (is_dir($dir)) {
			touch($dir.'/test.txt');
			if (!file_exists($dir.'/test.txt')) {
				echo '<p>Error: could not create test file <code>'.$dir
					.'/test.txt</code>. Please make sure that <code>'.$dir
					.'</code> is writable by the server.</p>';
			}
			else {
				unlink($dir.'/test.txt');
				$version=13;
			}
		}
	}
}
if ($version==13) { // set default theme
	$DBVARS['site_title']='Site Title';
	$DBVARS['site_subtitle']='Website\'s Subtitle';
	$version=14;
}
if ($version==14) { // set USERBASE define
	if (!isset($DBVARS['userbase'])) {
		$DBVARS['userbase']=SCRIPTBASE;
		if (!defined('USERBASE')) {
			define('USERBASE', SCRIPTBASE);
		}
	}
	$version=15;
}
if ($version==15) { // page summaries
	dbQuery(
		'create table page_summaries(page_id int default 0,parent_id int default 0,'
		.'rss text,amount_to_show int default 0)default charset=utf8'
	);
	$version=16;
}
if ($version==16) { // skins directory
	if (!isset($DBVARS['theme_dir'])) {
		$DBVARS['theme_dir']=$_SERVER['DOCUMENT_ROOT'].'/ww.skins';
	}
	$version=17;
}
if ($version==17) { // polls
	dbQuery(
		'create table if not exists poll('
		.'id int auto_increment not null primary key,name text,body text,'
		.'enabled smallint default 1)default charset=utf8'
	);
	dbQuery(
		'create table if not exists poll_answer (poll_id int, num int default 0,'
		.'answer text)default charset=utf8'
	);
	dbQuery(
		'create table if not exists poll_vote(poll_id int, num int default 0,'
		.'ip text)'
	);
	$version=18;
}
if ($version==18) { // logs
	dbQuery(
		'create table logs( log_date datetime, log_type enum("page","menu"), '
		.'ip_address char(15),type_data text,user_agent text,referer text,'
		.'ram_used int,bandwidth int,time_to_render float,db_calls int)'
		.'charset=utf8'
	);
	$version=19;
}
if ($version==19) { // log user files and theme files
	dbQuery(
		'alter table logs change log_type log_type '
		.'enum("page","menu","file","design_file")'
	);
	$version=20;
}
if ($version==20) { // change page_type to char string in Pages table
	dbQuery('alter table pages change type type varchar(64)');
	$version=21;
}
if ($version==21) { // add plugins to config if not enabled
	if ($DBVARS['plugins']=='') {
		$DBVARS['plugins']='polls,image_gallery,forms,panels,'
			.'banner-image,mailing-list';
	}
	$version=22;
}
if ($version==22 || $version==23) { // add verification hash to user_accounts
	dbQuery('alter table user_accounts add verification_hash text');
	$version=24;
}
if ($version==24) { // add short_url
	dbQuery(
		'CREATE TABLE `short_urls` ( `id` int(11) NOT NULL AUTO_INCREMENT,'
		.'`cdate` datetime DEFAULT NULL, `long_url` text,'
		.'`short_url` char(32) DEFAULT NULL,'
		.'PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8'
	);
	$version=25;
}
if ($version==25) { // change page_type to char string in Pages table
	dbQuery('alter table pages add associated_date date');
	$version=26;
}
if ($version==26) { // add "extras" to user_account, for metadata
	dbQuery('alter table user_accounts add extras text');
	$version=27;
}
if ($version==27) { // create personal copy of theme if it exists
	if (!file_exists(USERBASE.'/themes-personal')) {
		mkdir(USERBASE.'/themes-personal');
	}
	if (file_exists(THEME_DIR.'/'.THEME)) {
		Copy_recursive(THEME_DIR.'/'.THEME, USERBASE.'/themes-personal/'.THEME);
	}
	$DBVARS['theme_dir_personal']=USERBASE.'/themes-personal';
	$version=28;
}
if ($version==28) { // update user accounts to remember last login and last view
	dbQuery(
		'alter table user_accounts add last_login datetime '
		.'default "0000-00-00 00:00:00"'
	);
	dbQuery(
		'alter table user_accounts add last_view datetime default '
		.'"0000-00-00 00:00:00"'
	);
	$version=29;
}
if ($version==29) { // add original_body to page data
	dbQuery('alter table pages add original_body mediumtext after body');
	dbQuery('update pages set original_body=body');
	$version=30;
}
if ($version==30) { // add metadata to groups table
	dbQuery('alter table groups add meta text');
	dbQuery('update groups set meta="{}"');
	$version=31;
}
if ($version==31) { // add "date_created" to user_account
	dbQuery('alter table user_accounts add date_created datetime');
	$version=32;
}
if ($version==32) { // add "alias" field to pages table
	dbQuery('alter table pages add alias text after associated_date');
	dbQuery('update pages set alias=name');
	$version = 33;
}
if ($version==33) { // clear cache...
	Core_cacheClear('pages');
	$version = 34;
}
if ($version==34) { // add page id to short_url
	dbQuery('alter table short_urls add page_id int default 0');
	$version=35;
}
if ($version==35) { // convert user address to new format
	$users=dbAll('select id,address from user_accounts');
	$query='update user_accounts set address=CASE ';
	for ($i=0;$i<count($users);++$i) {
		$add=(@$users[$i]['address']=='')
			?array()
			:explode('\n', $users[$i]['address']);
		$s=count($add);
		$address=json_encode(
			array(
				'default' => array(
					'street' => @$add[$s-4],
					'street2' => @$add[$s-3],
					'town' => @$add[$s-2],
					'county' => @$add[$s-1],
					'country' => '',
					'default' => 'yes',
				)
			)
		);
		$query.='when id='.$users[$i]['id'].' then "'.addslashes($address).'"';
	}
	$query.=' ELSE address END;';
	dbQuery($query);
	$version=36;
}
if ($version==36) {
	dbQuery('alter table pages change associated_date associated_date datetime');
	$version=37;
}
if ($version==37) { // languages
	dbQuery(
		'create table languages(str text,lang char(5),context varchar(32),'
		.'trstr text) default charset=utf8'
	);
	$version=38;
}
if ($version==38) { // languages_notfound
	dbQuery(
		'create table languages_notfound(str text,lang char(5),context varchar(32),'
		.'requests int default 0) default charset=utf8'
	);
	$version=39;
}
if ($version==39) { // cron
	dbQuery(
		'create table cron('
		.'id int auto_increment not null primary key,'
		.'name text,'
		.'notes text,'
		.'period text,'
		.'period_multiplier int,'
		.'next_date datetime,'
		.'func text'
		.')default charset=utf8'
	);
	dbQuery(
		'insert into cron values(1, "clear cache", "clears temporary files, etc,'
		.' which can eventually fill up your server\'s hard-drive", "day", "1", '
		.'date_add(date(now()), interval 1 day), "Core_cacheClear")'
	);
	$version=40;
}
if ($version==40) {
	dbQuery('alter table pages add date_publish datetime');
	dbQuery('alter table pages add date_unpublish datetime');
	dbQuery(
		'update pages set date_publish=cdate,date_unpublish="2100-01-01" '
		.'where !(special&2)'
	);
	dbQuery(
		'update pages set date_publish=cdate,date_unpublish=now() '
		.'where special&2'
	);
	$version=41;
}
if ($version==41) { // user locations
	dbQuery('alter table user_accounts add location_lat float');
	dbQuery('alter table user_accounts add location_lng float');
	$version=42;
}
if ($version==42) { // language names
	dbQuery(
		'create table language_names(id int auto_increment not null primary key,'
		.'name text, code char(5), is_default smallint default 0,'
		.'complete float default 0) default charset=utf8'
	);
	dbQuery('insert into language_names values(1, "English", "en", 1, 100);');
	$version=43;
}
if ($version==43) { // add link field to page, so name field can be changed
	dbQuery('alter table pages add link text');
	dbQuery('update pages set link=name');
	$version=44;
}
if ($version==44) { // stats archive tables
	dbQuery(
		'create table logs_archive('
		.'cdate date'
		.',unique_visitors int default 0'
		.',page_loads int default 0'
		.',ram_used int default 0'
		.',bandwidth_used int default 0'
		.',render_time int default 0'
		.',db_calls int default 0'
		.')default charset=utf8'
	);
	dbQuery(
		'create table logs_referers('
		.'cdate date'
		.',referer text'
		.',page text'
		.',amt int default 0'
		.')default charset=utf8'
	);
	dbQuery(
		'create table logs_pages('
		.'cdate date'
		.',page text'
		.',amt int default 0'
		.')default charset=utf8'
	);
	$version=45;
}
if ($version==45) { // named locations
	dbQuery(
		'create table locations(id int auto_increment not null primary key,'
		.'name text, lat float, lng float, is_default smallint default 0)'
		.'default charset=utf8'
	);
	$version=46;
}
if ($version==46) { // remove names from addresses. was causing confusion
	$users=dbAll('select id,address from user_accounts');
	foreach ($users as $u) {
		if (!$u['address']) {
			$u['address']='{}';
		}
		$as=json_decode($u['address']);
		$addresses=array();
		foreach ($as as $k=>$v) {
			$addresses[]=$v;
		}
		dbQuery(
			'update user_accounts set address="'
			.addslashes(json_encode($addresses)).'" where id='.$u['id']
		);
	}
	$version=47;
}
if ($version==47) { // localised number formats
	$DBVARS['site_thousands_sep']=',';
	$DBVARS['site_dec_point']='.';
	$version=48;
}
if ($version==48) { // add user avatar
	dbQuery('alter table user_accounts add avatar text');
	$version=49;
}
if ($version==49) { // add emails admin area
	require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/api-admin.php';
	Core_adminMenusAdd(
		'Site Options>Emails',
		'javascript:Core_screen(\'CoreSiteoptions\', \'js:Emails\')'
	);
	$version=50;
}
if ($version==50) { // change user_accounts phone into a contact field
	dbQuery('alter table user_accounts change phone contact text');
	$rs=dbAll('select id,contact from user_accounts');
	foreach ($rs as $r) {
		$c=array(
			'phone'=>$r['contact']
		);
		dbQuery(
			'update user_accounts set contact="'.addslashes(json_encode($c)).'"'
			.' where id='.$r['id']
		);
	}
	$version=51;
}
if ($version==51) { // add table for email templates
	dbQuery(
		'CREATE TABLE `email_templates` ('
		.'`name` text, `body` text'
		.') ENGINE=InnoDB DEFAULT CHARSET=utf8'
	);
	dbQuery(
		'insert into email_templates values'
		.' ("_body","{{include file=\'_header.tpl\'}}'."\n".'{{$email_body}}'."\n"
		.'{{include file=\'_footer.tpl\'}}")'
		.',("_header","<!doctype html>\n<html><head></head><body>")'
		.',("_footer","</body></html>")'
	);
	$version=52;
}
if ($version==52) { // record sent emails
	dbQuery(
		'create table emails_sent( id int auto_increment not null primary key,'
		.'to_email text, body text, headers text, cdate datetime )'
		.'default charset=utf8;'
	);
	$version=53;
}
if ($version==53) { // forgot to add subject
	dbQuery(
		'alter table emails_sent add subject text'
	);
	$version=54;
}
if ($version==54) { // locations can have sublocations
	dbQuery(
		'alter table locations add parent_id int default 0;'
	);
	$version=55;
}
if ($version==55) { // dummy step
	$version=56; // just forcing a Core_cacheClear, after upgrading Smarty
}

$DBVARS['version']=$version;
Core_configRewrite();
Core_cacheClear();

echo '<p>Site upgraded. Please <a href="/">click here</a> to return to the '
	.'site.</p><script>document.location="/";</script>';
