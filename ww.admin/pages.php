<?php
/**
  * Page section loader. loads the pages menu and the page form iframe
  *
  * PHP Version 5
  *
  * @category WebworksWebme
  * @package  None
  * @author   Kae Verens <kae@kvsites.ie>
  * @license  GPL Version 2
  * @link     http://www.kvweb.me/
 */

require_once 'header.php';

echo '<h1>'.__('Pages').'</h1>';
// { left side
echo '<div class="sub-nav">'
	.'<div id="pages-wrapper"></div>'
	.'</div>';
// }
// { right side
echo '<div class="pages_iframe"><div id="reports-wrapper"></div></div>';
// }
// { scripts, etc
WW_addScript('/j/jstree/_lib/jquery.cookie.js');
WW_addScript('/j/jstree/jquery.jstree.js');
WW_addScript('/j/jquery.remoteselectoptions.js');
WW_addScript('/ww.admin/pages/menu2.js');
WW_addInlineScript('window.page_menu_currentpage='.$id.';');
WW_addCSS('/ww.admin/pages/menu.css');
WW_addCSS('/ww.admin/pages/css.css');
// }

require_once 'footer.php';
