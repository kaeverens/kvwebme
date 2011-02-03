<?php
$html.='<form action="#"><input name="search" class="search';
if(isset($_REQUEST['search']))$html.='" value="'.htmlspecialchars($_REQUEST['search']);
else $html.=' empty" value="search';
$html.='" /></form>';
$html.='<style type="text/css">input.search{background:url(/i/search.png) white no-repeat right;}input.search.empty{color:#999;font-style:italic}</style>';
WW_addScript('/ww.plugins/search/j/js.js');
