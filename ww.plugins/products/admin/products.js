$('a.delete-product').live('click', function(){
	var $tr=$(this).closest('tr');
	var id=$tr[0].id.replace(/product-row-/, '');
	var name=$tr.find('td.edit-link>a').text();
	if (!confirm(
		'are you want to delete the product "'+name+'"?'
	)) {
		return;
	};
	document.location='/ww.admin/plugin.php?_plugin=products&_page=products'
		+'&delete='+id;
});
