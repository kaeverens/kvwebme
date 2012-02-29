<?php
/**
	* User management - edit
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

echo '<h3>Edit User</h3>';
$groups=array();
$id=(int)$_REQUEST['id'];
// { handle actions
if (isset($_REQUEST['action'])) {
	if ($action=='delete') {
		dbQuery("delete from user_accounts where id=$id");
		dbQuery("delete from users_groups where user_accounts_id=$id");
		unset($_REQUEST['id']);
		redirect('/ww.admin/siteoptions.php?page=users');
	}
	if ($action=='Save') {
		$addresses=array();
		// { address
		if (!isset($_POST['address'])) {
			$_POST['address']=array();
		}
		foreach ($_POST['address'] as $name=>$address) {
			$addresses[$name]=array(
				'street'=>@$_POST['street-'.$name],
        'street2'=>@$_POST['street2-'.$name],
        'town'=>@$_POST['town-'.$name],
        'county'=>@$_POST['county-'.$name],
				'country'=>@$_POST['country-'.$name],
			);
			if ($_POST['default-address']==$name) {
				$addresses[$name]['default']='yes';
			}
		}
		$addresses=json_encode($addresses);
		// }
		$sql='set email="'.addslashes($_REQUEST['email']).'",'
			.'name="'.addslashes($_REQUEST['name']).'",'
			.'location_lat='.((float)$_REQUEST['location_lat']).','
			.'location_lng='.((float)$_REQUEST['location_lng']).','
			.'phone="'.addslashes($_REQUEST['phone']).'",'
			.'active="'.(int)$_REQUEST['active'].'",'
			.'address="'.addslashes($addresses).'"';
		if (isset($_REQUEST['extras'])) {
			$extras=array();
			foreach ($_REQUEST['extras'] as $k=>$v) {
				if ($v=='') {
					continue;
				}
				$extras[$v]=$_REQUEST['extras_vals'][$k];
			}
			$sql.=',extras="'.addslashes(json_encode($extras)).'"';
		}
		if (isset($_REQUEST['password']) && $_REQUEST['password']!='') {
			if ($_REQUEST['password']!==$_REQUEST['password2']) {
				echo '<em>Password not updated. Must be entered the same twice.</em>';
			}
			else {
				$sql.=',password=md5("'.addslashes($_REQUEST['password']).'")';
			}
		}
		if ($id==-1) {
			dbQuery('insert into user_accounts '.$sql.',date_created=now()');
			$id=dbOne("select last_insert_id() as id limit 1", 'id');
		}
		else {
			dbQuery('update user_accounts '.$sql.' where id='.$id);
		}
		dbQuery("delete from users_groups where user_accounts_id=$id");
		// { first, create new groups if required
		if (isset($_REQUEST['new_groups'])) {
			foreach ($_REQUEST['new_groups'] as $ng) {
				$n=addslashes($ng);
				dbQuery("insert into groups set name='$n',parent=0");
				$_REQUEST['groups'][dbOne('select last_insert_id() as id', 'id')]=true;
			}
		}
		// }
		if (isset($_REQUEST['groups'])) {
			foreach ($_REQUEST['groups'] as $k=>$n) {
				dbQuery("insert into users_groups set user_accounts_id=$id,groups_id=".(int)$k);
			}
		}
		// { now remove any groups other than Administrator that are not used at all
		$rs=dbAll(
			'select id from (select groups.id,groups_id from groups left join use'
			.'rs_groups on groups.id=groups_id) as derived where groups_id is null'
		);
		foreach ($rs as $r) {
			if ($r['id']!='1') {
				dbRow('delete from groups where id='.$r['id']);
			}
		}
		// }
		echo '<em>users updated</em>';
		if (isset($_REQUEST['email-to-send'])) {
			$site=preg_replace('/www\./', '', $_SERVER['HTTP_HOST']);
			mail(
				$_REQUEST['email'],
				'['.$site.'] user status update',
				$_REQUEST['email-to-send'],
				'Reply-to: no-reply@'.$site."\nFrom: no-reply@".$site,
				"-fno-reply@".$site
			);
		}
		Core_cacheSave('user-session-resets', $id, true);
	}
}
// }
// { form
$r=dbRow("select * from user_accounts where id=$id");
if (!is_array($r) || !count($r)) {
	$r=array(
		'id'=>-1,
		'email'=>'',
		'name'=>'',
		'phone'=>'',
		'active'=>0,
		'address'=>array(
			'default'=>array(
				'street'=>'',
				'street2'=>'',
				'town'=>'',
				'county'=>'',
				'country'=>'',
				'default'=>'yes'
			)
		),
		'parent'=>$_SESSION['userdata']['id']
	);
}
echo '<div id="tabs"> <ul> <li><a href="#details">User Details</a></li> <'
	.'li><a href="#locations">Locations</a></li> </ul> <form action="siteoption'
	.'s.php?page=users&amp;id='.$id.'" method="post">';
echo '<input type="hidden" name="id" value="'.$id.'" />';
if (!isset($r['extras'])) {
	$r['extras']='';
}
// { user details
echo '<div id="details"><table><tr><th>Name</th><td><input name="name" va'
	.'lue="'.htmlspecialchars($r['name']).'" /></td><th>Password</th><td><i'
	.'nput name="password" type="password" /></td>'
	.'<td rowspan="6" id="extras-wrapper"><input type="hidden" value="'
	.htmlspecialchars($r['extras'], ENT_QUOTES).'" /></td></tr>';
echo '<tr><th>Email</th><td><input name="email" value="'
	.htmlspecialchars($r['email']).'" /></td><th>(repeat)</th><td><input na'
	.'me="password2" type="password" /></td></tr>';
// { phone
echo '<th>Phone</th><td><input name="phone" value="'
	.htmlspecialchars($r['phone']).'" /></td></tr>';
// }
// { groups
echo '<tr><th>Groups</th><td class="groups">';
$grs=dbAll('select id,name from groups');
$gms=array();
foreach ($grs as $g) {
	$groups[$g['id']]=$g['name'];
}
$grs=dbAll("select groups_id from users_groups where user_accounts_id=$id");
foreach ($grs as $g) {
	$gms[$g['groups_id']]=true;
}
foreach ($groups as $k=>$g) {
	echo '<input type="checkbox" name="groups['.$k.']"';
	if (isset($gms[$k])) {
		echo ' checked="checked"';
	}
	echo ' />'.htmlspecialchars($g).'<br />';
}
echo '</td></tr>';
// }
echo '<tr><th>Active</th><td><select name="active"><option value="0">No</'
	.'option><option value="1"'.($r['active']?' selected="selected"':'')
	.'>Yes</option></select></td></tr>';
echo '<tr style="display:none" id="users-email-to-send"><th>Email to send'
	.' to user</th><td colspan="3" id="users-email-to-send-holder"></td></tr>';
echo '</table>';
echo '</div>';
// }
// {locations 
echo '<div id="locations">';
// { physical location
echo '<h2>Currently located</h2>'
	.'<p id="user-location">The user is recorded as being located at '
	.'Lat:<input type="number" name="location_lat" value="'
	.((float)$r['location_lat']).'"/>, '
	.'Long:<input type="number" name="location_lng" value="'
	.((float)$r['location_lng']).'"/> <a href="#">edit</a></p>';
// }
// { addresses
echo '<h2>Addresses</h2> <a id="new-address" href="javascript:;" style="flo'
	.'at:right">[+] Add Address</a> <div id="add-content">';
if (!is_array($r['address'])) {
	if ($r['address']=='') {
		$r['address']='[]';
	}
	$r['address']=json_decode($r['address'], true);
}
foreach ($r['address'] as $name=>$address) {
	$select=(@$address['default']=='yes')?' checked="checked"':'';
	echo '<table class="address-table"><tr> <th colspan="2"><input type="ra'
		.'dio"'.$select.' name="default-address" value="'.$name.'"/> <h3>'
		.str_replace('-', ' ', $name).'</h3> <a href="javascript:;" class="dele'
		.'te-add">[-]</a></th> <input type="hidden" name="address['.$name
		.']"/> <tr> <th>Street</th> <td><input type="text" name="street-'
		.$name.'" value="'.$address['street'].'"/></td> </tr> <tr> <th>Street'
		.' 2</th> <td><input type="text" name="street2-'.$name.'" value="'
		.$address['street2'].'"/></td> </tr> <tr> <th>Town</th> <td><input ty'
		.'pe="text" name="town-'.$name.'" value="'.$address['town'].'"/></td>'
		.'</tr> <tr> <th>County</th> <td><input type="text" name="county-'
		.$name.'" value="'.$address['county'].'"/></td> </tr> <tr> <th>Countr'
		.'y</th> <td><input type="text" name="country-'.$name.'" value="'
		.$address['country'].'"/></td> </tr> <th></tr></table>';
}
echo '</div><br style="clear:both"/>';
// }
echo '</div>';
// }
echo '<input type="submit" name="action" value="Save" />';
echo '</form></div>';
WW_addScript('/ww.admin/siteoptions/users.js');
// }
