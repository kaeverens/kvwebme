var MP3={
	options:{
		link_to_play:false,
		play_button:false,
		big_play_button:false,
		progress:false,
	},
	t:0, // holds the position timeout
	playing:false, // true if file is being played
	init:function(){ // initialise and setup
		// { get options from html
		var opts={};
		var names=[
			'link_to_play',
			'play_button',
		];
		var list=$('.mp3_playlist');
		for(var i=0;i<names.length;++i) {
			var val=list.attr(names[i]);
			if(val){
				opts[names[i]]=Boolean(val);
			}
		}
		$.extend(this.options,opts);
		if($('.mp3_play_link').length)
			this.options.big_play_button=true;
		if($('.mp3_progress').length)
			this.options.progress=true;
		// }
		if(this.options.play_button){
			$('.play_button').live('click',function(){
				var $this=$(this);
				if(MP3.options.big_play_button)
					return MP3.mainButton($this);
				if($this.html()=='Play'){
					var file=$this.attr('href');
					MP3.play(file);
					$this.html('Pause');
				}
				else{
					MP3.pause();
					$this.html('Play');
				}
				return false;
			});
		}
		if(this.options.link_to_play){
			$('.link_to_play').live('click',function(){
				var $this=$(this);
				if(MP3.options.big_play_button)
					return MP3.mainButton($this);
				var playing=$this.attr('playing');
				if(playing){
					MP3.pause();
					$this.attr('playing',false);
				}
				else{
					var file=$this.attr('href');
					MP3.play(file);
					$this.attr('playing',true);
				}
				return false;
			});
		}
		if(this.options.big_play_button){
			$('.mp3_play_link').live('click',function(){
				var $this=$(this);
				if($this.hasClass('playing')){
					$this.removeClass('playing');
					var position=jwplayer().getPosition();
					$this.attr('position',position);
					MP3.pause();
				}
				else{
					var file=$this.attr('file');
					var position=$this.attr('position');
					if(position)
						jwplayer().seek(position);
					else if(file)
						MP3.play(file);
					$this.addClass('playing');
				}
			});
		}
		if(this.options.progress){
			$('.mp3_progress').progressbar({value:0});
			$('.mp3_progress').click(function(e){ // change progress
				if(MP3.playing==true){
					var maxWidth=$(this).css('width').slice(0,-2);
					var clickPos=e.pageX-this.offsetLeft;
					var percentage=clickPos/maxWidth*100;
					var time=(jwplayer().getDuration()*percentage)/100;
					jwplayer().seek(time);
					$('.mp3_progress').progressbar('option','value',percentage);
				}
			});
		}
	},
	play:function(file){ // play an mp3 file
		this.playing=true;
		$('#mp3-container_wrapper').remove();
		$('body').append('<div id="mp3-container" style="display:none"></div>');
		jwplayer('mp3-container').setup({
			flashplayer:'/ww.plugins/mp3/frontend/player.swf',
			file:file,
			height:270,
			width:480
		});
		$('#mp3-container_wrapper').css({'left':0,'top':0,'width':0,'height':0});
		jwplayer().play();
		if(this.options.progress)
			this.beginProgress();
	},
	pause:function(){
		this.playing=false;
		jwplayer().pause();
		if(this.options.progress)
			clearTimeout(this.t);
	},
	mainButton:function($this){
		var button=$('.mp3_play_link');
		if(!button.hasClass('playing'))
			button.addClass('playing');
		var file=$this.attr('href');
		$this.attr('position','');
		button.attr('file',file);
		MP3.play(file);
		return false;
	},
	beginProgress:function(){
		this.t=setTimeout(function(){
			var position=(jwplayer().getPosition()/jwplayer().getDuration()*100);
			$('.mp3_progress').progressbar({'value':position});
			MP3.beginProgress();
		},600);
	}
};
$(function(){
	MP3.init();
});
