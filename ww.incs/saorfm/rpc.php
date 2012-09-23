<?php
/**
	* SaorFM rpc file
	*
	* PHP Version 5
	*
	* This file holds the SaorFM rpc handler, which uses
	* the saorFM core
	*
	* @category SaorFM
	* @package  RPC
	* @author   Conor Mac Aoidh <conormacaoidh@gmail.com>
	* @author   Kae Verens <kae@verens.com>
	* @license  http://www.opensource.org/licenses/bsd-license.php BSD License
	* @link     http://www.saorfm.org/
	*/

$action=@$_REQUEST['action'];

if ($action=='') {
	// no action was requested.
  echo '{"error":18}';
	require_once '../../ww.incs/basics.php';
  Core_quit();
}
/**
	* require SaorFM core and create an instance
	* of the core class
	*/
require 'SaorFM.php';
$SaorFM=new SaorFM();
if ($SaorFM->initErrors != '{}') {
	// error initialising SaorFM
	header('Content-type: text/javascript');
	echo $SaorFM->initErrors;
	Core_quit();
}
/**
	* switch according to the desired action
	*/
switch($action){
	case 'copy': // {
		$from=$_GET['from'];
		$to=$_GET['to'];
		$error=$SaorFM->copy($from, $to);
	break;
	// }
	case 'delete': // {
		$file=$_GET['file'];
		$error=$SaorFM->delete($file);
	break;
	// }
	case 'get': // {
		if (isset($_GET['file'])) {
			$file=$SaorFM->sanitiseFilename($_GET['file']);
			if ($SaorFM->checkDirectoryName(preg_replace('#[^/]*$#', '', $file))) {
				$name=preg_replace('#.*/#', '', $file);
				if ($SaorFM->checkFilename($name)) {
					$filepath=SAORFM_FILES.$file;
					if (file_exists($filepath)) {
						if (!is_dir($filepath)) {
							if (isset($_SERVER['HTTP_USER_AGENT'])
								&& strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')
							) {
								$name = preg_replace(
									'/\./',
									'%2e',
									$name,
									substr_count($name, '.')-1
								);
							}
							@set_time_limit(0);
							header('Cache-Control: max-age = 2592000');
							header('Expires-Active: On');
							header('Expires: Fri, 1 Jan 2500 01:01:01 GMT');
							header('Pragma:');
							$filesize=filesize($filepath);
							header('Content-Length: '.(string)(filesize($filepath)));
							if (isset($_GET['forcedownload'])) {
								header('Content-Type: force/download');
								header('Content-Disposition: attachment; filename="'.$name.'"');
							} else {
								$finfo = finfo_open(FILEINFO_MIME_TYPE);
								header('Content-Type: '.finfo_file($finfo, $filepath));
							}
							header('Content-Transfer-Encoding: binary');
							if ($file = fopen($filepath, 'rb')) { // send file
								while ((!feof($file))&&(connection_status()==0)) {
									print(fread($file, 1024*8));
									flush();
								}
								fclose($file);
							}
							Core_quit();
						}
						else {
							$error='{"error":29}';
						}
					} else { // source file does not exist.
						$error='{"error":4}';
					}
				}
				else { // invalid file name
					$error='{"error":8}';
				}
			}
			else { // invalid directory name
				$error='{"error":9}';
			}
		}
		else { // missing "file" parameter
			$error='{"error":28}';
		}
	break;
	// }
	case 'listFiles': // {
		$directory=@$_GET['directory'];
		$error=$SaorFM->listFiles($directory);
	break;
	// }
	case 'mkdir': // {
		$dir=$_GET['dir'];
		$error=$SaorFM->mkdir($dir);
	break;
	// }
	case 'move': // {
		$from=$_GET['from'];
		$to=$_GET['to'];
		$error=$SaorFM->move($from, $to);
	break;
	// }
	case 'upload': // {
		// { if no directory supplied, set to '/'
		if (!isset($_REQUEST['directory'])) {
			$directory='/';
		}
		else {
			$directory=$_REQUEST['directory'];
		}
		// }
		if (isset($_FILES['file']['tmp_name'])
			&& is_uploaded_file($_FILES['file']['tmp_name'])
		) {
			if ($SaorFM->checkDirectoryName($directory)) {
				move_uploaded_file(
					$_FILES['file']['tmp_name'],
					SAORFM_FILES.$directory.'/'.$_FILES['file']['name']
				);
				$error='{}';
			}
			else { // invalid directory
				$error='{"error_9"}';
			}
		}
		else { // no file uploaded
			$error='{"error_27"}';
		}
	break;
	// }
	default: // {
		// unknown action "$1" requested
		$error='{"error":19,"error-params":["'.addslashes($action).'"]}';
		// }
}

header('Content-type: text/javascript');
echo $error;
