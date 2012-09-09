<?php
/**
	* SMS dashboard
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

if (!Core_isAdmin()) {
	Core_quit();
}
require SCRIPTBASE.'ww.plugins/sms/admin/libs.php';

if (!isset($DBVARS['sms_email']) || !$DBVARS['sms_email']
	|| !$DBVARS['sms_password']
) {
	echo '<em>You have not set up your textr.mobi account yet. Please '
		.'<a href="/ww.admin/plugin.php?_plugin=sms&amp;_page=setup&amp;'
		.'account=new">click here</a> to do so.</em>';
	return;
}

echo '<table style="width:100%">'
	.'<tr><th width="20%">Credits</th>'
	.'<td width="20%">'.SMS_getCreditBalance().'</td>'
	.'<th rowspan="2" width="20%">Purchase credits</th><td rowspan="2">'
	.'<select id="sms_purchase_amt"><option value="0">--</option>'
	.'<option>200</option></select></td>'
	.'<td id="sms_paypal_button_holder" rowspan="2" width="20%"></td></tr>'
	.'<tr><th>Price per credit</th><td>&euro;'
	.sprintf('%.2f', SMS_getCreditPrice()).'</td></tr>'
	.'</table><script src="/ww.plugins/sms/admin/dashboard.js"></script>';
