<?php
/**
	* CoreGraphics class
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

/**
	* CoreGraphics object
	*
	*	@category WebME
	* @package  WebME
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvweb.me/
	*/
class CoreGraphics{
	/**
		* convert image
		*
		* @param string $from file to convert
		* @param string $to   convert to what
		*
		* @return null
		*/
	static function convert($from, $to) {
		if (!file_exists($from) && @fopen($from, 'r')!=true) {
			return false;
		}
		switch (@$GLOBALS['DBVARS']['graphics-method']) {
			case 'imagick': // {
				$thumb=new Imagick();
				$thumb->read($from);
				$thumb->writeImage($to);
				$thumb->clear();
				$thumb->destroy();
			break; // }
			default: // { fallback to GD
				$extFrom=CoreGraphics::getType($from);
				switch (preg_replace('/.*\./', '', $to)) {
					case 'png': // {
						$extTo='png';
					break; // }
					default:
						$extTo='jpeg';
				}
				$arr=getimagesize($from);
				if ($arr===false) {
					return false;
				}
				$load='imagecreatefrom'.$extFrom;
				$save='image'.$extTo;
				if (!function_exists($load) || !function_exists($save)) {
					return false;
				}
				$im=$load($from);
				$save($im, $to, $extTo=='jpeg'?100:9);
				imagedestroy($im);
				// }
		}
	}

	/**
		* resize an image
		*
		* @param string  $from      file to convert
		* @param string  $to        convert to what
		* @param int     $width     width to convert to
		* @param int     $height    height to convert to
		* @param boolean $keepratio keep image ratio, or force an exact resize
		*
		* @return null
		*/
	static function resize($from, $to, $width, $height, $keepratio=true) {
		if (!file_exists($from) && @fopen($from, 'r')!=true) {
			return false;
		}
		switch (@$GLOBALS['DBVARS']['graphics-method']) {
			case 'imagick': // {
				$thumb=new Imagick();
				$thumb->read($from);
				$thumb->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1, true);
				$thumb->writeImage($to);
				$thumb->clear();
				$thumb->destroy();
			break; // }
			default: // { fallback to GD
				$extFrom=CoreGraphics::getType($from);
				switch (preg_replace('/.*\./', '', $to)) {
					case 'png': // {
						$extTo='png';
					break; // }
					default:
						$extTo='jpeg';
				}
				$size=getimagesize($from);
				if ($size===false) {
					return false;
				}
				$load='imagecreatefrom'.$extFrom;
				$save='image'.$extTo;
				if (!function_exists($load) || !function_exists($save)) {
					return false;
				}
				if (strpos($from, '/')!==0) { // external image
					$tmp=USERBASE.'/ww.cache/'.md5($from).'.'.$extFrom;
					if (!file_exists($tmp)) {
						copy($from, $tmp);
					}
					$im=$load($tmp);
					unlink($tmp);
				}
				else {
					$im=$load($from);
				}
				if ($keepratio) {
					$multx=$size[0]/$width;
					$multy=$size[1]/$height;
					if ($multx>$multy) {
						$mult=$multx;
					}
					else {
						$mult=$multy;
					}
					$width=$size[0]/$mult;
					$height=$size[1]/$mult;
				}
				$imresized=imagecreatetruecolor($width, $height);
				imagealphablending($imresized, false);
				imagecopyresampled(
					$imresized, $im,
					0, 0, 0, 0,
					$width, $height, $size[0], $size[1]
				);
				imagesavealpha($imresized, true);
				$save($imresized, $to, $extTo=='jpeg'?100:9);
				imagedestroy($imresized);
				imagedestroy($im);
				// }
		}
		return true;
	}

	/**
		* get the type of image
		*
		* @param string $fname filename
		*
		* @return string image type
		*/
	static function getType($fname) {
		if (!file_exists($fname) && @fopen($fname, 'r')!=true) {
			return false;
		}
		$data=getimagesize($fname);
		if (@$data['mime']) {
			return preg_replace('/.*\//', '', $data['mime']);
		}
		$ext=strtolower(pathinfo($fname, PATHINFO_EXTENSION));
		return $ext=='jpg'?'jpeg':$ext;
	}
}
