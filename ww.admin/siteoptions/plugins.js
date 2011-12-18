$(function( ){
	$( "#installed_plugins" ).dataTable({ "bPaginate" : false });
	$( "#available_plugins" ).dataTable({ "bPaginate" : false });
	$( "#tabs" ).tabs({
		"show" : function( event, ui ){
			var oTable = $( ".display", ui.panel ).dataTable({ "bRetrieve" : true });
			if ( oTable.length > 0 )
				oTable.fnAdjustColumnSizing();
		}
	});
});
