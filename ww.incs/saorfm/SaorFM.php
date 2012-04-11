<?php
/**
	* SaorFM core
	*
	* PHP Version 5
	*
	* This file holds the core SaorFM class, which is the main controller
	* of the system
	*
	* @category SaorFM
	* @package  None
	* @author   Kae Verens <kae@verens.com>
	* @author   Conor Mac Aoidh <conormacaoidh@gmail.com>
	* @license  http://www.opensource.org/licenses/bsd-license.php BSD License
	* @link     http://www.saorfm.org/
	*/

/**
	* main controller class of the SaorFM project
	*
	* @category SaorFM
	* @package  None
	* @author   Kae Verens <kae@verens.com>
	* @author   Conor Mac Aoidh <conormacaoidh@gmail.com>
	* @license  http://www.opensource.org/licenses/bsd-license.php BSD License
	* @link     http://www.saorfm.org/
	* @todo     add error method which returns either JSON or literal errors
	*           depending on the preferance in config.php
*/
class SaorFM{
	private $_config=array();

	/**
	  * SaorFM object constructor.
	  *
	  * @return void
	  */
	function __construct() {
		// { load and check the config file
		$config=dirname(__FILE__).'/config.php';
		if (!file_exists($config)) {
			// config.php file does not exist.
			return $this->initErrors='{"error":1}';
		}
		require $config;
		if (!isset($SaorFM_config)) {
			// $SaorFM_config array missing from config.php file.
			return $this->initErrors='{"error":2}';
		}
		// }
		// { set up private variable $_config
		if (is_object($SaorFM_config)) {
			$this->_config=$SaorFM_config;
		}
		else {
			$this->_config=json_decode($SaorFM_config);
			if (!is_object($this->_config)) {
				return $this->initErrors='{"error":11}';
			}
		}
		// }
		// { check the files_directory exists
		if (!is_dir($this->_config->user_files_directory)) {
			// files directory does not exist
			return $this->initErrors='{"error":20}';
		}
		// }
		if (!defined('SAORFM_CORE')) {
			define('SAORFM_CORE', dirname(__FILE__).'/');
		}
		if (!defined('SAORFM_FILES')) {
			define('SAORFM_FILES', $this->_config->user_files_directory.'/');
		}
		if (isset($this->_config->plugins)) {
			foreach ($this->_config->plugins as $name) {
				require_once dirname(__FILE__).'/plugins/'.$name.'/functions.php';
			}
		}
		$this->initErrors='{}';
	}

	/**
		* Checks a directory name to see if it is valid and safe.
		* Useful for avoiding hacks.
		*
		* @param string $directory filename to be checked
		*
		* @return boolean is filename valid
		*/
	public function checkDirectoryName($directory='') {
		// if directory contains '..', it could be a hack attempt
		if (strpos($directory, '..')!==false) {
			return false;
		}
		if ($this->trigger('checkfilename', $directory)) {
			return false;
		}
		return true;
	}

	/**
		* Checks a filename to see if it is valid and safe.
		* Useful for avoiding hacks.
		*
		* @param string $filename filename to be checked
		*
		* @return boolean is filename valid
		*/
	public function checkFilename($filename='') {
		// check if the filename is blank, contains '/', begins or ends with '.'
		if ($filename=='' || preg_match('#^\.|/|\.$#', $filename)) {
			return false;
		}
		/**
		* @todo check against banned extension list
		*/
		return true;
	}

