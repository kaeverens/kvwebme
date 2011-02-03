<?php
class PageVars{
	static $instancesByNameAndValue=array();
	function __construct(){
	}
	function getByNameAndValue($name,$value,$includePageRow=false){
		if (!array_key_exists($name,self::$instancesByNameAndValue) || !array_key_exists($value,self::$instancesByNameAndValue[$name])){
			if(!array_key_exists($name,self::$instancesByNameAndValue))self::$instancesByNameAndValue[$name]=array();
			if($includePageRow){
				self::$instancesByNameAndValue[$name][$value]=dbRow("SELECT * FROM page_vars,pages WHERE page_vars.name='".addslashes($name)."' AND value='".addslashes($value)."' AND pages.id=page_vars.page_id LIMIT 1");
			}
			else{
				self::$instancesByNameAndValue[$name][$value]=dbRow("SELECT * FROM page_vars WHERE name='".addslashes($name)."' AND value='".addslashes($value)."' LIMIT 1");
			}
		}
		return self::$instancesByNameAndValue[$name][$value];
	}
}
