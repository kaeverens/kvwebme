<?php
class Pages{
	static $instancesByParent = array();
	public $pages=array();
	function __construct($constraint, $filter=true){
		global $isadmin;
		$filter=($isadmin || !$filter)?'':' && !(special&2)';
		$rs=dbAll(
			"select * from pages where $constraint $filter order by special&2,ord,name"
		);
		if (!count($rs)) {
			$rs=array();
		}
		foreach ($rs as $r) {
			$this->pages[] = Page::getInstance($r['id'], $r);
		}
		Pages::$instancesByParent[$constraint] =& $this;
	}
	static function getInstancesByType($type){
		$constraint='type="'.addslashes($type).'"';
		if (!array_key_exists($constraint, self::$instancesByParent)) {
			new Pages($constraint, false);
		}
		return self::$instancesByParent[$constraint];
	}
	static function getInstancesByParent($pid=0, $filter=true){
		if (!is_numeric($pid)) {
			return false;
		}
		$constraint='parent='.$pid;
		if (!array_key_exists($constraint, self::$instancesByParent)) {
			new Pages($constraint, $filter);
		}
		return self::$instancesByParent[$constraint];
	}
	static function precache($ids){
		if (count($ids)) {
			$rs3=dbAll('select * from pages where id in ('.join(',', $ids).')');
			$pvars=dbAll('select * from page_vars where page_id in ('.join(',', $ids).')');
			$rs2=array();
			foreach ($pvars as $p) {
				if (!isset($rs2[$p['page_id']])) {
					$rs2[$p['page_id']]=array();
				}
				$rs2[$p['page_id']][]=$p;
			}
			foreach ($rs3 as $r) {
				if (isset($rs2[$r['id']])) {
					Page::getInstance($r['id'], $r, $rs2[$r['id']]);
				}
				else {
					Page::getInstance($r['id'], $r);
				}
			}
		}
	}
}
