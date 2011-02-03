var id='0',pagesContentsChildren=[],pagesContentsParents=[],pagesContentsRowById=[], CMSextra='',inAdmin=1;
$(function(){
	var classes=[];
	els=$('*');
	for(i=0;els[i];i++){
		if(els[i].className!=''){
			cn=els[i].className;
			if(cn.indexOf(' ')>-1){
				cn=cn.split(" ");
				for(var j=0;j<cn.length;++j)classes[cn[j]]=1;
			}else{
				classes[cn]=1;
			}
		}
	}
	if(classes['accordion']){ // accordion
		accordionParams={active:'.current',clearStyle:true,autoHeight:false,header:'.accordion-header',fillSpace:false,navigation:true};
		$('.accordion').accordion(accordionParams);
		$('.accordion0').accordion(accordionParams);
	}
	var page=document.location.toString().replace(/.*admin\/(.*)\.php.*/,'$1');
});
