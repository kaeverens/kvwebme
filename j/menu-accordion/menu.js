$(function(){
	$('div.menu-accordion ul ul').each(function(){
		var $this=$(this);
		if( !$this.closest( 'div.menu-accordion' ).hasClass( 'expanded' ) )
			$this.css( 'display', 'none' );
		$this
			.addClass('is-fg-submenu')
			.prev()
				.addClass('has-submenu')
				.click(function(){
					var $this=$(this);
					$this.next().toggle(200);
					this.blur();
					return false;
				});
	});
	var pid=pagedata.id;
	var $menu=$('.menu-pid-'+pid).closest('ul');
	do{
		$menu.prev().trigger('click');
		$menu=$menu.prev().closest('ul');
	}while ($menu.length);
	if( $( 'div.menu-accordion' ).hasClass( 'expand-selected' ) )
        	$( 'div.menu-accordion .menu-pid-'+pid ).siblings( 'ul' ).css( 'display', 'block' );
});
