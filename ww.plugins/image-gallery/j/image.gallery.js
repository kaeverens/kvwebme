function ig_setImages(data){
	ig.images=data;
	ig_updateGallery(ig.imgAt);
	window.Lightbox.initialize(data);
}
function ig_updateGallery(at){
	var a,imgNum,img,x,y;
	if(at<0)at=0;
	ig.imgAt=at;
	if(ig.images.length>ig.x*ig.y){
		var prev=at?'<a href="javascript:ig_updateGallery('+(at-ig.x*ig.y)+')" id="image_gallery_prev" class="prev">&lt;-- prev</a>':'';
		$('#image_gallery_prev_wrapper').html(prev);
		var next=(at+ig.x*ig.y<ig.images.length)?'<a href="javascript:ig_updateGallery('+(at+ig.x*ig.y)+')" id="image_gallery_next" class="next">next --&gt;</a>':'';
		$('#image_gallery_next_wrapper').html(next);
	}
	for(y=0;y<ig.y;++y){
		for(x=0;x<ig.x;++x){
			imgNum=at+(y*ig.x)+x;
			document.getElementById('igCell_'+y+'_'+x).innerHTML='';
			if(imgNum>=ig.images.length)continue;
			img={
				'src':'/kfmget/'+ig.images[imgNum].id+',width='+ig.thumbsize+',height='+ig.thumbsize,
				'caption':ig.images[imgNum].caption
			};
			var $a=$('<a href="javascript:Lightbox.show('+imgNum+')" id="image_gallery_thumb_'+imgNum+'"><img src="'+img.src+'" title="'+img.caption+'" /><br style="clear:both" /><span class="caption">'+img.caption.replace(/\\\\n/g,'<br />')+'</span></a>');
			if(ig.hoverphoto)$a.bind('mouseover',function(){
				ig_update_static_photo(this.id.replace(/image_gallery_thumb_/,''));
			});
			$('<div class="gallery_image" style="text=align:center"></div>').append($a).appendTo('#igCell_'+y+'_'+x);
		}
	}
	if(!ig.first_call && document.getElementById('image_gallery_picture')){
		ig_update_static_photo(0);
	}
	ig.first_call=true;
}
function ig_update_static_photo(imgNum){
	var el=document.getElementById('image_gallery_picture');
	var width=el.offsetWidth,height=el.offsetHeight;
	el.style.background='url(/kfmget/'+ig.images[imgNum].id+',width='+width+',height='+height+') no-repeat center center';
}
var ig={
	first_call:0
};
var Lightbox={
	hideFrame:function(){
		Lightbox.frameVisible=0;
		$('#lightbox_frame,#lightbox_shader,#lightbox_wrapper').remove();
	},
	initialize:function(data){
		this.data=data;
	},
	preload:function(){
		var $preload=$('<img src="/kfmget/'+this.data[this.at].id+',width='+this.imageMaxWidth+',height='+this.imageMaxHeight+'" id="lightbox_preloader" style="position:absolute;left:-4000px;visibility:hidden" />');
		$preload
			.load(this.showImage)
			.appendTo(document.body);
		this.data[this.at].img=$preload[0];
	},
	show:function(at){
		this.at=(at+this.data.length)%this.data.length;
		if(!Lightbox.frameVisible){ // build frame
			this.showFrame();
		}
		{ // show image
			var imgData=this.data[this.at];
			if(!imgData.isLoaded)this.preload();
			else this.showImage();
		}
	},
	showFrame:function(){
		var margin=.05;
		var fixed='absolute';
		var ws={x:$(window).width(),y:$(window).height()};
		this.frameMaxWidth=ws.x*(1-margin*2);
		this.frameMaxHeight=ws.y*(1-margin*2);
		var wrapper=$('<div id="lightbox_wrapper"></div>')
			.css({
				'position':'absolute',
				'top':$(window).scrollTop(),
				'width':ws.x,
				'height':ws.y,
				'left':0
			});
		wrapper.appendTo(document.body);
		this.shader=$('<div id="lightbox_shader"></div>')
			.css({
				'position':'absolute',
				'top':0,
				'width':ws.x,
				'height':ws.y,
				'left':0,
				'opacity':.7,
				'background':'#000'
			})
			.click(this.hideFrame);
		this.shader.appendTo(wrapper);
		$(window).scroll(function() {
			$('#lightbox_wrapper').css('top', $(this).scrollTop() + "px");
		});
		this.frame=$('<div id="lightbox_frame"></div>')
			.css({
				'position':fixed,
				'top':ws.y*margin,
				'width':this.frameMaxWidth,
				'height':this.frameMaxHeight,
				'left':ws.x*margin,
				'background':'#ccc',
				'z-index':20
			})
			.appendTo(wrapper);
		$('<div id="lightbox_controls" style="position:absolute;left:9px;bottom:9px;right:9px;border:1px solid #000;background:#eee;text-align:center"><a href="javascript:Lightbox.show(Lightbox.at-1)" id="lightbox_prev" style="float:left;width:64px;height:64px;background:url(/i/arrow_left.png) no-repeat"></a><a href="javascript:Lightbox.show(Lightbox.at+1)" id="lightbox_next" style="float:right;width:64px;height:64px;background:url(/i/arrow_right.png) no-repeat"></a><div id="lightbox_caption" style="margin:0 70px;height:64px;text-align:center;font-style:italic"></div><a href="javascript:Lightbox.hideFrame()" id="image_gallery_close_lightbox" style="position:absolute;z-index:2;bottom:5px;left:100px;right:100px">close</a></div>')
			.appendTo(this.frame[0]);
		this.imageMaxWidth=this.frameMaxWidth-20;
		this.imageMaxHeight=this.frameMaxHeight-100;
		$('<div id="lightbox_imageWrapper" style="position:absolute;left:9px;top:9px;right:9px;bottom:89px;text-align:center;border:1px solid #000;background:#fff no-repeat center center"></div>')
			.appendTo(this.frame[0]);
		Lightbox.frameVisible=1;
	},
	showImage:function(){
		Lightbox.imageWrapper=$('#lightbox_imageWrapper')[0];
		var ws={x:$(window).width(),y:$(window).height()};
		if(document.getElementById('lightbox_preloader')){
			var img=document.getElementById('lightbox_preloader');
			Lightbox.data[Lightbox.at].width=+img.offsetWidth;
			Lightbox.data[Lightbox.at].height=+img.offsetHeight;
			Lightbox.data[Lightbox.at].isLoaded=1;
			$(img).remove();
		}
		Lightbox.imageWrapper.innerHTML='';
		var minwidth=+Lightbox.data[Lightbox.at].width<200?200:Lightbox.data[Lightbox.at].width;
		var minheight=+Lightbox.data[Lightbox.at].height<200?200:Lightbox.data[Lightbox.at].height;
		Lightbox.imageWrapper.style.backgroundImage='url(/i/ajax-loader.gif)';
		$(Lightbox.frame).animate({
			'left':(+ws.x-minwidth-20)/2,
			'top':(+ws.y-Lightbox.data[Lightbox.at].height-100)/2,
			'height':+Lightbox.data[Lightbox.at].height+100,
			'width':+minwidth+20
		},400,'swing',function(){
			if(!document.getElementById('lightbox_caption'))return;
			$('<img src="/kfmget/'+Lightbox.data[Lightbox.at].id+',width='+Lightbox.imageMaxWidth+',height='+Lightbox.imageMaxHeight+'" />').appendTo(Lightbox.imageWrapper);
			Lightbox.imageWrapper.style.backgroundImage='none';
			document.getElementById('lightbox_caption').innerHTML=Lightbox.data[Lightbox.at].caption.replace(/\\\\n/g,'<br />');
		});
	}
};
$(function(){
	ig.gallery=document.getElementById('image_gallery');
	ig.hoverphoto=document.getElementById('image_gallery_picture')?1:0;
	$.extend(ig,eval('('+ig.gallery.className.replace(/^[^{]*/,'')+')'));
	x_ig_getImages(ig.dirid,ig_setImages);
});
