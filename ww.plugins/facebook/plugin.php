<?php
/**
	* definition file for FaceBook plugin
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@webworks.ie>
	* @license  GPL 2.0
	* @link     None
	*/

// { define $plugin
$plugin=array(
	'name' => 'FaceBook',
	'description' => 'add various FaceBook widgets to your site',
	'admin' => array(
		'widget' => array(
			'form_url' => '/ww.plugins/facebook/admin/widget.php'
		)
	),
	'frontend' => array(
		'widget' => 'FaceBook_widgetShow'
	)
);
// }

/**
	* returns a HTML string to show the FaceBook widget
	*
	* @param object $vars plugin parameters
	*
	* @return string
	*/
function FaceBook_widgetShow($vars=null) {
	global $PAGEDATA;
	if (!isset($vars->show_faces)) {
		$vars->show_faces='1';
	}
	$show_faces=$vars->show_faces;
	if (!isset($vars->layout)) {
		$vars->layout='standard';
	}
	switch ($vars->layout) {
		case 'standard': // {
			$w=225;
			$h=$show_faces=='1'?80:35;
		break; // }
		case 'button_count': // {
			$w=90;
			$h=20;
		break; // }
		default: // {
			$vars->layout='box_count';
			$w=55;
			$h=65;
			//}
	}
	return '<iframe src="http://www.facebook.com/widgets/like.php?href='
		.urlencode('http://'.$_SERVER['HTTP_HOST'].$PAGEDATA->getRelativeURL())
		.'&layout='.$vars->layout.'&show_faces='.$show_faces
		.'" scrolling="no" frameborder="0"'
		.' style="border:none;width:'.$w.'px;height:'.$h.'px"></iframe>';
}
