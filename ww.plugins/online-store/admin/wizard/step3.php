<?php
/**
	* invoice
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

session_start();

if (isset($_POST['wizard-login'])) { // validate post data
	$email=$_POST['wizard-email'];
	if ($email=='') {
		die(
			__('The email is a required field')
			.'. <input type="submit" value="'.htmlspecialchars(__('Back'))
			.'" class="back-link"/>'
		);
	}
	$login=$_POST['wizard-login'];
	$_SESSION['wizard']['payment']['email']=$email;
	$_SESSION['wizard']['payment']['login']=$login;	
	$_SESSION['wizard']['payment']['paypal']=@$_POST['wizard-payment-Paypal'];
	$_SESSION['wizard']['payment']['transfer']
		=@$_POST['wizard-payment-Bank_Transfer'];
	$_SESSION['wizard']['payment']['realex']=@$_POST['wizard-payment-Realex'];

	if ($_SESSION['wizard']['payment']['paypal']==1) { // paypal details
		$_SESSION['wizard']['payment']['paypal-email']
			=$_POST['wizard-paypal-email'];
	}

	if ($_SESSION['wizard']['payment']['transfer']==1) { //bank transfer details
		$_SESSION['wizard']['payment']['transfer-bankname']
			=$_POST['wizard-transfer-bank-name'];
		$_SESSION['wizard']['payment']['transfer-sortcode']
			=$_POST['wizard-transfer-sort-code'];
		$_SESSION['wizard']['payment']['transfer-accountname']
			=$_POST['wizard-transfer-account-name'];
		$_SESSION['wizard']['payment']['transfer-number']
			=$_POST['wizard-transfer-account-number'];
		$_SESSION['wizard']['payment']['transfer-message']
			=$_POST['wizard-transfer-message-to-buyer'];
	}

	if ($_SESSION['wizard']['payment']['realex']==1) { // realex details
		$_SESSION['wizard']['payment']['realex-merchantid']
			=$_POST['wizard-realex-merchant-id'];
		$_SESSION['wizard']['payment']['realex-secret']
			=$_POST['wizard-realex-shared-secret'];
		$_SESSION['wizard']['payment']['realex-redirect']
			=$_POST['wizard-realax-redirect-after-payment'];
		$_SESSION['wizard']['payment']['realex-mode']
			=$_POST['wizard-realax-mode'];
	}
}

require_once '../../../../ww.incs/basics.php';
echo '<h2>'.__('Company Details').'</h2><i>'
	.__(
		'These details are used to populate the invoice sent to customers.'
	 .' Fields left blank will simply not appear on the invoice'
	)
	.'</i><div style="height:300px;overflow:auto"><table>';

// { company name
echo '<tr>
	<th>'.__('Company Name').'</th>
	<td><input type="text" name="wizard-company-name"/></td>
';
// }
// { company telephone
echo '
	<th>'.__('Telephone').'</th>
	<td><input type="text" name="wizard-company-telephone"/></td>
</tr>';
// }
// { company address
echo '<tr>
	<th>'.__('Address').'</th>
	<td rowspan="3"><textarea name="wizard-company-address" style="width:95%">'
	.'</textarea></td>';
// }
// { company fax
echo '
	<th>'.__('Fax').'</th>
	<td><input type="text" name="wizard-company-fax"/></td>
</tr>';
// }
// { company email
echo '<tr>
	<td colspan="2"></td>
	<th>'.__('Email').'</th>
	<td><input type="email" name="wizard-company-email"></td>
</tr>';
// }
// { VAT NO
echo '<tr>
	<td colspan="2"></td>
	<th>'.__('VAT Number').'</th>
	<td><input type="text" name="wizard-company-vat-number"></td>
</tr>';
// }
// { invoice selection
echo '<tr>
	<th colspan="2">'.__('Which invoice format would you like?').'</th>
	<td colspan="2"></td>
</tr>
<tr>
	<td colspan="2">
			<input type="radio" name="wizard-company-invoice" value="1" checked="checked"/>
			'.__('Standard').'
			<button class="preview-invoice k-button" id="1">'.__('Preview').'</button>
	</td>
	<td colspan="2">
	    <input type="radio" name="wizard-company-invoice" value="2"/>
			'.__('Business').'
			<button class="preview-invoice k-button" id="2">'.__('Preview').'</button>
	</td>
</tr>
';
// }

echo '</table></div>'
	.'<input type="submit" value="'.htmlspecialchars(__('Back'))
	.'" class="back-link"/>'
	. '<input type="submit" value="'.htmlspecialchars(__('Next'))
	.'" class="next-link" style="float:right"/>';
