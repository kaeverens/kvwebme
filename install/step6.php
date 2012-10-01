<?php
/**
	* installer step 6
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

require 'header.php';


/**
  * retrieve a URL's text contents
  *
  * @param string $url URL of the file
  *
  * @return string contents of the file
  */
function curl( $url ) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$response = curl_exec($ch);
	if ($response===false) {
		die('Curl error: '.curl_error($ch));
	}
	curl_close($ch);
	return $response;
}

if (!$_SESSION['userbase_created']) { // user shouldn't be here
  header('Location: /install/step4.php');
  Core_quit();
}

$ignore_cms_plugins=1;
require_once '../ww.incs/basics.php';


//Here starts the actual content of the page
?>
<script>
$(function() {
  $("#tabs").tabs();
});
</script>
<div id="tabs">
	<ul>
		<li><a href="#tabs-1">Download</a></li>
		<li><a href="#tabs-2">Upload</a></li>		
	</ul>
	<div id="tabs-1">

		<?php
		echo '<div id="themes-carousel">'
			.__(
				'Loading.. If you have no internet connection, please <a href="/'
				.'install/step7.php?theme=skipped">click here to proceed.</a>'
			)
			.'</div>';
		?>		
	</div>

	<div id="tabs-2">
		
		<?php
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
					alert( "must be a zip file!" );
					$( this ).attr( "value", "" );
					return false;
				}
			});

		});
		</script>

		<h2>Theme Upload</h2>
		<p>This uploader can be used to upload themes from your local computer.</p>
		<form id="themes-upload-form" enctype="multipart/form-data" action="/instal'
		.'l/theme-upload.php" method="post" target="upload-ifra'
		.'me">
		<table id="theme-upload">
			<tr>
				<td><input type="file" name="theme-zip" id="theme-zip"/></td>
				<td><input type="submit" name="install-theme" value="Upload & Install"/'
				.'></td>
			</tr>
		</table>	
		</form>
		<iframe name="upload-iframe" style="display:none" href="javascript:;"></ifr'
		.'ame>';
		?>
	</div>
	
</div>

<?php
if (isset($_POST[ 'install-theme' ])) { // install theme if selected

	// get id
	$id = ( int ) @$_POST[ 'theme_id' ];
	if ( $id == 0 ) {
	  Core_quit();
	}

	$themeapi=DistConfig::get('themes-api');
	$themeUrl=$themeapi.'/api.php?theme='.$id;
	$theme=curl($themeUrl);

	if ( $theme == false ) {
		die(__('Theme does not exist. %1', array($themeUrl), 'core'));
	}

	$theme = json_decode($theme, true);

	echo '<h2>'.__('Downloading Theme').'</h2>';

	// downloading
	echo __('Downloading...').'<br/>';
	$zipfile = curl($theme[ 'download' ]);
	$theme_dir = USERBASE.'/themes-personal/';
	@mkdir($theme_dir);
	file_put_contents($theme_dir . $theme[ 'name' ] . '.zip', $zipfile);

	// extracting
	echo __('Extracting...').'<br/>';
	shell_exec('cd ' . $theme_dir . ' && unzip -o ' .  $theme[ 'name' ] . '.zip');

	// cleaning
	echo __('Removing Zip File...').'<br/>';
	shell_exec('rm -rf ' . $theme_dir . $theme[ 'name' ] . '.zip');

	echo __('Theme Download Successful').'<br/>';

	$DBVARS['theme'] = $theme[ 'name' ];

	$variant = @$_POST[ 'theme_variant' ];
	if ( $variant != '' ) {
		$DBVARS['theme_variant'] = $variant;
	}
	Core_configRewrite();
	Core_cacheClear('pages');
	$_SESSION[ 'theme_selected' ] = true;
	echo '<script defer="defer">document.location="/install/step7.php";</script>';
	Core_quit();
}

echo
	'<script defer="defer" src="/ww.plugins/ratings/ratings.js"></script>'
	.'<script defer="defer" src="/j/jquery.tooltip.min.js"></script>'
	.'<script defer="defer" src="/ww.plugins/themes-api/carousel.js"></script>'
	.'<script defer="defer" src="/install/themes.js"></script>'
	.'<link rel="stylesheet" type="text/css" href="/ww.plugins/themes-api'
	.'/api.css"/>'
	.'<script defer="defer">window.installed_themes=[];</script>'
	.'<h1>'.__('Select Themes').'</h1>'	
	.'<div id="preview-dialog" style="display:none">'
	.'<iframe src="javascript:;" id="preview-frame"></iframe>'
	.'</div>';
require 'footer.php';
