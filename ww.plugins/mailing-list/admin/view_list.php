<?php
/*
	Webme Mailing List Plugin v0.2
	File: admin/view_list.php
	Developer: Conor Mac Aoidh <http://macaoidh.name>
	Report Bugs: <conor@macaoidh.name>
*/

$fields=dbAll('select name,value from mailing_list_options');
foreach($fields as $field){
	$FIELD[$field['name']]=$field['value'];
}

echo '<link rel="stylesheet" type="text/css" href="/ww.plugins/mailing-list/files/mailing-list.css"/>';

if($FIELD['use_js']==1)
echo '
<script type="text/javascript" src="/ww.plugins/mailing-list/files/jquery.tablesorter.min.js"></script> 
<script type="text/javascript">
$(table_sorter);
function table_sorter(){ 
	$(".tablesorter").tablesorter();
	$(".tablesorter tbody tr td a:first-child")
	.click(delete_row);
} 
function delete_row(){
	var id=this.href.replace(/.*!/,"");
	if(confirm("Are you sure you want to delete this email from the list?")){
		$.getJSON("/ww.admin/plugin.php?_plugin=mailing-list&_page=index&mailing_list=delete&id="+id);
		$(this).parent().parent().fadeOut("slow",function(){
			$(this).remove();
		});
	}
	return false;
}
</script>
';

function get_contents ($type, $status, $name, $mobile) {
	global $FIELD;
	$f='';
	if ($type=='headers') {
		if ($FIELD['col_name']==1) {
			$f.='<th>Name</th>';
		}
		if ($FIELD['col_mobile']==1) {
			$f.='<th>Mobile</th>';
		}
		if ($FIELD['dis_pend']==1) {
			$f.='<th>Status</th>';
		}
		return $f;
	}
	else if ($type=='columns') {
		if ($FIELD['col_name']==1) {
			$f.='<td id="name">'.htmlspecialchars($name).'</td>';
		}
		if ($FIELD['col_mobile']==1) {
			$f.='<td id="mobile">'.htmlspecialchars($mobile).'</td>';
		}
		if ($FIELD['dis_pend']==1) {
			$f.='<td id="status">'.htmlspecialchars($status).'</td>';
		}
		return $f;
	}
}

if($FIELD['dis_pend']==1) $lists=dbAll('select * from mailing_list');
else $lists=dbAll('select * from mailing_list where status="activated"');

echo '<h3>Email List</h3>';

if(isset($deleted)) echo '<em>'.$deleted.'</em>';

echo '<table class="tablesorter">';

if(count($lists)==0) echo '<tr><td colspan="4">No subscriptions yet!</td></tr>';
else echo	'<thead><tr><th>Num</th><th>Email</th>'.get_contents('headers').'<th>Delete</th></tr></thead>';

echo '<tbody>';

$num='';
foreach($lists as $list){
	$num++;
	echo '<tr><td id="num">'.$num.'</td><td id="email">'.htmlspecialchars($list['email']).'</td>'
		.get_contents('columns', $list['status'], $list['name'], $list['mobile'])
		.'<td id="delete"><a href="'.$_url.'&mailing_list=delete&id=!'
		.htmlspecialchars($list['id']).'">[x]</a></td></tr>';
}

echo '</tbody></table>';
?>
