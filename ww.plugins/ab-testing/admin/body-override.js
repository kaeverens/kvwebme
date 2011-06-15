$(function(){
	$('select[name="page_vars[abtesting-target]"]').remoteselectoptions({
		url:'/ww.admin/pages/get_parents.php',
	});
});
