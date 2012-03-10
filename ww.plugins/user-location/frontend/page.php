<?php
function User_getLocation_frontendShow() {
	if (!isset($_SESSION['userdata'])) {
		return '<em>You must be logged in to use this page.</em>';
	}
	if ($_SESSION['userdata']['latitude']) {
		$lat=$_SESSION['userdata']['latitude'];
		$lng=$_SESSION['userdata']['longitude'];
	}
	else {
		$lat=54.248103;
		$lng=-6.967491;
	}
	return '<div id="map_canvas" style="width:100%; height:400px"></div>'
		.'<form action="/ww.plugins/user-location/frontend/save.php"'
		.' method="post" id="localjobs4me_getLocation_form">'
		.'<input name="lat" value="'.$lat.'" type="hidden"/>'
		.'<input name="lng" value="'.$lng.'" type="hidden"/>'
		.'<input type="submit" value="Set Location"/></form>'
		.'<script defer="defer" src="http://maps.google.com/maps/api/js?sensor=true"></script>'
		.'<script defer="defer" src="http://code.google.com/apis/gears/gears_init.js"></script>'
		.'<script defer="defer" src="/ww.plugins/user-location/frontend/page.js"></script>';
}
