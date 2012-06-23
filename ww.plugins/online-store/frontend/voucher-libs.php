<?php
/**
	* functions to handle vouchers
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

// { OnlineStore_voucherAmount

/**
  * how much is this voucher worth
  *
  * @param string $code       the voucher code
	* @param string $email      the email address of the user
	* @param float  $grandTotal the value of the basket
  *
  * @return float the value of the voucher
  */
function OnlineStore_voucherAmount($code, $email, $grandTotal) {
	$voucher=OnlineStore_voucherCheckValidity($code, $email);
	if (@$voucher['error']) {
		return 0;
	}
	$value=(float)$voucher['value'];
	if ($value<0) {
		$value=0;
	}
	if ($voucher['value_type']=='percentage') {
		if ($value>100) {
			$value=100;
		}
		return $grandTotal*($value/100);
	}
	return $value>$grandTotal
		?$grandTotal
		:$value;
}

// }
// { OnlineStore_voucherCheckValidity

/**
  * check if a voucher is valid
  *
  * @param string $code  the voucher to check
	* @param string $email the user's email address
  *
  * @return boolean
  */
function OnlineStore_voucherCheckValidity($code, $email) {
	if (isset($GLOBALS['OnlineStore_voucherInstance'])) {
		return $GLOBALS['OnlineStore_voucherInstance'];
	}
	$rs=dbAll(
		'select * from online_store_vouchers where code="'.addslashes($code).'"'
		.' and start_date<now() and end_date>now()'
	);
	$valid=false;
	$error=__('invalid voucher code, or voucher has expired');
	foreach ($rs as $voucher) {
		if ($voucher['user_constraints']=='userlist') {
			$voucher['users_list']=json_decode($voucher['users_list'], true);
			// { if you're not on the guest-list, you're not coming in!
			if (!in_array($_SESSION['userdata']['id'], $voucher['users_list']['users'])
				&& !in_array($email, $voucher['users_list']['emails'])
			) {
				$error=__('your email address is not associated with this voucher');
				continue;
			}
			// }
			// { has the quota of voucher copies been used up?
			if ($voucher['usages_in_total']
				&& isset($voucher['users_list']['total_uses'])
				&& $voucher['users_list']['total_uses'] >= $voucher['usages_in_total']
			) {
				$error=__('this voucher has been used up');
				continue;
			}
			// }
			if ($voucher['usages_per_user']) {
				// { has this email address's quota been used up?
				$usesbyemail=(int)@$voucher['users_list']['uses_by_email'][$email];
				if ($usesbyemail>=$voucher['usages_per_user']) {
					$error=__('you have used your quota of this voucher');
					continue;
				}
				// }
				// { has the user account's quota been used up?
				$uid=(int)@$_SESSION['userdata']['id'];
				$usesbyuser=(int)@$voucher['users_list']['uses_by_user'][$uid];
				if ($uid && $usesbyuser>=$voucher['usages_per_user']) {
					continue;
				}
				// }
			}
		}
		$valid=$voucher;
		break;
	}
	if (!$valid) {
		return array(
			'error'=>$error
		);
	}
	$GLOBALS['OnlineStore_voucherInstance']=$valid;
	return $valid;
}

// }
// { OnlineStore_voucherRecordUsage

/**
  * record that a voucher was used
  *
  * @param int   $order_id the order ID
	* @param float $amount   how much was used
  *
  * @return null
  */
function OnlineStore_voucherRecordUsage($order_id, $amount) {
	global $OnlineStore_voucherInstance;
	// { record total number of uses
	if (!isset($OnlineStore_voucherInstance['users_list'])
		|| !is_array($OnlineStore_voucherInstance['users_list'])
	) {
		$OnlineStore_voucherInstance['users_list']=array();
	}
	$uses=(int)@$OnlineStore_voucherInstance['users_list']['total_uses'];
	$OnlineStore_voucherInstance['users_list']['total_uses']=$uses+1;
	// }
	// { record number of uses by this email address
	$email=$_REQUEST['Email'];
	if (!isset($OnlineStore_voucherInstance['users_list']['uses_by_email'])) {
		$OnlineStore_voucherInstance['users_list']['uses_by_email']=array();
	}
	if (!isset($OnlineStore_voucherInstance['users_list']['uses_by_email'][$email])) {
		$OnlineStore_voucherInstance['users_list']['uses_by_email'][$email]=0;
	}
	$OnlineStore_voucherInstance['users_list']['uses_by_email'][$email]++;
	// }
	if (@$_SESSION['userdata']['id']) { // record number of uses by this user
		$uid=$_SESSION['userdata']['id'];
		if (!isset($OnlineStore_voucherInstance['users_list']['uses_by_user'])) {
			$OnlineStore_voucherInstance['users_list']['uses_by_user']=array();
		}
		if (!isset($OnlineStore_voucherInstance['users_list']['uses_by_user'][$uid])) {
			$OnlineStore_voucherInstance['users_list']['uses_by_user'][$uid]=0;
		}
		$OnlineStore_voucherInstance['users_list']['uses_by_user'][$uid]++;
	}
	dbQuery(
		'update online_store_vouchers set users_list="'
		.addslashes(json_encode($OnlineStore_voucherInstance['users_list']))
		.'" where id='.$OnlineStore_voucherInstance['id']
	);
}

// }
