$(function() {
	function showMaps() {
		if (!window.google) {
			window.google={};
		}
		if (!google.maps) {
			if (!google.loading) {
				window.google={loading:true};
				$('<script src="http://maps.googleapis.com/maps/api/js?sensor=true&'
					+'callback=Products_showMap"></script>')
					.appendTo(document.body);
			}
			setTimeout(showMaps, 500);
			return;
		}
		var $maps=$('.products-map');
		$maps.each(function() {
			var $this=$(this), pid=$this.data('pid'), lat=$this.data('lat'),
				lng=$this.data('lng');
			var myOptions={
				zoom:12,
				center:new google.maps.LatLng(lat, lng),
				mapTypeId:google.maps.MapTypeId.ROADMAP
			};
			var map=new google.maps.Map(this, myOptions);
			var marker=new google.maps.Marker({
				position: new google.maps.LatLng(lat, lng), 
				map     : map
			});
		});
	}
	showMaps();
});
