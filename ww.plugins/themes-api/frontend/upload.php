<?php
/**
	* allow logged-in users to upload themes
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Conor Mac Aoidh <conormacaoidh@gmail.com>
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

/**
 * check user is logged in
 */
$user_id = @$_SESSION[ 'userdata' ][ 'id' ];

if ($user_id == 0 ) {
	header('location: /_r?type=loginpage');
}
$homepage = json_decode(@$_SESSION[ 'userdata' ][ 'extras' ], true);
$homepage = @$homepage[ 'Homepage (URL):' ];

/**
 * add scripts to cache
 */
WW_addScript('themes-api/files/swfobject.js');
WW_addScript('themes-api/files/uploadify.jquery.min.js');
WW_addScript('/j/validate.jquery.min.js');
WW_addScript('/j/ckeditor-3.6.2/ckeditor.js');
WW_addScript('/j/ckeditor-3.6.2/adapters/jquery.js');
WW_addCSS('/ww.plugins/themes-api/files/uploadify.css');
WW_addCSS('/ww.css/forms.css');
WW_addCSS('/ww.plugins/themes-api/files/jquery-ui-1.8.12.custom.css');

$script = '
	function displayTags( tags ){
		var html = "";
		for( var i in tags )
			html += "<span id=\'" + tags[ i ] + "\'>" + tags[ i ]
				+ " <a class=\'x\' href=\'javascript:;\'>[x]</a>, </span>";
		$( "#tags-display" ).html( html );
	}

	$( ".x" ).live( "click", function( ){
		var tag = $( this ).parent( ).attr( "id" );
		var tags = $( "#hidden-tags" ).attr( "value" ).split( "," );
		var new_tags = [ ];
		for( var i in tags ){
			if( tags[ i ] != tag )
				new_tags.push( tags[ i ] );
		}
		displayTags( new_tags );
		$( "#hidden-tags" ).val( new_tags.join( "," ) );
	});

	$( "#theme-upload" ).uploadify({
		"uploader"	:	"/ww.plugins/themes-api/files/uploadify.swf",
		"script"	:	"/ww.plugins/themes-api/frontend/theme-upload.php",
		"cancelling"	:	"/ww.plugins/themes-api/files/cancel.png",
		"auto"		:	false,
		"removeCompleted":	false,
		"multi"		:	false,
		"method"	:	"post",
		"fileDataName"	:	"theme-zip",
		"buttonText"	:	"Select Zip FIle",
		"fileDesc"	:	"Compressed Zip Files",
		"fileExt"	:	"*.zip",
		"onComplete"	:	function( event, ID, fileObj, response, data ){
			if( response == "error" )
				alert(
					"There has been an unexpected error, please try uploading the fil"
					+"e again."
				);
			else{
				$( "#return-id" ).attr( "value", response );
				setTimeout( verify_data, 1000 );
			}
		}
	});

	$( function( ){
		var tags = $( "#hidden-tags" ).attr( "value" );
		if( tags != "" )
			displayTags( tags.split( "," ) );
	});

	$( "#add" ).click( function( ){
		var value = $( "#tags" ).val( ).split( "," );
		var tags = $( "#hidden-tags" ).attr( "value" );
		var new_tags = [ ];

		if( tags != "" ){
			tags = tags.split( "," );
			for( var i = 0; i < tags.length; ++i )
					new_tags.push( tags[ i ] );
		}

		for( var i in value ){
			if( value[ i ] != "" )
				new_tags.push( value[ i ] );
		}

		$( "#tags" ).attr( "value", "" );
		displayTags( new_tags );
		$( "#hidden-tags" ).attr( "value", new_tags.join( "," ) );	
	} );

	$( "#delete" ).click( function( ){
		$( "#hidden-tags" ).attr( "value", "" );
		$( "#tags-display" ).html( "" );
	} );

	$( "#accordion" ).accordion({ event : "" });

	$( "#stage-1" ).click( function( ){

		var stage1 = { "theme_version" : { "required" : true } };
		$().validate( stage1, function( msg ){
			$( "#error-msg" ).html( msg );
		});

		var valid = $().validate( "execute" );

		if( valid == false )
			return false;

		$( "#accordion" ).accordion( "activate", 1 );

	} );

	$( "#stage-2" ).click( function( ){
		var version = $( "input[name=\'theme_version\']" ).val( );
		var description = bbcode_editor.getData();
		var tags = $( "input[name=\'theme_tags\']" ).val( );

		$( "#theme-upload" ).uploadifySettings(
			"scriptData",
			{
				"version" : version,
				"description" : description,
				"tags" : tags,
				"author" : "' . $user_id . '",
				"author_url" : "' . $homepage . '",
				"PHPSESSID" : "' . session_id(). '"
			}
		);
		$( "#theme-upload" ).uploadifyUpload( );
	} );

	function verify_data( response ){
		$( "#accordion" ).accordion( "activate", 2 );
		var id = $( "#return-id" ).attr( "value" );
		var hash = Math.floor( Math.random( ) * 1001 ); 
		$.post("/ww.plugins/themes-api/frontend/verify.php?hash=" + hash, "id=" + id,
			function( data ){
				$( "#loading" ).html(
					"<img src=\'/ww.plugins/themes-api/files/loading.gif\'/>"
				);
				switch( data ){
					case "screenshot":
						$( "#theme-error" ).html(
							"The \'./screenshot.png\' file could not be found. All themes are"
							+"required to provide screenshots. Please correct your zip file, "
							+"refresh the page and try again."
						);
					break;
					case "h":
						$( "#theme-error" ).html(
							"The \'./h\' directory could not be found, this directory is a "
							+"requirement in all themes. Please correct your zip file, refr"
							+"esh the page and try again."
						);
					break;
					case "c":
						$( "#theme-error" ).html(
							"The \'./c\' directory could not be found, this directory is a "
							+"requirement in all themes. Please correct your zip file, refr"
							+"esh the page and try again."
						);
					break;
					case "no h":
						$( "#theme-error" ).html(
							"No HTML files were found in the \'./h\' directory. Please corr"
							+"ect your zip file, refresh the page and try again."
						);
					break;
					case "panels":
						$( "#theme-error" ).html(
							"All themes require a header and footer panel and a left or rig"
							+"ht panel"
						);
					break;
					case "base":
						$( "#theme-error" ).html(
							"The zip does not have the correct directory structure. All zip"
							+"s must contain a folder according to the name of the zip file"
							+", which contains the theme files."
						);
					break;
					case "cs":
						$( "#theme-error" ).html(
							"The cs directory is optional, but if it is present then all cs"
							+"s variant files in the directory must have corresponding scre"
							+"enshot files in the png. These screenshot files must have the"
							+" same name as the css files."
						);
					break;
					case "ok":
						$( "#theme-error" ).html( "Theme Verified!" );
						setTimeout(
							function( ){
								$( "#accordion" ).accordion( "activate", 3 );
							},
							1500
						);
					break;
					default:
						$( "#theme-error" ).html( data );
				}
				$( "#loading" ).html( "" );
			}
		);
	}

	/**
	 * if user exits page without finishing, clean db and files
	 */
	$( window ).unload( function( ){
		var id = $( "#return-id" ).attr( "value" );

		if( id != "" ){
			var hash = Math.floor( Math.random( ) * 1001 );
			$.post("/ww.plugins/themes-api/frontend/clean.php?hash="+hash,
				"id=" + id
			);
		}
	});

	var bbcode_editor=CKEDITOR.replace( "theme_description", {
		extraPlugins : "bbcode",
		removePlugins : "bidi,button,dialogadvtab,div,filebrowser,flash,format,"
			+"forms,horizontalrule,iframe,indent,justify,liststyle,pagebreak,"
			+"showborders,stylescombo,table,tabletools,templates",
		toolbar : [
				["Source", "-", "Save","NewPage","-","Undo","Redo"],
				["Find","Replace","-","SelectAll","RemoveFormat"],
				["Link", "Unlink", "Image"],
				"/",
				["FontSize", "Bold", "Italic","Underline"],
				["NumberedList","BulletedList","-","Blockquote"],
				["TextColor", "-", "Smiley","SpecialChar", "-", "Maximize"]
			],
		smiley_images : [
				"regular_smile.gif","sad_smile.gif","wink_smile.gif",
				"teeth_smile.gif","tounge_smile.gif",
				"embaressed_smile.gif","omg_smile.gif",
				"whatchutalkingabout_smile.gif","angel_smile.gif","shades_smile.gif",
				"cry_smile.gif","kiss.gif"
			],
		smiley_descriptions : [
				"smiley", "sad", "wink", "laugh", "cheeky", "blush", "surprise",
				"indecision", "angel", "cool", "crying", "kiss"
			]
	});
