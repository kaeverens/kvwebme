<?php
require 'header.php';

if(!$_SESSION['userbase_created']){ // user shouldn't be here
  header('Location: /install/step4.php');
  exit;
}

function curl( $url ){
        $ch = curl_init( );
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        $response = curl_exec( $ch );
        curl_close( $ch );
        return $response;
}

require '../ww.incs/basics.php';

if( isset( $_POST[ 'install-theme' ] ) ){ // install theme if selected

	// get id
	$id = ( int ) @$_POST[ 'theme_id' ];
	if( $id == 0 )
	  exit;

	$theme = curl( 'http://kvweb.me/ww.plugins/themes-api/api.php?theme=' . $id );

	if( $theme == false )
		die( 'theme does not exist' );

	$theme = json_decode( $theme, true );

	echo '<h2>Downloading Theme</h2>';

	// downloading
	echo 'Downloading...<br/>';
	$zipfile = curl( $theme[ 'download' ] );
	$theme_dir = USERBASE . 'themes-personal/';
	file_put_contents( $theme_dir . $theme[ 'name' ] . '.zip', $zipfile );

	// extracting
	echo 'Extracting...<br/>';
	shell_exec( 'cd ' . $theme_dir . ' && unzip -o ' .  $theme[ 'name' ] . '.zip' );

	// cleaning
	echo 'Removing Zip File..<br/>';
	shell_exec( 'rm -rf ' . $theme_dir . $theme[ 'name' ] . '.zip' );

	echo 'Theme Download Successful<br/>';

	$DBVARS['theme'] = $theme[ 'name' ];

	$variant = @$_POST[ 'theme_variant' ];
	if( $variant != '' )
		$DBVARS['theme_variant'] = $variant;

	config_rewrite( );
	cache_clear( 'pages' );

	$_SESSION[ 'theme_selected' ] = true;
	header( 'location: step7.php' );

}


echo '
<script type="text/javascript" src="/ww.incs/proxy.php?url=http://kvweb.me/ww.plugins/ratings/ratings.js"></script>
<script type="text/javascript" src="/ww.incs/proxy.php?url=http://kvweb.me/ww.plugins/themes-api/carousel.js"></script>
<script type="text/javascript" src="/install/themes.js"></script>
<link rel="stylesheet" type="text/css" href="/ww.plugins/themes-api/api.css"/>
<script type="text/javascript">
window.installed_themes = [];
</script>

<h1>Select Themes</h1>

<div id="themes-carousel">
Loading...
</div>
<div id="preview-dialog" style="display:none">
<iframe src="javascript:;" id="preview-frame"></iframe>
</div>

';

require 'footer.php';
