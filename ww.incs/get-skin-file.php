<?php
require_once 'basics.php';
require_once 'Core_getMimeType.php';

if (!isset($_REQUEST['filename'])) {
	Core_quit();
}
$file=THEME_DIR.'/'.$_REQUEST['filename'];
if (strpos($file, '..')!==false || strpos($file, '/.')!==false) {
	Core_quit();
}

if (!file_exists($file) || !is_file($file)) {
	$file=$DBVARS['theme_dir_personal'].'/'.$_REQUEST['filename'];
	if (!file_exists($file) || !is_file($file)) {
		$file=$DBVARS['theme_dir_personal'].'/'.$DBVARS['theme'].'/'.$_REQUEST['filename'];
		if (!file_exists($file) || !is_file($file)) {
			header("HTTP/1.0 404 Oh No!");
			Core_quit();
		}
	}
}

function CSS_colourCode($code) {
	if ($code{0}=='#') {
		$code=substr($code, 1, strlen($code)-1);
	}
	if (strlen($code)==3) {
		$chars=str_split($code);
		foreach ($chars as $k=>$v) {
			$chars[$k]=$v.$v;
		}
		$code=join('', $chars);
	}
	return $code;
}
$mimetype=Core_getMimeType(preg_replace('/.*\./', '', $file));
if ($mimetype=='text/css') {
	$parsed=USERBASE.'/ww.cache/css_'.str_replace('/', '|', $file);
	if (!file_exists($parsed) || filectime($parsed)<filectime($file)) {
		$f=file_get_contents($file);
		// { cool stuff
		preg_match_all('/\.([a-z\-]*)\(([^\)]*)\);/', $f, $matches);
		for ($i=0; $i<count($matches[0]); ++$i) {
			switch($matches[1][$i]) {
				case 'linear-gradient': // {
					$bits=preg_split('/, */', $matches[2][$i]);
					$colours=array();
					foreach ($bits as $k=>$v) {
						if (strpos($v, '#')===false) {
							continue;
						}
						$colours[]=CSS_colourCode($v);
					}
					if ($bits[0]=='left') { // horizontal
						$css='background:-moz-linear-gradient(left,#'.$colours[0].' 0%,#'
							.$colours[1].' 100%);'
							.'background:-webkit-gradient(linear,left top,right top,'
							.'color-stop(0%,#'.$colours[0].'), color-stop(100%,#'.$colours[1].'));'
							.'background:-webkit-linear-gradient(left,#'.$colours[0].' 0%,#'
							.$colours[1].' 100%);'
							.'background:-o-linear-gradient(left,#'.$colours[0].' 0%,'
							.'#'.$colours[1].' 100%);'
							.'background: -ms-linear-gradient(left, #'.$colours[0].' 0%,'
							.'#'.$colours[1].' 100%);'
							.'background: linear-gradient(left, #'.$colours[0].' 0%,'
							.'#'.$colours[1].' 100%);'
							.'filter: progid:DXImageTransform.Microsoft.gradient( '
							.'startColorstr=\'#'.$colours[0].'\', '
							.'endColorstr=\'#'.$colours[1].'\',GradientType=1 );';
					}
					else {
						if (count($colours)<2) {
							$css='background: #'.$colours[0].';';
						}
						else {
							$css='background: -moz-linear-gradient(top, #'.$colours[0].' 0%,'
								.'#'.$colours[1].' 100%);'
								.'background: -webkit-gradient(linear, left top, left bottom, '
								.'color-stop(0%,#'.$colours[0].'), color-stop(100%,#'.$colours[1].'));'
								.'background: -webkit-linear-gradient(top, #'.$colours[0].' 0%,#'
								.$colours[1].' 100%);'
								.'background: -o-linear-gradient(top, #'.$colours[0].' 0%,#'
								.$colours[1].' 100%);'
								.'background: -ms-linear-gradient(top, #'.$colours[0].' 0%,#'
								.$colours[1].' 100%);'
								.'background: linear-gradient(top, #'.$colours[0].' 0%,#'
								.$colours[1].' 100%);'
								.'filter: progid:DXImageTransform.Microsoft.gradient( startColorstr=\'#'
								.$colours[0].'\', endColorstr=\'#'.$colours[1].'\',GradientType=0 );';
						}
					}
				break; // }
				case 'border-radius': // {
					$radius=$matches[2][$i];
					$css='-moz-border-radius:'.$radius.';'
						.'-webkit-border-radius:'.$radius.';'
						.'border-radius:'.$radius.';';
				break; // }
				case 'box-shadow': // {
					$rules=$matches[2][$i];
					$css='box-shadow:'.$rules.';'
						.'-moz-box-shadow:'.$rules.';'
						.'-webkit-box-shadow:'.$rules.';';
				break; // }
				case 'rotate': // {
					$degs=$matches[2][$i];
					$css='-moz-transform:rotate('.$degs.');'
						.'-webkit-transform:rotate('.$degs.');'
						.'-o-transform:rotate('.$degs.');'
						.'-ms-transform:rotate('.$degs.');';
				break;
				default:
					$css=$matches[0][$i];
			}
			$f=str_replace($matches[0][$i], $css, $f);
		}
		// }
		$f=str_replace('{{$THEMEDIR}}', '/ww.skins/'.THEME, $f);
		file_put_contents($parsed, $f);
	}
	$file=$parsed;
}

header('Content-Description: File Transfer');
header('Content-Type: '.$mimetype);
header('Content-Transfer-Encoding: binary');
header('Expires-Active: On');
header('Cache-Control: max-age = 99999999');
header('Expires: '. date('r', time()+9999999));
header('Pragma:');
header('Content-Length: ' . filesize($file));
if (ob_get_length()===false) {
	ob_start();
}
ob_clean();
flush();
ob_start();
readfile($file);

Core_flushBuffer('design_file');
