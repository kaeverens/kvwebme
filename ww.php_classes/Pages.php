<?php
/**
	* the Page object
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

// { class Pages

/**
	* Pages object
	*
	*	@category WebME
	* @package  WebME
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvweb.me/
	*/
class Pages{
	static $instancesByParent = array();
	public $pages=array();
	
	// { __construct

	/**
		* get list of pages that have a common parent
		*
		* @param string  $constraint the SQL constraint to use
		* @param boolean $filter     whether to only show "published" pages
		*
		* @return object the Pages object
		*/
	function __construct($constraint, $filter=true) {
		global $isadmin;
		$filter=($isadmin || !$filter)?'':' && !(special&2)';
		$rs=dbAll(
			"select * from pages where $constraint $filter "
			."order by special&2,ord,name"
		);
		if (!count($rs)) {
			$rs=array();
		}
		foreach ($rs as $r) {
			$this->pages[] = Page::getInstance($r['id'], $r);
		}
		Pages::$instancesByParent[$constraint] =& $this;
	}

	// }
	// { getInstancesByType

	/**
		* get list of pages that are all the same type
		*
		* @param string $type the type of the pages
		*
		* @return object the Pages object
		*/
	static function getInstancesByType($type) {
		$constraint='type like "'.addslashes($type).'%"';
		if (!array_key_exists($constraint, self::$instancesByParent)) {
			new Pages($constraint, false);
		}
		return self::$instancesByParent[$constraint];
	}

	// }
	// { getInstancesByParent

	/**
		* get list of pages that have a common parent
		*
		* @param int     $pid    the parent ID
		* @param boolean $filter whether to only show "published" pages
		*
		* @return object the Pages object
		*/
	static function getInstancesByParent($pid=0, $filter=true) {
		if (!is_numeric($pid)) {
			return false;
		}
		$constraint='parent='.$pid;
		if (!array_key_exists($constraint, self::$instancesByParent)) {
			new Pages($constraint, $filter);
		}
		return self::$instancesByParent[$constraint];
	}

	// }
}

// }
