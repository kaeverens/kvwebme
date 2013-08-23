function createPopup (defaultName, id, what) {
	if (/[^A-Za-z \-0-9]/.test(defaultName)) {
		defaultName= '';
	}
	var html
		= '<div id="dialog">Name'+
			'<input id="products_page_name" type="text"'+
			'value="'+defaultName+'"/><br/>'+
			'Parent <select id="products_page_parent">'+
						'<option value="0" selected="selected">'+
							'--none--'+
						'</option>'+
					'</select></div>';

	$(html).dialog(
		{
			modal:true,
			buttons:{
				'Create Page': function () {
					var name= $('#products_page_name').val();
					var parentPage= $('#products_page_parent').val();
					if(name=='') {
						return alert('Please enter a name for your page');
					}
					if (/[^A-Za-z \-0-9]/.test(name)) {
						return alert(
							'Only letters or numbers are allowed in a page name'
						);
					}
					$.post (
						'/ww.plugins/products/admin/insert-page.php',
						{
							"id":id,
							"what":what,
							"name":name,
							"parent":parentPage
						},
						confirm_create, 
						"json"
					);
				},
				'Cancel': function () {
					$(this).remove();
				}
			}
		}
	);
	$('#products_page_parent').remoteselectoptions({url:'/a/f=adminPageParentsList'});
}
function confirm_create (data) {
	if (data) {
		alert(data.message);
	}
	if (data.status) {
		$(
			'<a href="'+data.url+'"target=_blank>'+
		  	'Click here to view this on the front end'+
		  	'</a>'
		)
		.insertBefore($('#page_create_link'));
		$('#page_create_link').remove();
	}
	$('#dialog').remove();
}
