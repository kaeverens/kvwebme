$(function(){
	function products_data_fields_setup(){
		var ta=$('#data_fields');
		var data=ta.val();
		if(data=='')data='[]';
		window.data_fields=eval(data);
		ta.css('display','none');
		$('<div id="data_fields_rows"/>').insertAfter(ta);
		products_data_fields_redraw();
	}
	function products_data_fields_add_row(rdata,i){
		// { internal name
		var row='<li><table width="100%"><tr><td width="20%"><input class="product-type-fd-name" id="product_type_fd'+i+'_name" value="'+htmlspecialchars(rdata.n)+'" /></td>';
		// }
		// { displayed name
		if (!rdata.ti) {
			rdata.ti=rdata.n
		}
		row+='<td width="20%"><input class="product-type-fd-title" id="product_type_fd'+i+'_title" value="'+htmlspecialchars(rdata.ti)+'" /></td>';
		// }
		// { type
		row+='<td width="20%"><select id="product_type_fd'+i+'_type">';
		var types=['inputbox','textarea','date','checkbox','selectbox'];
		for(var j=0;j<types.length;++j){
			row+='<option value="'+types[j]+'"';
			if(types[j]==rdata.t)row+=' selected="selected"';
			row+='>'+types[j]+'</option>';
		}
		row+='</select></td>';
		// }
		// { searchable
		row+='<td><input class="product-type-fd-searchable" type="checkbox"';
		if(rdata.s)row+=' checked="checked"';
		row+=' /></td>';
		// }
		// { required
		row+='<td><input class="product-type-fd-required" type="checkbox"';
		if(rdata.r)row+=' checked="checked"';
		row+=' /></td>';
		// }
		// { user-entered
		row+='<td><input class="product-type-fd-user-entered" type="checkbox"';
		if(rdata.u)row+=' checked="checked"';
		row+=' /></td>';
		// }
		// { extra
		if(rdata.t=='selectbox'){
			row+='<td width="20%"><textarea id="product_type_fd'+i+'_extra" class="small">'+htmlspecialchars(rdata.e)+'</textarea></td>';
		}
		else {
			row+='<td width="20%">&nbsp;</td>';
		}
		// }
		row+='</tr></table></li>';
		return row;
	}
	function products_data_fields_redraw(){
		var wrapper=$('#data_fields_rows');
		wrapper.empty();
		table='<table width="100%"><tr><th width="20%">Internal Name</th>'
			+'<th width="20%">Displayed Name</th><th width="20%">Type</th>'
			+'<th>Searchable</th><th>Required</th><th>User-entered</th>'
			+'<th width="20%">Extra</th></tr></table><ul id="product_type_rows">';
		var rows=0;
		$.each(window.data_fields,function(i,rdata){
			table+=products_data_fields_add_row(rdata,rows++);
		});
		table+=products_data_fields_add_row({n:''},rows);
		table=$(table+'</ul>');
		table.appendTo(wrapper);
		$('#product_type_rows').sortable({
			"update":products_data_fields_reset_value
		});
		$('input.product-type-fd-name',table).change(function(){
			products_data_fields_reset_value();
			products_data_fields_redraw();
		});
		$('#data_fields_rows .product-type-fd-name').each(function(){
			products_data_check_field_name(this);
		});
	}
	function products_data_fields_reset_value(){
		var vals=[];
		var rows=$("#product_type_rows tr");
		rows.each(function(){
			var $this=$(this),n=$this.find('.product-type-fd-name').val();
			if(n=='')return;
			vals.push({
				"n":n,
				"ti":$this.find('.product-type-fd-title').val(),
				"t":$this.find('select').val(),
				"s":$this.find('.product-type-fd-searchable')[0].checked?1:0,
				"r":$this.find('.product-type-fd-required')[0].checked?1:0,
				"u":$this.find('.product-type-fd-user-entered')[0].checked?1:0,
				"e":$this.find('textarea.small').val()
			});
		});
		$('#data_fields').val(Json.toString(vals));
		window.data_fields=vals;
	}
	function products_data_check_field_name(el){
		var name=$(el).val();
		var errors=[];
		if(name.replace(/[^a-zA-Z0-9_]/,'')!==name)errors.push('please only use letters a-z and underscores _');
		if(name.toLowerCase()!==name)errors.push('please only use lowercase letters');
		if(errors.length){
			el.title=errors.join(', ');
			el.className="product-type-fd-name error";
		}
		else{
			el.className="product-type-fd-name";
			el.title='';
		}
	}
	function products_validate_form(){
		if($('#data_fields_rows .product-type-fd-name.error').length){
			alert("one or more field names has an error\nhover your mouse over the field name to get an explanation");
			return false;
		}
		return true;
	}
	products_data_fields_setup();
	$(".tabs").tabs();
	$('input[type=submit]').mousedown(products_data_fields_reset_value);
	$('div.has-left-menu>form').submit(products_validate_form);
	$('#data_fields_rows .product-type-fd-name').live('keyup',function(){
		products_data_check_field_name(this);
	});
});
