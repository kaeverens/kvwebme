<?php
/**
	* store details
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Conor Mac Aoidh <conormacaoidh@gmail.com>
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
require_once SCRIPTBASE.'ww.admin/admin_libs.php';

if (isset($_POST['wizard-name'])) { // validate post data
	$name=$_POST['wizard-name'];
	if ($name=='') {
		die(
			'all fields are required <input type="submit" value="Back" class="bac'
			.'k-link"/>'
		);
	}
	$_SESSION['wizard']['name']=$name;
}

echo '<h2>Payment Details</h2><i>Now, some basic details about payment. Eve'
	.'rything here is optional, if you don\'t know what it is then just leave'
	.' it blank</i><div style="height:300px;overflow:auto"><table>';

// { admin email address
$email=@$_SESSION['userdata']['email'];
echo '<tr><th>What is your email address? When purchases are made, you will'
	.' be alerted at this address.</th><td><input type="email" name="wizard-e'
	.'mail" value="'.htmlspecialchars($email).'"/></td></tr>';
// }
// { Users must log in to purchase
echo '
	<tr>
		<th>Do customers need to log in before purchasing?</th>
		<td><select name="wizard-login">
			<option value="no">No</option>
			<option value="yes">Yes</option>
		</select></td>
	</tr>';
// }
// { payment types checkboxes
$payment_types=array('Paypal','Bank Transfer','Realex');
echo '
	<tr>
		<th>Which payment methods would you like to use?</th>
		<td></td>
	</tr>';

foreach ($payment_types as $type) {
	echo '<tr><td><span style="margin-left:30px">'.$type.'</span></td><td>'
		.'<input type="checkbox" name="wizard-payment-'.$type.'" id="'.$type
		.'" value="1" class="toggle"></td></tr>';
}

// }
// { payment - paypal
echo '<tr id="Paypal-toggle" style="display:none"><td colspan="2">'
	.'<h2>Paypal</h2><i>Don\'t have a paypal account? <a href="https://regist'
	.'ration.paypal.com/" target="_blank">Register here.</a></i><table><tr>'
	.'<td>Paypal Email Address</td><td><input type="email" name="wizard-paypa'
	.'l-email"></td></tr></table></td></tr>';
// }
// { payment - bank transfer
echo '<tr id="Bank-Transfer-toggle" style="display:none"><td colspan="2"><h'
	.'2>Bank Transfer</h2><table><tr><th>Bank Name</th><td><input type="text"'
	.' name="wizard-transfer-bank-name"/></td></tr><tr><th>Sort Code</th><td>'
	.'<input type="text" name="wizard-transfer-sort-code"/></td></tr><tr><th>'
	.'Account Name</th><td><input type="text" name="wizard-transfer-account-n'
	.'ame"/></td></tr><tr><th>Account Number</th><td><input type="text" name='
	.'"wizard-transfer-account-number"/></td></tr><tr><th>Message To Buyer</t'
	.'h><td>'
	.ckeditor(
		'wizard-transfer-message-to-buyer',
		'<p>Thank you for your purchase. Please send {{$total}} to the followin'
		.'g bank account, quoting the invoice number {{$invoice_number}}:</p><t'
		.'able><tr><th>Bank</th><td>{{$bank_name}}</td></tr><tr><th>Account Nam'
		.'e</th><td>{{$account_name}}</td></tr><tr><th>Sort Code</th><td>{{$sor'
		.'t_code}}</td></tr><tr><th>Account Number</th><td>{{$account_number}}<'
		.'/td></tr></table>'
	)
	.'</td></tr></table></td></tr>';
// }
// { payment - realex
echo '<tr id="Realex-toggle" style="display:none"><td colspan="2">'
	.'<h2>Realex</h2><i>Don\'t have a realex account? <a href="http://www.rea'
	.'lexpayments.com/apply-now" target="_blank">Apply here</a></i><table>'
	.'<tr><th>Merchant ID</th><td><input type="text" name="wizard-realex-merc'
	.'hant-id"/></td></tr><tr><th>Shared Secret</th><td>'
	.'<input type="text" name="wizard-realex-shared-secret"/></td></tr><tr>'
	.'<th>Redirect After Payment</th><td><select name="wizard-relax-redirect-'
	.'after-payment" id="redirect-after-payment"><option value="0" selected="'
	.'selected">---</option></select></td></tr><tr><th>Mode</th><td>'
	.'<select name="wizard-relax-mode"><option value="test">Test Mode</option>'
	.'<option value="live">Live</option></select>In test mode, you can use th'
	.'e realex payment method by adding "?testmode=1" to the URL.  </td></tr>'
	.'<tr><td colspan="2"> Note that some manual configuration is necessary. '
	.'You will need to provide RealEx with a template (see their Real Auth De'
	.'velopers Guide for an example), and with the following Response ScriptU'
	.'RL: http://'.$_SERVER['SERVER_NAME'].'/ww.plugins/online-store/verify/r'
	.'ealex.php </td></tr></table></td></tr></table></div><input type="submit'
	.'" value="Back" class="back-link"/><input type="submit" value="Next" cla'
	.'ss="next-link" style="float:right"/>';
