<?php

/**
 * ww.admin/siteoptions/themes/upload.php, KV-Webme
 *
 * allows users to upload themes from their local
 * computer
 *
 * @author     Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @license    GPL 2.0
 * @version    1.0
 */

require '../../../ww.incs/basics.php';
require SCRIPTBASE . 'ww.admin/admin_libs.php';

echo '
<script type="text/javascript">
$( document ).ready( function( ){

	/**
	 * make sure its a zip file
	 */
	$( "#theme-zip" ).change( function( ){
		var filename = $( this ).val( ).split( "." );
		var extention = filename[ filename.length - 1 ];
		if( extention != "zip" ){
			alert( "must be a zip file!" );
			$( this ).attr( "value", "" );
			return false;
		}
	});

});
</script>

<h2>Theme Upload</h2>
<p><i>This uploader can be used to upload themes from your local computer.</i></p>
<form enctype="multipart/form-data" action="/ww.admin/siteoptions/themes/theme-upload.php" method="post">
<table id="theme-upload">
	<tr>
		<td><input type="file" name="theme-zip" id="theme-zip"/></td>
		<td><input type="submit" name="upload-theme" value="Upload"/></td>
		<td><input type="submit" name="install-theme" value="Upload & Install"/></td>
	</tr>
</table>
</form>
';


?>
