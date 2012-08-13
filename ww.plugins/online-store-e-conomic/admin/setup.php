<?php

if (isset($_REQUEST['action']) && $_REQUEST['action']=='save') {
	$DBVARS['economic_user_id']=$_REQUEST['user_id'];
	$DBVARS['economic_password']=$_REQUEST['password'];
	$DBVARS['economic_agreement_no']=$_REQUEST['agreement_no'];
	$DBVARS['economic_enabled']=$_REQUEST['enabled'];
	if ($DBVARS['economic_enabled']) {
		$DBVARS['economic_cashbook']=$_REQUEST['cashbook'];
		$DBVARS['economic_debtorgroup']=$_REQUEST['debtorgroup'];
		$DBVARS['economic_productgroup']=$_REQUEST['productgroup'];
	}
	Core_configRewrite();
}

$agreement_no=isset($DBVARS['economic_agreement_no'])?$DBVARS['economic_agreement_no']:'';
$user_id=isset($DBVARS['economic_user_id'])?$DBVARS['economic_user_id']:'';
$password=isset($DBVARS['economic_password'])?$DBVARS['economic_password']:'';
$enabled=isset($DBVARS['economic_enabled'])?(int)$DBVARS['economic_enabled']:0;

echo '<form method="post" action="'.$_url.'" id="e-conomic-setup"><table>'
	.'<tr><th>'.__('Enabled').'</th><td><select name="enabled"><option value="0">'
	.__('No').'</option><option value="1"'
	.($enabled?' selected="selected"':'').'>'.__('Yes')
	.'</option></select></td></tr>'
	.'<tr><th>'.__('Agreement no.').'</th>'
	.'<td><input name="agreement_no" value="'.htmlspecialchars($agreement_no)
	.'"/></td></tr>'
	.'<tr><th>'.__('User ID.').'</th>'
	.'<td><input name="user_id" value="'.htmlspecialchars($user_id)
	.'"/></td></tr>'
	.'<tr><th>'.__('Password').'</th>'
	.'<td><input name="password" type="password" value="'
	.htmlspecialchars($password).'"/></td></tr>';
if ($DBVARS['economic_enabled']) {
	try{
		$OSE=new OnlineStoreEconomics(
			$DBVARS['economic_agreement_no'],
			$DBVARS['economic_user_id'],
			$DBVARS['economic_password']
		);
		// { cashbook
		$books=$OSE->getCashBooks();
		echo '<tr><th>'.__('CashBook to record sales in').'</th><td>'
			.'<select name="cashbook">';
		foreach ($books as $k=>$v) {
			echo '<option value="'.$k.'"';
			if (isset($DBVARS['economic_cashbook']) && $DBVARS['economic_cashbook']==$k) {
				echo ' selected="selected"';
			}
			echo '>'.htmlspecialchars($v->Name).'</option>';
		}
		echo '</select></td></tr>';
		// }
		// { customer group
		$debtorgroups=$OSE->getDebtorGroups();
		echo '<tr><th>'.__('Debtor Group to add new customers to').'</th><td>'
			.'<select name="debtorgroup">';
		foreach ($debtorgroups as $k=>$v) {
			echo '<option value="'.$k.'"';
			if (isset($DBVARS['economic_debtorgroup']) && $DBVARS['economic_debtorgroup']==$k) {
				echo ' selected="selected"';
			}
			echo '>'.htmlspecialchars($v->Name).'</option>';
		}
		echo '</select></td></tr>';
		// }
		// { products group
		$productgroups=$OSE->getProductGroups();
		echo '<tr><th>'.__('Product Group to add new customers to').'</th><td>'
			.'<select name="productgroup">';
		foreach ($productgroups as $k=>$v) {
			echo '<option value="'.$k.'"';
			if (isset($DBVARS['economic_productgroup']) && $DBVARS['economic_productgroup']==$k) {
				echo ' selected="selected"';
			}
			echo '>'.htmlspecialchars($v->Name).'</option>';
		}
		echo '</select></td></tr>';
		// }
		// { login button
		echo '<tr><th></th><td>'
			.'<button id="login-to-external">'.__('Login to external dashboard')
			.'</button></td></tr>';
		// }
	}
	catch(Exception $e) {
		echo '<tr><td></td><td class="error">'.__('Error connection to E-Conomic Server').'</td></tr>';
	}
}
echo '</table>';
echo '<input type="hidden" name="action" value="save"/>'
	.'<button>'.__('Save').'</button></form>';

WW_addScript('online-store-e-conomic/admin/setup.js');
