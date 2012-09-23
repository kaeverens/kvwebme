// gallery object
//
// used to create image galleries of different
// layouts/types
// @author Conor Mac Aoidh <conormacaoidh@gmail.com>
var Gallery={
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
		var $imgwrap=$('.ad-image');
		if (!$imgwrap.length && this.displayed) {
			$('<a href="javascript:Gallery.closeWholepageImage()" '
				+'class="image-gallery-close-wholepage">back</a>')
				.appendTo('#image-gallery-wrapper');
			$imgwrap=$('<div id="gallery-image">'
				+'<div class="ad-image wholepage">'
				+'<h1 class="caption"/>'
				+'<span class="image"><img/></span>'
				+'<p class="description"/>'
				+'<em style="display:block;text-align:right" class="author"/>'
				+'</div></div>')
				.appendTo('#image-gallery-wrapper');
			this.addLinksToLargeImage();
			$('div.ad-gallery,#image-gallery-nav').css('display', 'none');
			if (Gallery.options.hidesidebar) {
				$('#sidebar1').toggle().attr('id', 'sidebar1_bak');
			}
		}
		var $img=$imgwrap.find('img');
		var files=this.images;
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
					:files[e].url+'/w='+this.options.imageWidth+'/h='+this.options.imageHeight;
				$img
					.hide()
					.css({'width':'auto','height':'auto'})
					.attr('src', src)
					.attr('title',files[e].caption)
					.attr('num',e)
					.attr('sequence', sequence)
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
						var $wrapper=$imgwrap.closest('#gallery-image');
						if (spanheight==0) {
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
					if (!$imgwrap.is('.wholepage')) {
						var $tmp=$imgwrap.find('.wholepage');
						if ($tmp.length) {
							$imgwrap=$tmp;
						}
					}
					$imgwrap.css({
						'width':width+'px',
						'height':height+'px'
					});
					$img
						.hide()
						.attr('src','')
						.attr('title','')
						.attr('num',e)
						.attr('sequence',sequence);
					$imgwrap.append('<div id="image-gallery-video" style="display:none">');
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
		this.displayed++; // mark that at least one image has been displayed
	},
	displayImageCallback:function() { // executed when display image animation complete
		if(typeof(Gallery.options.customDisplayCaption)=='function') {
			return Gallery.options.customDisplayCaption();
		}
		var $img=$('.ad-image img');
		var index=+$img.attr('num'), idata=Gallery.images[index];
		// { madly over-coded caption stuff
		var caption=idata.caption;
		var $caption=$('.ad-image .caption');
		if (caption=="") {
			$caption.hide();
		}
		else {
			var width=$img.width()-14;
			var offset=$img.height();
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
				+Gallery.mediaDisplay(file)
				+ '</li>';
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
		var opts={}, names=["imageHeight","imageWidth","effect"];
		for(var k in names) {
			var val=$('#gallery-image').attr(names[k]);
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
		$('#gallery-image').css({
			'height':Gallery.options.imageHeight+'px',
			'width':Gallery.options.imageWidth+'px'
		});
		setTimeout(function() {
			$('#gallery-image').addClass('imagegallery-converted');
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
			var n=+$('.ad-image img').attr('num');
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
			var n=+$('.ad-image img').attr('num');
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


function lightbox_show(src, sequence, seq_num) {
	$('#lightbox-image').closest('table').dialog('close');
	var max_width=parseInt($(window).width()*.9),
		max_height=parseInt($(window).height()*.9);
	if (/kfmget\/[0-9]/.test(src)) {
		src=src.replace(/,.*/, '');
		src=src+',width='+max_width+',height='+max_height;
	}
	var left_arrow='',right_arrow='';
	var width_to_add=26;
	sequence=sequence.toString().split(',');
	if (sequence.length>1) {
		var lnum=+seq_num-1;
		if (lnum<0) {
			lnum=sequence.length-1;
		}
		left_arrow='<td><a href="javascript:lightbox_show(\''
			+sequence[lnum]+'\',\''+sequence+'\','+lnum
			+');"><img src="/ww.plugins/image-gallery/frontend/arrow-left.png"/>'
			+'</a></td>';
		var rnum=+seq_num+1;
		if (rnum>=sequence.length) {
			rnum=0;
		}
		right_arrow='<td><a href="javascript:lightbox_show(\''
			+sequence[rnum]+'\',\''+sequence+'\','+rnum
			+');"><img src="/ww.plugins/image-gallery/frontend/arrow-right.png"/>'
			+'</a></td>';
		width_to_add+=60;
	}
	$('object').each(function(){
		var $this=$(this);
		$this.attr('lightbox-visibility', $this.css('visibility'));
		$this.css('visibility', 'hidden');
	});
	$('<table><tr>'+left_arrow+'<td><img id="lightbox-image" src="'+src+'"/></td>'+right_arrow+'</tr></table>')
		.dialog({
			"modal":true,
			"close":function(){
				$(this).remove();
				$('object').each(function(){
					var $this=$(this);
					$this.css('visibility', $this.attr('lightbox-visibility'));
					$this.removeAttr('lightbox-visibility');
				});
			}
		});
	$('#lightbox-image').load(function(){
		var $this=$(this);
		while ($this[0].offsetWidth>max_width || $this[0].offsetHeight>max_height) {
			var r=max_width/$this[0].offsetWidth;
			var r2=max_height/$this[0].offsetHeight;
			if (r>r2) {
				r=r2;
			}
			$($this[0]).css({
				'width':$this[0].offsetWidth*r,
				'height':$this[0].offsetHeight*r
			});
		}
		$this.closest('table').dialog({
			width:$this[0].offsetWidth+width_to_add
		});
		var $dialog=$this.closest('.ui-dialog');
		$dialog.css({
			"left":$(window).width()/2-$dialog[0].offsetWidth/2,
			"top":$(window).height()/2-$dialog[0].offsetHeight/2+$(document).scrollTop()
		});
	});
}
