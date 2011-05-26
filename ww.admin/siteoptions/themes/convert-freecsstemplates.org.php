<?php
$failure_message='';
if (!isset($theme_folder)) { // called directly. don't do this.
	exit;
}

// { build template
$dom=new DOMDocument();
$dom->loadHTMLFile($theme_folder.'/index.html');

function empty_element($dom, $id) {
	$el=$dom->getElementById($id);
	if ($el) {
		while ($el->firstChild) {
			$el->removeChild($el->firstChild);
		}
	}
	return $dom;
}

// { figure out header element
$header='';
if ($dom->getElementById('header')) {
	$header='header';
}
else if ($dom->getElementById('logo')) {
	$header='logo';
}
// }
// { figure out col1, col2, content elements
$col1='';
$col2='';
$content='';
if ($dom->getElementById('left') && $dom->getElementById('right')) {
	$col1='left';
	$content='right';
}
else if ($dom->getElementById('columnA')) {
	$col1='columnA';
	$content='columnB';
	if ($dom->getElementById('columnC')) {
		$col2='columnC';
	}
}
else if ($dom->getElementById('colOne')) {
	$col1='colOne';
	$content='colTwo';
	if ($dom->getElementById('colThree')) {
		$col2='colThree';
	}
}
else if ($dom->getElementById('sidebar1')) {
	$col1='sidebar1';
	$content='content';
	if ($dom->getElementById('sidebar2')) {
		$col2='sidebar2';
	}
}
// }

$dom=empty_element($dom, 'menu');
$dom=empty_element($dom, $col1);
if ($col2) {
	$dom=empty_element($dom, $col2);
}
$dom=empty_element($dom, $content);
$dom=empty_element($dom, $header);
$dom=empty_element($dom, 'footer');

$h=$dom->saveHTML();
$h=preg_replace('#(<link.*?href=")([^"]*).css"#', '\1{{$THEMEDIR}}/c/\2.css"', $h);
$h=preg_replace('/\s+/', ' ', $h);
$h=preg_replace('#<!DOCTYPE[^>]*>#', "<!doctype html>\n", $h);
$h=preg_replace('#(<[^>]* id="'.$header.'"[^>]*>)(</div>)#', '\1{{PANEL name="header"}}\2', $h);
$h=preg_replace('#(<[^>]* id="'.$col1.'"[^>]*>)(</div>)#', '\1{{PANEL name="sidebar1"}}\2', $h);
if ($col2) {
	$h=preg_replace('#(<[^>]* id="'.$col2.'"[^>]*>)(</div>)#', '\1{{PANEL name="sidebar2"}}\2', $h);
}
$h=preg_replace('#(<[^>]* id="'.$content.'"[^>]*>)(</div>)#', '\1{{$PAGECONTENT}}\2', $h);
$h=str_replace('<div id="footer"></div>', '<div id="footer">{{PANEL name="footer"}}</div>', $h);
$h=str_replace('<div id="menu"></div>', '<div id="menu">{{MENU direction="horizontal"}}</div>', $h);

$h=preg_replace('#<meta[^>]*>#', '', $h);
$h=preg_replace('#<title[^>]*>[^>]*>#', '', $h);
$h=str_replace('<head>', '<head>{{$METADATA}}', $h);

mkdir($theme_folder.'/h');
file_put_contents($theme_folder.'/h/_default.html', $h);
unlink($theme_folder.'/index.html');
// }
// { design (CSS, etc)
mkdir($theme_folder.'/c');

$files=new DirectoryIterator($theme_folder);
foreach ($files as $file) {
	$n=$file->getFilename();
	if ($file->isDot() || $n=='h' || $n=='license.txt') {
		continue;
	}
	if ($file->getExtension()=='css') {
		$css=file_get_contents($file->getPathname());
		// { menus
		$css=preg_replace('/(#menu1\s*){/', '\1,#menu-fg-0,.fg-menu-container{', $css);
		$css=preg_replace('/(#menu1\s*ul\s*){/', '\1,#menu-fg-0 ul,.fg-menu-container ul{', $css);
		$css=preg_replace('/(#menu1\s*li\s*){/', '\1,#menu-fg-0 li,.fg-menu-container li{', $css);
		$css=preg_replace('/(#menu1\s*a\s*){/', '\1,#menu-fg-0 a,.fg-menu-container a{', $css);
		$css=preg_replace('/(#menu1\s*a:hover\s*){/', '\1,#menu-fg-0 a:hover,.fg-menu-container a:hover{', $css);
		$css.='.fg-menu-container{padding-bottom:0 !important}';
		// }
		file_put_contents($file->getPathname(), $css);
	}
	rename($file->getPathname(), $theme_folder.'/c/'.$n);
}
// }
