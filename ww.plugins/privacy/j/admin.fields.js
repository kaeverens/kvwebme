window.privacy_input_types=['input box','email','textarea','date','checkbox','selectbox','hidden','ccdate'];
function privacyfieldsAddRow(){
	privacyfieldElements++;
	$('<li><table width="100%"><tr><td width="30%"><input name="page_vars[privacy_extra_fields]['+privacyfieldElements+'][name]" /></td><td width="30%"><select name="page_vars[privacy_extra_fields]['+privacyfieldElements+'][type]"><option>'+privacy_input_types.join('</option><option>')+'</option></select></td><td width="10%"><input type="checkbox" name="page_vars[privacy_extra_fields]['+privacyfieldElements+'][is_required]" /></td><td></td></tr></table></li>').appendTo($('#privacy_fields'));
	$('#privacy_fields').sortable();
	$('#privacy_fields input,#privacy_fields select,#privacy_fields textarea').bind('click.sortable mousedown.sortable',function(ev){
		ev.target.focus();
	});
}
function privacyfieldsChange(e){
}
function privacy_export(id){
	if (!id) {
		return alert('cannot export from an empty privacy database');
	}
	if (!(+$('select[name="page_vars\\[privacys_record_in_db\\]"]').val())) {
		return alert('this privacy doesn\'t record to database');
	}
	var d=$('#export_from').val();
	document.location='/ww.plugins/privacys/j/export.php?date='+d+'&id='+id;
}
if (!privacyfieldElements) {
	var privacyfieldElements=0;
}
$(function(){
	$('.tabs').tabs();
	privacyfieldsAddRow();
	$('.date').datepicker({dateFormat:'yy-m-d'});
});
