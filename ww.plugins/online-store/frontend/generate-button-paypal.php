<?php
/**
	* generate a PayPal button
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     None
	*/

global $DBVARS;
$total=sprintf("%.2f", $total);
// { redirect URL for successful purchases
if (!$return) {
	$rp=Page::getInstanceByType('privacy');
	if ($rp) {
		$return=$rp->getAbsoluteUrl().'?onlinestore_iid='.$id;
	}
	else {
		$return='http://'.$_SERVER['HTTP_HOST'].'/';
	}
}
// }
$html='<form id="online-store-paypal" method="post" action="https://www.pay'
	.'pal.com/cgi-bin/webscr"><input type="hidden" value="_xclick" name="cmd"'
	.'/><input type="hidden" value="'
	.$PAGEDATA->vars['online_stores_paypal_address'].'" name="business"/>'
	.'<input type="hidden" value="Purchase made from '.$_SERVER['HTTP_HOST']
	.'" name="item_name"/>'
	.'<input type="hidden" value="'.$id.'" name="item_number"/>'
	.'<input type="hidden" value="'.$total.'" name="amount"/>'
	.'<input type="hidden" value="'.$DBVARS['online_store_currency']
	.'" name="currency_code"/><input type="hidden" value="1" name="no_shipping"/>'
	.'<input type="hidden" value="1" name="no_note"/>'
	.'<input type="hidden" name="return" value="'.htmlspecialchars($return).'" />'
	.'<input type="hidden" value="http://'.$_SERVER['HTTP_HOST']
	.'/ww.plugins/online-store/verify/paypal.php" name="notify_url"/>'
	.'<input type="hidden" value="IC_Sample" name="bn"/><input type="image" a'
	.'lt="Make payments with payPal - it\'s fast, free and secure!" name="sub'
	.'mit" src="https://www.paypal.com/en_US/i/btn/x-click-but23.gif" style="'
	.'width:68px;height:23px;"/><img w'
	.'idth="1" height="1" src="https://www.paypal.com/en_US/i/scr/pixel.gif" '
	.'alt=""/></form>';
