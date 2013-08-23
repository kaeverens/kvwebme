<?php
/**
  * script for extracting data from Form database and saving as a CSV file
  *
  * PHP Version 5
  *
	* @category   None
  * @package    None
  * @subpackage Form
  * @author     Kae Verens <kae@kvsites.ie>
  * @license    GPL Version 2
  * @link       www.kvweb.me
 */

require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!Core_isAdmin()) {
	die(__('access denied'));
}

if (isset($_REQUEST['id'])) {
	$id=(int)$_REQUEST['id'];
}
else {
	$id=0;
}
if (!$id) {
	Core_quit();
}
$date=$_REQUEST['date'];
if (!preg_match('/^20[0-9][0-9]-[0-9][0-9]-[0-9][0-9]$/', $date)) {
	die(__('invalid date format'));
}

header('Content-type: application/octet-stream');
header('Content-Disposition: attachment; filename="form'.$id.'-export.csv"');

// { ids
$ids=array();
$rs=dbAll(
	'select id,date_created from forms_saved where forms_id='
	.$id.' and date_created>"'.$date.'"'
);
foreach ($rs as $r) {
	$ids[$r['id']]=$r['date_created'];
}
// }
// { columns
$cols=array();
$rs=dbAll('select name from forms_fields where formsId="'.$id.'" order by id');
foreach ($rs as $r) {
	$cols[]=$r['name'];
}
// }
// { do the export
echo '"'.__('Date Submitted').'","';
echo join('","', $cols).'"'."\n";
foreach ($ids as $id=>$date) {
	echo '"'.$date.'",';
	for ($i=0;$i<count($cols);++$i) {
		$r=dbRow(
			'select value from forms_saved_values where forms_saved_id='.$id
			.' and name="'.addslashes($cols[$i]).'"'
		);
		echo '"'.str_replace('\\"', '""', addslashes($r['value'])).'"';
		if ($i<count($cols)-1) {
			echo ',';
		}
		else {
			echo "\n";
		}
	}
}
// }
