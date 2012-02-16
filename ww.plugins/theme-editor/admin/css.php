<?php

if (isset($_REQUEST['action']) && ($_REQUEST['action']=='save')) {
	file_put_contents(
		THEME_DIR.'/'.THEME.'/c/'.$name.'.css',
		$_REQUEST['theme-body']
	);
	$write=file_put_contents(
		THEME_DIR.'/'.THEME.'/c/'.$name.'.css',
		$_REQUEST['theme-body']
	);
	if ($write===false) {
		echo '<em>failed to write to '.THEME_DIR.'/'.THEME.'/c/'.$name
			.'.css. Please check the permissions on the file.</em>';
	}
}
$f=file_get_contents(THEME_DIR.'/'.THEME.'/c/'.$name.'.css');

echo '<form action="/ww.admin/plugin.php" method="post">'
	.'<input type="hidden" name="_plugin" value="theme-editor" />'
	.'<input type="hidden" name="_page" value="index" />'
	.'<input type="hidden" name="name" value="'.$name.'" />'
	.'<input type="hidden" name="type" value="c" />'
	.'<textarea id="theme-body" name="theme-body">'.htmlspecialchars($f)
	.'</textarea>'
	.'<br /><input type="submit" onclick="document.getElementById(\'theme-b'
	.'ody\').value=editor.getCode();" name="action" value="save" /></form>';
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
		height:($(window).height()-$('#main').offset().top-45)+'px',
	  path: "/j/CodeMirror-0.93/js/",
		stylesheet: ["/j/CodeMirror-0.93/css/csscolors.css"],
		lineNumbers:true,
		lineWrapping:true
	});
});
</script>
