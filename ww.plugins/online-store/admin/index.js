window.os_statuses=['Unpaid','Paid','Paid and Delivered'];
function os_invoice(id, print){
	var w=$(window);
	var wh=w.height(),ww=w.width();
	var p=print?'&print=1':'';
	$('<iframe id="externalSite" class="externalSite" src="/ww.plugins/online-store/admin/show-invoice.php?id='+id+p+'" />').dialog({
		autoOpen: true,
		width: ww-100,
		height: wh-100,
		modal: true,
		resizable: true,
		autoResize: true
	}).width(ww-130).height(wh-130);    
}
function os_form_vals(id){
	var w=$(window);
	var wh=w.height(),ww=w.width();
	$('<iframe id="externalSite" class="externalSite" src="/ww.plugins/online-store/admin/show-details.php?id='+id+'" />').dialog({
		autoOpen: true,
		width: ww-100,
		height: wh-100,
		modal: true,
		resizable: true,
		autoResize: true
	}).width(ww-130).height(wh-130);    
}
function os_status(id,current_status){
	var options=[];
	for(var i=0;i<window.os_statuses.length;++i){
		var html='<option value="'+i+'"';
		if(i==current_status){
			html+=' selected="selected"';
		}
		html+='>'+window.os_statuses[i]+'</option>';
		options.push(html);
	}
	var target=$('#os_status_'+id);
	$('<select id="os_status_select_'+id+'">'+options.join('')+'</select>')
		.change(os_status_change)
		.insertAfter(target);
	target.remove();
}
function os_update_fields(force){
	if(!force && !window.ckeditor_body.checkDirty()){
		return;
	}
	var $wrapper=$('#online-stores-fields').empty();
	var $form=$('<div id="online-stores-tester" style="display:none">'+window.ckeditor_body.getData()+'</div>').appendTo($wrapper);
	for(var i in os_fields){
		if(typeof(os_fields[i])!="object"){
			continue;
		}
		os_fields[i].show=0;
	}
	var $inputs=$form.find('input,select,textarea');
	var c=0,to_show=[];
	for(var i=0;i<$inputs.length;++i){
		if(!os_fields[$inputs[i].name]){
			os_fields[$inputs[i].name]={
				required:0
			}
		}
		os_fields[$inputs[i].name].show=1;
		++c;
		to_show.push($inputs[i].name);
	}
	$wrapper.empty();
	if(!c){
		$wrapper.append('<em>no fields defined. please create a form in the Form tab.</em>');
	}
	else{
		var table='<table id="online_stores_fields_table" style="width:100%">'
			+'<tr><th>Name</th><th>Required</th></tr>';
		for(var i=0;i<c;++i){
			table+='<tr><td></td><td></td><td></td></tr>';
		}
		$wrapper.append(table+'</table>');
		var $rows=$wrapper.find('tr');
		for(var i=0;i<c;++i){
			var $row=$($rows[i+1]);
			$row.data('os_name',to_show[i]);
			var $cells=$row.find('td');
			$($cells[0]).text(to_show[i]);
			$(
				'<input class="is-required" type="checkbox"'
				+(os_fields[to_show[i]].required?' checked="checked"':'')
				+' />'
			).appendTo($cells[1]);
		}
	}
	$('<input id="online_stores_fields_input" type="hidden" name="page_vars[online_stores_fields]" />').val(Json.toString(os_fields)).appendTo($wrapper);
}
function os_update_fields_value(){
	var name=$(this).closest('tr').data('os_name');
	if(this.className=='is-required'){
		os_fields[name].required=this.checked?1:0;
	}
	$('#online_stores_fields_input').val(Json.toString(os_fields));
}
function os_status_change(ev){
	var el=ev.target;
	var id=el.id.replace(/os_status_select_/,''),val=+$(el).val();
	$.get('/ww.plugins/online-store/admin/change-status.php?id='+id+'&status='+val,function(){
		$('#os_status_select_'+id).replaceWith(
			$('<a id="os_status_'+id+'" href="javascript:;">'+window.os_statuses[val]+'</a>')
				.click(function(){
					os_status(id,val);
				})
		);
	});
}
$(function(){
	$('.tabs').tabs();
	$('#online-store-status').change(function(ev){
		document.location='/ww.admin/pages/form.php?id='
			+window.page_menu_currentpage+'&online-store-status='
			+$(ev.target).val();
	});
	os_update_fields();
	$('.ui-tabs-nav').live('mousedown',os_update_fields);
	$('form').bind('submit',os_update_fields);
	$("#online_store_redirect_to").remoteselectoptions({
		url:"/ww.admin/pages/get_parents.php"
	});
});
$('#online_stores_fields_table input').live('click',os_update_fields_value);


