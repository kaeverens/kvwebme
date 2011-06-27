// gallery object
//
// used to create image galleries of different
// layouts/types
// @author Conor Mac Aoidh <conormacaoidh@gmail.com>
var Gallery={
	options:{ // default options for the gallery
		// list, grid or custom
		display:'list',
		// columns, rows
		items:6,
		rows:1,
		// thumbnail size
		thumbsize:90,
		// set false to disable next and previous links
		// - these can be set up manually
		links:true,
		// "hover" effect. although the popup actually
		// happens on click, the zoom and opacity happen on hover
		hover:'opacity',
		// main image dimensions
		imageHeight:350,
		imageWidth:350,
		// main image transition effect
		effect:'fade',
		// set the ratio ( of the thumbnails ) - normal or crop
		ratio:'normal',
		// this option adds partial support for changing
		// the amount of items loaded each time the next/prev
		// links are clicked. only tested with vals 1 and 2
		listSwitch:2,
		// slideshow
		slideshow:false,
		// slideshow interval between slide change
		slideshowTime:2500,
		// default is empty string, only used for non image-gallery plugin
		// uses of the gallery
		directory:'',
		// custom display functions
		// for adding a new method of
		// displaying the gallery
		customDisplayInit:null,
		customDisplayNext:null,
		customDisplayPrevious:null,
		customDisplaySlideshow:null,
		customDisplayImageCallback:null,
		customDisplayCaption:null
	},
	// keeps track of how far through the images.files
	// array the grid display is
	position:0,
	width:0,
	// grid display - the count of how many items are
	// displayed currently
	current:0,
	height:0,
	// used to hold the timeout for the slideshow function
	t:null,
	// holds the images associated with this gallery
	// populated in the init function
	images:{},
	// returns a selector for gallery-images
	gallery:function(){ return $('.ad-gallery') },
	init:function(){ // collects options from html and sets events
		// { get options from html
		var $gallery=this.gallery();
		var opts={};
		var names={
			"display":"display", "thumbsize":"thumbsize", "imageHeight":"height",
			"imageWidth":"width", "effect":"effect", "hover":"hover",
			"slideshow":"slideshow", "directory":"directory", "ratio":"ratio"
		};
		for (var k in names) {
			var val=$gallery.attr(names[k]);
			if (val) {
				opts[k]=val;
			}
		}
		$.extend(this.options, opts);
		this.options.thumbsize=parseInt(this.options.thumbsize);
		if (this.options.slideshow) {
			this.options.slideshowTime=$gallery.attr('slideshowtime');
		}
		// }
		$.post(
			'/ww.plugins/image-gallery/frontend/get-images.php?id='+pagedata.id,
			{'image_gallery_directory':Gallery.options.directory },
			function(items){
				Gallery.images=items;
				if(Gallery.images.files.length==0)
					return Gallery.noImages();
				Gallery.display();
			},
			'json'
		);
		if(this.options.display=='grid'){
			$('#next-link')
				.live('click',function(){	
					if(Gallery.options.slideshow=='true')
						Gallery.resetTimeout();
					if($('.ad-thumb-list').hasClass('working'))
						return;
					Gallery.displayNext();
				});
			$('#prev-link')	
				.live('click',function(){
					if(Gallery.options.slideshow=='true')
						Gallery.resetTimeout();
					if($('.ad-thumb-list').hasClass('working'))
						return;
					Gallery.displayPrevious();
				});
		}
		$('.images-container a').live('click',function(){
			if(Gallery.options.slideshow=='true')
				Gallery.resetTimeout();
			var i=$(this).attr('id');
			$('.images-container img').removeClass('image-selected');
			$('img',this).addClass('image-selected');
			Gallery.displayImage(i);
			if(Gallery.options.hover!='popup')
				return false;
		});
		if(this.options.display=='list'){
			$('#next-link').live('mouseenter',function(){
				if(Gallery.options.slideshow=='true')
					Gallery.resetTimeout();
				$(this).addClass('hover');
				$elm=$(this);
				setTimeout(function(){
					if($elm.hasClass('hover')){
						Gallery.displayNext();
						$elm.removeClass('hover');
						$elm.trigger('mouseenter');
					}
				},750);
			}).live('mouseleave',function(){
				$(this).removeClass('hover');
			});	
			$('#prev-link').live('mouseenter',function(){
				if(Gallery.options.slideshow=='true')
					Gallery.resetTimeout();
				$(this).addClass('hover');
				$elm=$(this);
				setTimeout(function(){
					if($elm.hasClass('hover')){
						Gallery.displayPrevious();
						$elm.removeClass('hover');
						$elm.trigger('mouseenter');
					}
				},750);
			}).live('mouseleave',function(){
				$(this).removeClass('hover');
			});
		}
		if(this.options.hover=='zoom'){
			$('.images-container img').live('mouseenter',function(){
				if(Gallery.options.slideshow=='true')
					Gallery.resetTimeout();
				$(this).addClass('img-hover');
				$elm=$(this);
				setTimeout(function(){
					if($elm.hasClass('img-hover')){
						var width,height;
						if(!$elm.attr('w')){
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
								function(){
									$elm.attr('t','false');
								}
							).addClass('timeout');
						}
					},500);
			}).live('mouseleave',function(){
				if($(this).hasClass('timeout')){
					var width=$(this).attr('w');
					var height=$(this).attr('h');
					$(this).animate({
						'width':width+'px',
						'height':height+'px',
						'margin':'0'
					},
						300
					);
				}
				$(this).removeClass('img-hover');
			});
		}
		else if(this.options.hover=='opacity'){
			$('.images-container img').live('mouseenter',function(){
				if(Gallery.options.slideshow=='true')
					Gallery.resetTimeout();
				if($(this).hasClass('image-selected'))
					return;
				if(!$(this).hasClass('working')){
					$elm=$(this);
					$elm.addClass('working').addClass('img-hover');
					$elm.animate({'opacity':'1'},function(){
						$elm.removeClass('working');
					});
				}
			}).live('mouseleave',function(){
				if($(this).hasClass('image-selected'))
					return;
				if(!$(this).hasClass('working')){
					$elm=$(this);
					$elm.addClass('working');
					$elm.animate({'opacity':'0.7'},function(){
						$elm.removeClass('working').removeClass('img-hover');
					});
				}
			});
		}
		$('#gallery-image').css({'height':Gallery.options.imageHeight+'px',
			'width':Gallery.options.imageWidth+'px'});
	},
  count:function(){ // counts the images object
    var size = 0, key;
    for (key in this.images.files)
      ++size;
    return size;
  },
	display:function(){ // initial display function - gets called once
		this.displayImage(0);
		var html='<div id="gallery-container">'
							+'<div id="slider"></div>'
						+'</div>';
		this.gallery().html(html);
		switch(this.options.display){	
			case 'custom':
				if(typeof(this.options.customDisplayInit)=='function'){
					return this.options.customDisplayInit();
				}
				this.options.display='list';
			case 'list':
				var items=this.gallery().attr('cols');
				this.options.items=(items)?parseInt(items):6;
				this.options.rows=1;
				this.width=((this.options.thumbsize+2)*this.options.items);
				this.gallery().addClass('list');
				var els=[];
				for(var i=0;i<this.options.items;++i)
					els[i]=i;
				var list=this.displayList(els);
				var html='<div class="images-container" style="overflow:hidden"><ul class="ad-thumb-list"'
				+' style="width:'+(this.width+400)+'px">'+list+'</ul></div>';
				$('#slider').html(html);
				this.gallery().css({'width':this.width+'px'});
				this.height=(this.options.thumbsize+15);
				$('.ad-thumb-list').css({'height':this.height+'px'});
				if(this.options.links==true){ // next/prev links
					$('#gallery-image .ad-image').append(
						'<div id="big-prev-link"></div><div id="big-next-link"></div>'
					);
					$('#big-next-link,#big-prev-link').css({'height':this.options.imageHeight+'px'});
					$('#big-next-link').live('click',function(){
						if(Gallery.options.slideshow=='true')
							Gallery.resetTimeout();
						if(!$('.ad-thumb-list').hasClass('working')){
								var n=parseInt($('.ad-image img').attr('num'));
								$('#'+n+' img').removeClass('image-selected');
								n=((n+1)==Gallery.count())?0:++n;
								$('#'+n+' img').addClass('image-selected');
								Gallery.displayNext(1);
								Gallery.displayImage(n);
						}
					});
					$('#big-prev-link').live('click',function(){
						if(Gallery.options.slideshow=='true')
							Gallery.resetTimeout();
						if(!$('.ad-thumb-list').hasClass('working')){
							var n=parseInt($('.ad-image img').attr('num'));
							$('#'+n+' img').removeClass('image-selected');
							n=(n==0)?(Gallery.count()-1):--n;
							$('#'+n+' img').addClass('image-selected');
							Gallery.displayPrevious(1);
							Gallery.displayImage(n);
						}
					});
				}
			break;
			case 'grid':
				var items=this.gallery().attr('cols');
				this.options.items=(items)?parseInt(items):4;
				var rows=this.gallery().attr('rows');
				this.options.rows=(rows)?parseInt(rows):4;
				this.gallery().addClass('grid');
				this.displayGrid();
				this.width=((this.options.thumbsize+2)*this.options.items)+4;
				this.gallery().css({'width':this.width+'px'});
			break;
		}
		this.height=(this.options.thumbsize+15)*this.options.rows;
		$('#gallery-container').css({'height':this.height+'px'});
		if(this.options.links==true){ // next/prev links
			this.gallery().append(
				'<div id="prev-link"></div><div id="next-link"></div>'
			);
			$('#next-link,#prev-link').css({'height':this.height+'px'});
		}
		$('.images-container img:first').addClass('image-selected');		
		if(this.options.slideshow=='true'){ // activate slideshow
			setTimeout("Gallery.slideshow()",this.options.slideshowTime);
		}
	},
	displayGrid:function(){ // shows the grid display using a carousel
		var file,size=this.options.thumbsize,row=0,j,html='<table class="images-container"><tr>';
		this.current=0;
		$.each(this.images.files,function(i){
			if(i%Gallery.options.items==0){
				++row;
				html+='</tr><tr>';
			}
			j=Gallery.position;
			if(row==(Gallery.options.rows+1)||!Gallery.images.files[j])
				return false;
			file=Gallery.images.files[j];
			var dimensions=Gallery.ratio(file);
			html+='<td style="width:'+size+'px">'
					+ Gallery.imgHTML(file.id, j, dimensions, size)+ '</td>';
			++Gallery.position;
			++Gallery.current;
		});
		html+='</tr></table>';
		$('#slider').append(html);
	},
	displayList:function(els){ // displays elements from this.images.files in a list
		var file,size=this.options.thumbsize,html='',i;
		for(i=els[0];i<=els[els.length-1];++i){
			if(!Gallery.images.files[i]){
				return i==els[0]?false:html;
			}
			file=Gallery.images.files[i];
			var dimensions=Gallery.ratio(file);
			html+='<li style="width:'+size+'px;">'
				+Gallery.imgHTML(file.id, i, dimensions, size)
				+ '</li>';
		};
		return html;
	},
	imgHTML: function(fid, id, xy, size) {
		var style=Gallery.options.ratio=='crop'
			?' style="width:'+size+'px;height:'+size+'px;overflow:hidden"':'';
		var popup=Gallery.options.hover=='popup'
			?' target="popup"'
			:(Gallery.options.hover=='opacity'?' style="opacity:0.7"':'');
		return '<a href="/kfmget/'+fid+'" id="'+id+'"'+popup+style+'><img src="'
			+'/kfmget/'+fid+',width='+xy[0]+',height='+xy[1]+'"/></a>';
	},
	displayNext:function(num){ // displays the next "page" of content
		switch(this.options.display){
			case 'custom':
				if(typeof(this.options.customDisplayNext)=='function'){
					return this.options.customDisplayNext();
				}
				this.options.display='list';
			case 'list':
				if($('.ad-thumb-list').hasClass('working'))
					return;
				$('.ad-thumb-list').addClass('working');
				var current=parseInt($('.ad-thumb-list li:last a').attr('id'));
				var max=(num==null)?this.options.listSwitch:num,width=0;
				var left=parseInt($('#slider').css('left'));
				var list=[];
				for(var i=1;i<=max;++i)
					list[(i-1)]=(current+i);
				var item=this.displayList(list);
				if(item==false){
					this.position=0;
					var list=[];
					for(var i=0;i<max;++i)
						list[i]=i;
					item=this.displayList(list);
				}
				var count=item.split('li');
				count=(count.length-1)/2;
				for(var i=0;i<count;++i)
					width+=$('.ad-thumb-list li:eq('+i+')').width();
				$('.ad-thumb-list').append(item);
				$('#slider').animate({
					'left':(left-width)+'px'
				},2000,function(){
					for(var i=0;i<count;++i)
						$('.ad-thumb-list li:eq(0)').remove();
					$('#slider').css({'left':left+'px'});
					$('.ad-thumb-list').removeClass('working')
				});
				break;
			case 'grid':
				if(this.position==this.count())
					return this.bump('left');
				this.displayGrid();
				$('#slider .images-container:first')
					.css({'left':'-'+this.width+'px'});
				$('#slider .images-container:last')
					.css({'left':0});
				$('#slider')
					.css({'left':this.width+'px'})
					.animate({'left':0},1750,function(){
      	  	$('#slider .images-container:first').remove();
					});
				break;
		}
	},
	displayPrevious:function(num){ // does the opposite of displayNext
		switch(this.options.display){
			case 'custom':
				if(typeof(this.options.customDisplayPrevious)=='function'){
					return this.options.customDisplayPrevious();
				}
				this.options.display='list';
			case 'list':
				if($('.ad-thumb-list').hasClass('working'))
					return;
				$('.ad-thumb-list').addClass('working');
				var current=parseInt($('.ad-thumb-list li:first a').attr('id'));
				var max=(num==null)?this.options.listSwitch:num,width=0;
				var left=parseInt($('#slider').css('left'));
				var list=[];
				for(var i=1;i<=max;++i)
					list[(i-1)]=(current+(i-max)-1);
				var item=this.displayList(list);
				if(item==false){
					var pos=this.count();
					var list=[];
					for(var i=1;i<=max;++i)
						list[(i-1)]=(pos+(i-max)-1);
					item=this.displayList([(pos-2),(pos-1)]);
				}
				var count=item.split('li');
				count=(count.length-1)/2;
				for(var i=1;i<count;++i)
					width+=$('.ad-thumb-list li:eq('+(this.options.items-i)+')').width();
				$('.ad-thumb-list').prepend(item);
				$('#slider')
					.css({'left':'-'+width+'px'})
					.animate({
						'left':left+'px'
					},2000,function(){
						for(var i=0;i<count;++i)
							$('.ad-thumb-list li:last').remove();
						$('.ad-thumb-list').removeClass('working');
					});
				break;
			case 'grid':
				if(this.position<=(this.options.rows*this.options.items))
					return this.bump('right');
				this.position-=(this.options.rows*this.options.items)+this.current;
				$('.images-container').css({'left':this.width+'px'});
				this.displayGrid();
				$('#slider')
					.css({'left':'-'+this.width+'px'})
					.animate({'left':0},1750,function(){
						$('#slider .images-container:first').remove();
					});
				break;
		}
	},
	bump:function(offset){ // bump effect
		var pos=parseInt($('#slider').css('left'));
		$('#slider').animate(
			{'left':(offset=='left')?(pos-20)+'px':(pos+20)+'px'}
			,200,function(){
			$(this).animate({'left':0},200);
		});
		return false;
	},
	displayImage:function(e){ // displays the main "big" image if present
		var files=this.images.files;
		if(!files[e])
			return;
		var current=$('.ad-image img').attr('num');
		var sequence=[];
		for (var i=0;i<files.length;++i) {
			sequence[i]=files[i].id;
		}
		$('.ad-image span').hide();
		$('.ad-image img')
			.hide()
			.attr(
				'src','/kfmget/'+files[e].id+',width='
				+ this.options.imageWidth+',height='
				+ this.options.imageHeight
			)
			.attr('title',files[e].caption)
			.attr('num',e)
			.attr('sequence', sequence)
			.one('load',function(){
				$('.ad-image').css({'width':$('.ad-image img').width()+'px','height':Gallery.options.imageHeight+'px'});
				switch(Gallery.options.effect){
          case 'fade': 
            $(this).fadeIn('slow',Gallery.displayImageCallback); 
          	break; 
          case 'slideVertical': 
						$(this).show('slide',{'direction':(current<e?'up':'down')},500,Gallery.displayImageCallback);
          	break; 
          case 'slideHorizontal': 
						$(this).show('slide',{'direction':(current<e?'right':'left')},500,Gallery.displayImageCallback);
          	break; 
				}
			});
	},
	displayImageCallback:function(){ // executed when display image animation complete
		Gallery.caption();
		if(typeof(Gallery.options.customDisplayImageCallback)=='function'){
			return Gallery.options.customDisplayImageCallback();
		}
	},
	caption:function(){ // displays the caption on the main image
		if(typeof(Gallery.options.customDisplayCaption)=='function')
			return Gallery.options.customDisplayCaption();
		var caption=$('.ad-image img').attr('title');
		if(caption=="")
			return;
		var width=$('.ad-image img').width()-14;
		$('.ad-image span')
			.html(caption)
			.css('width', width+'px')
			.slideDown('fast');
	},
	slideshow:function(){ // creates a slideshow using settimeout
		if($('.ad-image').hasClass('working'))
			return;
		$('.ad-image').addClass('working');
		var n=parseInt($('.ad-image img').attr('num'));
		$('#'+n+' img').removeClass('image-selected');
		n=(n+1)==Gallery.count()?0:++n;
		$('#'+n+' img').addClass('image-selected');
		switch(this.options.display){	
			case 'custom':
				if(typeof(this.options.customDisplaySlideshow)=='function'){
					return this.options.customDisplaySlideshow();
					break;
				}
				else
					this.options.display='list';
			case 'list':	
				Gallery.displayNext(1);
				Gallery.displayImage(n);
				break;
			case 'grid':
				if(n!=0&&n%(this.options.items*this.options.rows)==0)
					Gallery.displayNext();
				else if(n==(this.count()-1))
					Gallery.displayPrevious();
				Gallery.displayImage(n);
				break;
		}
		Gallery.t=setTimeout("Gallery.slideshow()",Gallery.options.slideshowTime);
		$('.ad-image').removeClass('working');
	},
	resetTimeout:function(){ // resets the slideshow timeout
		clearTimeout(Gallery.t);
		Gallery.t=setTimeout("Gallery.slideshow()", Gallery.options.slideshowTime);
	},
	noImages:function(){ // die message - there are no images
		return this.gallery().html('<p><i>No Images were found</i></p>');
	},
	ratio:function(file){
		if(this.options.ratio=='normal')
			return [this.options.thumbsize,this.options.thumbsize];
		return file.height>file.width
			?[this.options.thumbsize, (file.height*(this.options.thumbsize/file.width))]
			:[(file.width*(this.options.thumbsize/file.height)), this.options.thumbsize];
	}
};

$(function(){
	// initialise the gallery
	Gallery.init();
	$('.ad-image img')
		.live('click', function(){
			var $this=$(this);
			var sequence=$this.attr('sequence').split(',');
			for (var i=0;i<sequence.length;++i) {
				sequence[i]='/kfmget/'+sequence[i];
			}
			lightbox_show($this.attr('src').replace(/,.*/, ''), sequence, $this.attr('num'));
		})
});
