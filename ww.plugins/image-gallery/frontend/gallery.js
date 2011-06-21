var Gallery={
	options:{
		display:'list',
		items:6,
		rows:1,
		transition:'fade',
		thumbsize:90,
		links:true,
		hover:'popup',
		imageHeight:350,
		imageWidth:350,
		effect:'fade',
	},
	position:0,
	listPosition:0,
	width:0,
	current:0,
	height:0,
	slide:0,
	images:{},
	gallery:function(){ return $('.ad-gallery') },
	init:function(){
		// { get options from html
		var display=this.gallery().attr('display');
		if(display)
			this.options.display=display;
		var thumbsize=this.gallery().attr('thumbsize');
		if(thumbsize)
			this.options.thumbsize=parseInt(thumbsize);
		var imageHeight=$('#gallery-image').attr('height');
		if(imageHeight){
			this.options.imageHeight=imageHeight;
			$('#gallery-image').css({'height':imageHeight});
		}
		var imageWidth=$('#gallery-image').attr('width');
		if(imageWidth){
			this.options.imageWidth=imageWidth;
			$('#gallery-image').css({'width':imageWidth});
		}
		var effect=$('#gallery-image').attr('effect');
		if(effect)
			this.options.effect=effect;
		var hover=this.gallery().attr('hover');
		if(hover)
			this.options.hover=hover;
		// }

		$.get(
			'/ww.plugins/image-gallery/frontend/get-images.php?id='+pagedata.id,
			function(items){
				Gallery.images=items;
				Gallery.display();
			},
			'json'
		);
		$('#next-link').live('click',function(){
			Gallery.displayNext();
		});
		$('#prev-link').live('click',function(){
			Gallery.displayPrevious();
		});

		$('.images-container a').live('click',function(){
			var i=$(this).attr('id');
			$('.images-container img').removeClass('image-selected');
			$('img',this).addClass('image-selected');
			Gallery.displayImage(i);
			if(Gallery.options.hover!='popup')
				return false;
		});

		if(this.options.display=='list'){
			$('#next-link').live('mouseenter',function(){
				$(this).addClass('hover');
				$elm=$(this);
				setTimeout(function(){
					if($elm.hasClass('hover')){
						Gallery.displayNext();
						$elm.removeClass('hover');
						$elm.trigger('mouseenter');
					}
				},500);
			}).live('mouseleave',function(){
				$(this).removeClass('hover');
			});	
			$('#prev-link').live('mouseenter',function(){
				$(this).addClass('hover');
				$elm=$(this);
				setTimeout(function(){
					if($elm.hasClass('hover')){
						Gallery.displayPrevious();
						$elm.removeClass('hover');
						$elm.trigger('mouseenter');
					}
				},500);
			}).live('mouseleave',function(){
				$(this).removeClass('hover');
			});
		}

		if(this.options.hover=='zoom'){
			$('.images-container img').live('mouseenter',function(){
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
								'margin-top':'-12.5%',
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
						'margin':'0',
					},
						300
					);
				}
				$(this).removeClass('img-hover');
			});
		}
	},
	// counts the images object
  count:function(){
    var size = 0, key;
    for (key in this.images.files)
      ++size;
    return size;
  },
	display:function(){
		this.displayImage(0);
		var html='<div id="gallery-container">'
							+'<div id="slider"></div>'
						+'</div>';
		this.gallery().html(html);
		switch(this.options.display){	
			case 'custom':
				if(typeof(this.options.customDisplay)=='function'){
					return this.options.customDisplay;
					break;
				}
				else
					this.options.display='list';
			case 'list':
				this.width=((this.options.thumbsize+6)*this.options.items);
				var items=this.gallery().attr('cols');
				this.options.items=(items)?parseInt(items):6;
				this.options.rows=1;
				this.gallery().addClass('list');
				var html='<div class="images-container"><ul class="ad-thumb-list"'
				+' style="width:'+(this.width+200)+'px">'
				+'</ul><ul class="holder-list" style="display:none"></ul></div>';
				$('#slider').html(html);
				this.displayList();
				$('.holder-list li').clone().appendTo('.ad-thumb-list');
				this.gallery().css({'width':this.width+'px'});
				this.listPosition=(++this.options.items);
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
			if(this.options.display=='list'){
				$('#gallery-image .ad-image').append(
					'<div id="big-prev-link"></div><div id="big-next-link"></div>'
				);
				$('#big-next-link,#big-prev-link').css({'height':this.options.imageHeight+'px'});
				$('#big-next-link').live('click',function(){
					var n=parseInt($('.ad-image img').attr('num'));
					if(n!=Gallery.count())
						++n;
					Gallery.displayNext();
					Gallery.displayImage((n+1));
				});
				$('#big-prev-link').live('click',function(){
					var n=parseInt($('.ad-image img').attr('num'));
					--n;
					var ret=Gallery.displayPrevious();
					if(ret==false&&n==-1)
						return false;
					Gallery.displayImage(n);
				});
			}
			this.gallery().append(
				'<div id="prev-link"></div><div id="next-link"></div>'
			);
			$('#next-link,#prev-link').css({'height':this.height+'px'});
		}
		$('.images-container img:first').addClass('image-selected');		
	},
	displayGrid:function(){
		var file,size=this.options.thumbsize,popup
		,row=0,html='<table class="images-container"><tr>';
		this.current=0;
		$.each(this.images.files,function(i){
			if(i%Gallery.options.items==0){
				++row;
				html+='</tr><tr>';
			}
			if(row==(Gallery.options.rows+1)||!Gallery.images.files[Gallery.position])
				return false;
			file=Gallery.images.files[Gallery.position];
			popup=(Gallery.options.hover=='popup')?' target="popup"':'';
			html+='<td>'
					+ '<a href="/kfmget/'+file.id+'" id="'+Gallery.position+'"'+popup+'>'
						+ '<img src="/kfmget/'+file.id+',width='+size+',height='+size+'"/>'
					+ '</a>'
				+ '</td>';
			++Gallery.position;
			++Gallery.current;
		});
		html+='</tr></table>';
		$('#slider').append(html);
	},
	displayList:function(pos){
		var file,size=this.options.thumbsize,popup
		html='',row=0;
		$.each(this.images.files,function(i){
			if(!Gallery.images.files[Gallery.position])
				return false;
			if(i==Gallery.options.items)
				return false;
			file=Gallery.images.files[Gallery.position];
			popup=(Gallery.options.hover=='popup')?' target="popup"':'';
			html+='<li>'
					+ '<a href="/kfmget/'+file.id+'" id="'+Gallery.position+'"'+popup+'>'
						+ '<img src="/kfmget/'+file.id+',width='+size+',height='+size+'"/>'
					+ '</a>'
				+ '</li>';
			++Gallery.position;
		});
		$('.holder-list').append(html);
	},
	displayNext:function(){
		switch(this.options.display){
			case 'list':
				if($('.ad-thumb-list').hasClass('working'))
					return;
				$('.ad-thumb-list').addClass('working');
				var current=$('.ad-thumb-list li:eq(0)');
				var width=current.width();
				var left=parseInt($('#slider').css('left'));
				var next;
				if(this.listPositon>this.options.items)
					next=this.listPosition+this.options.items;
				else
					next=this.listPosition;
				if(next>=this.count()){
					next=0;
					this.listPosition=0;
				}
				if(next>=$('.holder-list > li').size())
					this.displayList();
				$('.holder-list li:eq('+next+')').clone().appendTo('.ad-thumb-list');
				$('#slider').animate({
					'left':(left-width)+'px'
				},500,function(){
					$('.ad-thumb-list li:first').remove();
					$('#slider').css({'left':left+'px'});
					$('.ad-thumb-list').removeClass('working')
				});
				++this.listPosition;
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
				.animate({'left':'0'},1750,function(){
        	$('#slider .images-container:first').remove();
				});
			break;
		}
	},
	displayPrevious:function(){
		switch(this.options.display){
			case 'list':
				if($('.ad-thumb-list').hasClass('working'))
					return;
				$('.ad-thumb-list').addClass('working');
				var current=$('.ad-thumb-list li:last');
				var width=current.width();
				var left=parseInt($('#slider').css('left'));
				var prev=(this.listPosition-this.options.items-1);
				if(prev<0){
					$('.ad-thumb-list').removeClass('working');
					return this.bump('right');
				}
				$('.holder-list li:eq('+prev+')')
					.clone()
					.prependTo('.ad-thumb-list');
				$('#slider')
					.css({'left':'-'+width+'px'})
					.animate({
						'left':left+'px'
					},500,function(){
						$('.ad-thumb-list li:last').remove();
						$('.ad-thumb-list').removeClass('working')
					});
				--this.listPosition;
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
	// bump effect
	bump:function(offset){
		var pos=parseInt($('#slider').css('left'));
		$('#slider').animate(
			{'left':(offset=='left')?(pos-20)+'px':(pos+20)+'px'}
			,200,function(){
			$(this).animate({'left':0},200);
		});
		return false;
	},
	displayImage:function(e){
		if(!this.images.files[e])
			return;
		$('.ad-image span').hide();
		$('.ad-image img')
			.hide()
			.load(function(){
				switch(Gallery.options.effect){
					case 'fade':
						$(this).fadeIn('slow',Gallery.caption);
					break;
					case 'slideUp':
						$(this).slideDown('slow',Gallery.caption);
					break;
					case 'slideDown':
						$(this).slideDown('slow',Gallery.caption);
					break;
				}
			})
			.attr(
				'src','/kfmget/'+this.images.files[e].id+',width='
				+ this.options.imageHeight+',height='
				+ this.options.imageWidth
			)
			.attr('title',this.images.files[e].caption)
			.attr('num',e);
	},
	caption:function(){
		var caption=$('.ad-image img').attr('title');
		if(caption=="")
			return;
		var width=$('.ad-image img').width()-14;
		$('.ad-image span')
			.html(caption)
			.css({
				'width':width+'px',
			})
			.slideDown('fast');
	},
};
$(function(){
	Gallery.init();
});
