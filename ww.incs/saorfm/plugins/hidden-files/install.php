<?php
/**
	* SaorFM Hidden Files plugin installation file
	*
	* PHP Version 5
	*
	* @category SaorFM
	* @package  Hidden_Files
	* @author   Kae Verens <kae@verens.com>
	* @license  http://www.opensource.org/licenses/bsd-license.php BSD License
	* @link     http://www.saorfm.org/
	*/

if (!isset($this->_config->hiddenfiles)) {
	// the default pattern hides files that begin with .
	$this->_config->hiddenfiles='#^/?\.#';
}
