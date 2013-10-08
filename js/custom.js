//
// Wordpress 3.5 Media Uploader
//
jQuery(document).ready(function($){
    var custom_uploader;
    $('#upload_image_button').click(function(e) {
        e.preventDefault();
        //If the uploader object has already been created, reopen the dialog
        if (custom_uploader) {
            custom_uploader.open();
            return;
        }
 
        //Extend the wp.media object
        custom_uploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Image',
            button: {
                text: 'Choose Image'
            },
            multiple: false
        });
 
        //When a file is selected, grab the URL and set it as the text field's value
        custom_uploader.on('select', function() {
            attachment = custom_uploader.state().get('selection').first().toJSON();
            $('#upload_image').val(attachment.url);
        });
 
        //Open the uploader dialog
        custom_uploader.open();
    });
});

//
// Map preview
//

// getting the values
jQuery(document).ready(function () {

	var coordinates = jQuery('#coordinates').val();
	var coordinates = coordinates.split(",");
	var lat = coordinates[0];
	var lon = coordinates[1];
	var infobox_text = jQuery('#infobox_text').val();
	var zoom_level = jQuery('#zoom_level').val();
	zoom_level = parseFloat(zoom_level);
	var map_type = jQuery('#map_type').val();
	
	if (map_type == 'SATELLITE'){
		var map_type_satellite = google.maps.MapTypeId.SATELLITE
	}
	if (map_type == 'ROADMAP'){
		var map_type_satellite = google.maps.MapTypeId.ROADMAP
	}
	if (map_type == 'HYBRID'){
		var map_type_satellite = google.maps.MapTypeId.HYBRID
	}
	if (map_type == 'TERRAIN'){
		var map_type_satellite = google.maps.MapTypeId.TERRAIN
	}
	
	var marker_icon = jQuery('#upload_image').val();

	// creating the map
	function initialize_jander() {
		var myLatlng2 = new google.maps.LatLng(lat,lon);
		var mapOptions = {
			zoom: zoom_level,
			center: myLatlng2,
			mapTypeId: map_type_satellite
		}
		var map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);

		var marker = new google.maps.Marker({
			position: myLatlng2,
			map: map,
			icon: marker_icon,
			title: infobox_text
		});
	  
		var infowindow = new google.maps.InfoWindow({
			content: infobox_text
		});
		
		google.maps.event.addListener(marker, 'click', function() {
			infowindow.open(map,marker);
		});
	}
	google.maps.event.addDomListener(window, 'load', initialize_jander);
});