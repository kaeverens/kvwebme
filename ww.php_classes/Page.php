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

// { class Page

/**
	* Page object
	*
	*	@category WebME
	* @package  WebME
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvweb.me/
	*/
class Page{
	static $instances			  = array();
	static $instancesByName		= array();
	static $instancesByNAndP	   = array();
	static $instancesByProductType = array();
	static $instancesBySpecial	 = array();
	static $instancesByType		= array();
	public $vals;

	// { __construct
	/**
		* instantiate a Page object
		*
		* @param mixed $v       ID of the page, or other method of identification
		* @param int   $byField which method of identification to use
		* @param array $fromRow pre-filled array of page data to load
		* @param array $pvq     pre-filled array of meta-data to load
		*
		* @return object the page instance
		*/
	function __construct($v, $byField=0, $fromRow=0, $pvq=0) {
		// byField: 0=ID; 1=Name
		if (!$byField && is_numeric($v)) {
			if ($fromRow) {
				$r=$fromRow;
			}
			else {
				if ($v) {
					$r=Core_cacheLoad('pages', 'id'.$v, -1);
					if ($r===-1) {
						$r=dbRow("select * from pages where id=$v limit 1");
						if (count($r)) {
							Core_cacheSave('pages', 'id'.$v, $r);
						}
					}
				}
				else {
					$r=array();
				}
			}
		}
		elseif ($byField == 1) { // by alias (name)
			if (preg_match('/[^a-zA-Z0-9 \-_]/', $v)) {
				return false;
			}
			$name=strtolower(str_replace('-', '_', $v));
			$fname='page_by_name_'.md5($name);
			$r=Core_cacheLoad('pages', $fname, -1);
			if ($r===-1) {
				$r=dbRow(
					"select * from pages where alias like '".addslashes($name)
					."' limit 1"
				);
				if (count($r)) {
					Core_cacheSave('pages', $fname, $r);
				}
			}
		}
		elseif ($byField == 2) { // by type
			$fname='page_by_type_'.$v;
			$r=Core_cacheLoad('pages', $fname);
			if ($r===false) {
				$r=dbRow("select * from pages where type like '$v%' limit 1");
				if ($r===false) {
					$r=array();
				}
				Core_cacheSave('pages', $fname, $r);
			}
		}
		elseif ($byField == 3 && is_numeric($v)) { // by special
			$fname='page_by_special_'.$v;
			$r=Core_cacheLoad('pages', $fname);
			if ($r===false) {
				$r=dbRow("select * from pages where special&$v limit 1");
				if ($r===false) {
					$r=array();
				}
				Core_cacheSave('pages', $fname, $r);
			}
		}
		elseif ($byField == 4) {
			$r=$v;
		}
		else {
			return false;
		}
		if (!count($r || !is_array($r))) {
			return false;
		}
		if (!isset($r['id'])) {
			$r['id']=0;
		}
		if (!isset($r['type'])) {
			$r['type']=0;
		}
		if (!isset($r['special'])) {
			$r['special']=0;
		}
		if (!isset($r['name'])) {
			$r['name']='NO NAME SUPPLIED';
		}
		foreach ($r as $k=>$v) {
			$this->{$k}=$v;
		}
		if (!isset($r['alias'])) {
			$r['alias']=$r['name'];
		}
		$this->urlname=$r['alias'];
		$this->dbVals=$r;
		self::$instances[$this->id] =& $this;
		self::$instancesByName[preg_replace(
			'/[^,a-z0-9]/',
			'-',
			strtolower($this->urlname)
		)] =& $this;
		self::$instancesBySpecial[$this->special] =& $this;
		// {page type
		if (strpos($this->type, '|')) {
			$this->plugin=preg_replace('/\|.*/', '', $this->type);
			$this->type=preg_replace('/.*\|/', '', $this->type);
		}
		// }
		self::$instancesByType[$this->type] =& $this;
		// { set up values if supplied. otherwise, delay it 'til required
		$this->__valuesLoaded=false;
		if ($pvq) {
			$this->initValues($pvq);
		}
		// }
	}

	// }
	// { getInstance

	/**
		* get an instance of a page by its ID
		*
		* @param int   $id      ID of the page
		* @param array $fromRow pre-filled array of page data to load
		* @param array $pvq     pre-filled array of meta-data to load
		*
		* @return object the page instance
		*/
	static function getInstance($id=0, $fromRow=false, $pvq=false) {
		if (!is_numeric($id)) {
			return false;
		}
		if (!array_key_exists($id, self::$instances)) {
			self::$instances[$id]=new Page($id, 0, $fromRow, $pvq);
		}
		return self::$instances[$id];
	}

	// }
	// { getInstanceByName

	/**
		* get an instance of a page by name
		*
		* @param string $name the name of the page to find
		*
		* @return object the page instance
		*/
	static function getInstanceByName($name='') {
		if (preg_match('/[^!,a-zA-Z0-9 \-_\/]/', $name)) {
			return false;
		}
		$name=strtolower($name);
		$nameIndex=preg_replace('#[^,a-z0-9/]#', '-', $name);
		if (array_key_exists($nameIndex, self::$instancesByName)) {
			return self::$instancesByName[$nameIndex];
		}
		if (strpos($name, '/')) {
			$names=explode('/', $nameIndex);
			$pid=0;
			foreach ($names as $n) {
				$p=self::getInstanceByNameAndParent($n, $pid);
				if (!$p || !isset($p->id)) {
					return false;
				}
				$pid=$p->id;
			}
			self::$instancesByName[$nameIndex]=$p;
		}
		else {
			self::$instancesByName[$nameIndex]=new Page($name, 1);
		}
		return self::$instancesByName[$nameIndex];
	}

