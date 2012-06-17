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
		$fname=THEME_DIR.'/'.THEME.'/h/'.$name;
		echo '<em>'.__(
			'Failed to write to %1. Please check the permissions on the file.',
			array($fname),
			'core'
		)
		.'</em>';
	}
}
$f=file_get_contents($tpl);

echo '<form action="/ww.admin/plugin.php" method="post">';
echo '<input type="hidden" name="_plugin" value="theme-editor" />';
echo '<input type="hidden" name="_page" value="index" />';
echo '<input type="hidden" name="name" value="'.$name.'" />';
echo '<input type="hidden" name="type" value="h" />';
echo '<input type="hidden" name="action" value="save"/>';
echo '<textarea id="theme-body" name="theme-body">',htmlspecialchars($f)
	,'</textarea>';
echo '<br /><input type="submit" onclick="document.getElementById(\'theme-b'
	,'ody\').value=editor.getCode();" value="'.__('Save').'" /></form>';
?>
<style>
.CodeMirror-wrapping{
	border: 1px solid #000;
}
</style>
<script type="text/javascript">
$(function(){
	var $textarea=$('#theme-body');
	var editor = CodeMirror
		.fromTextArea($textarea[0], {
			mode: {
				name: "smarty",
				leftDelimiter: "{{",
				rightDelimiter: "}}"
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
