<?php
/**
  * api
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

// { IssueTracker_typesGet

/**
	* get a list of issue types
	*
	* @return array list
	*/
function IssueTracker_typesGet() {
	return dbAll('select id,name from issuetracker_types order by name');
}

// }
