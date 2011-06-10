<?php

/**
 * admin/wizard/step2.php, KV-Webme Online Store Plugin
 *
 * store details
 *
 * @author     Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @license    GPL 2.0
 * @version    1.0
 */

require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
require SCRIPTBASE.'ww.admin/admin_libs.php';

if(isset($_POST['wizard-name'])){ // validate post data
	$name=$_POST['wizard-name'];
	if($name=='')
		die('all fields are required <input type="submit" value="Back" class="back-link"/>');
	$_SESSION['wizard']['name']=$name;
}

echo '
<h2>Payment Details</h2>
<i>Now, some basic details about payment. Everything here is optional, if
you don\'t know what it is then just leave it blank</i>
<table>';

// { admin email address
echo '
	<tr>
		<th>What is your email address? When purchases are made, you will be alerted at this address.</th>
		<td><input type="email" name="wizard-email" value="'.$_SESSION['userdata']['email'].'"/></td>
	</tr>';
// }
// { Users must log in to purchase
echo '
	<tr>
		<th>Should customers log in before purchasing?</th>
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

foreach($payment_types as $type){
	echo '<tr>
		<td><span style="margin-left:30px">'.$type.'</span></td>
		<td>
			<input type="checkbox" name="wizard-payment-'.$type.'"
			id="'.$type.'" value="1" class="toggle">
		</td>
	</tr>';
}

// }
// { payment - paypal
echo '
	<tr id="Paypal-toggle" style="display:none">
		<td colspan="2">
			<h2>Paypal</h2>
			<i>Don\'t have a paypal account? <a href="https://registration.paypal.com/" target="_blank">Register here.</a></i>
			<table>
				<tr>
					<td>Paypal Email Address</td>
					<td><input type="email" name="wizard-paypal-email"></td>
				</tr>
			</table>
		</td>
	</tr>';
// }
// { payment - bank transfer
echo '<tr id="Bank-Transfer-toggle" style="display:none">
	<td colspan="2">
		<h2>Bank Transfer</h2>
		<table>
			<tr>
				<th>Bank Name</th>
				<td><input type="text" name="wizard-transfer-bank-name"/></td>
			</tr>
			<tr>
				<th>Sort Code</th>
				<td><input type="text" name="wizard-transfer-sort-code"/></td>
			</tr>
			<tr>
				<th>Account Name</th>
				<td><input type="text" name="wizard-transfer-account-name"/></td>
			</tr>
			<tr>
				<th>Account Number</th>
				<td><input type="text" name="wizard-transfer-account-number"/></td>
			</tr>
			<tr>
				<th>Message To Buyer</th>
				<td>'.ckeditor('wizard-transfer-message-to-buyer','
<p>Thank you for your purchase. Please send {{$total}} to the following bank account, quoting the invoice number {{$invoice_number}}:</p>
<table>
<tr><th>Bank</th><td>{{$bank_name}}</td></tr>
<tr><th>Account Name</th><td>{{$account_name}}</td></tr>
<tr><th>Sort Code</th><td>{{$sort_code}}</td></tr>
<tr><th>Account Number</th><td>{{$account_number}}</td></tr>
</table>

				').'</td>
			</tr>
		</table>
	</td>
</tr>';
// }
// { payment - realex
echo '<tr id="Realex-toggle" style="display:none">
	<td colspan="2">
		<h2>Realex</h2>
      <i>Don\'t have a realex account? <a href="http://www.realexpayments.com/apply-now" target="_blank">Apply here</a></i>
		<table>
			<tr>
				<th>Merchant ID</th>
				<td><input type="text" name="wizard-realex-merchant-id"/></td>
			</tr>
			<tr>
				<th>Shared Secret</th>
				<td><input type="text" name="wizard-realex-shared-secret"/></td>
			</tr>
			<tr>
				<th>Redirect After Payment</th>
				<td><select name="wizard-relax-redirect-after-payment" id="redirect-after-payment">
					<option value="0" selected="selected">---</option>				
				</select></td>
			</tr>
			<tr>
				<th>Mode</th>
				<td><select name="wizard-relax-mode">
					<option value="test">Test Mode</option>
					<option value="live">Live</option>
				</select>In test mode, you can use the realex payment method by adding "?testmode=1" to the URL.
				</td>
			</tr>
			<tr>
				<td colspan="2">
					Note that some manual configuration is necessary. You will need to provide RealEx with a template (see their Real Auth Developers Guide for an example), and with the following Response ScriptURL:
					http://webme.l/ww.plugins/online-store/verify/realex.php
				</td>
			</tr>
		</table>
	</td>
</tr>';
// }

echo '</table>';

echo '<input type="submit" value="Back" class="back-link"/>'
. '<input type="submit" value="Next" class="next-link" style="float:right"/>';

?>
