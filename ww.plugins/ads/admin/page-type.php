<?php
/**
  * Ads page type
  *
  * PHP Version 5
  *
  * @category   None
  * @package    None
  * @subpackage None
  * @author     Kae Verens <kae@kvsites.ie>
  * @license    GPL Version 2
  * @link       www.kvweb.me
 */

$c = '<div class="tabs">'
	.'<ul>'
	.'<li><a href="#ads-header">Header</a></li>'
	.'<li><a href="#ads-footer">Footer</a></li>'
	.'<li><a href="#ads-payment-details">Payment Details</a></li>'
	.'</ul>';
// { header
$c.='<div id="ads-header">'
	.'<p>'.__('This text will appear above the contents.').'</p>';
$c.=ckeditor('body', $page['body']);
$c.='</div>';
// }
// { footer
$c.='<div id="ads-footer">'
	.'<p>'.__('This text will appear below the contents.').'</p>';
$c.=ckeditor(
	'page_vars[footer]',
	(isset($vars['footer'])?$vars['footer']:'')
);
$c.='</div>';
// }
// { payment details
$c.='<div id="ads-payment-details">'
	.'<p>Paypal address: <input name="page_vars[ads-paypal]" value="'
	.htmlspecialchars($vars['ads-paypal']).'" type="email" /></p>'
	.'</div>';
// }
$c.='</div>';