';

WW_addInlineScript($script);

$theme_version = ( isset( $_SESSION[ 'theme_upload' ][ 'version' ] ) )
	?$_SESSION[ 'theme_upload' ][ 'version' ]
	:'';
$theme_description = ( isset( $_SESSION[ 'theme_upload' ][ 'description' ] ) )
	?$_SESSION[ 'theme_upload' ][ 'description' ]
	:'';
$theme_tags = ( isset( $_SESSION[ 'theme_upload' ][ 'tags' ] ) )
	?$_SESSION[ 'theme_upload' ][ 'tags' ]
	:'';

$html='<style type="text/css">.error{border:1px solid #600;background:#f99}'
	.'</style><h3>Theme Uploader</h3><div id="accordion"><h3>'
	.'<a href="#">Theme Details</a></h3><div><p id="error-msg" style="color:red">'
	.'</p><input type="hidden" name="theme_tags" id="hidden-tags" value="'
	.$theme_tags.'"/><table><tr><td colspan="2"><i>The theme name will be taken'
	.' from the name of the zip file</i></td></tr><tr><td>Version:</td><td>'
	.'<input type="text" name="theme_version" value="'.$theme_version.'"/></td>'
	.'</tr><tr><td>Description:</td><td>&nbsp;</td></tr><tr><td colspan="2">'
	.'<textarea name="theme_description" id="theme_description" value="'
	.$theme_description.'"></textarea></td></tr><tr><td>Tags:</td><td><input '
	.'type="text" id="tags"/><a href="javascript:;" id="add">Add</a>'
	.'<a href="javascript:;" id="delete">Clear</a></td></tr><tr><td colspan="2">'
	.'<span id="tags-display">&nbsp;</span></td></tr><tr><td>'
	.'<input type="submit" value="Continue" id="stage-1"/></td><td>&nbsp;</td>'
	.'</tr></table></div><h3><a href="#">Upload</a></h3><div><table><tr>'
	.'<td colspan="2"><input id="theme-upload" name="file_upload" type="file" />'
	.'</td></tr><tr><td><input type="submit" value="Continue" id="stage-2"/></td>'
	.'<td id="loading"></td></tr></table></div><h3><a href="#">Verify '
	.'<span id="loading"></span></a></h3><div>'
	.'<input type="hidden" id="return-id" value=""/>'
	.'<h3 id="theme-error" style="color:red"></h3></div><h3>'
	.'<a href="#">Finish</a></h3><div><h3>Upload Complete!</h3>'
	.'<p>Your theme is now awaiting moderation, you will receive an email if '
	.'it is approved.</p></div></div>';
