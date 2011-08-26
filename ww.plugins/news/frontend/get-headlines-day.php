<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';

$y=(int)$_REQUEST['y'];
$m=(int)$_REQUEST['m'];
$d=(int)$_REQUEST['d'];
$p=(int)$_REQUEST['p'];
if( $y<1000 || $y>9999 || $m<1 || $m>12 || $d<1 || $d>31) {
	exit;
}
$m=sprintf('%02d', $m);

$sql='select id from pages where parent='.$p.' and associated_date="'.$y.'-'.$m.'-'.$d.'" order by associated_date';
$ps=dbAll($sql);
$headlines=array();
foreach ($ps as $p) {
	$page=Page::getInstance($p['id']);
	$headlines[]=array(
		'url'=>$page->getRelativeURL(),
		'adate'=>$page->associated_date,
		'headline'=>htmlspecialchars($page->name)
	);
}
echo json_encode($headlines);
