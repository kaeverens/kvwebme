$(function(){
	var currencies_cell=$('#currencies');
	var i=0,html='<table>'
		+'<tr><th>'+__('Name')+'</th><th>'+__('ISO')+'</th><th>'+__('Symbol')+'</th><th>'+__('Value')+'</th>'
		+'<th>'+__('Delete')+'</th></tr></table><ul id="os-currencies-ul">';
	for (;i<os_currencies.length;++i) {
		var cur=os_currencies[i];
		html+='<li id="os-currency-'+cur.iso+'"><table><tr>'
			+'<td>'+cur.name+'<input type="hidden" name="os-currencies_name[]"'
			+' value="'+htmlspecialchars(cur.name)+'" /></td>'
			+'<td>'+cur.iso+'<input type="hidden" name="os-currencies_iso[]"'
			+' value="'+htmlspecialchars(cur.iso)+'" /></td>'
			+'<td>'+cur.symbol+'<input type="hidden" name="os-currencies_symbol[]"'
			+' value="'+htmlspecialchars(cur.symbol)+'" /></td>'
			+'<td><input name="os-currencies_value[]" value="'
			+cur.value+'" size="4" /></td>'
			+'<td><a href="javascript:os_deleteCurrency(\''
			+cur.iso+'\')">[x]</a></td>'
			+'</tr></table></li>';
	}
	html+='</ul><a href="javascript:os_addCurrency();">'+__('Add a currency')+'</a>';
	$(html).appendTo(currencies_cell);
	os_setSortable();
	$('.accordion')
		.accordion({
			'autoHeight':false,
			'collapsible':true,
			'active':true
		});
});
function os_addCurrency(){
	/* TODO - the ability to add custom currencies please */
	var currencies=[
		['Euro','EUR','€',1],
		['Sterling','GBP','£',1.14342],
		['US Dollar','USD','$',.71252],
		['Dansk Krone', 'DKK', 'DKK',.13427]
	];
	var html='';
	var $existing=$('input[name="os-currencies_iso[]"]');
	for (var i=0;i<currencies.length;++i) {
		var ok=1, j=0;
		for (;j<$existing.length;++j) {
			if ($existing[j].value==currencies[i][1]) {
				ok=0;
			}
		}
		if (!ok) {
			continue;
		}
		html+='<option value="'+i+'">'+currencies[i][0]+'</option>';
	}
	if (html=='') {
		return alert(
			__('No more currencies installable.\nIf the currency you wanted was not installable, please contact your administrator')
		);
	}
	var $dialog=$(
		'<div><p>'+__('Please choose a currency from the list below')+'</p>'
		+'<select id="os-currency-chooser">'+html+'</select>'
	).dialog({
		"close": function() {
			$(this).remove();
		},
		"modal": true,
		"buttons": {
		/* TODO - translation */
			"Save": function() {
				var cur=currencies[+$('#os-currency-chooser').val()];
				cur={
					"name":cur[0],
					"iso":cur[1],
					"symbol":cur[2],
					"value":cur[3]
				}
				os_unsetSortable();
				var html='<li id="os-currency-'+cur.iso+'"><table><tr>'
					+'<td>'+cur.name+'<input type="hidden" name="os-currencies_name[]"'
					+' value="'+htmlspecialchars(cur.name)+'" /></td>'
					+'<td>'+cur.iso+'<input type="hidden" name="os-currencies_iso[]"'
					+' value="'+htmlspecialchars(cur.iso)+'" /></td>'
					+'<td>'+cur.symbol+'<input type="hidden" name="os-currencies_symbol[]"'
					+' value="'+htmlspecialchars(cur.symbol)+'" /></td>'
					+'<td><input name="os-currencies_value[]" value="'
					+cur.value+'" size="4" /></td>'
					+'<td><a href="javascript:os_deleteCurrency(\''
					+cur.iso+'\')">[x]</a></td>'
					+'</tr></table></li>';
				$('#os-currencies-ul')
					.append(html);
				os_setSortable();
				$dialog.remove();
			}
		}
	});
}
function os_deleteCurrency(iso) {
	if($('#os-currencies-ul>li').length<2) {
		// TODO: translation needed
		return alert('Cannot remove default currency');
	}
	$('#os-currency-'+iso)
		.fadeOut(500,function(){
			$(this).remove();
			os_resetConversionRates();
		});
}
function os_setSortable() {
	$('#os-currencies-ul')
		.sortable({
			"stop": os_resetConversionRates
		})
}
function os_unsetSortable() {
	$('#os-currencies-ul')
		.sortable("destroy");
}
function os_resetConversionRates() {
	var $inps=$('input[name="os-currencies_value[]"]'), i=0;
	if ($inps[0].value=='1') {
		return;
	}
	var c=1/$inps[0].value;
	for (;i<$inps.length;++i) {
		$inps[i].value*=c;
	}
}
