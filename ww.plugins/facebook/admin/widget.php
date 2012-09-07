<?php
/**
	* widget form for the FaceBook plugin
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     None
	*/

require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!Core_isAdmin()) {
	die('access denied');
}

// { what to show
echo '<strong>'.__('What to show').'</strong><br/>'
	.'<select name="what_to_show">';
$options=array(
	'like'=>'Like box',
	'like-gateway'=>'Like gateway'
);
$what_to_show=isset($_REQUEST['what_to_show'])
	?$_REQUEST['what_to_show']:'like';
foreach ($options as $k=>$v) {
	echo '<option value="'.$k.'"';
	if ($what_to_show==$k) {
		echo ' selected="selected"';
	}
	echo ">$v</option>";
}
echo '</select><br/>';
// }
// { "like" box
echo '<div class="like"';
if ($what_to_show!='like') {
	echo ' style="display:none"';
}
echo '>';
// { layout
$layouts=array(
	'standard'     => 'standard',
	'button_count' => 'button',
	'box_count'    => 'box'
);
$layout=isset($_REQUEST['layout'])?$_REQUEST['layout']:'standard';
echo '<strong>'.__('Layout to use').'</strong><br/>'
	.'<select name="layout">';
foreach ($layouts as $k=>$v) {
	echo '<option value="'.$k.'"';
	if ($layout==$k) {
		echo ' selected="selected"';
	}
	echo ">$v</option>";
}
echo '</select><br/>';
// }
// { show faces
$show_faces=isset($_REQUEST['show_faces'])?$_REQUEST['show_faces']:'1';
echo '<strong>'.__('Show faces').'</strong><br/>'
	.'<select name="show_faces">'
	.'<option value="1">'.__('Yes').'</option>'
	.'<option value="0"';
if ($show_faces!='1') {
	echo ' selected="selected"';
}
echo '">'.__('No').'</option></select>';
// }
echo '</div>';
// }
// { "like-gateway"
echo '<div class="like-gateway"';
if ($what_to_show!='like-gateway') {
	echo ' style="display:none"';
}
echo '>';
echo '<strong>App ID</strong><br/>'
	.'<input name="app_id" value="'.addslashes(@$_REQUEST['app_id']).'"/><br/>'
	.'<strong>App Secret</strong><br/>'
	.'<input name="app_secret" value="'.addslashes(@$_REQUEST['app_secret']).'"/><br/>'
	.'<strong>Message for the "Click To Like" link</strong><br/>'
	.'<textarea name="click_message">'.htmlspecialchars(@$_REQUEST['click_message']).'</textarea>'
	.'<strong>What to post to the user\'s wall when clicked.</strong><br/>'
	.'<textarea name="wall_message">'.htmlspecialchars(@$_REQUEST['wall_message']).'</textarea>'
	.'<strong>Thank you message after the post is sent.</strong><br/>'
	.'<textarea name="thankyou_message">'.htmlspecialchars(@$_REQUEST['thankyou_message']).'</textarea>';
echo '</div>';
// }
