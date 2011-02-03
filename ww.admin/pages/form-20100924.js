function pages_validate(){
	var ok=pages_validate_name();
	if(ok)return true;
	alert('Your form has errors. Hover the mouse over any inputs marked as errors to see explanations of those errors.');
	return false;
}
function pages_validate_name(){
	var $name=$('#name');
	var name=$name.val();
	var errors=[];
	if(name.length<4)errors.push('name must be at least 4 characters in length');
	else{
		if(/^[^0-9\w\u00C0-\u00FF]/.test(name))errors.push('Begins with non-alphanumeric character');
		if(/[^0-9\w\u00C0-\u00FF]$/.test(name))errors.push('Ends with non-alphanumeric character');
		if(name.replace(/[\-',0-9_ \w\u00C0-\u00FF]/g,'')!='')errors.push('Only use alphanumeric characters, spaces, hyphens or underscores');
	}
	if(!errors.length){
		$name[0].className='';
		$name[0].title='';
		return true;
	}
	$name[0].className='error';
	$name[0].title=errors.join('. ');
	return false;
}
$(function(){
	$('.tabs').tabs();
	$('#pages_form select[name=type]').remoteselectoptions({url:'/ww.admin/pages/get_types.php'});
	$('#pages_form select[name=parent]').remoteselectoptions({
		url:'/ww.admin/pages/get_parents.php',
		other_GET_params:page_menu_currentpage
	});
	$('#pages_form').submit(pages_validate);
	$('#name').keyup(pages_validate_name);
	$('form#pages_form').submit(function() {
		return pages_check_page_length($(this).attr('maxLength'))
	});
});
function pages_check_page_length(maxLength) {
	if (!+maxLength) {
		return true;
	}
	var textAreas = $('textarea[name=body]');
	for (i=0; i<textAreas.length; ++i) {
		var contents = $(textAreas[i]).val();
		if (contents.length>maxLength) {
			return confirm(
				'This page has more characters than the set limit. This may cause '
				+'problems\nDo you want to save the page anyway?'
			);
		}
	}
	return true;
}
