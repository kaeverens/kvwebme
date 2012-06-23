<?php
/**
	* allows users to upload themes from their local computer
	*
	* PHP version 5.3
	*
	* @category None
	* @package  None
	* @author   Conor Mac Aoidh <conormacaoidh@gmail.com>
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

require_once '../../../ww.incs/basics.php';
require_once SCRIPTBASE . 'ww.admin/admin_libs.php';

echo '
<script type="text/javascript">
$(function( ){

	/**
	 * make sure its a zip file
	 */
	$( "#theme-zip" ).change( function( ){
		var filename = $( this ).val( ).split( "." );
		var extention = filename[ filename.length - 1 ];
		if( extention != "zip" ){
			alert( "'.__('must be a zip file!').'" );
			$( this ).attr( "value", "" );
			return false;
		}
	});

});
</script>

<h2>'.__('Theme Upload').'</h2>
<p>'.__('This uploader can be used to upload themes from your local computer.')
	.'</p>
<form id="themes-upload-form" enctype="multipart/form-data" action="/ww.adm'
.'in/siteoptions/themes/theme-upload.php" method="post" target="upload-ifra'
.'me">
<table id="theme-upload">
	<tr>
		<td><input type="file" name="theme-zip" id="theme-zip"/></td>
		<td><input type="submit" name="upload-theme" value="Upload"/></td>
		<td><input type="submit" name="install-theme" value="'
		.__('Upload & Install').'"/'
		.'></td>
	</tr>
</table>
</form>
<iframe name="upload-iframe" style="display:none" href="javascript:;"></ifr'
.'ame>';
