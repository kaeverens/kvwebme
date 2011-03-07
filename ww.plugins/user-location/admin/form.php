<?php
/**
  * admin page for setting a user's location
  *
  * PHP Version 5
  *
  * @category   
  * @package    Webme
  * @subpackage 
  * @author     Kae Verens <kae@kvsites.ie>
  * @license    GPL Version 2
  * @link       www.kvsites.ie
 */

// { tabs nav
$c.= '<div class="tabs">'
	.'<ul>'
	.'<li><a href="#f-header">Header</a></li>'
	.'<li><a href="#footer">Footer</a></li>'
	.'<li><a href="#main">Main Details</a></li>'
	.'</ul>';
// }
// { header
$c.='<div id="f-header"><p>Text to be shown above the form</p>'
	.ckeditor('body', $page['body'])
	.'</div>';
// }
// { footer
$c.='<div id="footer"><p>Text to appear below the form.</p>';
$c.=ckeditor('page_vars[footer]',(isset($vars['footer'])?$vars['footer']:''));
$c.='</div>';
// }
// { main details
$c.= '<div id="main">';
$c.= '</div>';
// }
$c.= '</div>';
