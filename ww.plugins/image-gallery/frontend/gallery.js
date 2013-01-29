var Gallery={
	tagPlugins:[],
	options:{ // default options for the gallery
		display:'list',	// list, grid or custom
		items:6,	// columns, rows
		rows:1,
		thumbsize:90,	// thumbnail size
		thumbsizex:90,	// thumbnail size
		thumbsizey:90,	// thumbnail size
		links:true,	// set false to disable next and previous links
		hover:'opacity',	// "hover" effect
		click:'popup', // what to do when a large image is clicked
		effect:'fade',	// main image transition effect
		ratio:'normal',	// ratio of the thumbnails - normal or crop
		// this option adds partial support for changing the amount of items
		// loaded when the next/prev links are clicked. tested with vals 1 and 2
		listSwitch:2,
		galleryWidth:null,	// allow for manual override of the gallery width
		slideshow:false,
		hidesidebar:false,
		slideshowTime:2500,	// slideshow interval between slide change
		directory:'',
		// custom display functions
		customDisplayInit:null,
		customDisplayNext:null,
		customDisplayPrevious:null,
		customDisplaySlideshow:null,
		customDisplayImageCallback:null,
		customDisplayCaption:null
	},
	// { local vars
	position:0, // keeps track of how far through the images array the grid display is
	width:0,
	current:0, // grid display - the count of how many items are displayed currently
	height:0,
	t:null, // used to hold the timeout for the slideshow function
	images:{}, // holds the images associated with this gallery. populated in init()
	cached_gallery:null,
	displayed:0, // have any images been displayed from the current list
	// }
	addLinksToLargeImage:function() {
		if (!this.options.links) {
			return;
		}
		$('#gallery-image .ad-image')
			.append('<div id="big-prev-link"/><div id="big-next-link"/>');
		$('#big-next-link,#big-prev-link')
			.css('height', this.options.imageHeight+'px');
	},
	applyFrame:function() {
		var src=this.src,$img=$(this);
		var newsrc='/i/blank.gif', bgoffset='0 0', newwidth=$img.width(),
			newheight=$img.height(),
			ratio=Gallery.options.imageWidth/Gallery.options.thumbsizex;
		if (Gallery.frame.type) {
			var furl=Gallery.frame.type=='--custom--'
				?'/image-galleries/frame-'+window.pagedata.id+'.png'
				:'TODO';
			var padding=Gallery.frame.padding, border=Gallery.frame.border;
			newsrc='/a/p=image-gallery/f=frameGet/w='+newwidth+'/h='
				+newheight+'/pa='+padding+'/bo='+border+furl+'/ratio='+ratio;
		}
		$img
			.css({
				'background':'url("'+src+'") no-repeat '+bgoffset,
				'width'     :newwidth+'px',
				'height'    :newheight+'px'
			})
			.attr('image', src)
			.attr('src', newsrc);
	},
	bump:function(offset) { // bump effect
		var pos=parseInt($('#slider').css('left'));
		$('#slider').animate(
			{'left':(offset=='left')?(pos-20)+'px':(pos+20)+'px'}
			,100,function() {
			$(this).animate({'left':0},100);
		});
		return false;
	},
	closeWholepageImage:function() {
		$('.ad-image,.image-gallery-close-wholepage,#gallery-image').remove();
		$('div.ad-gallery,#image-gallery-nav').css('display', 'block');
		if (Gallery.options.hidesidebar) {
			$('#sidebar1_bak').toggle().attr('id', 'sidebar1');
		}
	},
	count:function() { // counts the images object
		var size=0,key;
		for(key in this.images) {
			++size;
		}
		return size;
  },
	display:function() { // initial display function - gets called once
		this.displayImage(0);
		this.gallery().html('<div id="gallery-container"><div id="slider" style="width:100%"/></div>');
		if (this.options.display=='custom' && typeof(this.options.customDisplayInit)=='function') {
			return this.options.customDisplayInit();
		}
		var items=this.gallery().attr('cols');
		var dis=this.options.display;
		this.options.items=items?parseInt(items):(dis=='grid'?4:6);
		this.width=this.options.galleryWidth==null
			?(this.options.thumbsizex+4)*this.options.items+4
			:this.options.galleryWidth;
		this.gallery().addClass(dis);
		if (dis=='grid') {
			var rows=this.gallery().attr('rows');
			this.options.rows=rows?parseInt(rows):4;
			this.displayGrid();
		}
		else {
			this.options.display='list';
			this.options.rows=1;
			var els=[];
			for(var i=0;i<this.options.items;++i) {
				els[i]=i;
			}
			var list=this.displayList(els);
			$('#slider').html(
				'<div class="images-container" style="overflow:hidden"><ul class='
				+'"ad-thumb-list" style="width:'+(this.width+400)+'px">'+list
				+'</ul></div>'
			);
			this.height=this.options.thumbsizey;
			$('.ad-thumb-list').css('height', this.height+'px');
			this.addLinksToLargeImage();
		}
		this.gallery().css({'width':this.width+'px'});
		this.height=(this.options.thumbsizey)*this.options.rows;
		var actualHeight=$('#slider>table').outerHeight();
		if (actualHeight>this.height) {
			this.height=actualHeight;
		}
		$('#gallery-container').css('height', this.height+'px');
		if (this.options.links==true && !$('#image-gallery-nav').length) {
			this.gallery().append('<div id="prev-link"/><div id="next-link"/>');
			$('#next-link,#prev-link').css('height', this.height+'px');
		}
		$('.images-container img:first').addClass('image-selected');		
		if (this.options.slideshow=='true') { // activate slideshow
			setTimeout("Gallery.slideshow()", this.options.slideshowTime);
		}
		this.updateNav();
	},
	displayGrid:function() { // shows the grid display using a carousel
		var file, sizex=Gallery.options.thumbsizex, sizey=Gallery.options.thumbsizey, row=0, j;
		var html='<table class="images-container" style="width:100%"><tr>';
		Gallery.current=0;
		$.each(Gallery.images,function(i) {
			if(i%Gallery.options.items==0) {
				++row;
				html+='</tr><tr>';
			}
			j=Gallery.position;
			if(row==(Gallery.options.rows+1)||!Gallery.images[j]) {
				return false;
			}
			file=Gallery.images[j];
			html+='<td style="width:'+sizex+'px;height:'+sizey+'px">'
				+Gallery.mediaDisplay(file)+'</td>';
			++Gallery.position;
			++Gallery.current;
		});
		html+='</tr></table>';
		var $slider=$('#slider');
		$slider
			.append(html)
			.find('img')
			.one('load', Gallery.applyFrame)	
			.load(function() { // center images vertically
				var $this=$(this);
				var $span=$this.closest('span');
				if ($span.height()<$this.height()) {
					$span.css('height', $this.height()+'px');
				}
				$span.css({
					'line-height':$span.height()+'px'
				});
				$this.css('vertical-align', 'middle');
			});
	},
	displayImage:function(e) { // displays the main "big" image if present
		$('#image-gallery-video_wrapper').remove();
		var $imgWrap=$('.ad-image');
		if (!$imgWrap.length && Gallery.displayed) {
			$('<a href="javascript:Gallery.closeWholepageImage()" '
				+'class="image-gallery-close-wholepage">back</a>')
				.appendTo('#image-gallery-wrapper');
			$imgWrap=$('<div id="gallery-image">'
				+'<div class="ad-image wholepage">'
				+'<h1 class="caption"/>'
				+'<span class="image"><img/></span>'
				+'<p class="description"/>'
				+'<em style="display:block;text-align:right" class="author"/>'
				+'</div></div>')
				.appendTo('#image-gallery-wrapper');
			Gallery.addLinksToLargeImage();
			$('div.ad-gallery,#image-gallery-nav').css('display', 'none');
			if (Gallery.options.hidesidebar) {
				$('#sidebar1').toggle().attr('id', 'sidebar1_bak');
			}
		}
		var $img=$imgWrap.find('img');
		var files=Gallery.images;
		if(!files[e]) {
			return;
		}
		var current=$img.attr('num');
		var sequence=[];
		for (var i=0;i<files.length;++i) {
			sequence[i]=files[i].id;
			if (files[i].caption == undefined) {
				files[i].caption='';
			}
			if (files[i].author == undefined) {
				files[i].author='';
			}
			if (files[i].description == undefined) {
				files[i].description='';
			}
		}
		switch(files[e].media) {
			case 'image': // {
				var src=$('html.backgroundsize').length
					?files[e].url
					:files[e].url+'/w='+Gallery.options.imageWidth
					+'/h='+Gallery.options.imageHeight;
				$img
					.hide()
					.css({
						'width':'auto',
						'height':'auto'
					})
					.attr({
						'src':src,
						'title':files[e].caption,
						'num':e,
						'sequence':sequence
					})
					.one('load',function() {
						var newsrc='/i/blank.gif', bgoffset='0 0', newwidth=$img.width(),
							newheight=$img.height();
						if (newwidth>Gallery.options.imageWidth) {
							var ratio=Gallery.options.imageWidth/newwidth;
							newwidth*=ratio;
							newheight*=ratio;
						}
						if (newheight>Gallery.options.imageHeight) {
							var ratio=Gallery.options.imageHeight/newheight;
							newwidth*=ratio;
							newheight*=ratio;
						}
						if (Gallery.frame.type) {
							var furl=Gallery.frame.type=='--custom--'
								?'/image-galleries/frame-'+window.pagedata.id+'.png'
								:'TODO';
							var padding=Gallery.frame.padding, border=Gallery.frame.border;
							newsrc='/a/p=image-gallery/f=frameGet/w='+newwidth+'/h='
								+newheight+'/pa='+padding+'/bo='+border+furl;
						}
						var marginTop=0;
						var spanheight=$img.closest('span').height();
						var $wrapper=$imgWrap.closest('#gallery-image');
						if (spanheight<50) {
							spanheight=newheight;
						}
						if (newheight<spanheight) {
							marginTop=(spanheight-newheight)/2;
						}
						else if (newheight>spanheight) {
							$wrapper.css('height', $wrapper.height()+(newheight-spanheight)+'px');
						}
						$('#big-next-link,#big-prev-link')
							.css('height', $wrapper.css('height'));
						$img
							.css({
								'background':'url("'+src+'") no-repeat '+bgoffset,
								'background-size':'100%',
								'width'     :newwidth+'px',
								'height'    :newheight+'px',
								'margin-top':marginTop
							})
							.attr('image', src)
							.attr('src', newsrc);
						if (newwidth>$wrapper.width()) {
							$wrapper.css({
								'width':newwidth+'px'
							});
						}
						switch(Gallery.options.effect) {
    		      case 'fade': 
        		    $img.fadeIn('slow',Gallery.displayImageCallback); 
          		break; 
          		case 'slideVertical': 
								$img.show('slide',{'direction':(current<e?'up':'down')},500,Gallery.displayImageCallback);
							break; 
          		case 'slideHorizontal': 
								$img.show('slide',{'direction':(current<e?'right':'left')},500,Gallery.displayImageCallback);
          		break; 
						}
					});
			break; // }
			case 'video': // {
				$.getScript('/ww.plugins/image-gallery/frontend/jwplayer.js',function() {
					var width=Gallery.options.imageWidth;
					var height=Gallery.options.imageHeight;
					if (!$imgWrap.is('.wholepage')) {
						var $tmp=$imgWrap.find('.wholepage');
						if ($tmp.length) {
							$imgWrap=$tmp;
						}
					}
					$imgWrap.css({
						'width':width+'px',
						'height':height+'px'
					});
					$img
						.hide()
						.attr('src','')
						.attr('title','')
						.attr('num',e)
						.attr('sequence',sequence);
					$imgWrap.append('<div id="image-gallery-video" style="display:none">');
					jwplayer('image-gallery-video').setup({
						'flashplayer':'/ww.plugins/image-gallery/frontend/player.swf',
						'file':files[e].href,
						'height':height,
						'width':width
					});
					jwplayer().setVolume(70);
					jwplayer().play();
					Gallery.displayImageCallback();
				});
			break; // }
		}
		Gallery.displayed++; // mark that at least one image has been displayed
	},
	displayImageCallback:function() {
		if (typeof(Gallery.options.customDisplayCaption)=='function') {
			return Gallery.options.customDisplayCaption();
		}
		var $img=$('.ad-image img'), $imgWrap=$img.closest('span'), $tagWrap2;
		if (userdata.isAdmin) {
			$('.ad-image .tag-wrapper').remove();
			$.contextMenu('destroy', '.ad-image img');
			$.contextMenu({
				'selector':'.ad-image img',
				'items':{
					'add-tag':{
						'name':'Add Tag',
						'icon':'edit',
						'callback':function() {
							var $tagWrap=$('<div class="tag-wrapper"><div/></div>')
								.css({
									'position':'absolute',
									'cursor':'crosshair',
									'left':$img[0].offsetLeft,
									'width':$img.width(),
									'top':$img[0].offsetTop,
									'height':$img.height()
								})
								.appendTo($imgWrap);
							$tagWrap2=$tagWrap.find('div')
								.css({
									'width':'100%',
									'height':'100%',
									'position':'relative'
								})
								.click(function(e) {
									var offset=$img.offset();
									var x=e.pageX-offset.left, y=e.pageY-offset.top;
									var $marker=$('<div/>')
										.css({
											'position':'absolute',
											'left':(x-50)+'px',
											'top':(y-50)+'px',
											'border':'1px solid #000',
											'background':'rgba(255,255,255,.3)',
											'width':'100px',
											'height':'100px'
										})
										.appendTo($tagWrap2);
									editTag(-1, {
										'x':parseInt(x*ratio),
										'y':parseInt(y*ratio),
										'notes':''
									});
									return false;
								});
							$.contextMenu('destroy', '.ad-image .tag-wrapper');
							$.contextMenu({
								'selector':'.ad-image .tag-wrapper',
								'items':{
									'finish-tagging':{
										'name':'Finish Tagging',
										'icon':'edit',
										'callback':function() {
											$('.ad-image .tag-wrapper').remove();
										}
									}
								}
							});
						}
					}
				}
			});
		}
		var index=+$img.attr('num'), idata=Gallery.images[index];
		// { madly over-coded caption stuff
		var caption=idata.caption;
		var $caption=$('.ad-image .caption');
		if (caption=="") {
			$caption.hide();
		}
		else {
			var width=$img.width()-14;
			var offset=$img.height()+ +$img.css('margin-top').replace('px', '');
			$caption
				.html(caption)
				.css({
					'width':width+'px',
					'display':'block',
					'left':$img.position().left
				});
			var height=$caption.outerHeight();
			$caption
				.css({'top':(offset-height)+'px'})
				.fadeIn('fast');
		}
		// }
		// { author, description
		$('.ad-image .author').html(idata.author);
		$('.ad-image .description').html(idata.description);
		// }
		function editTag(index, tag) {
			var x=tag.x, y=tag.y;
			var $tagWrap2=$('.tag-wrapper div');
			var html='<table>'
				+'<tr><th>Notes</th><td><textarea id="dialog-notes"></textarea></td></tr>';
			for (var i=0;i<Gallery.tagPlugins.length;++i) {
				var tp=Gallery.tagPlugins[i];
				html+=tp.tagEditHtml;
			}
			if (index!=-1) {
				html+='<tr><td colspan="2" style="text-align:left">'
					+'<input type="checkbox" class="delete"/>delete</td></tr>';
			}
			html+='</table>';
			var $dialog=$(html)
				.dialog({
					'modal':true,
					'width':'460px',
					'buttons':{
						'Save':function() {
							if (idata.tags===undefined) {
								idata.tags=[];
							}
							var tag={
								'x':x,
								'y':y,
								'notes':$('#dialog-notes').val()
							};
							index=index==-1?idata.tags.length:index;
							idata.tags[index]=tag;
							for (var i=0;i<Gallery.tagPlugins.length;++i) {
								idata=tp.onTagSave(idata, index);
							}
							if ($dialog.find('.delete').is(':checked')) {
								idata.tags[index]=idata.tags[idata.tags.length-1];
								idata.tags.pop();
							}
							$dialog.remove();
							$tagWrap2.find('div').remove();
							$.post('/a/p=image-gallery/f=tagsUpdate', {
								'id':idata.id,
								'tags':idata.tags
							}, function(ret) {
								showTags();
							});
						}
					},
					'close':function() {
						$dialog.remove();
						$tagWrap2.find('div').remove();
					}
				});
			$('#dialog-notes').val(tag.notes);
			for (var i=0;i<Gallery.tagPlugins.length;++i) {
				var tp=Gallery.tagPlugins[i];
				tp.onTagEditShow(idata, index);
			}
		}
		function showTags() {
			$('.ad-image .tag').remove();
			var tags=idata.tags||[], tagSize=50;
			for (var i=0;i<tags.length;++i) {
				var $tag=$(
					'<div class="tag"><img src="/i/blank.gif" style="width:'+tagSize+'px;'
					+'height:'+tagSize+'px;"/></div>')
					.css({
						'left':($img[0].offsetLeft+(+tags[i].x)/ratio-(tagSize/2))+'px',
						'top':($img[0].offsetTop+(+tags[i].y)/ratio-(tagSize/2))+'px',
						'width':tagSize+'px',
						'height':tagSize+'px'
					})
					.attr('title', tags[i].notes)
					.data('index', i)
					.click(function() {
						var index=$(this).data('index');
						for (var i=0;i<Gallery.tagPlugins.length;++i) {
							var tp=Gallery.tagPlugins[i];
							tp.onTagClick(idata, index);
						}
					})
					.appendTo($imgWrap);
			}
			$.contextMenu('destroy', '.ad-image .tag');
			$.contextMenu({
				'selector':'.ad-image .tag',
				'items':{
					'edit-tag':{
						'name':'Edit Tag',
						'icon':'edit',
						'callback':function(a, opts) {
							var index=+$(opts['$trigger']).data('index');
							editTag(index, idata.tags[index]);
						}
					}
				}
			});
		}
		var ratio=idata.width/$img.width();
		showTags();
		if(typeof(Gallery.options.customDisplayImageCallback)=='function') {
			return Gallery.options.customDisplayImageCallback();
		}
	},
	displayList:function(els) { // displays elements from this.images in a list
		var file, sizex=this.options.thumbsizex, html='', i;
		for(i=els[0];i<=els[els.length-1];++i) {
			if(!Gallery.images[i]) {
				return i==els[0]?false:html;
			}
			file=Gallery.images[i];
			html+='<li style="width:'+sizex+'px;height:'+sizex+'px">'
				+Gallery.mediaDisplay(file)+'</li>';
			++Gallery.position;
		};
		return html;
	},
	displayNext:function(num) { // displays the next "page" of content
		switch(this.options.display) {
			case 'custom': // {
				if(typeof(this.options.customDisplayNext)=='function') {
					return this.options.customDisplayNext();
				}
				this.options.display='list'; // }
			case 'list': // {
				var $thumblist=$('.ad-thumb-list');
				if ($thumblist.hasClass('working')) {
					return;
				}
				$thumblist.addClass('working');
				var current=parseInt($thumblist.find('li:last a').attr('id')),
					max=(num==null)?this.options.listSwitch:num, width=0,
					$slider=$('#slider'), left=parseInt($slider.css('left')), list=[];
				for(var i=1;i<=max;++i) {
					list[i-1]=current+i;
				}
				var item=this.displayList(list);
				if(item==false) {
					this.position=0;
					var list=[];
					for(var i=0;i<max;++i) {
						list[i]=i;
					}
					item=this.displayList(list);
				}
				var count=item.split('<li>').length;
				for(var i=0;i<count;++i) {
					width+=$thumblist.find('li:eq('+i+')').width();
				}
				$thumblist.append(item);
				$slider.animate({
					'left':(left-width)+'px'
				}, 100, function() {
					for(var i=0;i<count;++i) {
						$thumblist.find('li:eq(0)').remove();
					}
					$slider.css('left', left+'px');
					$thumblist.removeClass('working')
				});
				break; // }
			case 'grid': // {
				if(this.position==this.count()) {
					return this.bump('left');
				}
				this.displayGrid();
				$('#slider .images-container:first')
					.css('left', '-'+this.width+'px');
				$('#slider .images-container:last')
					.css('left', 0);
				this.slide(this.width); // }
		}
		this.updateNav();
	},
	displayPrevious:function(num) { // does the opposite of displayNext
		switch(this.options.display) {
			case 'custom': // {
				if(typeof(this.options.customDisplayPrevious)=='function') {
					return this.options.customDisplayPrevious();
				}
				this.options.display='list'; // }
			case 'list': // {
				var $thumblist=$('.ad-thumb-list');
				if($thumblist.hasClass('working')) {
					return;
				}
				$thumblist.addClass('working');
				var current=parseInt($thumblist.find('li:first a').attr('id'));
				var max=(num==null)?this.options.listSwitch:num,width=0;
				var left=parseInt($('#slider').css('left'));
				var list=[];
				for(var i=1;i<=max;++i) {
					list[(i-1)]=(current+(i-max)-1);
				}
				var item=this.displayList(list);
				if(item==false) {
					var pos=this.count();
					var list=[];
					for(var i=1;i<=max;++i) {
						list[i-1]=(pos+i-max-1);
					}
					item=this.displayList([pos-2,pos-1]);
				}
				var count=item.split('<li>').length;
				for(var i=1;i<count;++i) {
					width+=$thumblist.find('li:eq('+(this.options.items-i)+')').width();
				}
				$thumblist.prepend(item);
				$('#slider')
					.css('left', -width+'px')
					.animate({
						'left':left+'px'
					},100,function() {
						for(var i=0;i<count;++i) {
							$thumblist.find('li:last').remove();
						}
						$thumblist.removeClass('working');
					});
				break; // }
			case 'grid': // {
				if(this.position<=(this.options.rows*this.options.items)) {
					return this.bump('right');
				}
				this.position-=(this.options.rows*this.options.items)+this.current;
				$('.images-container').css({'left':this.width+'px'});
				this.displayGrid();
				this.slide(-this.width); // }
		}
		this.updateNav();
	},
	gallery:function() {
		if (!Gallery.cached_gallery) {
			Gallery.cached_gallery= $('.ad-gallery');
		}
		return Gallery.cached_gallery;
	},
	init:function() { // collects options from html and sets events
		// { get options from html
		var $gallery=this.gallery();
		var opts={};
		for (var k in this.options) {
			var val=$gallery.attr(k);
			if (val) {
				opts[k]=val;
			}
		}
		if (opts.links=='true') {
			opts.links=true;
		}
		$.extend(this.options, opts);
		// }
		// { thumbsize
		var ts=(''+this.options.thumbsize).replace(/[^0-9\.x]/g, '').split('x');
		this.options.thumbsizex=+ts[0];
		if (ts.length>1) {
			this.options.thumbsizey=+ts[1];
		}
		else {
			this.options.thumbsizey=+ts[0];
		}
		if (this.options.slideshow) {
			this.options.slideshowTime=$gallery.attr('slideshowtime');
		}
		var opts={}, names=["imageHeight","imageWidth","effect"],
			$galleryImage=$('#gallery-image');
		for(var k in names) {
			var val=$galleryImage.attr(names[k]);
			if(val) {
				opts[names[k]]=val;
			}
		}
		$.extend(this.options,opts);
		// }
		$.post('/a/p=image-gallery/f=galleryGet/id='+pagedata.id, {
				'image_gallery_directory':Gallery.options.directory
			}, function(ret) {
				// { cleanup
				for (var i=ret.items.length;i--;) {
					ret.items[i].url=ret.items[i].url.replace(/https?:\/\/[^\/]*\/f/, '');
				}
				// }
				Gallery.images=ret.items;
				Gallery.frame=ret.frame;
				Gallery.caption_in_slider=+ret['caption-in-slider'];
				Gallery.options.imageHeight=+(ret['image-height']||350);
				Gallery.options.imageWidth=+(ret['image-width']||350);
				$('<style> #gallery-image,.ad-image{min-height:'+Gallery.options.imageHeight+'px;}</style>').appendTo('head');
				var length=Gallery.images.length;
				if (length==0) {
					return this.gallery().html('<p><i>No Images were found</i></p>');
				}
				else if (length==1) {
					Gallery.options.links=false;
				}
				Gallery.display();
				var url=document.location.toString();
				if (/pid=[0-9]*$/.test(url)) {
					var pid=url.replace(/.*pid=/, '');
					for (var i=0;i<Gallery.images.length;++i) {
						if (Gallery.images[i].id==pid) {
							Gallery.displayImage(i);
						}
					}
				}
			}, 'json');
		if(this.options.display=='grid') {
			$('#next-link, #prev-link')
				.live('click',function() {	
					if(Gallery.options.slideshow=='true') {
						Gallery.resetTimeout();
					}
					if($('.ad-thumb-list').hasClass('working')) {
						return;
					}
					return this.id=='next-link'
						?Gallery.displayNext()
						:Gallery.displayPrevious();
				});
		}
		if(this.options.display=='list') {
			$('#next-link, #prev-link')
				.live('mouseenter',function() {
					if(Gallery.options.slideshow=='true') {
						Gallery.resetTimeout();
					}
					$el=$(this).addClass('hover');
					setTimeout(function() {
						if ($el.hasClass('hover')) {
							$el.removeClass('hover').trigger('mouseenter');
							return $el.attr('id')=='next-link'
								?Gallery.displayNext()
								:Gallery.displayPrevious();
						}
					},750);
				})
				.live('mouseleave',function() {
					$(this).removeClass('hover');
				});
		}
		if(this.options.hover=='zoom') {
			$('.images-container img')
				.live('mouseenter',function() {
					if(Gallery.options.slideshow=='true') {
						Gallery.resetTimeout();
					}
					$elm=$(this).addClass('img-hover');
					setTimeout(function() {
						if ($elm.hasClass('img-hover')) {
							var width, height;
							if (!$elm.attr('w')) {
								width=$elm.width();
								height=$elm.height();
								$elm
									.attr('w',width)
									.attr('h',height);
							}
							height=parseInt($elm.attr('h'))*1.25;
							width=parseInt($elm.attr('w'))*1.25;
							$elm
								.attr('t','true')
								.animate({
									'width':width+'px',
									'height':height+'px',
									'margin-left':'-12.5%',
									'margin-top':'-12.5%'
								},
									300,
									function() {
										$elm.attr('t','false');
									}
								).addClass('timeout');
							}
						},500);
				})
				.live('mouseleave',function() {
					if ($(this).removeClass('img-hover').hasClass('timeout')) {
						$(this).animate({
							'width':$(this).attr('w')+'px',
							'height':$(this).attr('h')+'px',
							'margin':'0'
						},
							300
						);
					}
				});
		}
		else if(this.options.hover=='opacity') {
			$('.images-container img')
				.live('mouseenter',function() {
					if(Gallery.options.slideshow=='true') {
						Gallery.resetTimeout();
					}
					if($(this).hasClass('image-selected')) {
						return;
					}
					if(!$(this).hasClass('working')) {
						$elm=$(this).addClass('working img-hover');
						$elm.animate({'opacity':'1'},function() {
							$elm.removeClass('working');
						});
					}
				})
				.live('mouseleave',function() {
					if($(this).hasClass('image-selected')) {
						return;
					}
					if(!$(this).hasClass('working')) {
						$elm=$(this).addClass('working');
						$elm.animate({'opacity':'0.7'},function() {
							$elm.removeClass('working img-hover');
						});
					}
				});
		}
		$galleryImage.css({
			'height':Gallery.options.imageHeight+'px',
			'width':Gallery.options.imageWidth+'px'
		});
		setTimeout(function() {
			$galleryImage.addClass('imagegallery-converted');
		}, 1000);
	},
	loadPage:function(num) { // shift the display to a specific page
		var imgsPerPage=Gallery.options.rows*Gallery.options.items;
		var oldPos=Gallery.position;
		var to=imgsPerPage*num;
		if (to>oldPos) {
			Gallery.current=imgsPerPage;
			Gallery.position=to-imgsPerPage;
			Gallery.displayNext();
		}
		else {
			Gallery.current=imgsPerPage;
			Gallery.position=to+imgsPerPage;
			Gallery.displayPrevious();
		}
	},
	mediaDisplay:function(file) {
		var sizex=Gallery.options.thumbsizex, sizey=Gallery.options.thumbsizey;
		var style=' style="width:'+sizex+'px;height:'+sizey+'px;overflow:hidden"';
		var popup=Gallery.options.hover=='popup'
			?' target="popup"'
			:(Gallery.options.hover=='opacity'?' style="opacity:0.7"':'');
		var xy=Gallery.options.ratio=='normal'
			?[sizex, sizey]
			:file.height>file.width
				?[sizex, (file.height*(sizex/file.width))]
				:[(file.width*(sizey/file.height)), sizey];
		var caption=Gallery.caption_in_slider
			?file.caption
			:'';
		var html='<a href="'+(file.media=='image'?file.url:file.href)+'" id="'
			+Gallery.position+'"'+popup+style+'>'
			+'<span class="image"><img src="'+file.url+'/w='+xy[0]+'/h='+xy[1]+'"/></span>'
			+'<span class="caption">'+caption+'</span></a>';
		return html;
	},
	resetTimeout:function() { // resets the slideshow timeout
		clearTimeout(Gallery.t);
		Gallery.t=setTimeout("Gallery.slideshow()", Gallery.options.slideshowTime);
	},
	slide:function(w) {
		$('#slider')
			.css('left', w+'px')
			.animate({'left':0},1750,function() {
				$('#slider .images-container:first').remove();
			});
	},
	slideshow:function() { // creates a slideshow using settimeout
		if($('.ad-image').hasClass('working')) {
			return;
		}
		$('.ad-image').addClass('working');
		var n=parseInt($('.ad-image img').attr('num'));
		$('#'+n+' img').removeClass('image-selected');
		n=(n+1)==Gallery.count()?0:++n;
		$('#'+n+' img').addClass('image-selected');
		switch(this.options.display) {	
			case 'custom':
				if(typeof(this.options.customDisplaySlideshow)=='function') {
					return this.options.customDisplaySlideshow();
				}
				this.options.display='list';
			case 'list':	
				Gallery.displayNext(1);
				Gallery.displayImage(n);
				break;
			case 'grid':
				if(n!=0&&n%(this.options.items*this.options.rows)==0) {
					Gallery.displayNext();
				}
				else if(n==(this.count()-1)) {
					Gallery.displayPrevious();
				}
				Gallery.displayImage(n);
				break;
		}
		this.resetTimeout();
		$('.ad-image').removeClass('working');
	},
	updateNav:function() { // update page nav if there is one
		var $nav=$('#image-gallery-nav'), length=Gallery.images.length;
		if (!$nav.length) {
			return;
		}
		var imgsPerPage=Gallery.options.rows*Gallery.options.items,
			curPage=Math.ceil(Gallery.position/imgsPerPage),
			numPages=Math.ceil(Gallery.images.length/imgsPerPage);
		var links=[];
		for (var i=1;i<numPages+1;++i) {
			var link='<a href="javascript:Gallery.loadPage('+i+');"';
			if (i==curPage) {
				link+=' class="current"';
			}
			link+='>'+i+'</a>';
			links.push(link);
		}
		$nav.find('td.pagelinks').html(links.join('')+'<br style="clear:both"/>');
	}
};
$(function() {
	Gallery.init();
	$('.ad-image img').live('click', function() {
		if (Gallery.options.click=='wholepage') {
			return;
		}
		var $this=$(this), sequence=$this.attr('sequence').split(','), i;
		var files=Gallery.images;
		for (i=0; i<files.length; ++i) {
			sequence[i]=files[i].url;
		}
		lightbox_show(
			$this.attr('image'),
			sequence,
			$this.attr('num')
		);
	})
	$('#big-next-link').live('click',function() {
		if(Gallery.options.slideshow=='true') {
			Gallery.resetTimeout();
		}
		if(!$('.ad-thumb-list').hasClass('working')) {
			var $imgWrapper=$('.ad-image');
			$imgWrapper.find('.tag').remove();
			var n=+$imgWrapper.find('img').attr('num');
			$('#'+n+' img').removeClass('image-selected');
			n=((n+1)==Gallery.count())?0:++n;
			$('#'+n+' img').addClass('image-selected');
			if ($('td.images-container:visible').length) {
				Gallery.displayNext(1);
			}
			Gallery.displayImage(n);
		}
	});
	$('#big-prev-link').live('click',function() {
		if(Gallery.options.slideshow=='true') {
			Gallery.resetTimeout();
		}
		if(!$('.ad-thumb-list').hasClass('working')) {
			var $imgWrapper=$('.ad-image');
			$imgWrapper.find('.tag').remove();
			var n=+$imgWrapper.find('img').attr('num');
			$('#'+n+' img').removeClass('image-selected');
			n=(n==0)?(Gallery.count()-1):--n;
			$('#'+n+' img').addClass('image-selected');
			if ($('td.images-container:visible').length) {
				Gallery.displayPrevious(1);
			}
			Gallery.displayImage(n);
		}
	});
	$('.images-container a').live('click',function() {
		if(Gallery.options.slideshow=='true') {
			Gallery.resetTimeout();
		}
		var i=$(this).attr('id');
		$('.images-container img').removeClass('image-selected');
		$('img',this).addClass('image-selected');
		Gallery.displayImage(i);
		if(Gallery.options.hover!='popup') {
			return false;
		}
	});
});
