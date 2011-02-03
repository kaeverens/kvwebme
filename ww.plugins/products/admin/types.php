<?php
if(!is_admin())exit;
if(isset($_REQUEST['delete']) && is_numeric($_REQUEST['delete'])){
	dbQuery('delete from products_types where id='.$_REQUEST['delete']);
	echo '<em>Product Type deleted.</em>';
}
$rs=dbAll('select id,name from products_types order by name');
if(!count($rs)){
	echo '<em>No existing product types. <a href="plugin.php?_plugin=products&amp;_page=types-edit">Click here to create one</a>.</em>';
}
else{
	echo '<a href="plugin.php?_plugin=products&amp;_page=types-edit">Create a new type</a> ';
	echo '<form method="post" style="display:inline" action="plugin.php?_plugin=products&amp;_page=types-edit">or <select name="from">';
	foreach($rs as $r){
		echo '<option value="'.$r['id'].'">'.htmlspecialchars($r['name']).'</option>';
	}
	echo '</select> <input type="submit" value="Copy Type" /></form>';
	echo '<br /><br /><div style="width:50%"><table class="datatable"><thead><tr><th>Name</th><th>&nbsp;</th></tr></thead><tbody>';
	foreach($rs as $r){
		echo '<tr><td class="edit-link"><a href="plugin.php?_plugin=products&amp;_page=types-edit&amp;id='.$r['id'].'">'.htmlspecialchars($r['name']).'</td><td>';
		echo '<a href="'.$_url.'&delete='.$r['id'].'" onclick="return confirm(\'are you sure you want to delete the '.htmlspecialchars($r['name']).' product type?\')" title="delete">[x]</a>';
		echo '&nbsp;';
		echo '</td></tr>';
	}
	echo '</tbody></table></div>';
}
echo '<p>Product Types describe what characteristics a class of products has. For example, a book would have an author, ISBN, etc., while a house would have an address, number of bedrooms, etc.</p>';
