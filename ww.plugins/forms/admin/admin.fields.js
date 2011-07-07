window.form_input_types=['input box', 'email', 'textarea', 'date',
	'checkbox', 'selectbox', 'hidden', 'ccdate', 'html-block', 'page-next',
	'page-previous', 'page-break', 'file'];
function formfieldsAddRow(){
	formfieldElements++;
	$('<li><table width="100%"><tr><td width="20%"><input name="formfieldElementsName['+formfieldElements+']" /></td><td width="20%"><select name="formfieldElementsType['+formfieldElements+']"><option>'+form_input_types.join('</option><option>')+'</option></select></td><td width="10%"><input type="checkbox" name="formfieldElementsIsRequired['+formfieldElements+']" /></td><td></td></tr></table></li>').appendTo($('#form_fields'));
	$('#form_fields').sortable();
	$('#form_fields input,#form_fields select,#form_fields textarea').bind('click.sortable mousedown.sortable',function(ev){
		ev.target.focus();
	});
}
function form_export(id){
	if (!id) {
		return alert('cannot export from an empty form database');
	}
	if (!(+$('select[name="page_vars\\[forms_record_in_db\\]"]').val())) {
		return alert('this form doesn\'t record to database');
	}
	var d=$('#export_from').val();
	document.location='/ww.plugins/forms/admin/export.php?date='+d+'&id='+id;
}
if (!formfieldElements) {
	var formfieldElements=0;
}
$(function(){
	function form_updateReplyto(){
		var $r=$('#form-replyto'),old=$r.val(),names=[];
		$('#form_fields select').each(function(){
			var val=$(this).val(),name;
			if (val!='email') {
				return;
			}
			name=$(this).closest('tr').find('input')[0].value;
			if (name=='') {
				return;
			}
			names.push(name);
		});
		$r
			.html('<option>'+names.join('</option><option>')+'</option>')
			.val(old);
	}
	formfieldsAddRow();
	$('.date').datepicker({dateFormat:'yy-m-d'});
	$('#tabs').tabs();
	$('#pages_form').submit(function(){
		form_updateReplyto();
		if (!$('#form-replyto').val()) {
			alert('the Reply-To on the Main Details tab must correspond to an Email field in the Form Fields tab');
			return false;
		}
	});
	$('#form_fields select').live('change',form_updateReplyto);
	$('.forms-textarea').each(function(){
		var $this=$(this);
		var $parent=$this.closest('td');
		var vals=$this.val();
		if (!vals) {
			vals='0,0';
		}
		vals=vals.split(/,/);
		var html='max characters: <input value="'+vals[0]+'" class="small"/>, '
			+'warn after: <input value="'+vals[1]+'" class="small"/>';
		$parent.append(html)
			.find('input').change(function(){
				var $inps=$parent.find('input');
				$this.val($($inps[1]).val()+','+$($inps[2]).val());
			});
	});
	$('.file-inputs').each(function(){
		var $this=$(this);
		var $parent=$this.closest('td');
		var vals=$this.val();
		if (!vals) {
			vals='on:*.jpg;*.gif;*.png;*.jpeg;*.doc;*.xls;*.txt;*.odt;*.zip;*.xlsx;*.docx;*.mp3;*.ogg;*.wav;*.acc';
		}
		vals=vals.split(/:/);
		var html='Allow multiple file uploads: <input type="checkbox" value="on"'
			+((vals[0]=='on')?' checked="checked"':'')+'/>, '
			+'Allowed file extentions: <input type="text" value="'+vals[1]+'"/>';
		$parent.append(html)
			.find('input').change(function(){
				var $inps=$parent.find('input');
				var check=($($inps[1]).is(':checked'))?'on':'off';
				$this.val(check+':'+$($inps[2]).val());
			});
	});
});
