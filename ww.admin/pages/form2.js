function Page_form(plugin, pageType) {
	var plugin_safe=plugin.charAt(0).toUpperCase()
		+plugin.slice(1).replace(/[^a-zA-Z0-9]/g, '');
	var pageType_safe=pageType.charAt(0).toLowerCase()
		+pageType.slice(1).replace(/[^a-zA-Z0-9]/g, '');
	var fname=plugin_safe+'_Pagetype_'+pageType_safe;
	if (window[fname]) {
		return window[fname]();
	}
	$.ajax({
		'url':'/ww.plugins/'+plugin+'/pagetype-'+pageType_safe+'.js',
		'dataType':'script',
		'success':function(){
			if (!window[fname]) {
				return;
			}
			Page_form(plugin, pageType);
		},
		'error':function() { // failed to load script. submit form instead
			return $('input[name=action]').click();
		}
	});
}
function Page_updatePageVars() {
	if (!window.page_vars) {
		return;
	}
	$.each(page_vars, function(k, v) {
		var $inp=$('input[name="page_vars['+k+']"],select[name="page_vars['+k+']"]');
		if (!$inp.length) {
			$inp=$('<input type="hidden" name="page_vars['+k+']"/>')
				.appendTo('#pages_form');
		}
		if ($.type(v)=='array' || $.type(v)=='object') {
			v=$.toJSON(v);
		}
		$inp.val(v);
	});
}
function pages_check_page_length(maxLength) {
	if (!+maxLength) {
		return true;
	}
	var textAreas = $('textarea[name=body]');
	for (i=0; i<textAreas.length; ++i) {
		var contents = $(textAreas[i]).val();
		if (contents.length>maxLength) {
			return confirm(
				// TODO: translation needed
				'This page has more characters than the set limit. This may cause '
				+'problems\nDo you want to save the page anyway?'
			);
		}
	}
	return true;
}
function pages_validate(){
	var ok=pages_validate_name();
	if (ok) {
		return true;
	}
	// TODO: translation needed
	alert('Your form has errors. Hover the mouse over any inputs marked as errors to see explanations of those errors.');
	return false;
}
function pages_validate_name(){
	return true;
	var $name=$('#name');
	var name=$name.val();
	var errors=[];
	if (name.length<3) {
		// TODO: translation needed
		errors.push('name must be at least 2 characters in length');
	}
	else {
		if (/^[^0-9\w\u00C0-\u00FF]/.test(name)) {
			// TODO: translation needed
			errors.push('Begins with non-alphanumeric character');
		}
		if (/[^0-9\w\u00C0-\u00FF]$/.test(name)) {
			// TODO: translation needed
			errors.push('Ends with non-alphanumeric character');
		}
		if (name.replace(/[\-',\/0-9_ \w\u00C0-\u00FF]/g,'')!='') {
			// TODO: translation needed
			errors.push('Only use alphanumeric characters, spaces, hyphens or underscores');
		}
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
	$('#pages_form select[name=type]').remoteselectoptions({url:'/a/f=adminPageTypesList'});
	$('#pages_form select[name=parent]')
		.remoteselectoptions({url:'/a/f=adminPageParentsList',
			other_GET_params:page_menu_currentpage
		});
	$('#pages_form input[name="special[1]"]').change(function() {
		var checked=$(this).is(':checked');
		if (checked) {
			$('input[name=date_publish]').val('2100-01-01 00:00:00');
			$('input[name=date_unpublish]').val('2100-01-01 00:00:00');
		}
		else {
			$('input[name=date_publish]').val('2000-01-01 00:00:00');
			$('input[name=date_unpublish]').val('2100-01-01 00:00:00');
		}
	});
	$('#pages_form .datetime')
		.datetimepicker({
			dateFormat: 'yy-mm-dd',
			timeFormat: 'hh:mm:ss',
			modal:      true,
			changeMonth:true,
			changeYear: true
		});
	var $form=$('#pages_form')
		.submit(pages_validate)
		.submit(function() {
			Page_updatePageVars();
			return pages_check_page_length($(this).attr('maxLength'))
		})
		.submit(function() { // delay to let tardy updates finish
			$('input[type=submit]').attr('disabled', true);
			if (!$(this).attr('ok-to-go')) {
				$(this).attr('ok-to-go', 1);
				setTimeout(function() {
					$('#pages_form').submit();
				}, 200);
				return false;
			}
			return true;
		});
	$('#name').keyup(pages_validate_name);
	$('select[name=type]')
		.change(function(){
			if (!$('#body-wrapper').length) {
				return $('input[name=action]').click();
			}
			var val=$(this).val();
			Page_form(val.replace(/\|.*/, ''), val.replace(/.*\|/, ''));
		});
	if ($('#body-wrapper').length) {
		$.each(page_vars, function(k, v) {
			if ($('input[name="page_vars['+k+']"],select[name="page_vars['+k+']"]').length) {
				return;
			}
			$('<input type="hidden" name="page_vars['+k+']"/>')
				.val(v)
				.appendTo($form);
		});
		$('select[name=type]').change();
	}
	Core_createTranslatableInputs();
});
