function products_admin_edit_product(){
	$('<div id="admin-overlay"></div>').appendTo(document.body);
	var id=this.id.replace(/products-([0-9]*)-admin/,'$1');
	$('<div id="admin-editor"><a href="javascript:admin_edit_page_close()">[x]</a><div id="admin-iframe-holder"><iframe src="/ww.admin/plugin.php?_plugin=products&_page=products-edit&id='+id+'&frontend-admin=1"></iframe></div></div>').appendTo(document.body);
}
$('div.products-product').live('mouseover',function(){
	if($('#'+this.id+'-admin').length){
		clearTimeout(window.productsAdminTimeout);
		return;
	}
	$('<div id="'+this.id+'-admin" class="products-admin-edit-button" style="cursor:pointer;position:fixed;background:#333;color:#fff;font-size:9px;border-radius:5px;-moz-border-radius:5px;padding:3px;border:1px solid red;">Edit Product</div>')
		.position({my: "right top", at: "right top", of:$('>table',this)})
		.click(products_admin_edit_product)
		.appendTo(document.body);
});
$('div.products-product').live('mouseout',function(){
	window.productsAdminTimeout=setTimeout('$("#'+this.id+'-admin").remove();',1);
});
$('div.products-admin-edit-button').live('mouseover',function(){
	if($('#'+this.id).length){
		clearTimeout(window.productsAdminTimeout);
	}
});
