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
$dom=empty_element($dom, 'menu');
$dom=empty_element($dom, 'left');
$dom=empty_element($dom, 'columnA');
$dom=empty_element($dom, 'right');
$dom=empty_element($dom, 'columnB');
$dom=empty_element($dom, 'header');
$dom=empty_element($dom, 'footer');
$dom=empty_element($dom, 'sidebar');
$dom=empty_element($dom, 'sidebar1');
$dom=empty_element($dom, 'sidebar2');

$h=$dom->saveHTML();
$h=preg_replace('#(<link.*?href=")([^"]*).css"#', '\1{{$THEMEDIR}}/\2.css"', $h);
$h=preg_replace('#<!DOCTYPE[^>]*>#', "<!doctype html>\n", $h);
$h=str_replace('<div id="header"></div>', '<div id="header">{{PANEL name="header"}}</div>', $h);
$h=str_replace('<div id="right"></div>', '<div id="right">{{PANEL name="right"}}</div>', $h);
$h=str_replace('<div id="columnB"></div>', '<div id="columnB">{{PANEL name="right"}}</div>', $h);
$h=str_replace('<div id="sidebar"></div>', '<div id="right">{{PANEL name="right"}}</div>', $h);
$h=str_replace('<div id="sidebar1"></div>', '<div id="right">{{PANEL name="left"}}</div>', $h);
$h=str_replace('<div id="sidebar2"></div>', '<div id="right">{{PANEL name="right"}}</div>', $h);
$h=str_replace('<div id="footer"></div>', '<div id="footer">{{PANEL name="footer"}}</div>', $h);
$h=str_replace('<div id="menu"></div>', '<div id="menu">{{MENU direction="horizontal"}}</div>', $h);
$h=str_replace('<div id="left"></div>', '<div id="left">{{$PAGECONTENT}}</div>', $h);
$h=str_replace('<div id="columnA"></div>', '<div id="columnA">{{$PAGECONTENT}}</div>', $h);

$h=preg_replace('#<meta[^>]*>#', '', $h);
$h=preg_replace('#<title[^>]*>[^>]*>#', '', $h);
$h=str_replace('<head>', '<head>{{$METADATA}}', $h);

mkdir ($theme_folder.'/h');
file_put_contents($theme_folder.'/h/_default.html', $h);
unlink($theme_folder.'/index.html');
// }
