<?php
/**
	* vouchers
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     None
	*/

if (isset($_REQUEST['voucher_id'])) {
	echo '<a href="'.$_url.'">'.__('List all vouchers').'</a>'
		.'<h3>'.__('Edit Voucher').'</h3>';
	$v_id=(int)$_REQUEST['voucher_id'];
	if (isset($_REQUEST['action']) && $_REQUEST['action']=='Save') {
		$r=dbRow("select * from online_store_vouchers where id=$v_id");
		$name=$_REQUEST['name'];
		if (!$name) {
			$name=__('No name supplied');
		}
		$users_list=json_decode($r['users_list'], true);
		if ($_REQUEST['user_constraints']=='userlist') {
			$users_list['emails']=array();
			foreach (explode("\n", $_REQUEST['user_emails']) as $email) {
				$email=trim($email);
				if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
					continue;
				}
				$users_list['emails'][]=$email;
			}
			$users_list['users']=array();
			foreach ($_REQUEST['user_ids'] as $uid=>$checked) {
				$users_list['users'][]=$uid;
			}
		}
		$sql='name="'.addslashes($name).'",'
			.'code="'.addslashes($_REQUEST['code']).'",'
			.'user_constraints="'.addslashes($_REQUEST['user_constraints']).'",'
			.'users_list="'.addslashes(json_encode($users_list)).'",'
			.'value="'.addslashes($_REQUEST['value']).'",'
			.'value_type="'.addslashes($_REQUEST['value_type']).'",'
			.'usages_per_user="'.addslashes($_REQUEST['usages_per_user']).'",'
			.'usages_in_total="'.addslashes($_REQUEST['usages_in_total']).'",'
			.'start_date="'.addslashes($_REQUEST['start_date']).'",'
			.'end_date="'.addslashes($_REQUEST['end_date']).'"';
		if (!$r) {
			dbQuery('insert into online_store_vouchers set '.$sql);
			$v_id=dbLastInsertId();
		}
		else {
			dbQuery('update online_store_vouchers set '.$sql.' where id='.$v_id);
		}
	}
	$r=dbRow("select * from online_store_vouchers where id=$v_id");
	if (!$r) {
		$r=array(
			'name'=>'',
			'code'=>md5(microtime()),
			'user_constraints'=>'public',
			'users_list'=>'{}',
			'value'=>'5',
			'value_type'=>'percentage',
			'usages_per_user'=>1,
			'usages_in_total'=>0,
			'start_date'=>date('Y-m-d'),
			'end_date'=>(date('Y')+1).date('-m-d')
		);
	}
	echo '<form method="post" action="'.$_url.'&amp;voucher_id='.$v_id.'">'
		.'<table id="onlinestore-vouchers-table">'
	// { name
		.'<tr><th>'.__('Name').'</th><td><input name="name" value="'
		.htmlspecialchars($r['name']).'"/></td></tr>'
	// }
	// { code
		.'<tr><th>'.__('Code').'</th><td><input name="code" value="'
		.htmlspecialchars($r['code']).'"/></td></tr>'
	// }
	// { user constraints
		.'<tr><th>'.__('Usable by').'</th><td><select name="user_constraints">';
	$user_constraints=array(
		/* TODO - translation /CB */
		'public'   => 'Anyone can use this voucher',
		'userlist' => 'Only people on the following list'
	);
	foreach ($user_constraints as $k=>$v) {
		echo '<option value="'.$k.'"';
		if ($k==$r['user_constraints']) {
			echo ' selected="selected"';
		}
		echo '>'.htmlspecialchars($v).'</option>';
	}
	echo '</select></td></tr>';
	// }
	// { users list
	echo '<tr id="onlinestore-vouchers-users-list"><td colspan="2" userslist="'
		.htmlspecialchars($r['users_list']).'"></td></tr>';
	// }
	// { value, value type
	echo '<tr><th>'.__('Value').'</th><td><input name="value" value="'
		.htmlspecialchars($r['value']).'"/> <select name="value_type">';
	$value_types=array(
		'percentage'=>'%',
		/* TODO - translation /CB */
		'value' =>'Cash in the checkout\'s currency'
	);
	foreach ($value_types as $k=>$v) {
		echo '<option value="'.$k.'"';
		if ($k==$r['value_type']) {
			echo ' selected="selected"';
		}
		echo '>'.htmlspecialchars($v).'</option>';
	}
	echo '</select></td></tr>';
	// }
	// { usages per person
	echo '<tr><th>'.__('Uses allowed per person').'</th><td>'
		.'<input name="usages_per_user" value="'
		.htmlspecialchars($r['usages_per_user']).'"/>'
		.__('(leave at 0 for no limit)').'</td></tr>';
	// }
	// { usages in total
	echo '<tr><th>'.__('Uses allowed in total').'</th><td>'
		.'<input name="usages_in_total" value="'
		.htmlspecialchars($r['usages_in_total']).'"/>'
		.__('(leave at 0 for no limit)').'</td></tr>';
	// }
	// { date range
	echo '<tr><th>'.__('Valid Dates').'</th><td>'
		.__('Valid from the morning of')
		.' <input class="date-human" name="start_date" value="'
		.$r['start_date'].'"/>'
		.__('Expiring the morning of').' <input class="date-human" '
		.'name="end_date" value="'.$r['end_date'].'"/></td></tr>';
	// }
	echo '<tr><th colspan="2"><input type="submit" name="action" value="'
		.htmlspecialchars(__('Save')).'"/></th></tr>';
	echo '</table></form>';
}
else {
	echo '<div style="width:400px;">'
		.'<table id="onlinestore-vouchers" style="width:100%">'
		.'<thead><tr><th>'.__('Name').'</th><th>'.__('Value').'</th>'
		.'<th>'.__('Expiry Date').'</th></tr></thead>'
		.'<tbody></tbody></table>'
		.'<a href="'.$_url.'&amp;voucher_id=0">'.__('Create a voucher')
		.'</a></div>';
}
WW_addScript('online-store/admin/vouchers.js');
