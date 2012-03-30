<?php
/**
	* the PageVars object
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

// { class PageVars

/**
	* PageVars object
	*
	*	@category WebME
	* @package  WebME
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvweb.me/
	*/
class PageVars{
	static $instancesByNameAndValue=array();
	/**
		* get a page variable by its name and value
		*
		* @param string  $name           name of the variable you're searching for
		* @param string  $value          value of the variable you're searching for
		* @param boolean $includePageRow whether to also return the page row
		*
		* @return PageVars object
		*/
	function getByNameAndValue($name, $value, $includePageRow=false) {
		if (!array_key_exists($name, self::$instancesByNameAndValue)
			|| !array_key_exists($value, self::$instancesByNameAndValue[$name])
		) {
			if (!array_key_exists($name, self::$instancesByNameAndValue)) {
				self::$instancesByNameAndValue[$name]=array();
			}
			if ($includePageRow) {
				self::$instancesByNameAndValue[$name][$value]=dbRow(
					"SELECT * FROM page_vars,pages WHERE page_vars.name='"
					.addslashes($name)."' AND value='".addslashes($value)
					."' AND pages.id=page_vars.page_id LIMIT 1"
				);
			}
			else {
				self::$instancesByNameAndValue[$name][$value]=dbRow(
					"SELECT * FROM page_vars WHERE name='".addslashes($name)
					."' AND value='".addslashes($value)."' LIMIT 1"
				);
			}
		}
		return self::$instancesByNameAndValue[$name][$value];
	}
}

// }
