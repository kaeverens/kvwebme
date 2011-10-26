<?php
class CoreGraphics{
	static function convert($from, $to) {
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
				$extTo  =CoreGraphics::getType($to);
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
	static function resize($from, $to, $width, $height, $keepratio=true) {
		switch (@$GLOBALS['DBVARS']['graphics-method']) {
			case 'imagick': // {
				$thumb=new Imagick();
				var_dump(method_exists($thumb, 'read')); exit;
				$thumb->read($from);
				$thumb->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1, true);
				$thumb->writeImage($to);
				$thumb->clear();
				$thumb->destroy();
			break; // }
			default: // { fallback to GD
				$extFrom=CoreGraphics::getType($from);
				$extTo  =CoreGraphics::getType($to);
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
				if ($keepratio) {
					$size=getimagesize($from);
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
					$width, $height, $arr[0], $arr[1]
				);
				imagesavealpha($imresized, true);
				$save($imresized, $to, $extTo=='jpeg'?100:9);
				imagedestroy($imresized);
				imagedestroy($im);
			// }
		}
		return true;
	}
	static function getType($fname) {
		$data=getimagesize($fname);
		if (@$data['mime']) {
			return preg_replace('/.*\//', '', $data['mime']);
		}
		$ext=strtolower(pathinfo($fname, PATHINFO_EXTENSION));
		return $ext=='jpg'?'jpeg':$ext;
	}
}
