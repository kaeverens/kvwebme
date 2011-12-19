<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if(!Core_isAdmin())die('access denied');

$ps=dbAll('select id,name from products order by name');
$end=count($ps);

echo "product_names=[\n";
for($i=0;$i<$end;++$i){
	echo '	["'.addslashes(__FromJson($ps[$i]['name'])).'",'.$ps[$i]['id'].']';
	if($i<$end-1)echo ',';
	echo "\n";
}
echo "];";
