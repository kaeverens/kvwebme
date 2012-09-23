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
		Core_quit(
			__('All fields are required')
			.' <input type="submit" value="'.htmlspecialchars(__('Back'))
			.'" class="back-link"/>'
		);
	}
	$_SESSION['wizard']['name']=$name;
}

echo '<h2>'.__('Payment Details').'</h2><i>'
	.__(
		'Now, some basic details about payment. Everything here is optional,'
		.' if you don\'t know what it is then just leave it blank'
	)
	.'</i><div style="height:300px;overflow:auto"><table>';
// { admin email address
$email=@$_SESSION['userdata']['email'];
echo '<tr><th>'
	.__(
		'What is your email address? When purchases are made, you will be alerted'
		.' at this address.'
	)
	.'</th><td><input type="email" name="wizard-email" value="'
	.htmlspecialchars($email).'"/></td></tr>';
// }
// { Users must log in to purchase
echo '
	<tr>
		<th>'.__('Do customers need to log in before purchasing?').'</th>
		<td><select name="wizard-login">
			<option value="no">'.__('No').'</option>
			<option value="yes">'.__('Yes').'</option>
		</select></td>
	</tr>';
// }
// { payment types checkboxes
$payment_types=array('Paypal','Bank Transfer','Realex');
echo '
	<tr>
		<th>'.__('Which payment methods would you like to use?').'</th>
		<td></td>
	</tr>';

foreach ($payment_types as $type) {
	echo '<tr><td><span style="margin-left:30px">'.$type.'</span></td><td>'
		.'<input type="checkbox" name="wizard-payment-'.$type.'" id="'.$type
		.'" value="1" class="toggle"></td></tr>';
}

// }
// { payment - paypal
echo '<tr id="Paypal-toggle" style="display:none"><td colspan="2"><h2>'
	.__('Paypal').'</h2><i>'.__('Don\'t have a paypal account?')
	.' <a href="https://registration.paypal.com/" target="_blank">'
	.__('Register here').'</a></i><table>'
	.'<tr><td>'.__('Paypal Email Address').'</td><td>'
	.'<input type="email" name="wizard-paypal-email"></td></tr>'
	.'</table></td></tr>';
// }
// { payment - bank transfer
echo '<tr id="Bank-Transfer-toggle" style="display:none"><td colspan="2"><h2>'
	.__('Bank Transfer').'</h2><table>'
	// { bank name
	.'<tr><th>'.__('Bank Name').'</th><td>'
	.'<input type="text" name="wizard-transfer-bank-name"/></td></tr>'
	// }
	// { sort code
	.'<tr><th>'.__('Sort Code').'</th><td>'
	.'<input type="text" name="wizard-transfer-sort-code"/></td></tr>'
	// }
	// { account name
	.'<tr><th>' .__('Account Name').'</th><td>'
	.'<input type="text" name="wizard-transfer-account-name"/></td></tr>'
	// }
	// { account number
	.'<tr><th>'.__('Account Number').'</th><td>'
	.'<input type="text" name="wizard-transfer-account-number"/></td></tr>'
	// }
	// { message to buyer
	.'<tr><th>'.__('Message To Buyer').'</th><td>'
	.ckeditor(
		'wizard-transfer-message-to-buyer',
		'<p>'.__(
			'Thank you for your purchase. Please send {{$total}} to the following'
			.' bank account, quoting the invoice number'
		)
		.' {{$invoice_number}}:</p><table>'
		.'<tr><th>'.__('Bank').'</th><td>{{$bank_name}}</td></tr>'
		.'<tr><th>' .__('Account Name').'</th><td>{{$account_name}}</td></tr>'
		.'<tr><th>'.__('Sort Code').'</th><td>{{$sort_code}}</td></tr>'
		.'<tr><th>'.__('Account Number').'</th><td>{{$account_number}}</td></tr>'
		.'</table>'
	)
	.'</td></tr></table></td></tr>';
// }
// { payment - realex
$hostname=$_SERVER['HTTP_HOST'];
echo '<tr id="Realex-toggle" style="display:none"><td colspan="2">'
	.'<h2>'.__('Realex').'</h2><i>'.__('Don\'t have a realex account?')
	.' <a href="http://www.realexpayments.com/apply-now" target="_blank">'
	.__('Apply here').'</a></i><table>'
	// { Merchant ID
	.'<tr><th>'.__('Merchant ID')
	.'</th><td><input type="text" name="wizard-realex-merchant-id"/></td></tr>'
	// }
	// { shared secret
	.'<tr><th>'.__('Shared Secret').'</th><td>'
	.'<input type="text" name="wizard-realex-shared-secret"/></td></tr>'
	// }
	// { redirect after payment
	.'<tr><th>'.__('Redirect After Payment').'</th><td><select'
	.' name="wizard-realax-redirect-after-payment" id="redirect-after-payment">'
	.'<option value="0" selected="selected">---</option></select></td></tr>'
	// }
	// { mode
	.'<tr><th>'.__('Mode').'</th><td><select name="wizard-realax-mode">'
	.'<option value="test">'.__('Test Mode').'</option>'
	.'<option value="live">Live</option></select>'
	.__(
		'In test mode, you can use the realex payment method by adding'
		.' "?testmode=1" to the URL'
	)
	.'</td></tr>'
	// }
	// { note
	.'<tr><td colspan="2">'
	.__(
		'Note that some manual configuration is necessary. You will need to'
		.' provide RealEx with a template (see their Real Auth Developers Guide'
		.' for an example), and with the following Response ScriptURL: http://%1'
		.'/ww.plugins/online-store/verify/realex.php',
		array($hostname), 'core'
	)
	.'</td></tr>'
	// }
	.'</table></td></tr>'
	.'</table></div><input type="submit" value="Back" class="back-link"/>'
	.'<input type="submit" value="'.htmlspecialchars(__('Next'))
	.'" class="next-link" style="float:right"/>';
