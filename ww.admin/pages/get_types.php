<?php
require '../../ww.incs/basics.php';
$selected=isset($_REQUEST['selected'])?$_REQUEST['selected']:0;
foreach($pagetypes as $a){
	$tmp='';
	if(has_access_permissions($a[2]) || !$a[2]){
//		$tmp=(is_int($selected) && $a[0]==$selected)?' selected="selected"':'';
		echo '<option value="'.$a[0].'"'.$tmp.'>'.htmlspecialchars($a[1]).'</option>';
	}
}
$plugin=false;
foreach($PLUGINS as $n=>$p){
	if(isset($p['admin']['page_type'])){
		$tmp='';
		if(!is_int($selected) && $selected==$n){
//			$tmp='" selected="selected';
			$plugin=$p;
		}
		echo '<option value="'.htmlspecialchars($n).$tmp.'">'.htmlspecialchars($n).'</option>';
	}
}
