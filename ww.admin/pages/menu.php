<?php
/**
	* show the left menu for the pages admin
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

WW_addScript('/j/jstree/_lib/jquery.cookie.js');
WW_addScript('/j/jstree/jquery.jstree.js');
WW_addScript('/j/jquery.remoteselectoptions.js');
WW_addScript('/ww.admin/pages/menu2.js');
WW_addCSS('/ww.admin/pages/menu.css');
echo '<div id="pages-wrapper"></div>';
