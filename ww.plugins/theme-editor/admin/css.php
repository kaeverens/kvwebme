<?php

if(isset($_REQUEST['action']) && ($_REQUEST['action']=='save')){
	file_put_contents(THEME_DIR.'/'.THEME.'/c/'.$name.'.css',$_REQUEST['theme-body']);
	if (file_put_contents(THEME_DIR.'/'.THEME.'/c/'.$name.'.css',$_REQUEST['theme-body'])===false) {
		echo '<em>failed to write to '.THEME_DIR.'/'.THEME.'/c/'.$name.'.css. Please check the permissions on the file.</em>';
	}
}
$f=file_get_contents(THEME_DIR.'/'.THEME.'/c/'.$name.'.css');

echo '<form action="/ww.admin/plugin.php" method="post">';
echo '<input type="hidden" name="_plugin" value="theme-editor" />';
echo '<input type="hidden" name="_page" value="index" />';
echo '<input type="hidden" name="name" value="'.$name.'" />';
echo '<input type="hidden" name="type" value="c" />';
echo '<textarea id="theme-body" name="theme-body">',htmlspecialchars($f),'</textarea>';
echo '<br /><input type="submit" onclick="document.getElementById(\'theme-body\').value=editor.getCode();" name="action" value="save" /></form>';
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
	  parserfile: ["parsecss.js"],
		reindentOnLoad:true,
		height:"450px",
	  path: "/j/CodeMirror-0.93/js/",
		stylesheet: ["/j/CodeMirror-0.93/css/csscolors.css"]
	});
});
</script>