	/**
		* Copies a file or directory.
		* If the source is a directory, the copy will be recursive.
		*
		* @param string $from file or directory to be copied
		* @param string $to   where to copy it to
		*
		* @return string JSON object
		*/
	public function copy($from, $to) {
		// { set up variables
		$from=$this->sanitiseFilename($from);
		$from_is_dir=false;
		$to=$this->sanitiseFilename($to);
		// separate the directory names and file names
		$from_dir=$this->sanitiseFilename(
			preg_replace('#^(.*)/[^/]*$#', '\1', $from)
		);
		$from_file=$this->sanitiseFilename(
			preg_replace('#.*/#', '', $from)
		);
		$to_dir=$this->sanitiseFilename(
			preg_replace('#^(.*)/[^/]*$#', '\1', $to)
		);
		$to_file=$this->sanitiseFilename(
			preg_replace('#.*/#', '', $to)
		);
		// }
		// { check various things
		// { check for hack attempts
		if (!$this->checkFilename($from_file)) {
			return '{"error":8}';
		}
		if (!($from_dir=='' || $this->checkDirectoryName($from_dir))) {
			return '{"error":9}';
		}
		if (!$this->checkFilename($to_file)) {
			return '{"error":8}';
		}
		if (!($to_dir=='' || $this->checkDirectoryName($to_dir))) {
			return '{"error":9}';
		}
		// }
		// { if 'to' is a directory, change variables to reflect a filename instead
		if (file_exists(SAORFM_FILES.$to) && is_dir(SAORFM_FILES.$to)) {
			$to_dir=$to_dir.'/'.$to_file;
			$to_file=$from_file;
			$to=$to.'/'.$from_file;
		}
		// }
		// { if destination already exists, return an error
		if (file_exists(SAORFM_FILES.$to)) {
			// destination file already exists.
			return '{"error":10}';
		}
		// }
		// { check if source file exists
		if (!file_exists(SAORFM_FILES.$from)) {
			// source file does not exist.
			return '{"error":4}';
		}
		// }
		// { check that we're not trying to copy a directory into itself
		if (is_dir(SAORFM_FILES.$from)) {
			$from_is_dir=true;
			if (substr($to, 0, strlen($from)+1) == $from.'/') {
				return '{"error":21}';
			}
		}
		// }
		// }
		// { if source file is a directory, recursively copy it
		if ($from_is_dir) {
			$ret=$this->mkdir($to);
			if ($ret!='{}') {
				return $ret;
			}
			$files=new DirectoryIterator(SAORFM_FILES.$from);
			foreach ($files as $file) {
				if ($file->isDot()) {
					continue;
				}
				$ret=$this->copy($from.'/'.$file->getFilename(), $to);
				if ($ret!='{}') {
					return $ret;
				}
			}
			return '{}';
		}
		// }
		if (!copy(SAORFM_FILES.$from, SAORFM_FILES.$to)) {
			// failed to copy "$1" to "$2".
			var_dump(copy(SAORFM_FILES.$from, SAORFM_FILES.$to));
			return '{"error":17,"error-params":["'.addslashes($from).'","'
				.addslashes($to).'"]}';
		}
		return '{}';
	}

	/**
		* delete
		*
		* Deletes a file or directory. If $full_filename is a directory,
		* will delete directory and all subdirectories and files.
		*
		* @param string $orig_filename file or directory to be deleted
		*
		* @return string JSON object
		*/
	public function delete($orig_filename) {
		// { set up variables
		$full_filename=$this->sanitiseFilename($orig_filename);
		// separate the directory name and file name
		$directory=$this->sanitiseFilename(
			preg_replace('#^(.*)/[^/]*$#', '\1', $full_filename)
		);
		$filename=$this->sanitiseFilename(
			preg_replace('#.*/#', '', $full_filename)
		);
		$full_filename=SAORFM_FILES.$full_filename;
		// }
		// { check various things
		// { check for hack attempts
		if (!$this->checkFilename($filename)) {
			return '{"error":8}';
		}
		if (!($directory=='' || $this->checkDirectoryName($directory))) {
			return '{"error":22}';
		}
		// }
		// { check file exists.
		if (!file_exists($full_filename)) {
			// file does not exist.
			return '{"error":6}';
		}
		// }
		// }
		// { if referred file is a directory, empty it first then delete
		if (is_dir($full_filename)) {
			$subfiles=new DirectoryIterator($full_filename);
			foreach ($subfiles as $f) {
				if ($f->isDot()) {
					continue;
				}
				$err=$this->delete($orig_filename.'/'.$f->getFilename());
				// if an error is encountered, return immediately
				if ($err!='{}') {
					return $err;
				}
			}
			if (!rmdir($full_filename)) {
				// failed to delete directory.
				return '{"error":23,"error-params":["'.addslashes($full_filename).'"]}';
			}
		}
		// }
		// { delete file
		else {
			if (!unlink($full_filename)) {
				// failed to delete file.
				return '{"error:7"}';
			}
		}
		// }
		return '{}';
	}

