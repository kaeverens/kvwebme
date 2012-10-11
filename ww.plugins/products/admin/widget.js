function Products_widgetTypeChanged() {
	$('form.panel-products').each(function(){
		var $this=$(this);
		$this.find('.diameter,.show-products').css('display', 'none');
		switch ($this.find('select[name=widget_type]').val()) {
			case 'Pie-Chart':
				$this.find('.diameter').css('display', 'block');
			break;
			case 'List Categories':
				$this.find('.show-products').css('display', 'block');
			break;
		}
	});
}
$('form.panel-products select[name=widget_type]')
	.live('change', Products_widgetTypeChanged);
