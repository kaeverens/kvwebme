<?php
/**
	* SaorFM Hidden Files plugin function list
	*
	* PHP Version 5
	*
	* @category SaorFM
	* @package  Hidden_Files
	* @author   Kae Verens <kae@verens.com>
	* @license  http://www.opensource.org/licenses/bsd-license.php BSD License
	* @link     http://www.saorfm.org/
	*/

	/**
		* check a filename to see if it should be hidden
		*
		* @param object &$saorfm   the SaorFM object
		* @param strong &$filename the filename to check
		*
		* @return boolean true to hide, false to not hide
		*/
function HiddenFiles_checkFilename(&$saorfm, &$filename) {
	return preg_match($saorfm->get('hiddenfiles'), $filename);
}