	/**
	  * error
	  *
	  * Determines in what format to return errors,
	  * ie. JSON or literal.
	  *
	  * @param string $error  JSON object
	  * @param array  $params to replace $1 and $2 if applicable
	  *
	  * @return string JSON object
	  */
	public function error($error,$params=array()) {
		if ($this->_config->json_errors==true) {
			return $error;
		}

		/*
		 * Check filename and check if language file exists
		 */
		$file=SAORFM_CORE.'lang/'.$this->_config->language.'.php';

		if ($this->checkFilename($this->_config->language.'.php')==false) {
			return '{"error":26}';
		}

		if (!file_exists($file)) {
			return '{"error":24}';
		}

		require $file;

		if (!isset($SaorFM_lang)) {
			return '{"error":25}';
		}

		$error=json_decode($error);

		/*
		 * Replace $1 $2 with $params
		 */

		return $SaorFM_lang['error_'.$error->error];		
	}

	/**
	  * get a value from the config
	  *
	  * @param string $name value to retrieve
	  *
	  * @return void
	  */
	public function get($name) {
		if (isset($this->_config->{$name})) {
			return $this->_config->{$name};
		}
		return null;
	}

	/**
		* install a plugin
		*
		* @param string $name name of the plugin to install
		*
		* @return string JSON object
		*/
	public function installPlugin($name) {
		if (!isset($this->_config->plugins)) {
			$this->_config->plugins=array();
		}
		// { check: plugin already installed.
		if (in_array($name, $this->_config->plugins)) {
			return '{"error":30}';
		}
		// }
		// { check: illegal plugin name.
		if (!preg_match('/^[a-z-]*$/', $name)) {
			return '{"error":31}';
		}
		// }
		// { check: plugin "$2" does not exist
		if (!is_dir(dirname(__FILE__).'/plugins/'.$name)) {
			return '{"error":32,"error-params":["'.$name.'"]}';
		}
		// }
		// { load up the config file
		$this->_config->plugins[]=$name;
		require_once dirname(__FILE__).'/plugins/'.$name.'/config.php';
		// }
		// { load up the functions file
		require_once dirname(__FILE__).'/plugins/'.$name.'/functions.php';
		// }
		// { run the installer if it exists
		if (file_exists(dirname(__FILE__).'/plugins/'.$name.'/install.php')) {
			require_once dirname(__FILE__).'/plugins/'.$name.'/install.php';
		}
		// }
		// { add triggers if they exist
		if (isset($config['triggers'])) {
			if (!isset($this->_config->triggers)) {
				$this->_config->triggers=new stdClass;
			}
			foreach ($config['triggers'] as $name=>$function) {
				if (!isset($this->_config->triggers->{$name})) {
					$this->_config->triggers->{$name}=array();
				}
				$this->_config->triggers->{$name}[]=$function;
			}
		}
		// }
		$this->writeConfig();
		return '{}';
	}

