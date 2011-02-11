function products_widget(id, categories) {
	function products_widget_drawText(text, url, x, y, distance, angle){
		var isie=$.browser.msie;
		var $str=$('<a href="'+url+'">'+text+'</a>')
			.css({
				"position":'absolute',
				'text-decoration':'none',
				'height':'1em'
			})
			.appendTo($image);
		var strw=$str[0].offsetWidth;
		var strh=$str[0].offsetHeight;
		y-=strh;
		rad=angle*deg2radians ;
		var strnx=(distance-strw)*Math.cos(rad);
		var strny=strh/2+(distance-strw)*Math.sin(rad);
		var strfx=distance*Math.cos(rad);
		var strfy=strh/2+distance*Math.sin(rad);
		var strmx=(strnx+strfx)/2;
		var strmy=(strny+strfy)/2;
		if ($.browser.msie) {
			if (angle<90) {
				$str.css({
					"left":x+strnx,
					"top" :y+strny
				});
			}
			else if (angle<180) {
				$str.css({
					"left":x+strfx,
					"top" :y+strfy-strw*Math.sin(rad)
				});
				rad-=Math.PI;
			}
			else if (angle<270) {
				$str.css({
					"left":x+strfx,
					"top" :y+strfy
				});
				rad-=Math.PI;
			}
			else {
				$str.css({
					"left":x+strnx,
					"top" :y+strny+strw*Math.sin(rad)
				});
			}
			costheta = Math.cos(rad);
			sintheta = Math.sin(rad);
			$str[0].style.filter="progid:DXImageTransform.Microsoft.Matrix(M11='1.0', sizingmethod='auto expand')";
			$str[0].filters.item(0).M11 = costheta;
	    $str[0].filters.item(0).M12 = -sintheta;
 		  $str[0].filters.item(0).M21 = sintheta;
	    $str[0].filters.item(0).M22 = costheta;
		}
		else {
			while (angle>90) {
				angle-=180;
			}
			$str.css({
				"left":x+strmx-strw/2,
				"top" :y+strmy
			});
			$str[0].style.MozTransform='rotate('+angle+'deg)';
			$str[0].style.webkitTransform='rotate('+angle+'deg)';
		}
		return $str;
	}
	function drawSegment(cat, bright){
		if ($image.bright) {
			var i=$image.bright-1;
			$image.bright=0;
			drawSegment(categories[i]);
		}
		if (bright) {
			var amt=.7;
			$image.bright=cat.index+1;
			var bg={
				r:parseInt(255-(255-cat.segment.bg.r)*amt),
				g:parseInt(255-(255-cat.segment.bg.g)*amt),
				b:parseInt(255-(255-cat.segment.bg.b)*amt)
			}
		}
		else bg=cat.segment.bg;
		$image
			.style({
				'fillStyle':'rgba('+bg.r+','+bg.g+','+bg.b+',.9)'
			})
			.beginPath()
			.moveTo( cat.segment.p1 )
			.lineTo( cat.segment.p2 )
			.lineTo( cat.segment.p3 )
			.lineTo( cat.segment.p4 )
			.fill()
			.closePath();
	}
	function getcat(x, y) {
		var dist=Math.sqrt(x*x+y*y);
		if (dist>radius || dist<radius*.1) {
			return;
		}
		var deg=0;
		if (x==0) {
			deg=y>0?90:-90;
		}
		else {
			var rad=Math.atan(y/x);
			deg=rad*(180/Math.PI);
			if (x<0) {
				deg+=180;
			}
			else if (y<0) {
				deg+=360;
			} 
		}
		return categories[parseInt(deg/(360/categories.length))];
	}
	var deg2radians = Math.PI/180;
	var $parent=$('#'+id)
		.empty()
		.css('position', 'relative');
	var diameter=$parent.css('width');
	var $image=$('<div>&nbsp;</div>')
		.css({
			width:diameter,
			height:diameter,
			position:'relative'
		})
		.appendTo($parent);
	$('<div class="product-title">&nbsp;</div>').appendTo($parent);
	$image
		.canvas()
		.style({
		'strokeStyle' : 'rgba(64,64,64,.6)',
		'lineWidth'   :.5 
	});
	var radius=diameter.replace(/px/, '')/2;
	var stepat=0;
	var step=(Math.PI*2)/categories.length;
	var stepDeg=360/categories.length;
	var stepDegAt=0;
	var oldX=radius*2;
	var oldY=radius;
	for (var i=0; i<categories.length; ++i) {
		stepat+=step;
		stepDegAt+=stepDeg;
		var x=radius+radius*Math.cos(stepat);
		var y=radius+radius*Math.sin(stepat);
		categories[i].index=i;
		categories[i].segment={
			p1:[oldX, oldY],
			p2:[radius+(oldX-radius)/10, radius+(oldY-radius)/10],
			p3:[radius+(x-radius)/10, radius+(y-radius)/10],
			p4:[x, y],
			bg:{
				r:Number('0x'+categories[i].col.substring(0,2)),
				g:Number('0x'+categories[i].col.substring(2,4)),
				b:Number('0x'+categories[i].col.substring(4,6))
			}
		}
		drawSegment(categories[i]);
		var $str=products_widget_drawText(
			categories[i].name,
			'/_r?type=products&product_cid='+categories[i].id,
			radius,
			radius,
			radius-10,
			stepDegAt-stepDeg/2
		);
		oldX=x;
		oldY=y;
	}
	$image
		.arc([radius,radius], {
			'radius':radius,
			'startAngle':0,
			'endAngle':360
		})
		.style({'strokeStyle' : 'rgba(64,64,64,.6)'})
		.stroke();

	$parent.mousemove(function(e){
		var x=e.pageX-this.offsetLeft-radius;
		var y=e.pageY-this.offsetTop-radius;
		var cat=getcat(x, y);
		if (cat && cat.id) {
			$parent.find('div.product-title').text(cat.name);
			if (!$image.bright || $image.bright!=cat.index+1) {
				drawSegment(cat, 1);
			}
		}
	});
	$parent.click(function(e){
		var x=e.pageX-this.offsetLeft-radius;
		var y=e.pageY-this.offsetTop-radius;
		var cat=getcat(x, y);
		if (cat && cat.id) {
			document.location='/_r?type=products&product_cid='+cat.id
		}
	});
}
