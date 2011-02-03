function plugin_file_details(){
	this.name='file_details',
	this.title="file details";
	this.mode=2;
	this.writable=2;
	this.category='returning'; // to put it near the bottom
	this.extensions=['all'];
	this.doFunction=function(files){
		var table=kfm_buildFileDetailsTable(File_getInstance(files[0]));
		kfm_modal_open(table,'File Details',[]); // TODO: new string
	}
}
kfm_addHook(new plugin_file_details());

function kfm_buildFileDetailsTable(res){
	if(!res)return;
	var table=document.createElement('table'),r,s;
	if(res.name){     // filename
		r=kfm.addRow(table);
		s=document.createElement('strong');
		s.innerHTML=kfm.lang.Filename;
		kfm.addCell(r,0,0,s);
		kfm.addCell(r,1,0,res.name);
	}
	if(res.dir){      // directory
		r=kfm.addRow(table);
		s=document.createElement('strong');
		s.appendChild(_('directory'));
		kfm.addCell(r,0,0,s);
		kfm.addCell(r,1,0,'/'+res.dir);
	}
	if(res.filesize){ // filesize
		r=kfm.addRow(table);
		s=document.createElement('strong');
		s.innerHTML=kfm.lang.Filesize;
		kfm.addCell(r,0,0,s);
		kfm.addCell(r,1,0,res.filesize);
	}
	if(res.tags&&res.tags.length){ // tags
		r=kfm.addRow(table);
		s=document.createElement('strong');
		s.innerHTML=kfm.lang.Tags;
		kfm.addEl(kfm.addCell(r,0),s);
		var arr=[],c=kfm.addCell(r,1);
		for(var i=0;i<res.tags.length;++i){
			kfm.addEl(c,kfm_tagDraw(res.tags[i]));
			if(i!=res.tags.length-1)kfm.addEl(c,', ');
		}
	}
	if(res.mimetype){ // mimetype
		r=kfm.addRow(table);
		s=document.createElement('strong');
		s.innerHTML=kfm.lang.Mimetype;
		kfm.addEl(kfm.addCell(r,0),s);
		kfm.addEl(kfm.addCell(r,1),res.mimetype);
		switch(res.mimetype.replace(/\/.*/,'')){
			case 'image':{
				if(res.caption){ // caption
					r=kfm.addRow(table);
					s=document.createElement('strong');
					s.innerHTML=kfm.lang.Caption;
					kfm.addCell(r,0,0,s);
					kfm.addCell(r,1).innerHTML=(res.caption).replace(/\n/g,'<br \/>');
				}
				break;
			}
		}
	}
	if(res.ctime){    // last change time
		r=kfm.addRow(table);
		s=document.createElement('strong');
		s.innerHTML=kfm.lang.LastModified;
		kfm.addEl(kfm.addCell(r,0),s);
		var d=(new Date(res.ctime*1000)).toGMTString();
		d=res.modified;
		kfm.addEl(kfm.addCell(r,1),d);
	}
	if(res.width) {
		r=kfm.addRow(table);
		s=document.createElement('strong');
		s.innerHTML=kfm.lang.ImageDimensions;
		kfm.addCell(r,0,0,s);
		kfm.addCell(r,1,0,res.width+" x "+res.height);	  
	}
	return table;
}
function kfm_showFileDetails(id){
	var res=File_getInstance(id);
	var fd=document.getElementById('kfm_file_details_panel'),el=document.getElementById('kfm_left_column');
		if(!el)return false;
	if(!fd){
		kfm_addPanel(document.getElementById('kfm_left_column'),'kfm_file_details_panel');
		kfm_refreshPanels(el);
	}
	var body=$j('#kfm_file_details_panel div.kfm_panel_body').html('')[0];
	if(!res){
		body.innerHTML=kfm.lang.NoFilesSelected;
		return;
	}
	var table=kfm_buildFileDetailsTable(res);
	kfm.addEl(body,table);
}
