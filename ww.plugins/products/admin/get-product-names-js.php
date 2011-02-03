<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if(!is_admin())die('access denied');

$ps=dbAll('select id,name from products order by name');
$end=count($ps);

echo "product_names=[\n";
for($i=0;$i<$end;++$i){
	echo '	["'.addslashes($ps[$i]['name']).'",'.$ps[$i]['id'].']';
	if($i<$end-1)echo ',';
	echo "\n";
}
echo "];";
