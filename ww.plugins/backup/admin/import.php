<?php
/**
	* Backup plugin import script
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

$errors=array();
if (isset($_POST['action']) && $_POST['action'] == 'submit') {
	$tmpdir='/tmp/webmeBackup-import-'.md5($_SERVER['HTTP_HOST'].microtime(true));
	mkdir($tmpdir);
	$uname=$_FILES['file']['tmp_name'];
	$password=addslashes($_POST['password']);
	$cmd='cd '.$tmpdir.' && unzip -oP "'.$password.'" "'.$uname.'"';
	`$cmd`;
	if (!file_exists($tmpdir.'/site')) {
		echo '<em>unzipping failed. incorrect password?</em>';
	}
	else {
		$udir=USERBASE;

		echo 'extracting files...<br />';
		`cd $udir && rm -rf f && unzip -o $tmpdir/site/files.zip`;

		echo 'extracting themes...<br />';
		`cd $udir && unzip -o $tmpdir/site/theme.zip`;

		echo 'importing config file...<br />';
		$config=json_decode(file_get_contents($tmpdir.'/site/config.json'), true);
		// { remove any version info from the archive - it could be out of date
		foreach ($config as $key=>$val) {
			if (preg_match('/.*\|version/', $key)) {
				unset($config[$key]);
			}
		}
		// }
		// { add back in any version info from the current site
		foreach ($DBVARS as $key=>$val) {
			if (preg_match('/.*\|version/', $key)) {
				$config[$key]=$val;
			}
		}
		// }
		$config['username']=$DBVARS['username'];
		$config['password']=$DBVARS['password'];
		$config['hostname']=$DBVARS['hostname'];
		$config['db_name']=$DBVARS['db_name'];
		$config['userbase']=$DBVARS['userbase'];
		$config['theme_dir']=$DBVARS['theme_dir'];
		$config['theme_dir_personal']=$DBVARS['theme_dir_personal'];
		$config['plugins']=(isset($config['plugins']) && $config['plugins']!='')
			?explode(',', $config['plugins']):
			array();
		$DBVARS=$config;
		Core_configRewrite();

		echo 'updating database if necessary...<br />';
		file_get_contents('http://'.$_SERVER['HTTP_HOST'].'/');

		echo 'extracting database...<br />';
		if (file_exists($tmpdir.'/site/db.json')) { // old version
			$dbbackup=json_decode(file_get_contents($tmpdir.'/site/db.json'));
			foreach ($dbbackup as $name=>$vals) {
				dbQuery('delete from '.addslashes($name));
				foreach ($vals as $row) {
					$parts=array();
					foreach ($row as $key=>$val) {
						$parts[]='`'.addslashes($key).'` = "'.addslashes($val).'" ';
					}
					$query='insert into `'.addslashes($name).'` set '.join(',', $parts);
					dbQuery($query);
				}
			}
		}
		else { // new version
			$tables=new DirectoryIterator($tmpdir.'/site/db');
			foreach ($tables as $table) {
				if ($table->isDot()) {
					continue;
				}
				echo $table.' ';
				dbQuery('delete from `'.addslashes($table).'`');
				$tmpdir2=$tmpdir.'/site/db/'.$table.'/';
				for ($i=0; file_exists($tmpdir2.$i.'.json'); $i++) {
					$rows=json_decode(file_get_contents($tmpdir2.$i.'.json'));
					foreach ($rows as $row) {
						$parts=array();
						foreach ($row as $key=>$val) {
							$parts[]='`'.addslashes($key).'` = "'.addslashes($val).'" ';
						}
						$query='insert into `'.addslashes($table).'` set '
							.join(',', $parts);
						dbQuery($query);
					}
				}
			}
			echo '<br/>';
		}

		echo 'cleaning up.<br />';
		CoreDirectory::delete($tmpdir);

		echo 'clearing local cache...<br />';
		CoreDirectory::delete($udir.'/ww.cache/*');

		echo 'done<img style="width:1px;height:1px" src="./" /><p>Import completed.</p>';
		dbQuery('update pages set alias=name where alias is null');
		Core_cacheClear();
		return;
	}
}

if (count($errors)) {
	echo '<em>'.join('<br />', $errors).'</em>';
}
echo '<em>NOTE: uploading a backup will OVERWRITE your present website.</em>'
	.'<p>Please only upload if you are certain you need to!</p>'
	.'<p>Seriously! Back away now if you are AT ALL unsure of this.</p>'
	.'<form action="/ww.admin/plugin.php?_plugin=backup&amp;_page=import"'
	.' method="post" enctype="multipart/form-data" /><table>'
	.'<tr><th>Backup file</th><td><input type="file" name="file" /></td></tr>'
	.'<tr><th>Password</th><td><input name="password" type="password" /></td>'
	.'</tr>'
	.'<tr><th colspan="2"><input type="submit" name="action" value="submit" />'
	.'</td></tr>'
	.'</table></form>';
