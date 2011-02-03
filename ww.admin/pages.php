<?php
/**
  * Page section loader. loads the pages menu and the page form iframe
  *
  * PHP Version 5
  *
  * @category WebworksWebme
  * @package  None
  * @author   Kae Verens <kae@webworks.ie>
  * @license  GPL Version 2
  * @link     http://www.webworks.ie/
 */

$dir=dirname(__FILE__);
require $dir.'/header.php';
if (isset($_REQUEST['id'])) {
	$id=(int)$_REQUEST['id'];
}
else {
	$id=0;
}
echo '<h1>Pages</h1>';
echo '<div class="left-menu">';
require_once 'pages/menu.php';
echo '</div>';
echo '<div class="has-left-menu">'
	,'<iframe id="page-form-wrapper" name="page-form-wrapper" '
	,'src="/ww.admin/pages/form.php?id=',$id,'"></iframe>'
	,'</div>'
	,'<script>window.page_menu_currentpage=',$id,';</script>'
	,'<style type="text/css">@import "pages/css.css";</style>';
require 'footer.php';
