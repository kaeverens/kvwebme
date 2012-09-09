<?php
/**
	* verify uploaded theme files
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

require '../../../ww.incs/basics.php';
require SCRIPTBASE.'ww.incs/mail.php';
require SCRIPTBASE.'ww.plugins/themes-api/api/funcs.php';

/**
 * check user is logged in
 */
$user_id = ( int) @$_SESSION[ 'userdata' ][ 'id' ];
if ($user_id == 0) {
	Core_quit();
}

/**
 * if id is not present, die
 */
$id = addslashes(@$_POST[ 'id' ]);
if ($id == 0) {
	die('error');
}

/**
 * get theme name from db
 */
$name = dbOne('select name from themes_api where id='.$id, 'name');
$f_name = str_replace(' ', '-', $name);

$extract_dir = USERBASE.'/f/themes_api/extract/';
$files_dir = $extract_dir.$f_name .'/';
$theme_dir = USERBASE.'/f/themes_api/themes/';

/**
 * cd to extract dir and unzip theme there
 */
shell_exec(
	'cd '.$extract_dir.' && unzip -o '.$theme_dir.$id.'/'.$id.'.zip'
);

/**
 * if required directory and file structure is not
 * present then die with error
 */
if (!is_dir($files_dir)) {
	ThemesApi_error('base', $id);
}

if (!file_exists($files_dir.'screenshot.png')) {
	ThemesApi_error('screenshot', $id);
}

if (!is_dir($files_dir.'c')) {
	ThemesApi_error('c', $id);
}

if (!is_dir($files_dir.'h')) {
	ThemesApi_error('h', $id);
}

/**
 * if cs dir is present, make sure its contents
 * are correct
 */
if (is_dir($files_dir.'cs')) {

	$handler = opendir($files_dir.'cs');
	while ($file = readdir($handler)) {

		if ($file == '.' && $file == '..') {
			continue;
		}

		/**
		 * get file name and extention
		 */
		$fname = explode('.', $file);
		$ext = end($fname);
		$fname = reset($fname);

		/**
		 * if css files are present, make sure they have
		 * corresponding png files
		 */
		if ($ext == 'css') {
			if (!file_exists($files_dir.'cs/'.$fname.'.png')) {
				ThemesApi_error('cs', $id);
			}
		}
	}
	closedir($handler);	
}

/**
 * get all the html files in an array
 */
$html = array();

$handler = opendir($files_dir.'h');
while ($file = readdir($handler)) {
	if ($file == '.' && $file == '..') {
		continue;
	}

	/**
	 * get file extention
	 */
	$ext = end(explode('.', $file));

	if ($ext == 'html') {
		array_push($html, $file);
	}
}
closedir($handler);

/**
 * if there are no html files,
 * throw error
 */
if (count($html) == 0) {
	ThemesApi_error('no h', $id);
}

/**
 * make sure header and footer and
 * ( left or right) panels are present
 */
foreach ($html as $file) {
	/**
	 * get the contents of the file for verification
	 */
	$contents = file_get_contents($files_dir.'h/'.$file);

	/**
	 * make sure all required panels are present
	 */
	$panels = array();
	preg_match_all('/\{\{PANEL name="?([^"}]*)"?\}\}/', $contents, $panels);

	if (!in_array(array('header', 'footer'), $panels[ 1 ])
		&& (!in_array('sidebar1', $panels[1]) && !in_array('sidebar2', $panels[1]))
	) {
		ThemesApi_error('panels', $id);
	}

	/**
	 * remove metadata tag and replace with
	 * title so that it passes xhtml5 validation
	 */
	$contents = str_replace(
		'{{$METADATA}}', '<title>'.__('Example').'</title>', $contents
	); 

	/**
	 * remove all tags and write to file for
	 * xhtml5 validation
	 */
	$contents = preg_replace('#\{\{.*?\}\}#m', '', $contents);
	file_put_contents($files_dir.'h/'.$file, $contents);
}

/**
 * init and setup cURL
 */ 
$curl = curl_init();
curl_setopt($curl, CURLOPT_HEADER, 0);
curl_setopt($curl, CURLOPT_VERBOSE, 0);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_URL, 'http://html5.validator.nu/');

foreach ($html as $file) {

	/**
	 * set the file to be sent
	 */
	$post_array = array(
		'file'	=>	'@'.$files_dir.'h/'.$file,
	);

	/**
	 * send via curl, and get response
	 */
	curl_setopt($curl, CURLOPT_POSTFIELDS, $post_array); 
	$response = curl_exec($curl);

	/**
	 * check if HTML is valid
	 */
	if (strpos($response, 'success') === false) {
		$response = preg_replace('#(<form.*?>).*?(</form>)#', '', $response);
		ThemesApi_error($response, $id);
	}

}

/**
 * copy screenshot file to themes dir
 * for use later
 */
shell_exec(
	'cd '.$theme_dir.$id.' && unzip -o '.$id.'.zip && mv '.$f_name.' '.$id
);
shell_exec(
	'convert "'.$theme_dir.$id.'/'.$id.'/screenshot.png" -resize 240x172 "'
	.$theme_dir.$id.'/'.$id.'/screenshot.png'
);

/**
 * figure out who is in the moderation team
 * and send them all an email
 */
$id = dbOne('select id from groups where name="moderators"', 'id');
$users = dbAll(
	'select name, email from user_accounts, users_groups where groups_id='.$id
	.' and user_accounts_id=id'
);
$url = ThemesApi_calculateUrl();
$users_c = count($users);

if ($users_c != 0 && $users != false) {
	for ($i = 0; $i < $users_c; ++$i) {
		$body = '<h3>'.__('Theme Moderation').'</h3>'
			.'<p>'.__('Hi %1,', array($users[$i]['name']), 'core').'</p>'
			.'<p>'.__(
				'A new theme named "%1" has been marked for moderation, please'
				.' <a href="%2/ww.admin">log in</a> and moderate the theme.',
				array($name, $url),
				'core'
			).'</p>'
			.'<p>'.__('Thanks<br/>---<br/>kvWebME').'</p>';
		send_mail(
			$users[ $i ][ 'email' ],
			'noreply@'.$_SERVER[ 'HTTP_HOST' ],
			__('Theme Moderation'),
			$body,
			false
		);
	}
}

ThemesApi_error('ok');