	/**
		* list the contents of a directory
		* named listFiles because "list" is a restricted keyword
		*
		* @param string $directory directory name
		*
		* @return string JSON object listing file names and sizes
		*/
	public function listFiles($directory) {
		$directory=$this->sanitiseFilename($directory);
		// { various checks
		if (!$this->checkDirectoryName($directory)) {
			// invalid directory
			return '{"error":9}';
		}
		if (!file_exists(SAORFM_FILES.$directory)) {
			// directory does not exist
			return '{"error":12}';
		}
		if (!is_dir(SAORFM_FILES.$directory)) {
			// is not a directory
			return '{"error":13,"error-params":["'.addslashes($directory).'"]}';
		}
		// }
		// { build list of files and sort them
		$files=new DirectoryIterator(SAORFM_FILES.$directory);
		$arr=array();
		foreach ($files as $f) {
			if ($f->isDot()) {
				continue;
			}
			if ($this->trigger('checkfilename', $f->getFilename())) {
				continue;
			}
			$arr[$f->getFilename()]=array(
				$f->getFilename(),
				$f->getSize(),
				$f->isDir()?1:0 // record as 1 or 0 to save http traffic
			);
		}
		ksort($arr);
		$files=array();
		foreach ($arr as $a) {
			// short names because we don't want the http request to be too large
			$files[]=array(
				'n'=>$a[0], // name of the file
				's'=>$a[1], // size (in bytes) of the file
				'd'=>$a[2]  // is the file a directory
			);
		}
		// }
		return json_encode($files);
	}

	/**
		* Creates a directory
		*
		* @param string $directory directory to create
		*
		* @return string JSON object
		*/
	public function mkdir($directory) {
		// { set up variables
		$directory=$this->sanitiseFilename($directory);
		// separate the directory name and file name
		if (strpos($directory, '/') !== false) {
			$pdir=$this->sanitiseFilename(
				preg_replace('#^(.*)/[^/]*$#', '\1', $directory)
			);
		}
		else {
			$pdir='';
		}
		$cdir=$this->sanitiseFilename(
			preg_replace('#.*/#', '', $directory)
		);
		// }
		// { check various things
		// { was a directory name supplied
		if ($cdir=='') {
			return '{"error":16}';
		}
		// }
		// { check for hack attempts
		if (!($directory=='' || $this->checkDirectoryName($directory))) {
			return '{"error":9}';
		}
		// }
		$directory=SAORFM_FILES.$directory;
		// { check parent directory exists and is a directory.
		if ($pdir!='') {
			if (!file_exists(SAORFM_FILES.$pdir)) {
				$ret=$this->mkdir($pdir);
				if ($ret!='{}') {
					return $ret;
				}
			}
			if (!is_dir(SAORFM_FILES.$pdir)) {
				// is not a directory
				return '{"error":13,"error-params":["'.addslashes($pdir).'"]}';
			}
		}
		if (file_exists($directory)) {
			// directory name already exists.
			return '{"error":14}';
		}
		// }
		// }
		if (!mkdir($directory)) {
			// failed to create directory.
			return '{"error":15}';
		}
		return '{}';
	}

	/**
		* move
		*
		* Moves a file to a different destination. Can also
		* be used to move directories, or to rename files.
		*
		* @param string $from file to be moved
		* @param string $to   where to move it
		*
		* @return string JSON object
		*/
	public function move($from, $to) {
		// { set up variables
		$from=$this->sanitiseFilename($from);
		$to=$this->sanitiseFilename($to);
		// separate the directory names and file names
		$from_dir=$this->sanitiseFilename(
			preg_replace('#^(.*)/[^/]*$#', '\1', $from)
		);
		$from_file=$this->sanitiseFilename(
			preg_replace('#.*/#', '', $from)
		);
		$to_dir=$this->sanitiseFilename(
			preg_replace('#^(.*)/[^/]*$#', '\1', $to)
		);
		$to_file=$this->sanitiseFilename(
			preg_replace('#.*/#', '', $to)
		);
		// }
		// { check various things
		// { check for hack attempts
		if (!$this->checkFilename($from_file)) {
			return '{"error":8}';
		}
		if (!($from_dir=='' || $this->checkDirectoryName($from_dir))) {
			return '{"error":9}';
		}
		if (!$this->checkFilename($to_file)) {
			return '{"error":8}';
		}
		if (!($to_dir=='' || $this->checkDirectoryName($to_dir))) {
			return '{"error":9}';
		}
		// }
		// { if 'to' is a directory, change variables to reflect a filename instead
		if (file_exists(SAORFM_FILES.$to) && is_dir(SAORFM_FILES.$to)) {
			$to_dir=$to_dir.'/'.$to_file;
			$to_file=$from_file;
			$to=$to.'/'.$from_file;
		}
		// }
		// { if destination file already exists, return an error
		if (file_exists(SAORFM_FILES.$to)) {
			// destination file already exists.
			return '{"error":10}';
		}
		// }
		// { check if source file exists
		if (!file_exists(SAORFM_FILES.$from)) {
			// source file does not exist.
			return '{"error":4}';
		}
		// }
		// }
		// { rename file
		if (!rename(SAORFM_FILES.$from, SAORFM_FILES.$to)) {
			// failed to rename file.
			return '{"error":5}';
		}
		// }
		return '{}';
	}

