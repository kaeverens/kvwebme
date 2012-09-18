<?php
/**
	* shopping basket widget admin
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!Core_isAdmin()) {
	die(__('access denied'));
}

// { slide-down
/* TODO - translation /CB */
echo '<strong>'.__('Slide-down cart').'</strong><br/>'
	.'<input type="checkbox" name="slidedown"';
if (@$_REQUEST['slidedown']) {
	echo ' checked="checked"';
}
echo '/><br/>';
echo '<div id="online-store-slide"';
if (!@$_REQUEST['slidedown']) {
	echo ' style="display:none"';
}
echo '>';
/* TODO - translation /CB */
echo '<strong>'.__('Slide animation').'</strong><br />'
	.'<select name="slidedown_animation">';
/* TODO - translation /CB */
$arr=array(
	'blind', 'bounce', 'clip', 'drop', 'explode', 'fold', 'highlight',
	'puff', 'pulsat', 'scale', 'shake', 'size', 'slide'
);
foreach ($arr as $v) {
	echo '<option';
	if ($v==@$_REQUEST['slidedown_animation']) {
		echo ' selected="selected"';
	}
	echo '>'.$v.'</option>';
}
echo '</select><br />';
/* TODO - translation /CB */
echo '<strong>'.__('Slide up auto-slider (in seconds)').'</strong>';
$s=(float)@$_REQUEST['slideup_delay'];
echo '<input name="slideup_delay" value="'.$s.'"/><br />';
echo '</div>';
// }
/* TODO - translation /CB */
// { template
echo '<strong>'.__('Template (leave blank to use a default one)').'</strong><br />'
	.'<textarea class="small" name="template">'
	.htmlspecialchars(@$_REQUEST['template']).'</textarea>'
	/* TODO - translation /CB */
	.'<a href="#" class="docs" page="/ww.plugins/online-store/docs/codes.html">'.__('Codes').'</a>';
// }
