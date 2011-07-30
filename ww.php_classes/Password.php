<?php
/**
	* password generator
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

class Password{
	/**
	  * generate a pronounceable password
	  *
	  * @return string the new password
	  */
	static function getNew() { // from http://www.blueroo.net/max/pwdgen.php
		$consts = 'bcdgjlmnprst';
		$vowels = 'aeiou';
		for ($x= 0;$x<6;$x++) {
			mt_srand((double)microtime() *1000000);
			$const[$x] = substr($consts, mt_rand(0, strlen($consts) -1), 1);
			$vow[$x] = substr($vowels, mt_rand(0, strlen($vowels) -1), 1);
		}
		return $const[0].$vow[0].$const[2].$const[1].$vow[1].$const[3].$vow[3]
			.$const[4];
	}
}