	// }
	// { getInstanceBySpecial

	/**
		* get an instance of a page by its special attribute
		*
		* @param int $sp special attribute value to search by
		*
		* @return object the page instance
		*/
	static function getInstanceBySpecial($sp=0) {
		if (!is_numeric($sp)) {
			return false;
		}
		if (!array_key_exists($sp, self::$instancesBySpecial)) {
			self::$instancesBySpecial[$sp]=new Page($sp, 3);
		}
		return self::$instancesBySpecial[$sp];
	}

	// }
	// { getInstanceByType

	/**
		* get an instance of a page by its type
		*
		* @param mixed $type integer code or string name of page type
		*
		* @return object the page instance
		*/
	static function getInstanceByType($type=0) {
		if (!array_key_exists($type, self::$instancesByType)) {
			new Page($type, 2);
		}
		if (!isset(self::$instancesByType[$type])) {
			return false;
		}
		return self::$instancesByType[$type];
	}

	// }
	// { getInstanceByNameAndParent

	/**
		* get an instance of a page by name and parent
		*
		* @param string $name   the name of the page
		* @param int    $parent the ID of the parent page
		*
		* @return object the page instance
		*/
	static function getInstanceByNameAndParent($name, $parent) {
		if (preg_match('/[^,a-zA-Z0-9 \-_]/', $name)) {
			return false;
		}
		$name=str_replace('-', '_', $name);
		if (!array_key_exists($name.'/'.$parent, self::$instancesByNAndP)) {
			$r=Core_cacheLoad('pages', md5($parent.'|'.$name));
			if ($r===false) {
				$r=dbRow(
					"SELECT * FROM pages WHERE parent=$parent AND alias LIKE '"
					.addslashes($name)."'"
				);
				if ($r===false) {
					$r=array();
				}
				Core_cacheSave('pages', md5($parent.'|'.$name), $r);
			}
			if (!count($r)) {
				return false;
			}
			self::$instancesByNAndP[$name.'/'.$parent] = new Page($r, 4);
		}
		return self::$instancesByNAndP[$name.'/'.$parent];
	}

	// }
	// { getAbsoluteURL

	/**
		* get an absolute URL for the page, starting from http/https
		*
		* @return string the URL
		*/
	function getAbsoluteURL() {
		$url=@$_SERVER['HTTPS']?'https':'http';
		$url.='://'.$_SERVER['HTTP_HOST'];
		return $url.$this->getRelativeURL();
	}

	// }
	// { getRelativeURL

	/**
		* get a relative URL for this page, starting from /
		*
		* @return string the URL
		*/
	function getRelativeURL() {
		if (isset($this->relativeURL)) {
			return $this->relativeURL;
		}
		if (isset($this->vars['_short_url'])) {
			$this->relativeURL='/'.dbOne(
				'select short_url from short_urls where page_id='.$this->id,
				'short_url'
			);
		}
		else {
			$this->relativeURL='';
			if (@$this->parent) {
				$p=Page::getInstance($this->parent);
				if ($p) {
					$this->relativeURL.=$p->getRelativeURL();
				}
			}
			$this->relativeURL.='/'.$this->getURLSafeName();
		}
		return $this->relativeURL;
	}

	// }
	// { getTopParentId

	/**
		* get the ID of the top-level parent of this page
		*
		* @return int ID of the top page
		*/
	function getTopParentId() {
		if (!isset($this->parent) || !$this->parent) {
			return $this->id;
		}
		$p=Page::getInstance($this->parent);
		return $p->getTopParentId();
	}

	// }
	// { getURLSafeName

	/**
		* get a version of the page's name which is safe for use in URLs
		*
		* @return string the name
		*/
	function getURLSafeName() {
		if (isset($this->getURLSafeName)) {
			return $this->getURLSafeName;
		}
		$r=$this->urlname;
		$r=preg_replace('/[^,a-zA-Z0-9,-]/', '-', __FromJson($r, true));
		$this->getURLSafeName=$r;
		return $r;
	}

	// }
	// { initValues

	/**
		* load up a page's meta values
		*
		* @param array $pvq pre-filled values array (optional)
		*
		* @return object the Page instance
		*/
	function initValues($pvq=false) {
		$this->vars=array();
		if (!$pvq) {
			$fname='page_vars_'.$this->id;
			$pvq=Core_cacheLoad('pages', $fname);
			if ($pvq===false) {
				$pvq=dbAll("select * from page_vars where page_id=".$this->id);
				Core_cacheSave('pages', $fname, $pvq);
			}
		}
		foreach ($pvq as $pvr) {
			$this->vars[$pvr['name']]=$pvr['value'];
		}
		return $this;
	}

	// }
	// { render
	
	/**
		* render a page template
		*
		* @return string rendered page
		*/
	function render() {
		foreach ($GLOBALS['PLUGINS'] as $plugin) {
			if (isset($plugin['frontend']['body_override'])) {
				return $plugin['frontend']['body_override']($this);
			}
		}
		$smarty=Core_smartySetup(USERBASE.'/ww.cache/pages');
		global $_languages;
		$fname=USERBASE.'/ww.cache/pages/template_'
			.md5($this->id.'|'.join(',', $_languages));
		if (!file_exists($fname) || !filesize($fname)) {
			file_put_contents(
				$fname,
				__FromJson(str_replace(array("\n", "\r"), ' ', $this->body))
			);
		}
		return $smarty->fetch($fname);
	}

	// }
}

// }
