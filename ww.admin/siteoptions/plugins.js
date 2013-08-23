$(function( ){
	$( "#installed_plugins" ).dataTable({ "bPaginate" : false }).fnSetFilteringDelay();
	$( "#available_plugins" ).dataTable({ "bPaginate" : false }).fnSetFilteringDelay();
	$( "#tabs" ).tabs({
		"show" : function( event, ui ){
			var oTable = $( ".display", ui.panel ).dataTable({ "bRetrieve" : true }).fnSetFilteringDelay();
			if ( oTable.length > 0 )
				oTable.fnAdjustColumnSizing();
		}
	});
});
