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
		// { address
		$addresses=array();
		if (!isset($_POST['address'])) {
			$_POST['address']=array();
		}
		foreach ($_POST['address'] as $name=>$address) {
			$addresses[$name]=array(
				'street'=>@$_POST['street-'.$name],
        'street2'=>@$_POST['street2-'.$name],
        'town'=>@$_POST['town-'.$name],
        'county'=>@$_POST['county-'.$name],
				'postcode'=>@$_POST['postcode-'.$name],
				'country'=>@$_POST['country-'.$name],
				'phone'=>@$_POST['phone-'.$name]
			);
			if ($_POST['default-address']==$name) {
				$addresses[$name]['default']='yes';
			}
		}
		$addresses=json_encode($addresses);
		// }
		// { contact
		$contact=array();
		foreach ($_REQUEST['contact'] as $k=>$v) {
			if ($v!='') {
				$contact[$k]=$v;
			}
		}
		$contact=json_encode($contact);
		// }
		$sql='set email="'.addslashes($_REQUEST['email']).'",'
			.'name="'.addslashes($_REQUEST['name']).'",'
			.'location_lat='.((float)$_REQUEST['location_lat']).','
			.'location_lng='.((float)$_REQUEST['location_lng']).','
			.'contact="'.addslashes($contact).'",'
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
		echo '<em>users updated</em>';
		if (isset($_REQUEST['email-to-send'])) {
			$site=preg_replace('/www\./', '', $_SERVER['HTTP_HOST']);
			Core_mail(
				$_REQUEST['email'],
				'['.$site.'] user status update',
				$_REQUEST['email-to-send'],
				'no-reply@'.$site
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
		'contact'=>'{}',
		'active'=>0,
		'address'=>'[]',
		'parent'=>$_SESSION['userdata']['id']
	);
}
// { table of contents
echo '<div id="tabs"><ul>'
	.'<li><a href="#details">User Details</a></li>'
	.'<li><a href="#locations">Locations</a></li>'
	.'<li><a href="#custom">Custom Data</a></li>'
	.'</ul> <form action="siteoption'
	.'s.php?page=users&amp;id='.$id.'" method="post">';
echo '<input type="hidden" name="id" value="'.$id.'" />';
if (!isset($r['extras'])) {
	$r['extras']='';
}
// }
// { user details
echo '<div id="details"><table class="wide">'
	.'<tr><th>Main</th><th>Contact Details</th></tr>'
	.'<tr>';
// { main details
echo '<td><table>';
// { name
echo '<tr><th>Name</th><td><input name="name"'
	.' value="'.htmlspecialchars($r['name']).'" /></td></tr>';
// }
// { password
echo '<tr><th>Password</th><td><input name="password" type="password" />'
	.'</td></tr>';
echo '<tr><th>(repeat)</th><td><input name="password2" type="password" />'
	.'</td></tr>';
// }
// { email
echo '<tr><th>Email</th><td><input name="email" value="'
	.htmlspecialchars($r['email']).'" /></td></tr>';
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
// { is active
echo '<tr><th>Active</th><td><select name="active"><option value="0">No</'
	.'option><option value="1"'.($r['active']?' selected="selected"':'')
	.'>Yes</option></select></td></tr>';
// }
echo '</table></td>';
// }
// { contact details
echo '<td><table>';
$contact=json_decode($r['contact'], true);
echo '<tr><th>Contact Name</th><td><input name="contact[contact_name]" value="'
	.htmlspecialchars(@$contact['contact_name']).'" title="if the user is'
	.' a company, then enter a contact name here"/></td></tr>';
echo '<tr><th>Business Phone</th><td><input name="contact[business_phone]" value="'
	.htmlspecialchars(@$contact['business_phone']).'"/></td></tr>';
echo '<tr><th>Business Email</th><td><input name="contact[business_email]" value="'
	.htmlspecialchars(@$contact['business_email']).'"/></td></tr>';
