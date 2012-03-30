<?php
require_once '../ww.incs/basics.php';

if (!isset($_REQUEST['filename'])) {
	exit;
}
$file=THEME_DIR.'/'.$_REQUEST['filename'];
if (strpos($file, '..')!==false || strpos($file, '/.')!==false) {
	exit;
}

if (!file_exists($file) || !is_file($file)) {
	$file=$DBVARS['theme_dir'].'/'.$_REQUEST['filename'];
	if (!file_exists($file) || !is_file($file)) {
		header("HTTP/1.0 404 Oh No!");
		exit;
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
$mimetype=get_mimetype(preg_replace('/.*\./', '', $file));
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

function get_mimetype($f) {
	$mimetypes = array(
		'ez'=>'application/andrew-inset', 'hqx'=>'application/mac-binhex40',
		'cpt'=>'application/mac-compactpro', 'doc'=>'application/msword',
		'bin'=>'application/octet-stream', 'dms'=>'application/octet-stream',
		'lha'=>'application/octet-stream', 'lzh'=>'application/octet-stream',
		'exe'=>'application/octet-stream', 'class'=>'application/octet-stream',
		'so'=>'application/octet-stream', 'dll'=>'application/octet-stream',
		'oda'=>'application/oda', 'pdf'=>'application/pdf',
		'ai'=>'application/postscript', 'eps'=>'application/postscript',
		'ps'=>'application/postscript', 'smi'=>'application/smil',
		'smil'=>'application/smil', 'mif'=>'application/vnd.mif',
		'xls'=>'application/vnd.ms-excel', 'ppt'=>'application/vnd.ms-powerpoint',
		'wbxml'=>'application/vnd.wap.wbxml', 'wmlc'=>'application/vnd.wap.wmlc',
		'wmlsc'=>'application/vnd.wap.wmlscriptc', 'bcpio'=>'application/x-bcpio',
		'vcd'=>'application/x-cdlink', 'pgn'=>'application/x-chess-pgn',
		'cpio'=>'application/x-cpio', 'csh'=>'application/x-csh',
		'dcr'=>'application/x-director', 'dir'=>'application/x-director',
		'dxr'=>'application/x-director', 'dvi'=>'application/x-dvi',
		'spl'=>'application/x-futuresplash', 'gtar'=>'application/x-gtar',
		'hdf'=>'application/x-hdf', 'js'=>'application/x-javascript',
		'skp'=>'application/x-koan', 'skd'=>'application/x-koan',
		'skt'=>'application/x-koan', 'skm'=>'application/x-koan',
		'latex'=>'application/x-latex', 'nc'=>'application/x-netcdf',
		'cdf'=>'application/x-netcdf', 'sh'=>'application/x-sh',
		'shar'=>'application/x-shar', 'swf'=>'application/x-shockwave-flash',
		'sit'=>'application/x-stuffit', 'sv4cpio'=>'application/x-sv4cpio',
		'sv4crc'=>'application/x-sv4crc', 'tar'=>'application/x-tar',
		'tcl'=>'application/x-tcl', 'tex'=>'application/x-tex',
		'texinfo'=>'application/x-texinfo', 'texi'=>'application/x-texinfo',
		't'=>'application/x-troff', 'tr'=>'application/x-troff',
		'roff'=>'application/x-troff', 'man'=>'application/x-troff-man',
		'me'=>'application/x-troff-me', 'ms'=>'application/x-troff-ms',
		'ustar'=>'application/x-ustar', 'src'=>'application/x-wais-source',
		'xhtml'=>'application/xhtml+xml', 'xht'=>'application/xhtml+xml',
		'zip'=>'application/zip', 'au'=>'audio/basic', 'snd'=>'audio/basic',
		'mid'=>'audio/midi', 'midi'=>'audio/midi', 'kar'=>'audio/midi',
		'mpga'=>'audio/mpeg', 'mp2'=>'audio/mpeg', 'mp3'=>'audio/mpeg',
		'aif'=>'audio/x-aiff', 'aiff'=>'audio/x-aiff', 'aifc'=>'audio/x-aiff',
		'm3u'=>'audio/x-mpegurl', 'ram'=>'audio/x-pn-realaudio',
		'rm'=>'audio/x-pn-realaudio', 'rpm'=>'audio/x-pn-realaudio-plugin',
		'ra'=>'audio/x-realaudio', 'wav'=>'audio/x-wav', 'pdb'=>'chemical/x-pdb',
		'xyz'=>'chemical/x-xyz', 'bmp'=>'image/bmp', 'gif'=>'image/gif',
		'ief'=>'image/ief', 'jpeg'=>'image/jpeg', 'jpg'=>'image/jpeg',
		'jpe'=>'image/jpeg', 'png'=>'image/png', 'tiff'=>'image/tiff',
		'tif'=>'image/tiff', 'djvu'=>'image/vnd.djvu', 'djv'=>'image/vnd.djvu',
		'wbmp'=>'image/vnd.wap.wbmp', 'ras'=>'image/x-cmu-raster',
		'pnm'=>'image/x-portable-anymap', 'pbm'=>'image/x-portable-bitmap',
		'pgm'=>'image/x-portable-graymap', 'ppm'=>'image/x-portable-pixmap',
		'rgb'=>'image/x-rgb', 'xbm'=>'image/x-xbitmap', 'xpm'=>'image/x-xpixmap',
		'xwd'=>'image/x-xwindowdump', 'igs'=>'model/iges', 'iges'=>'model/iges',
		'msh'=>'model/mesh', 'mesh'=>'model/mesh', 'silo'=>'model/mesh',
		'wrl'=>'model/vrml', 'vrml'=>'model/vrml', 'css'=>'text/css',
		'html'=>'text/html', 'htm'=>'text/html', 'asc'=>'text/plain',
		'txt'=>'text/plain', 'rtx'=>'text/richtext', 'rtf'=>'text/rtf',
		'sgml'=>'text/sgml', 'sgm'=>'text/sgml',
		'tsv'=>'text/tab-separated-values', 'wml'=>'text/vnd.wap.wml',
		'wmls'=>'text/vnd.wap.wmlscript', 'etx'=>'text/x-setext',
		'xsl'=>'text/xml', 'xml'=>'text/xml', 'mpeg'=>'video/mpeg',
		'mpg'=>'video/mpeg', 'mpe'=>'video/mpeg', 'qt'=>'video/quicktime',
		'mov'=>'video/quicktime', 'mxu'=>'video/vnd.mpegurl',
		'avi'=>'video/x-msvideo', 'movie'=>'video/x-sgi-movie',
		'ice'=>'x-conference/x-cooltalk'
	);
	$extension = preg_replace('/.*\./', '', $f);
	if (isset($mimetypes[$extension])) {
		return $mimetypes[$extension];
	}
	return 'unknown/mimetype';
}
Core_flushBuffer('design_file');
