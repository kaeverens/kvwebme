<?php
/**
	* admin page for editing a template
	*
	* PHP Version 5.3
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL Version 2
	* @link     http://webme.kvsites.ie/
	**/

$tpl=THEME_DIR.'/'.THEME.'/h/'.$name.'.html';

if (isset($_REQUEST['action']) && ($_REQUEST['action']=='save')) {
	if (file_put_contents($tpl, $_REQUEST['theme-body'])===false) {
		echo '<em>failed to write to '.THEME_DIR.'/'.THEME.'/h/'.$name
			.'.html. Please check the permissions on the file.</em>';
	}
}
$f=file_get_contents($tpl);

echo '<form action="/ww.admin/plugin.php" method="post">';
echo '<input type="hidden" name="_plugin" value="theme-editor" />';
echo '<input type="hidden" name="_page" value="index" />';
echo '<input type="hidden" name="name" value="'.$name.'" />';
echo '<input type="hidden" name="type" value="h" />';
echo '<textarea id="theme-body" name="theme-body">',htmlspecialchars($f)
	,'</textarea>';
echo '<br /><input type="submit" onclick="document.getElementById(\'theme-b'
	,'ody\').value=editor.getCode();" name="action" value="save" /></form>';
WW_addScript('/j/CodeMirror-0.93/js/codemirror.js');
?>
<style>
.CodeMirror-wrapping{
	border: 1px solid #000;
}
</style>
<script type="text/javascript">
$(function(){
	var editor = CodeMirror.fromTextArea("theme-body", {
	  parserfile: ["parsexml.js", "parsecss.js", "tokenizejavascript.js",
			"parsejavascript.js", "parsehtmlmixed.js"],
		reindentOnLoad:true,
		height:450,
	  path: "/j/CodeMirror-0.93/js/",
		stylesheet: [
			"/j/CodeMirror-0.93/css/xmlcolors.css",
			"/j/CodeMirror-0.93/css/jscolors.css",
			"/j/CodeMirror-0.93/css/csscolors.css"
		]
	});
});
</script>
