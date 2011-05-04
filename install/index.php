<?php
require 'header.php';

$phpversion = phpversion( );
$ver = split( "[/ ]", $_SERVER[ 'SERVER_SOFTWARE' ] );
$apacheversion = $ver[ 1 ] . ' ' . $ver[ 2 ];

$access=(is_writable($home_dir)) ? 'Granted':'<span style="color:#D36042">Not Granted <a href="#" id="howto">(?)</a></span>';
$php=($phpversion<<5) ? $phpversion : '<span style="color:#D36042">'.$phpversion.'</span>';
$apache=($ver[1]<<2) ? $apacheversion :'<span style="color:#D36042">'.$apacheversion.'</span>';

if(function_exists('apache_get_modules')){
        $modules=apache_get_modules();

        $mods=(in_array('mod_rewrite',$modules))?'Installed':'<span id="notgranted" style="color:#D36042">Not Installed</span>';
}
else
        $mods='Unknown';


echo '
        <div id="dialog" style="display:none" title="Help - Write Access">
                <p>The quickest way to get this working is to execute the follwing command, but it is also the <strong>least secure</strong> method:</p>
                <i>chmod -R 777 ' . $home_dir . '</i>
        </div>

<h3>Installation Requirements</h3>
<p><i style="clear:none">The requirements below are the minimum specifications needed to run the system reliably. You may install without meeting all of these requirements, but some aspects of the system may not function properly.</i></p>
<table class="row-color">
        <tr>
		<th>Software</th>
		<th>Required</th>
		<th>Current</th>
	</tr>
        <tr>
		<td>PHP Version:</td>
		<td>5</td>
		<th>'.$php.'</th>
	</tr>
        <tr>
		<td>Apache Version:</td>
		<td>2</td>
		<th>'.$apache.'</th>
	</tr>
        <tr>
		<td>Apache Rewrite Module:</td>
		<td>&nbsp;</td>
		<th>'.$mods.'</th>
	</tr>
        <tr>
		<td colspan="3">Write Access:</td>
	</tr>
        <tr>
		<td colspan="2">'.$home_dir.'</td>
		<th>'.$access.'</th>
	</tr>
</table>
<br>
<h2><a href="step1.php">Continue</a></h2>
<br style="clear:both"/>
';

require 'footer.php';