	/**
		* Clean up a file or directory name.
		* Trims spaces, replaces '\' with '/'.
		*
		* @param string $filename filename to be cleaned
		*
		* @return string cleaned filename
		*/
	public function sanitiseFilename($filename='') {
		$filename=trim($filename);
		$filename=str_replace('\\', '/', $filename);
		return $filename;
	}

	/**
	  * set a value in the config
	  *
	  * @param string $name  value to change
	  * @param string $value what to change it to
	  *
	  * @return void
	  */
	public function set($name, $value) {
		$this->_config->{$name}=$value;
	}

	/**
	  * run a trigger
	  *
	  * @param string $trigger name of the trigger
	  * @param mixed  $vals    values to pass to the trigger
	  *
	  * @return mixed whatever the trigger returns
	  */
	public function trigger($trigger, $vals) {
		if (!isset($this->_config->triggers->{$trigger})) {
			return false;
		}
		foreach ($this->_config->triggers->{$trigger} as $function) {
			$ret=$function($this, $vals);
			// if the trigger gives any response other than false, then return it
			if ($ret) {
				return $ret;
			}
		}
		return false;
	}

	/**
		* uninstall a plugin
		*
		* @param string $name name of the plugin to uninstall
		*
		* @return string JSON object
		*/
	public function uninstallPlugin($name) {
		// { check: plugin not installed.
		if (!in_array($name, $this->_config->plugins)) {
			return '{"error":33}';
		}
		// }
		// { remove from list of plugins
		unset($this->_config->plugins[array_search(
			$name,
			$this->_config->plugins
		)]);
		if (!count($this->_config->plugins)) {
			unset($this->_config->plugins);
		}
		// }
		// { run the uninstaller if it exists
		if (file_exists(dirname(__FILE__).'/plugins/'.$name.'/uninstall.php')) {
			require_once dirname(__FILE__).'/plugins/'.$name.'/uninstall.php';
		}
		// }
		// { load up the config file
		require dirname(__FILE__).'/plugins/'.$name.'/config.php';
		// }
		// { remove triggers if they exist
		if (isset($config['triggers'])) {
			foreach ($config['triggers'] as $name=>$function) {
				unset($this->_config->triggers->{$name}[
					array_search($function, $this->_config->triggers->{$name})
				]);
				if (!count($this->_config->triggers->{$name})) {
					unset($this->_config->triggers->{$name});
				}
			}
			if (!count((array)$this->_config->triggers)) {
				unset($this->_config->triggers);
			}
		}
		// }
		$this->writeConfig();
		return '{}';
	}

	/**
	  * writes the SaorFM configuration to the config.php file
	  * if no $config array is supplied, then the internal $_config array is used.
	  *
	  * @param array $config Current configuration of SaorFM.
	  *
	  * @return string JSON object
	  */
	public function writeConfig($config=false) {
		if ($config===false) {
			$config=$this->_config;
		}
		$php="<?php\n"
			.'$SaorFM_config=\''
			.json_encode($config)
			.'\';';
		/**
		  * we use dirname(__FILE__) instead of SAORFM_CORE because the constant
		  * may not have been set yet when this method is called.
			*/
		$config_file=dirname(__FILE__).'/config.php';
		file_put_contents($config_file, $php);
		if (file_get_contents($config_file) !== $php) {
			// failed to write config.php file.
			return '{"error":3}';
		}

		$this->_config=$config;
		return '{}';
	}
}
