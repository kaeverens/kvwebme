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

$script='function displayTags(tags){var html="";for(var i in tags)html+="<s'
	.'pan id=\'"+tags[i]+"\'>"+tags[i]+" <a class=\'x\' href=\'javascript:;\''
	.'>[x]</a>, </span>";$("#tags-display").html(html);}$(".x").live("click",'
	.'function(){var tag=$(this).parent().attr("id");var tags=$("#hidden-tags'
	.'").attr("value").split(",");var new_tags=[];for(var i in tags){if(tags['
	.'i]!=tag)new_tags.push(tags[i]);}displayTags(new_tags);$("#hidden-tags")'
	.'.val(new_tags.join(","));});$("#theme-upload").uploadify({"uploader":"/'
	.'ww.plugins/themes-api/files/uploadify.swf","script":"/ww.plugins/themes'
	.'-api/frontend/theme-upload.php","cancelling":"/ww.plugins/themes-api/fi'
	.'les/cancel.png","auto":false,"removeCompleted":false,"multi":false,"met'
	.'hod":"post","fileDataName":"theme-zip","buttonText":"'
	.__('Select Zip File')
	.'","fileDesc":"'
	.__('Compressed Zip Files')
	.'","fileExt":"*.zip","onComplete":function(event,ID,fileObj,response,dat'
	.'a){if(response=="error")alert("'
	.__(
		'There has been an unexpected error,please try uploading the file again.'
	)
	.'");else{$("#return-id").attr("value",response);setTimeout(verify_data,1'
	.'000);}}});$(function(){var tags=$("#hidden-tags").attr("value");if(tags'
	.'!= "")displayTags(tags.split(","));});$("#add").click(function(){var va'
	.'lue=$("#tags").val().split(",");var tags=$("#hidden-tags").attr("value"'
	.');var new_tags=[];if(tags!= ""){tags=tags.split(",");for(var i=0;i<tags'
	.'.length;++i)new_tags.push(tags[i]);}for(var i in value){if(value[i]!= "'
	.'")new_tags.push(value[i]);}$("#tags").attr("value","");displayTags(new_'
	.'tags);$("#hidden-tags").attr("value",new_tags.join(","));});$("#delete"'
	.').click(function(){$("#hidden-tags").attr("value","");$("#tags-display"'
	.').html("");});$("#accordion").accordion({event:""});$("#stage-1").click'
	.'(function(){var stage1={"theme_version":{"required":true}};$().validate'
	.'(stage1,function(msg){$("#error-msg").html(msg);});var valid=$().valida'
	.'te("execute");if(valid==false)return false;$("#accordion").accordion("a'
	.'ctivate",1);});$("#stage-2").click(function(){var version=$("input[name'
	.'=\'theme_version\']").val();var description=bbcode_editor.getData();var'
	.' tags=$("input[name=\'theme_tags\']").val();$("#theme-upload").uploadif'
	.'ySettings("scriptData",{"version":version,"description":description,"ta'
	.'gs":tags,"author":"'.$user_id.'","author_url":"'.$homepage.'","PHPSESSI'
	.'D":"'.session_id().'"});$("#theme-upload").uploadifyUpload();});functio'
	.'n verify_data(response){$("#accordion").accordion("activate",2);var id='
	.'$("#return-id").attr("value");var hash=Math.floor(Math.random()*1001);$'
	.'.post("/ww.plugins/themes-api/frontend/verify.php?hash="+hash,"id="+id,'
	.'function(data){$("#loading").html("<img src=\'/ww.plugins/themes-api/fi'
	.'les/loading.gif\'/>");switch(data){case "screenshot":$("#theme-error").'
	.'html("'
	.addslashes(
		__(
			'The "./screenshot.png" file could not be found. All themes are required'
			.' to provide screenshots. Please correct your zip file,refresh the page'
			.' and try again.'
		)
	)
	.'");break;case "h":$("#theme-error").html("' 
	.addslashes(
		__(
			'The "./h" directory could not be found,this directory is a requirement'
			.' in all themes. Please correct your zip file,refresh the page and try'
			.' again.'
		)
	)
	.'");break;case "c":$("#theme-error").html("' 
	.addslashes(
		__(
			'The "./c" directory could not be found,this directory is a requirement'
			.' in all themes. Please correct your zip file,refresh the page and try'
			.' again.'
		)
	)
	.'");break;case "no h":$("#theme-error").html("'
	.addslashes(
		__(
			'No HTML files were found in the "./h" directory. Please correct your zip'
			.' file,refresh the page and try again.'
		)
	)
	.'");break;case "panels":$("#theme-error").html("'
	.addslashes(
		__(
			'All themes require a header and footer panel and a left or right panel'
		)
	)
	.'");break;case "base":$("#theme-error").html("'
	.addslashes(
		__(
			'The zip does not have the correct directory structure. All zips must'
			.' contain a folder according to the name of the zip file,which contains'
			.' the theme files.'
		)
	)
	.'");break;case "cs":$("#theme-error").html("'
	.addslashes(
		__(
			'The cs directory is optional,but if it is present then all css variant'
			.' files in the directory must have corresponding screenshot files in the'
			.' png. These screenshot files must have the same name as the css files.'
		)
	)
	.'");break;case "ok":$("#theme-error").html("'
	.addslashes(__('Theme Verified!'))
	.'");setTimeout(function(){$("#accordion").accordion("activate",3);},1500'
	.');break;default:$("#theme-error").html(data);}$("#loading").html("");})'
	.';}$(window).unload(function(){var id=$("#return-id").attr("value");if(i'
	.'d!= ""){var hash=Math.floor(Math.random()* 1001);$.post("/ww.plugins/th'
	.'emes-api/frontend/clean.php?hash="+hash,"id=" + id);}});var bbcode_edit'
	.'or=CKEDITOR.replace("theme_description",{extraPlugins:"bbcode",removePl'
	.'ugins:"bidi,button,dialogadvtab,div,filebrowser,flash,format,forms,hori'
	.'zontalrule,iframe,indent,justify,liststyle,pagebreak,showborders,styles'
	.'combo,table,tabletools,templates",toolbar:[["Source","-","Save","NewPag'
	.'e","-","Undo","Redo"],["Find","Replace","-","SelectAll","RemoveFormat"]'
	.',["Link","Unlink","Image"],"/",["FontSize","Bold","Italic","Underline"]'
	.',["NumberedList","BulletedList","-","Blockquote"],["TextColor","-","Smi'
	.'ley","SpecialChar","-","Maximize"]],smiley_images:["regular_smile.gif",'
	.'"sad_smile.gif","wink_smile.gif","teeth_smile.gif","tounge_smile.gif","'
	.'embaressed_smile.gif","omg_smile.gif","whatchutalkingabout_smile.gif","'
	.'angel_smile.gif","shades_smile.gif","cry_smile.gif","kiss.gif"],smiley_'
	.'descriptions:["smiley","sad","wink","laugh","cheeky","blush","surprise"'
	.',"indecision","angel","cool","crying","kiss"]});';

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
	.'</style><h3>'
	.__('Theme Uploader')
	.'</h3><div id="accordion"><h3><a href="#">'
	.__('Theme Details')
	.'</a></h3><div><p id="error-msg" style="color:red"></p>'
	.'<input type="hidden" name="theme_tags" id="hidden-tags" value="'
	.$theme_tags.'"/><table><tr><td colspan="2"><i>'
	.__('The theme name will be taken from the name of the zip file')
	.'</i></td></tr><tr><td>'
	.__('Version')
	.':</td><td><input type="text" name="theme_version" value="'
	.$theme_version.'"/></td></tr><tr><td>'
	.__('Description')
	.':</td><td>&nbsp;</td></tr><tr><td colspan="2">'
	.'<textarea name="theme_description" id="theme_description" value="'
	.$theme_description.'"></textarea></td></tr><tr><td>'
	.__('Tags')
	.':</td><td><input type="text" id="tags"/><a href="javascript:;" id="add">'
	.__('Add')
	.'</a><a href="javascript:;" id="delete">'
	.__('Clear')
	.'</a></td></tr><tr><td colspan="2"><span id="tags-display">&nbsp;</span>'
	.'</td></tr><tr><td><input type="submit" value="'
	.__('Continue')
	.'" id="stage-1"/></td><td>&nbsp;</td>'
	.'</tr></table></div><h3><a href="#">'
	.__('Upload')
	.'</a></h3><div><table><tr>'
	.'<td colspan="2"><input id="theme-upload" name="file_upload" type="file" />'
	.'</td></tr><tr><td><input type="submit" value="'
	.__('Continue')
	.'" id="stage-2"/></td>'
	.'<td id="loading"></td></tr></table></div><h3><a href="#">'
	.__('Verify')
	.' <span id="loading"></span></a></h3><div>'
	.'<input type="hidden" id="return-id" value=""/>'
	.'<h3 id="theme-error" style="color:red"></h3></div><h3><a href="#">'
	.__('Finish')
	.'</a></h3><div><h3>'
	.__('Upload Complete!')
	.'</h3><p>'
	.__(
		'Your theme is now awaiting moderation, you will receive an email if it'
		.' is approved.'
	)
	.'</p></div></div>';