echo '<tr><th>Phone</th><td><input name="contact[phone]" value="'
	.htmlspecialchars(@$contact['phone']).'"/></td></tr>';
echo '<tr><th>Website</th><td><input name="contact[website]" value="'
	.htmlspecialchars(@$contact['website']).'"/></td></tr>';
echo '<tr><th>Mobile</th><td><input name="contact[mobile]" value="'
	.htmlspecialchars(@$contact['mobile']).'"/></td></tr>';
echo '<tr><th>Skype</th><td><input name="contact[skype]" value="'
	.htmlspecialchars(@$contact['skype']).'"/></td></tr>';
echo '<tr><th>Facebook</th><td><input name="contact[facebook]" value="'
	.htmlspecialchars(@$contact['facebook']).'"/></td></tr>';
echo '<tr><th>Twitter</th><td><input name="contact[twitter]" value="'
	.htmlspecialchars(@$contact['twitter']).'"/></td></tr>';
echo '<tr><th>LinkedIn</th><td><input name="contact[linkedin]" value="'
	.htmlspecialchars(@$contact['linkedin']).'"/></td></tr>';
echo '<tr><th>Blog</th><td><input name="contact[blog]" value="'
	.htmlspecialchars(@$contact['blog']).'"/></td></tr>';
echo '</table></td>';
// }
echo '</tr>'
	.'<tr style="display:none" id="users-email-to-send"><th>Email to send'
	.' to user</th><td colspan="3" id="users-email-to-send-holder"></td></tr>'
	.'</table></div>';
// }
// { locations 
echo '<div id="locations">';
// { physical location
echo '<h2>Currently located</h2>'
	.'<p id="user-location">The user is recorded as being located at '
	.'Lat:<input name="location_lat" value="'
	.((float)@$r['location_lat']).'"/>, '
	.'Long:<input name="location_lng" value="'
	.((float)@$r['location_lng']).'"/> <a href="#">edit</a></p>';
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
	$address=array_merge(
		array(
			'street'=>'',
			'street2'=>'',
			'town'=>'',
			'postcode'=>'',
			'county'=>'',
			'country'=>'',
			'phone'=>''
		),
		$address
	);
	echo '<table class="address-table">'
		.'<tr><th colspan="2"><input type="radio"'.$select.' name="default-address"'
		.' value="'.$name.'"/>default '
		.'<a href="javascript:;" class="delete-add" title="delete">[x]</a>'
		.'<input type="hidden" name="address['.$name.']"/></th></tr>'
		.'<tr><th>Street</th><td><input type="text" name="street-'.$name.'"'
		.' value="'.$address['street'].'"/></td></tr>'
		.'<tr><th>Street 2</th><td><input type="text" name="street2-'.$name.'"'
		.' value="'.$address['street2'].'"/></td></tr>'
		.'<tr> <th>Town</th> <td><input type="text" name="town-'.$name.'"'
		.' value="'.$address['town'].'"/></td></tr>'
		.'<tr> <th>Postcode</th> <td><input type="text" name="postcode-'.$name.'"'
		.' value="'.$address['postcode'].'"/></td> </tr>'
		.'<tr> <th>County</th> <td><input type="text" name="county-'.$name.'"'
		.' value="'.$address['county'].'"/></td> </tr>'
		.'<tr> <th>Country</th> <td><input type="text" name="country-'.$name.'"'
		.' value="'.$address['country'].'"/></td> </tr>'
		.'<tr> <th>Phone</th> <td><input type="text" name="phone-'.$name.'"'
		.' value="'.$address['phone'].'"/></td> </tr>'
		.'</table>';
}
echo '</div><br style="clear:both"/>';
// }
echo '</div>';
// }
// { custom data
echo '<div id="custom"><input type="hidden" value="'
	.htmlspecialchars($r['extras'], ENT_QUOTES).'" /></div>';
// }

echo '<input type="submit" name="action" value="Save" />';
echo '</form></div>';
WW_addScript('/ww.admin/siteoptions/users.js');
// }
