<?php
function get_subdirs($base, $dir) {
	$arr=array();
	$D=new DirectoryIterator($base.$dir);
	$ds=array();
	foreach ($D as $dname) {
		$d=$dname.'';
		if ($d{0}=='.') {
			continue;
		}
		if (!is_dir($base.$dir.'/'.$d)) {
			continue;
		}
		$ds[]=$d;
	}
	asort($ds);
	foreach ($ds as $d) {
		$arr[]=$dir.'/'.$d;
		$arr=array_merge($arr, get_subdirs($base, $dir.'/'.$d));
	}
	return $arr;
}
$id=(int)@$_REQUEST['id'];
if (isset($_REQUEST['action'])) {
	if ($_REQUEST['action']=='Save Protected Files') {
		$details=array(
		);
		foreach ($_REQUEST['details'] as $k=>$n) {
			$details[$k]=$n;
		}
		$q='message="'.addslashes($_REQUEST['message']).'",'
			.'template="'.addslashes($_REQUEST['template']).'",'
			.'directory="'.addslashes($_REQUEST['directory']).'",'
			.'recipient_email="'.addslashes($_REQUEST['recipient_email']).'",'
			.'details="'.addslashes(json_encode($details)).'"';
		if ($id) {
			dbQuery("update protected_files set $q where id=$id");
		}
		else {
			dbQuery("insert into protected_files set $q");
			$id=dbOne("select last_insert_id() as id", 'id');
		}
	}
	elseif ($_REQUEST['action']=='delete') {
		dbQuery("delete from protected_files where id=$id");
		$id=0;
	}
	Core_cacheClear('protected_files');
}

$r=dbRow('select * from protected_files where id='.$id);
$details=json_decode($r['details'], true);
switch (@$_REQUEST['view']) {
	case 'log': // {
		echo '<table><tr><th>Filename</th><th>Completed</th><th>Email</th><th>D'
			.'ate/Time</th></tr>';
		$fs=dbAll(
			'select file,success,email,last_access from protected_files_log where'
			.' pf_id='.$id.' order by last_access desc'
		);
		foreach ($fs as $f) {
			echo '<tr><td>'.htmlspecialchars($f['file']).'</td><td>'
				.($f['success']?'yes':'no').'</td><td><a href="mailto:'.$f['email']
				.'">'.$f['email'].'</a></td><td>'.$f['last_access'].'</td></tr>';
		}
		echo '</table>';
	break; // }
	default: // { show form
		echo '<form method="post" action="'.$_url.'"><table style="width:90%">';
		if (!isset($r['directory'])) {
			$r['directory']='/';
		}
		echo '<tr><th>Directory containing the files</th><td><select id="direct'
			.'ory" name="directory"><option value="'
			.htmlspecialchars($r['directory']).'">'
			.htmlspecialchars($r['directory']).'</option>';
		echo '</select><a class="button" href="#page_vars[directory]" onclick="'
			.'javascript:window.open(\'/j/kfm/?startup_folder=\'+$(\'#directory\''
			.').attr(\'value\'),\'kfm\',\'modal,width=800,height=600\');">Manage '
			.'Files</a></td>';
		// { link to log
		echo '<th>&nbsp;</th><td>';
		if ($id) {
			echo '<a href="/ww.admin/plugin.php?_plugin=protected-files&amp;_page'
				.'=index&amp;id='.$id.'&amp;view=log">view log</a>';
		}
		echo '</td></tr>';
		// }
		// { protection type
		echo '<tr><th>protection type</th><td><select name="details[type]"><opt'
			.'ion value="1">Require an email address</option><option value="2"';
		if ($details['type'] == 2) {
			echo ' selected="selected"';
		}
		echo '>Must be a group member</option></select></td>';
		// }
		if ($details['type'] == 2) {
			echo '<th>valid groups</th><td><input style="width:100%" name="detail'
				.'s[groups]" value="'.htmlspecialchars($details['groups']).'" /></td>';
		}
		echo '</tr>';
		// { email to send alerts to
		echo '<tr><th>Email to send download alerts to</th><td>'
			.'<input name="recipient_email" value="'
			.htmlspecialchars(@$r['recipient_email']).'" /></td>';
		// }
		// { page template
		echo '<th>Page Template</th><td>';
		$ex='ls '.THEME_DIR.'/'.THEME.'/h/*html';
		$d=`$ex`;
		$d=explode("\n", $d);
		array_pop($d);
		if (count($d)>1) {
			echo '<select name="template">';
			foreach ($d as $f) {
				$f=preg_replace('/^\.\.\/|\n|\r|$/', '', $f);
				$name=preg_replace('/.*themes-personal\/[^\/]*\/h\/|\.html/', '', $f);
				echo '<option ';
				if ($name==$r['template']) {
					echo ' selected="selected"';
				}
				echo '>'.$name.'</option>';
			}
			echo '</select>';
		}
		else {
			$name=htmlspecialchars(
				preg_replace('/.*themes-personal\/[^\/]*\/h\/|\.html/', '', $d[0])
			);
			echo '<input type="hidden" name="template" value="'.$name.'" />'.$name;
		}
		echo '</td></tr>';
		// }
		// { message
		echo '<tr><th>Message</th><td colspan="3">'
			.ckeditor('message', @$r['message'], 0, 0, 150).'</td></tr>';
		// }
		// { save
		echo '<tr><th colspan="2"><input type="hidden" name="id" value="'.$id
			.'" />';
		echo '<input type="submit" name="action" value="Save Protected Files" />';
		if ($id) {
			echo '<a style="margin-left:20px;" href="/ww.admin/plugin.php?_plugin'
				.'=protected_files&amp;id='.$id.'&amp;action=delete" onclick="retur'
				.'n confirm(\'are you sure you want to remove this?\')" title="dele'
				.'te">[x]</a>';
		}
		echo '</th></tr></table></form>';
		// }
		// }
}
?>
<script>
	$(function(){
		$('#directory').remoteselectoptions({url:'/a/f=adminDirectoriesGet'});
	});
</script>
