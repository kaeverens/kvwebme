function os_invoice(id, print){
	var w=$(window);
	var wh=w.height(),ww=w.width();
	var p=print?'&print=1':'';
	$('<iframe id="externalSite" class="externalSite" src="/ww.plugins/online-store/admin/show-invoice.php?id='+id+p+'" />').dialog({
		autoOpen: true,
		width: ww-100,
		height: wh-100,
		modal: true,
		resizable: true,
		autoResize: true
	}).width(ww-130).height(wh-130);    
}
