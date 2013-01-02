/*
 * jQuery 3d Carousel plugin
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 * tested with jQuery 1.4.2
 * written by Kae Verens: kae@kvsites.ie
 * version: 1.2
 */
(function( $ ){
	$.fn.k3dCarousel=function(options) {
		return this.each(function() {
			var i=0, sin, newWidth, numitems, degs, dirlinks, timer,
				positions=[], iter=0, w=this.offsetWidth,
				items=$(this).css('position','relative').find('img').css({
					'position' : 'absolute',
					'opacity'  : 0,
					'display'  : 'block'
				}),
				settings = {
					'r'  : w*0.3, // radius of the carousel
					'cX' : w/2,  // center X of the carousel
					'cY' : this.offsetHeight/2, // center Y
					'sT' : 1000, // how long it takes to move from spot to spot
					'wT' : 2000, // how long to pause before moving again
					'a'  : 0,    // arrows. examples: 0, ['&lt;','&gt;'], ['left','right']
					'd'  : 1,    // direction. 1 = ltr, -1 = rtl
					'p'  : 1     // pause on hover. 1=yes, 0=no
				};
			if (options) {
				$.extend(settings, options);
			}
			if ( settings.a.length==2 ) {
				dirlinks=function(dir){
					settings.d=dir;
					clearTimeout(timer);
					setPositions();
				};
				$('<div style="cursor:pointer;position:absolute;left:5px;top:5px" class="left" />')
					.html(settings.a[0])
					.click(function(){
						dirlinks(-1);
					})
					.appendTo(this);
				$('<div style="cursor:pointer;position:absolute;right:5px;top:5px" class="right" />')
					.html(settings.a[1])
					.click(function(){
						dirlinks(1);
					})
					.appendTo(this);
			}
			items.each(function(index,el){
				el.oW=el.offsetWidth;  // store the original with of the image
				el.oH=el.offsetHeight; // and the original height
				$(el).css({
					'left':settings.cX-el.oH/2,
					'top':0
				});
			})
				.mouseover(function(){
					if (!settings.d) {
						return;
					}
					settings.od=settings.d;
					settings.d=0;
					clearTimeout(timer);
				})
				.mouseout(function(){
					if (settings.d || !settings.od) {
						return;
					}
					settings.d=settings.od;
					timer=setTimeout(setPositions, settings.wT+settings.sT);
				});
			numitems=items.length;
			degs=Math.PI/(numitems/2);
			for (; i<numitems; ++i) {
				sin=Math.sin(degs*i);
				positions.push({
					'l' : settings.cX+(Math.cos(degs*i)*settings.r),
					'z' : parseInt(50*sin+50, 10),
					't' : sin*10+10,
					'o' : 0.45*sin+0.55,
					'm' : 0.4*sin+0.6
				});
				items[i]=$(items[i]);
			}
			function setPositions(){
				for (var i=0; i<numitems; ++i) {
					var inum=(i+iter)%numitems, position=positions[i];
					newWidth=position.m*items[inum][0].oW;
					items[inum].animate(
						{
							'left':    position.l-newWidth/2,
							'opacity': position.o,
							'top':     position.t,
							'width':   newWidth,
							'height':  position.m*items[inum][0].oH
						},
						settings.sT
					)
					.css('z-index',position.z);
				}
				iter+=settings.d;
				if (iter<0) {
					iter+=numitems;
				}
				timer=setTimeout(setPositions, settings.wT+settings.sT);
			}
			setPositions();
		});
	};
})( jQuery );
