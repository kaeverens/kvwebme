<?php
/**
  * library file for saving form fields to the database
  *
  * PHP Version 5
  *
  * @category   Whatever
  * @package    WebworksWebme
  * @subpackage Form
  * @author     Kae Verens <kae@webworks.ie>
  * @license    GPL Version 2
  * @link       www.webworks.ie
 */

dbQuery('delete from forms_fields where formsId="'.$id.'"');
if (isset($_POST['formfieldElementsName'])
	&&is_array($_POST['formfieldElementsName'])
) {
	foreach ($_POST['formfieldElementsName'] as $key=>$name) {
		$name=addslashes(trim($name));
		if ($name!='') {
			$type=addslashes($_POST['formfieldElementsType'][$key]);
			$isrequired=(isset($_POST['formfieldElementsIsRequired'][$key]))?1:0;
			$extra=addslashes($_POST['formfieldElementsExtra'][$key]);
			$query='insert into forms_fields set name="'.$name.'",type="'.$type.'",
				isrequired="'.$isrequired.'",formsId="'.$id.'",extra="'.$extra.'"';
			dbQuery($query);
		}
	}
}
