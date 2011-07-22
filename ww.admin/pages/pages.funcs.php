<?php
function selectkiddies($i=0, $n=1, $s=0, $id=0) {
	$q=dbAll(
		'select name,id,alias from pages where parent="'.$i.'" and id!="'.$id
		.'" order by ord,name'
	);
	if (count($q)<1) {
		return;
	}
	foreach ($q as $r) {
		if ($r['id']!='') {
			echo '<option value="'.$r['id'].'" title="'
				.htmlspecialchars($r['name']).'"';
			echo ($s==$r['id'])?' selected="selected">':'>';
			echo str_repeat('&raquo; ', $n);
			$name=$r['alias'];
			if (strlen($name)>20) {
				$name=substr($name, 0, 17).'...';
			}
			echo htmlspecialchars($name).'</option>';
			selectkiddies($r['id'], $n+1, $s, $id);
		}
	}
}
function showshortcuts($id, $parent) {
	$q=dbAll(
		'select id,name from pages where parent="'.$parent
		.'" order by ord desc,name'
	);
	if (count($q)) {
		echo '<ul>';
		foreach ($q as $r) {
			echo '<li>';
			echo '<input name="shortcuts['.$r['id'].']" type="checkbox"/>';
			$r2=dbRow(
				'select id,name from pagelinks where fromid="'.$id.'" and toid="'
				.$r['id'].'"'
			);
			if (count($r2)) {
				echo ' checked="checked"';
				$r['name']=$r2['name'];
			}
			echo ' />';
			echo '<input name="shortcutsName['.$r['id'].']" value="'
				.htmlspecialchars($r['name']).'"/>';
			showshortcuts($id, $r['id']);
			echo '</li>';
		}
		echo '</ul>';
	}
}
/**
 * transcribe
 *
 * replaces accented characters with their
 * non-accented equivellants
 */
function transcribe($string) {
    $a = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞ
ßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ';
    $b = 'aaaaaaaceeeeiiiidnoooooouuuuy
bsaaaaaaaceeeeiiiidnoooooouuuyybyRr';
    $string = utf8_decode($string);    
    $string = strtr($string, utf8_decode($a), $b);
    $string = strtolower($string);
    return utf8_encode($string);
} 
