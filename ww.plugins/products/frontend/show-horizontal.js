jQuery.fn.dataTableExt.oApi.fnSetFilteringDelay = function ( oSettings, iDelay ) {
	var
		_that = this,
		iDelay = (typeof iDelay == 'undefined') ? 250 : iDelay;
	 
	this.each( function ( i ) {
		$.fn.dataTableExt.iApiIndex = i;
		var
			$this = this,
			oTimerId = null,
			sPreviousSearch = null,
			anControl = $( 'input', _that.fnSettings().aanFeatures.f );
		 
			anControl.unbind( 'keyup' ).bind( 'keyup', function() {
			var $$this = $this;
 
			if (sPreviousSearch === null || sPreviousSearch != anControl.val()) {
				window.clearTimeout(oTimerId);
				sPreviousSearch = anControl.val(); 
				oTimerId = window.setTimeout(function() {
					$.fn.dataTableExt.iApiIndex = i;
					_that.fnFilter( anControl.val() );
				}, iDelay);
			}
		});
		 
		return this;
	} );
	return this;
}
 
$(function(){
	var cols=[],cols_names=[];
	var oTable=$('.product-horizontal');
	$('.products-num-results').remove();
	var numRows=oTable.find('tr').length-2;
	oTable.find('thead th').each(function(){
		var n=$(this).attr('o');
		cols.push({'sName':n});
		cols_names.push(n);
	});
	oTable.dataTable({
		"sScrollY": oTable[0].offsetHeight*.6,
		"sScrollX": "100%",
		"bProcessing": true,
		"bServerSide": true,
		"sAjaxSource": '/ww.plugins/products/frontend/get-datatable-data.php?pid='
			+pagedata.id,
		"aoColumns": cols,
		"bScrollInfinite": true,
		"bScrollCollapse": true,
		"iDisplayLength" : numRows,
		"oLanguage": { "sSearch": "Search all columns:" }
	}).fnSetFilteringDelay();
	$('#products-export').append(
		'<input type="hidden" name="sColumns" value="'+cols_names+'" />'
	);

	var oInput=$('table.product-horizontal tfoot input,table.product-horizontal tfoot select');
	var tSearch=$('.dataTables_filter input');
	var onchange=function(){
		clearTimeout(window.dt_filter);
		var $this=this;
		var $export=$('#products-export');
		if ($this.name=='') {
			var n='sSearch';
		}
		else {
			var n='sSearch_'+oInput.index($this);
		}
		var $export_inp=$export.find('input[name="'+n+'"]');
		if (!$export_inp.length) {
			$export_inp=$('<input type="hidden" name="'+n+'" />');
			$export.append($export_inp);
		}
		$export_inp.val($this.value);
		window.dt_filter=setTimeout(function(){
			oTable.fnFilter( $this.value, oInput.index($this) );
		}, 500);
	};
	var asInitVals=[];
	oInput.keyup( onchange )
		.change( onchange )
		.each( function (i) {
			asInitVals[i] = this.value;
		} )
		.focus( function () {
			if ( this.className == "search_init" ) {
				this.className = "";
				this.value = "";
			}
		} )
		.blur( function (i) {
			if ( this.value == "" ) {
				this.className = "search_init";
				this.value = asInitVals[$("tfoot input").index(this)];
			}
		} );
	tSearch.keyup( onchange )
		.each( function (i) {
			asInitVals[i] = this.value;
		} )
		.focus( function () {
			if ( this.className == "search_init" ) {
				this.className = "";
				this.value = "";
			}
		} )
		.blur( function (i) {
			if ( this.value == "" ) {
				this.className = "search_init";
				this.value = asInitVals[$("tfoot input").index(this)];
			}
		} );
});
