<?php
/**
  * Classified Ads page type
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
	.'<li><a href="#ads-main">Ads</a></li>'
	.'<li><a href="#ads-header">Header</a></li>'
	.'<li><a href="#ads-footer">Footer</a></li>'
	.'<li><a href="#ads-categories">Categories</a></li>'
	.'<li><a href="#ads-types">Types</a></li>'
	.'<li><a href="#ads-payment-details">Payment Details</a></li>'
	.'</ul>';
// { main
$c.='<div id="ads-main">'
	.'<table><thead><tr><th>ID</th><th>Created</th><th>Expiry</th><th>User</th><th>Cost</th>'
	.'<th>Paid</th><th></th></tr></thead>'
	.'<tbody></tbody></table><button id="classifiedads-newad">'.__('New Ad').'</button></div>';
// }
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
// { ad types
$c.='<div id="ads-types"><select id="ads-types-ids">'
	.'<option value="0"> -- '.__('choose').' -- </option>';
$rs=dbAll('select id,name from classifiedads_types order by name');
foreach ($rs as $r) {
	$c.='<option value="'.$r['id'].'">'.htmlspecialchars($r['name']).'</option>';
}
$c.='<option value="-1">'.__('add new').'</option></select>'
	.'<div id="ads-types-wrapper"></div></div>';
// }
// { ad categories
$c.='<div id="ads-categories"></div>';
// }
// { payment details
$c.='<div id="ads-payment-details">'
	.'<p>Paypal address: <input name="page_vars[classified-ads-paypal]" value="'
	.htmlspecialchars(@$vars['classified-ads-paypal']).'" type="email" /></p>'
	.'</div>';
// }
$c.='</div>';
WW_addScript('/ww.plugins/classified-ads/admin/page-type.js');