pandp=[];
pandp_open=[];
function pandp_add_top(i,data){
	var text='hide';
	var name=$('<input id="pandp_name_'+i+'" />')
		.val(data.name || '')
	var constraint=$('<div class="pand-constraint" id="pandp_constraint_wrapper_'+i+'"></div>');
	if(!pandp_open[i]){
		text='show';
		constraint.css('display','none');
	}
	var users_only=$('<input type="checkbox" id="pandp_users_only_'+i+'"'+(data.users_only?' checked="checked"':'')+' title="tick if this postage method is only available to logged-in users" />');
	var opener=$('<a id="pandp_opener_'+i+'" href="javascript:pandp_showhide('+i+','+(pandp_open[i]?0:1)+')">'+text+'</a>');
//	$('<div><a href="javascript:pand_countries_select('+i+')" id="pandp_countries_'+i+'" class="pandp_countries">Countries: '
//		+(data.countries && data.countries.length?join(', ',data.countries):'all')
//		+'</a></div>').appendTo(constraint);
	var row=$('<div class="pandp_row">Postage Name: </div>')
		.append(name)
		.append(users_only)
		.append(opener)
		.append(constraint);
	$('#postage_wrapper').append(row);
	pandp_show_constraints(i, data.constraints || []);
}
function pandp_showhide(i,v){
	pandp_open[i]=v;
	$('#pandp_constraint_wrapper_'+i).css('display',v?'block':'none');
	$('#pandp_opener_'+i)
		.replaceWith('<a id="pandp_opener_'+i+'" href="javascript:pandp_showhide('+i+','+(pandp_open[i]?0:1)+')">'+(pandp_open[i]?'hide':'show')+'</a>');
}
function pand_countries_select(i){
	if(!window.pandp_countries)$.getJSON('/ww.admin/products/postage_countries.php',function(ret){
		window.pandp_countries=ret;
		pandp_countries_select_show(i); 
	});
	else pandp_countries_select_show(i);
}
function pandp_countries_select_show(i){
	if(!window.pandp_countries_dialog){
		var h='<form style="max-height:300px;height:300px;overflow:auto;">';
		var c,i;
		for(i=0;i<window.pandp_countries.length;++i){
			c=window.pandp_countries[i];
			h+='<div><input id="pandp_countries_opt_'+c.iso+'" value="'+c.iso+'" type="checkbox" />'+c.name+'</div>';
		}
		h+='</form>';
		window.pandp_countries_dialog=$(h);
		window.pandp_countries_dialog.dialog({
			autoOpen:false,
			modal:true,
			buttons:{
				'Choose':function(){
				}
			}
		});
	}
	window.pandp_countries_dialog.dialog('open');
}
function pandp_rebuild_constraints(prefix){
	var cstrs=[],el;
	for(var i=0;el=document.getElementById('pandp_constraint_'+prefix+i);++i){
		var cstr={};
		cstr.type=el.value;
		switch(cstr.type){
			case 'set_value': case 'total_less_than_or_equal_to': case 'total_more_than_or_equal_to':
			case 'total_weight_less_than_or_equal_to': case 'total_weight_more_than_or_equal_to': // {
				cstr.value=document.getElementById('pandp_constraint_value_'+prefix+i).value;
				break;
			// }
		}
		if(cstr.type!='set_value')cstr.constraints=pandp_rebuild_constraints(prefix+i+'_');
		cstrs.push(cstr);
	}
	return cstrs;
}
function pandp_rebuild_value_from_top(){
	pandp=[];
	for(var i=0;el=document.getElementById('pandp_constraint_wrapper_'+i);++i){
		var cstr={};
		cstr.name=document.getElementById('pandp_name_'+i).value;
		if(!cstr.name)continue;
		cstr.constraints=pandp_rebuild_constraints(i+'_');
		cstr.users_only=$('#pandp_users_only_'+i).is(':checked');
		pandp.push(cstr);
	}
	$('#postage').val(Json.toString(pandp));
	pandp_rebuild_widget();
}
function pandp_rebuild_widget(){
	$('#postage_wrapper').empty();
	var has_blank=0;
	for(var i=0;i<pandp.length;++i){
		pandp_add_top(i,pandp[i]);
		if(pandp[i].name=='')has_blank=1;
	
	}
	if(!has_blank)pandp_add_top(i,{});
	pandp_showhide(i,'show');
}
function pandp_show_constraints(i, cstrs_old){
	if(cstrs_old.length==0 || cstrs_old[cstrs_old.length-1].type!='set_value')cstrs_old.push({
		type:'set_value',
		value:'0'
	});
	var cstrs=[];
	for(var j=0;j<cstrs_old.length;j++){
		cstrs.push(cstrs_old[j]);
		if(cstrs_old[j].type=='set_value')j=cstrs_old.length;
	}
	var options=[
		['set_value','set postage to'],
		['total_less_than_or_equal_to','if total <='],
		['total_more_than_or_equal_to','if total >='],
		['total_weight_less_than_or_equal_to','if weight <='],
		['total_weight_more_than_or_equal_to','if weight >=']
	];
	var wrapper=$('#pandp_constraint_wrapper_'+i);
	for(var j=0;j<cstrs.length;++j){
		var prefix=j?'else ' : '';
		var cstr=cstrs[j];
		var opts=[],tmp;
		for(k=0;k<options.length;++k){
			tmp='<option value="'+options[k][0]+'"';
			if(options[k][0]==cstr.type)tmp+=' selected="selected"';
			tmp+='>'+prefix+options[k][1]+'</option>';
			opts.push(tmp);
		}
		var selectbox=$('<select id="pandp_constraint_'+i+'_'+j+'">'+opts.join('')+'</select>')
			.change(pandp_rebuild_value_from_top);
		wrapper.append(selectbox);
		switch(cstr.type){
			case 'set_value': // {
				if(!cstr.value)cstr.value=0;
				$('<input id="pandp_constraint_value_'+i+'_'+j+'">')
					.val(cstr.value)
					.appendTo(wrapper);
				break;
			// }
			case 'total_less_than_or_equal_to': case 'total_more_than_or_equal_to':
			case 'total_weight_less_than_or_equal_to': case 'total_weight_more_than_or_equal_to': // {
				if(!cstr.value)cstr.value=0;
				$('<input id="pandp_constraint_value_'+i+'_'+j+'" class="small">')
					.val(cstr.value)
					.appendTo(wrapper);
				break;
			// }
		}
		wrapper.append('<div class="pand-constraint" id="pandp_constraint_wrapper_'+i+'_'+j+'"></div>');
		if(cstr.type!='set_value')pandp_show_constraints(i+'_'+j, cstr.constraints || []);
	}
}
$(function(){
	var p=document.getElementById('postage').value;
	if(p)pandp=eval('{'+p+'}');
	else $('#postage').val('[]');
	pandp_rebuild_widget();
	$('#postage_wrapper').live('change',pandp_rebuild_value_from_top);
	$('#action').mousedown(pandp_rebuild_value_from_top);
});
