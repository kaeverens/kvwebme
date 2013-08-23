$(function(){
	var $dialog = $('<div></div>')
		.html('<br/>'+
		'Name:<input id="onTheFlyName" type="text" /><br/>'+
		'<br/>Parent:<select id="onTheFlyParent"><option>None</option></select>'+
		'<br/><center><input type="button" id="onTheFlyAddButton" value="Add"/></center>'
		)
		.dialog({autoOpen: false, title: 'Add a category'});
	$('#addCategory').click(function(){
		$dialog.dialog('open');
		return false;
	});
	
	$("#onTheFlyAddButton").click(function(){
		var onTheFlyName=$("#onTheFlyName").val();
		var selectedParent=$("#onTheFlyParent").val();
		if (selectedParent=="None") {
			selectedParent=0;
		}
	
		if (!onTheFlyName) {
			return alert("Please enter a name");
		}
	
		$.ajax({
			type: 'POST',
			url: '/a/p=products/f=adminCategoryNew',
			data: {
				"parent_id":selectedParent,
				"name":onTheFlyName
			},
			complete: function(xhr, status){
				var response=$.parseJSON(xhr.responseText);
				id=response.attrs.id;
				$("#onTheFlyName").val('');
				//for use in initialising the checkboxes from "Categories" tab
				var parent=$("#onTheFlyParent option:selected").text();
				parent=parent.replace(/\W{1,}/,'');
				//we initialise the content of the list
				$("#onTheFlyParent").empty();
				$("#onTheFlyParent").append( new Option("None",0));
				$("#onTheFlyParent").remoteselectoptions({url:"/a/p=products/f=adminCategoriesGetRecursiveList"});
				//we initialise the content of the list from the "Categories" tab
				var noOptions=0;
				$('select[name="products_default_category"] option').each(function(){
					noOptions++;
				});
				if(noOptions>1){
					var $sel=$('select[name="products_default_category"]');
					var defaultCategory=$('option:selected', $sel).text();
					var defaultCategoryVal=$sel.val();
					$('select[name="products_default_category"]').empty();
					$('select[name="products_default_category"]').append( new Option(defaultCategory, defaultCategoryVal));
					$('select[name="products_default_category"]').remoteselectoptions({url:"/a/p=products/f=adminCategoriesGetRecursiveList"});
				}
				// We update the check buttons from "Categories" tab
				if(parent=="None") {
					$("#categories>ul")
						.append('<li><input checked type="checkbox" name="product_categories['+id+']"/>'+onTheFlyName+'</input></li>');
				}
				else {
					$("#categories li:contains('"+parent+"'):last")
						.append('<ul><li><input checked type="checkbox" name="product_categories['+id+']"/>'+onTheFlyName+'</li></ul>');
				}
				$dialog.dialog('close');
			}
		});
	});
});
