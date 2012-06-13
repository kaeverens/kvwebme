function os_invoice(id, type, print){
	if (type=='pdf') {
		document.location='/a/p=online-store/f=invoicePdf/id='+id;
		return;
	}
	var w=$(window);
	var wh=w.height(),ww=w.width();
	var p=print?'&print=1':'';
	$('<iframe id="externalSite" class="externalSite" src="/ww.plugins/online-store/frontend/show-invoice.php?id='+id+p+'" />').dialog({
		autoOpen: true,
		width: ww-100,
		height: wh-100,
		modal: true,
		resizable: true,
		autoResize: true
	}).width(ww-130).height(wh-130);    
}
