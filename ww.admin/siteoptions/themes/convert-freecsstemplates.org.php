<?php
/**
	* convert a freecsstemplates.org theme
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

// { Theme_emptyElement

/**
	* function for removing all content from an element
	*
	* @param object $dom the DOM object
	* @param string $id  the ID of the object to clear
	*
	* @return object the amended DOM object
	*/
function Theme_emptyElement($dom, $id) {
	$el=$dom->getElementById($id);
	if ($el) {
		while ($el->firstChild) {
			$el->removeChild($el->firstChild);
		}
	}
	return $dom;
}

// }

$failure_message='';
if (!isset($theme_folder)) { // called directly. don't do this.
	Core_quit();
}

// { build template
$dom=new DOMDocument();
$dom->loadHTMLFile($theme_folder.'/index.html');


// { figure out header element
$header='';
$d=$dom->getElementById('header');
if ($d) {
	$header='header';
	$dhtml=$d->ownerDocument->saveXML($d); 
	if (strpos($dhtml, 'id="logo"') && strpos($dhtml, 'id="menu"')) {
		$header='logo';
	}
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
else if ($dom->getElementById('sidebar')) {
	$col1='sidebar';
	if ($dom->getElementById('main')) {
		$content='main';
	}
	else {
		$content='content';
	}
}
// }
// { footer columns
$fcol1='';
$fcol2='';
$fcol3='';
if ($dom->getElementById('column1')) {
	$fcol1='column1';
}
if ($dom->getElementById('column2')) {
	$fcol2='column2';
}
if ($dom->getElementById('column3')) {
	$fcol3='column3';
}

$dom=Theme_emptyElement($dom, 'menu');
$dom=Theme_emptyElement($dom, $col1);
if ($col2) {
	$dom=Theme_emptyElement($dom, $col2);
}
$dom=Theme_emptyElement($dom, $content);
$dom=Theme_emptyElement($dom, $header);
$dom=Theme_emptyElement($dom, 'footer');
$dom=Theme_emptyElement($dom, $fcol1);
$dom=Theme_emptyElement($dom, $fcol2);
$dom=Theme_emptyElement($dom, $fcol3);

$h=$dom->saveHTML();
$h=preg_replace(
	'#(<link.*?href=")([^"]*).css"#',
	'\1{{$THEMEDIR}}/c/\2.css"',
	$h
);
$h=preg_replace('/\s+/', ' ', $h);
$h=preg_replace('#<!DOCTYPE[^>]*>#', "<!doctype html>\n", $h);
$h=preg_replace(
	'#(<[^>]* id="'.$header.'"[^>]*>)(</div>)#',
	'\1{{PANEL name="header"}}\2',
	$h
);
$h=preg_replace(
	'#(<[^>]* id="'.$col1.'"[^>]*>)(</div>)#',
	'\1{{PANEL name="sidebar1"}}\2',
	$h
);
if ($col2) {
	$h=preg_replace(
		'#(<[^>]* id="'.$col2.'"[^>]*>)(</div>)#',
		'\1{{PANEL name="sidebar2"}}\2',
		$h
	);
}
if ($fcol1) {
	$h=preg_replace(
		'#(<[^>]* id="'.$fcol1.'"[^>]*>)(</div>)#',
		'\1{{PANEL name="footer-column1"}}\2',
		$h
	);
}
if ($fcol2) {
	$h=preg_replace(
		'#(<[^>]* id="'.$fcol2.'"[^>]*>)(</div>)#',
		'\1{{PANEL name="footer-column2"}}\2',
		$h
	);
}
if ($fcol3) {
	$h=preg_replace(
		'#(<[^>]* id="'.$fcol3.'"[^>]*>)(</div>)#',
		'\1{{PANEL name="footer-column3"}}\2',
		$h
	);
}
$h=preg_replace(
	'#(<[^>]* id="'.$content.'"[^>]*>)(</div>)#',
	'\1{{$PAGECONTENT}}\2',
	$h
);
$h=str_replace(
	'<div id="footer"></div>',
	'<div id="footer">{{PANEL name="footer"}}</div>',
	$h
);
$h=str_replace(
	'<div id="menu"></div>',
	'<div id="menu">{{MENU direction="horizontal"}}</div>',
	$h
);
$h=preg_replace(
	'#<div style="clear: both;">[^<]*</div>#',
	'<div style="clear: both;"></div>',
	$h
);
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
		$css=preg_replace(
			'/(#menu1\s*){/', // }
			'\1,#menu-fg-0,.fg-menu-container{', // }
			$css
		);
		$css=str_replace(array("\n", "\r", "\t"), ' ', $css);
		$css=preg_replace('/\s+/', ' ', $css);
		$css=preg_replace(
			'/(#menu1\s*ul\s*){/', // }
			'\1,#menu-fg-0 ul,.fg-menu-container ul{', // }
			$css
		);
		$css=preg_replace(
			'/(#menu1\s*li\s*){/', // }
			'\1,#menu-fg-0 li,.fg-menu-container li{', // }
			$css
		);
		$css=preg_replace(
			'/(#menu1\s*a\s*){/', // }
			'\1,#menu-fg-0 a,.fg-menu-container a{', // }
			$css
		);
		$css=preg_replace(
			'/(#menu1\s*a:hover\s*){/',
			'\1,#menu-fg-0 a:hover,.fg-menu-container a:hover{',
			$css
		);
		$css=preg_replace(
			'/(#menu\s+a\s*{[^}]*)display\s*:\s*block\s*;/',
			'\1display:inline-block;',
			$css
		);
		$css=preg_replace('#}\s*#', "}\n", $css);
		$css=preg_replace('#/\*#', "\n/*", $css);
		$css=preg_replace('#\*/#', "*/\n", $css);
		$css=str_replace('.current_page_item a', 'a.ajaxmenu_currentPage', $css);
		$css=str_replace('li.current_page_item', 'a.ajaxmenu_currentPage', $css);
		$css.='.fg-menu-container{padding-bottom:0 !important}';
		// }
		file_put_contents($file->getPathname(), $css);
	}
	@rename($file->getPathname(), $theme_folder.'/c/'.$n);
}
// }
