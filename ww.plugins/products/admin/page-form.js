function products_what_to_show_change(){
	var val=+$('#products_what_to_show').val();
	$('#products_what_to_show_1').css('display',val==1?'table-row':'none');
	if(val!=1)$('#products_what_to_show_1 select').val('0');
	$('#products_what_to_show_2').css('display',val==2?'table-row':'none');
	if(val!=2)$('#products_what_to_show_2 select').val('0');
	$('#products_what_to_show_3').css('display',val==3?'table-row':'none');
	if(val!=3)$('#products_what_to_show_3 select').val('0');
	$('#products_search').css('display',val<3?'table-row':'none');
	$('#products_order_by').css('display',val<3?'table-row':'none');
	$('#products_per_page').css('display',val<3?'table-row':'none');
	$('#products-show-multiple-with-row').css('display',val!=3?'table-row':'none');
}
$(function(){
	$('.tabs').tabs();
	$('#products_what_to_show').change(products_what_to_show_change);
	products_what_to_show_change();
	$('#products_order_by_select')
		.remoteselectoptions({
			url:'/a/p=products/f=adminDatafieldsList',
			other_GET_params:function(){
				var val=$('#products_what_to_show').val();
				switch(val){
					case '1': // { product type
						return $('#products_what_to_show_1 select').val();
					// }
					case '2': // { category
						return 'c'+$('#products_what_to_show_2 select').val();
					// }
				}
				return '';
			}
		});
	$('#products_what_to_show_1 select,#products_what_to_show_2 select').change(function(){
		$('#products_order_by_select').trigger('mousedown');
	});
});
