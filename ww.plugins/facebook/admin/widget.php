<?php
/**
	* widget form for the FaceBook plugin
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@webworks.ie>
	* @license  GPL 2.0
	* @link     None
	*/

require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!is_admin()) {
	die('access denied');
}

// { layout
$layouts=array(
	'standard'     => 'standard',
	'button_count' => 'button',
	'box_count'    => 'box'
);
$layout=isset($_REQUEST['layout'])?$_REQUEST['layout']:'standard';
echo '<strong>layout to use</strong><select name="layout">';
foreach ($layouts as $k=>$v) {
	echo '<option value="'.$k.'"';
	if ($layout==$k) {
		echo ' selected="selected"';
	}
	echo ">$v</option>";
}
echo '</select>';
// }
// { show faces
$show_faces=isset($_REQUEST['show_faces'])?$_REQUEST['show_faces']:'1';
echo '<strong>show faces</strong><select name="show_faces">'
	.'<option value="1">Yes</option>'
	.'<option value="0"';
if ($show_faces!='1') {
	echo ' selected="selected"';
}
echo '">No</option></select>';
// }
