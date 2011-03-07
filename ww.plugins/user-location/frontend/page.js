(function(){
	var initialLocation;
	var map;
	  
	function handleNoGeolocation() {
		var lat=+$('input[name=lat]').val(),lng=+$('input[name=lng]').val();
		var initialLocation=new google.maps.LatLng(lat, lng);
	  map.setCenter(initialLocation);
	}
	$(function() {
	  var myOptions = {
	    zoom: 7,
	    mapTypeId: google.maps.MapTypeId.ROADMAP
	  };
	  map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
	  // Try W3C Geolocation method (Preferred)
	  if(navigator.geolocation) {
	    navigator.geolocation.getCurrentPosition(function(position) {
	      initialLocation = new google.maps.LatLng(
					position.coords.latitude,
					position.coords.longitude
				);
	      map.setCenter(initialLocation);
	    }, function() {
	      handleNoGeolocation();
	    }, {
				timeout:10000
			});
	  }
		else if (google.gears) { // Try Google Gears Geolocation
	    var geo = google.gears.factory.create('beta.geolocation');
	    geo.getCurrentPosition(function(position) {
	      initialLocation = new google.maps.LatLng(
					position.latitude,
					position.longitude
				);
	      map.setCenter(initialLocation);
	    }, function() {
	      handleNoGeolocation();
	    });
	  }
		else { // Browser doesn't support Geolocation
	    handleNoGeolocation();
	  }
		$('#localjobs4me_getLocation_form').submit(function(){
			var ctr=map.getCenter();
			$('input[name=lat]').val(ctr.lat());
			$('input[name=lng]').val(ctr.lng());
		})
	});
})();
