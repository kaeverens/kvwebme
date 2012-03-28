$(function(){
	var relations=[];
	$($('#product-relations select')[0])
		.find('option')
		.each(function(){
			relations.push([this.value,this.innerHTML]);
		});
	function addRow(){
		var $tr=$('<tr><td></td><td></td></tr>');
		var opts=[];
		for (var i=0; i<relations.length; ++i) {
			var r=relations[i];
			opts.push('<option value="'+r[0]+'">'+r[1]+'</option>');
		}
		$('<select name="product-relations-type[]">'+opts.join('')+'</select>')
			.appendTo($tr.find('td')[0]);
		$('<select name="products-relations-product[]"><option value="">'
			+' -- please choose -- </option>')
			.remoteselectoptions({
				'url':'/a/p=products/f=adminProductsList',
				'cache_id':'product_names'
			})
			.appendTo($tr.find('td')[1]);
		$tr.appendTo('#product-relations');
	}
	$('#product-relations select.products-relations-product')
		.remoteselectoptions({
			'url':'/a/p=products/f=adminProductsList',
			'cache_id':'product_names'
		});
	$('#product-relations select').live('change', function(){
		$('#product-relations tr').each(function(){
			var $selects=$(this).find('select');
			if ($selects.length<1) {
				return;
			}
			if ($($selects[0]).val() == '' && $($selects[1]).val() == '') {
				$(this).remove();
			}
		});
		addRow();
	});
});
