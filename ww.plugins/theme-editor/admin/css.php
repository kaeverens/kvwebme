<?php

if (isset($_REQUEST['action']) && ($_REQUEST['action']=='save')) {
	$fname=THEME_DIR.'/'.THEME.'/c/'.$name.'.css';
	file_put_contents(
		$fname,
		$_REQUEST['theme-body']
	);
	$write=file_put_contents(
		$fname,
		$_REQUEST['theme-body']
	);
	if ($write===false) {
		echo '<em>'.__(
			'Failed to write to %1. Please check the permissions on the file.',
			array($fname),
			'core'
		)
		.'</em>';
	}
}
$f=file_get_contents(THEME_DIR.'/'.THEME.'/c/'.$name.'.css');

echo '<form action="/ww.admin/plugin.php" method="post">'
	.'<input type="hidden" name="_plugin" value="theme-editor" />'
	.'<input type="hidden" name="_page" value="index" />'
	.'<input type="hidden" name="name" value="'.$name.'" />'
	.'<input type="hidden" name="type" value="c" />'
	.'<input type="hidden" name="action" value="save"/>'
	.'<textarea id="theme-body" name="theme-body">'.htmlspecialchars($f)
	.'</textarea>'
	.'<br /><input type="submit" onclick="document.getElementById(\'theme-b'
	.'ody\').value=editor.getCode();" value="'.__('Save').'" /></form>';
?>
<script type="text/javascript">
$(function(){
	var $textarea=$('#theme-body');
	var editor = CodeMirror
		.fromTextArea($textarea[0], {
			mode: {
				name: "css"
			},
			indentUnit: 1,
			indentWithTabs: true,
			lineWrapping:true,
			lineNumbers:true
		});
	$('.CodeMirror-scroll').css({
		'height':($(window).height()-$('#main').offset().top-45)+'px',
		'border':'1px solid #000'
	});
});
</script>
