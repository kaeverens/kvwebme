<?php
require_once '../../ww.incs/basics.php';
$userdir=USERBASE.'/f';
if (!isset($_SESSION['userdata'])) {
	die('{"error":"not logged in"}');
}
if (!Core_isAdmin()) {
	$userdir=$userdir.'/users/'.$_SESSION['userdata']['id'];
	if (!file_exists($userdir) || !is_dir($userdir)) {
		mkdir($userdir, 0755, true);
	}
}

/**
  * SaorFM configuration file
  *
  * PHP Version 5
  *
  * This file holds all configuration details
  *
  * @category SaorFM
  * @package  None
  * @author   Kae Verens <kae@verens.com>
  * @author   Conor Mac Aoidh <conormacaoidh@gmail.com>
  * @license  http://www.opensource.org/licenses/bsd-license.php BSD License
  * @link     http://www.saorfm.org/
*/

$SaorFM_config=(object)array(

/**
 * Language to use for errors (if enabled)
 * 
 * Choices: en ga
 */
  'language'=>'en',

/**
 * Display errors as JSON object or in $language
 * specified above.
 *
 * bool true or false
 */

  'json_errors'=>false,

/**
 *
 * User files directory. This is the base directory
 * for saorfm operation. Must grant write access
 * to this directory.
 * 
 */

  'user_files_directory'=>$userdir,
	'hiddenfiles'=>'#^/?\.#',
	'plugins'=>array(
		'hidden-files'
	),
	'triggers'=>(object)array(
		'checkfilename'=>array(
			'HiddenFiles_checkFilename'
		)
	)

);
