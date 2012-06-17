<?php
/**
	* general details for the site
	*
	* PHP version 5.3
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

echo '<h2>'.__('General').'</h2>';
// { handle actions
if ($action=='Save') {
	$DBVARS['f_cache']=$_REQUEST['f_cache'];
	$DBVARS['maintenance-mode']=$_REQUEST['maintenance-mode'];
	$DBVARS['maintenance-mode-message']=$_REQUEST['maintenance-mode-message'];
	if (@$_REQUEST['disable-hidden-sitemap']) {
		$DBVARS['disable-hidden-sitemap']=1;
	}
	else {
		unset($DBVARS['disable-hidden-sitemap']);
	}
	if (@$_REQUEST['disable-jqueryui-css']) {
		$DBVARS['disable-jqueryui-css']=(int)$_REQUEST['disable-jqueryui-css'];
	}
	else {
		unset($DBVARS['disable-jqueryui-css']);
	}
	$DBVARS['site_title']=$_REQUEST['site_title'];
	if (@$_REQUEST['canonical_name']) {
		$DBVARS['canonical_name']=$_REQUEST['canonical_name'];
	}
	else {
		unset($DBVARS['canonical_name']);
	}
	$DBVARS['site_subtitle']=$_REQUEST['site_subtitle'];
	$DBVARS['site_thousands_sep']=$_REQUEST['site_thousands_sep'];
	$DBVARS['site_dec_point']=$_REQUEST['site_dec_point'];
	if (isset($_FILES['site_favicon'])
		&& file_exists($_FILES['site_favicon']['tmp_name'])
	) {
		$tmpname=addslashes($_FILES['site_favicon']['tmp_name']);
		$newdir=USERBASE.'/f/skin_files';
		if (!file_exists(USERBASE.'/f/skin_files')) {
			mkdir(USERBASE.'/f/skin_files');
		}
		$files=glob($newdir.'/favicon-*');
		foreach ($files as $f) {
			unlink($f);
		}
		$from=addslashes($_FILES['site_favicon']['tmp_name']);
		$to=addslashes($newdir.'/favicon.png');
		CoreGraphics::resize($from, $to, 32, 32);
	}
	if (isset($_FILES['site_logo'])
		&& file_exists($_FILES['site_logo']['tmp_name'])
	) {
		$tmpname=addslashes($_FILES['site_logo']['tmp_name']);
		$newdir=USERBASE.'/f/skin_files';
		if (!file_exists($newdir)) {
			mkdir($newdir);
		}
		$files=glob($newdir.'/logo-*');
		foreach ($files as $f) {
			unlink($f);
		}
		CoreGraphics::convert($_FILES['site_logo']['tmp_name'], $newdir.'/logo.png');
	}
	$pageLengthLimit = $_REQUEST['site_page_length_limit'];
	if (!empty($pageLengthLimit)&&is_numeric($pageLengthLimit)) {
		$DBVARS['site_page_length_limit'] = $pageLengthLimit;
	}
	else if (isset($DBVARS['site_page_length_limit'])) {
		unset($DBVARS['site_page_length_limit']);
	}
	Core_configRewrite();
	Core_cacheClear();
	echo '<em>'.__('options updated').'</em>';
}
if ($action=='remove_logo') {
	unlink(USERBASE.'/f/skin_files/logo.png');
}
// }
// { form
echo '<form method="post" action="siteoptions.php?page=general" enctype="mu'
	.'ltipart/form-data"><input type="hidden" name="MAX_FILE_SIZE" value="999'
	.'9999" /><table>';
// { website title and subtitle
echo '<tr><th>'.__('Website Title').'</th><td><input name="site_title" value="'
	.htmlspecialchars($DBVARS['site_title']).'" /></td></tr>'
	.'<tr><th>'.__('Website Subtitle')
	.'</th><td><input name="site_subtitle" value="'
	.htmlspecialchars($DBVARS['site_subtitle']).'" /></td></tr>';
// }
// { canonical domain name
$canonical_name=@$DBVARS['canonical_name']
	?' value="'.htmlspecialchars($DBVARS['canonical_name']).'"'
	:'';
echo '<tr><th>'.__('Canonical Domain Name')
	.'</th><td><input name="canonical_name" '
	.'placeholder="'.__('leave blank to accept multiple domain names').'"'
	.$canonical_name.' /></td></tr>';
// }
// { logo
echo '<tr><th>'.__('Logo').'</th><td><input type="file" name="site_logo" />'
	.'<br />';
if (file_exists(USERBASE.'/f/skin_files/logo.png')) {
	echo '<img src="/f/skin_files/logo.png?rand='.mt_rand(0, 9999).'" /><a hre'
		.'f="/ww.admin/siteoptions.php?action=remove_logo" onclick="return conf'
		.'irm(\''.__('are you sure you want to remove the logo?').'\')" title="'
		.__('remove logo').'">[x]</a>';
}
echo '</td></tr>';
// }
// { favicon
echo '<tr><th>'.__('Favicon').'</th><td>'
	.'<input type="file" name="site_favicon" /><br />';
if (file_exists(USERBASE.'/f/skin_files/favicon.ico')) {
	echo '<img src="/f/skin_files/favicon.ico?rand='.mt_rand(0, 9999)
		.'" /><a href="/ww.admin/siteoptions.php?action=remove_favicon" '
		.'onclick="return confirm(\''
		.__('are you sure you want to remove the favicon?')
		.'\')" title="'.__('remove favicon').'">[x]</a>';
}
echo '</td></tr>';
// }
// { page length limit
echo '<tr><th>'.__('Page Length Limit').'</th>';
echo '<td><input type="text" name="site_page_length_limit"';
if (isset($DBVARS['site_page_length_limit'])) {
	echo ' value="'.$DBVARS['site_page_length_limit'].'"';
}
echo ' /></td></tr>';
// }
// { uploaded files cache
echo '<tr><th>'.__('How long browsers should cache uploaded files for')
	.'</th><td><select name="f_cache"><option value="0">forever</option>';
$arr=array(
	'1 hour'=>1,
	'1 day'=>24,
	'1 week'=>168,
	'1 month'=>672
);
foreach ($arr as $k=>$v) {
	echo '<option value="'.$v.'"';
	if (isset($DBVARS['f_cache']) && $v==$DBVARS['f_cache']) {
		echo ' selected="selected"';
	}
	echo '>'.__($k).'</option>';
}
echo '</select></td></tr>';
// }
// { disable hidden menu sitemap
echo '<tr><th>'.__('Disable the hidden sitemap').' (<a href="/Home?webmespe'
	.'cial=sitemap">'.__('this').'</a>)</th><td>'
	.'<select name="disable-hidden-sitemap"><option value="0">'.__('No')
	.'</option>';
echo '<option value="1"';
if (@$DBVARS['disable-hidden-sitemap']) {
	echo ' selected="selected"';
}
echo '>'.__('Yes').'</option>';
echo '</select></td></tr>';
// }
// { maintenance mode
$script='$(function(){
	$("select[name=\'maintenance-mode\']").change(function(){
		$(".maintenance-message").toggle();
	});
  $( "#add-ip" ).click( function( ){
		var test=prompt("'.__('Please enter the IP Address').'");
		alert(test);
	});

});';
WW_addInlineScript($script);
$display=' style="display:none"';
echo '<tr><th>Enable maintenance mode</th><td>
	<select name="maintenance-mode">
		<option value="No">'.__('No').'</option>
		<option value="yes"';
if (@$DBVARS['maintenance-mode']=='yes') {
	echo ' selected="selected"';
	$display='';
}
if (!isset($DBVARS['maintenance-mode-ips'])
	|| $DBVARS['maintenance-mode-ips']==''
) {
	$DBVARS['maintenance-mode-ips']=$_SERVER['REMOTE_ADDR'];
}
echo '>'.__('Yes').'</option>
</select></td></tr>
<tr class="maintenance-message" '.$display.'>
	<th>'.__('IP Addresses').'</th>
	<td><textarea name="maintenance-mode-ips" style="width:200px;height:50px">'
		.htmlspecialchars(@$DBVARS['maintenance-mode-ips'])
		.'</textarea></td></tr>';
$message=(@$DBVARS['maintenance-mode-message']=='')
	?'<h1>'.__('Temporarily Unavailable').'</h1><p>'
	.__('This website is undergoing maintenance and is temporarily unavailable')
	.'.</p>'
	:$DBVARS['maintenance-mode-message'];
echo '<tr '.$display.' class="maintenance-message">
	<th>'.__('Maintenance mode message').':</th>
	<td>'.ckeditor('maintenance-mode-message', $message).'</td>
</tr>';
// }
// { disable jquery-ui css
$values=array(
	'Load jQuery UI CSS normally', 'Load only in admin area',
	'Load only in front-end', 'Do not load at all'
);
echo '<tr><th>'.__('Don\'t load the jQuery-UI CSS').'</th><td>'
	.'<select name="disable-jqueryui-css">';
foreach ($values as $k=>$v) {
	echo '<option value="'.$k.'"';
	if ($k==(int)@$DBVARS['disable-jqueryui-css']) {
		echo ' selected="selected"';
	}
	echo '>'.$v.'</option>';
}
echo '</select></td></tr>';
// }
// { number format
echo '<tr><th>'.__('Number Format').'</th><td>'
	.'999<input name="site_thousands_sep"'
	.' value="'.htmlspecialchars($DBVARS['site_thousands_sep']).'"'
	.' style="width:10px;height:1em;text-align:center" />'
	.'999<input name="site_dec_point"'
	.' value="'.htmlspecialchars($DBVARS['site_dec_point']).'"'
	.' style="width:10px;height:1em;text-align:center" />'
	.'99</td></tr>';
// }
echo '</table><input type="hidden" name="action" value="Save"/>'
	.'<input type="submit" value="'.__('Save').'" /></form>';
// }
