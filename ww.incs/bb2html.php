<?php

// A simple FAST parser to convert BBCode to HTML
// Trade-in more restrictive grammar for speed and simplicty
//
// (please do not remove credit)
// author: Louai Munajim
// website: http://elouai.com
// date: 2004/Apr/18


function bb2html($text) {
	$text=preg_replace(
		'#\[url\]([^\[]*)\[/url]#',
		'<a href="\1">\1</a>',
		htmlspecialchars($text)
	);
  $bbcode = array(
		"[list]", "[*]", "[/list]", 
		"[img]", "[/img]", 
		"[b]", "[/b]", 
		"[u]", "[/u]", 
		"[i]", "[/i]",
		'[color="', "[/color]",
		"[size=\"", "[/size]",
		'[url="', "[/url]",
		"[mail=\"", "[/mail]",
		"[code]", "[/code]",
		"[quote]", "[/quote]",
		'"]'
	);
  $htmlcode = array(
		"<ul>", "<li>", "</ul>", 
		"<img src=\"", "\">", 
		"<b>", "</b>", 
		"<u>", "</u>", 
		"<i>", "</i>",
		"<span style=\"color:", "</span>",
		"<span style=\"font-size:", "</span>",
		'<a href="', "</a>",
		"<a href=\"mailto:", "</a>",
		"<code>", "</code>",
		"<table width=100% bgcolor=lightgray><tr><td bgcolor=white>", "</td></tr></table>",
		'">'
	);
  $newtext = str_replace($bbcode, $htmlcode, $text);
  $newtext = nl2br($newtext);//second pass
  return $newtext;
}
