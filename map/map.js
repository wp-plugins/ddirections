var WPmap = {

    // HTML Elements we'll use later!
    mapContainer   : document.getElementById('map-container'),
    dirContainer   : document.getElementById('dir-container'),
    toInput        : document.getElementById('map-config-address'),
    fromInput      : document.getElementById('from-input'),
    unitInput      : document.getElementById('unit-input'),
    geoDirections  : document.getElementById('geo-directions'),
    nativeLinkElem : document.getElementById('native-link'),
    startLatLng    : null,
    destination    : null,
    geoLocation    : null,
    geoLat         : null,
    geoLon         : null,

    // Google Maps API Objects
    dirService     : new google.maps.DirectionsService(),
    dirRenderer    : new google.maps.DirectionsRenderer(),
    map:null,

    /**
     *
     * Determine if the geolocation API is available in the browser.
     *
     */
    getGeo : function(){
        if (!! navigator.geolocation)
            return navigator.geolocation;
        else
            return undefined;
    },

    /**
     * Get the Current Position
     */
    getGeoCoords : function (){
        
        WPmap.geoLoc.getCurrentPosition(WPmap.setGeoCoords, WPmap.geoError)
    },

    /**
     * Set the Lat/Lon Values to the object.
     * Set extra buttons for users with Geo Capabilities
     */
    setGeoCoords : function (position){

        WPmap.geoLat = position.coords.latitude;
        WPmap.geoLon = position.coords.longitude;
        WPmap.showGeoButton();
        WPmap.setNativeMapLink();
    },

    /**
     * Geo Errors - Useful for Dev - Hidden in production.
     */
    geoError : function(error) {
        var message = "";
        // Check for known errors
        switch (error.code) {
            case error.PERMISSION_DENIED:
                message = "This website does not have permission to use " +
                    "the Geolocation API";
                break;
            case error.POSITION_UNAVAILABLE:
                message = "Sorry, your current position cannot be determined, please enter your address instead.";
                break;
            case error.PERMISSION_DENIED_TIMEOUT:
                message = "Sorry, we're having trouble trying to determine your current location, please enter your address instead.";
                break;
        }
        // If it's an unknown error, build a message that includes
        // information that helps identify the situation, so that
        // the error handler can be updated.
        if (message == "")
        {
            var strErrorCode = error.code.toString();
            message = "The position could not be determined due to " +
                "an unknown error (Code: " + strErrorCode + ").";
        }
       console.log(message);
    },

    /**
     * Show the 'use current location' button
     */
    showGeoButton : function(){

        var geoContainer = document.getElementById('geo-directions');
        geoContainer.removeClass('hidden');
    },

    /*  Show the 'open in google maps' button */
    setNativeMapLink: function(){

        var locString   = WPmap.geoLat + ',' + WPmap.geoLon;
        var destination = WPmap.toInput.value;
        var newdest     = destination.replace(' ', '');
        WPmap.nativeLinkElem.innerHTML = ('<a href="http://maps.google.com/maps?mrsp=0' 
            + '&amp;daddr='
            + newdest 
            + '&amp;saddr=' 
            + locString
            + '" class="map-button">Abrir en Google Maps</a>');
    },

    /* Determine whether an Admin has entered lat/lon values or a regular address. */
    getDestination:function() {

        var toInput = WPmap.toInput.value;
        var isLatLon  = (/^(\-?\d+(\.\d+)?),\s*(\-?\d+(\.\d+)?)$/.test(toInput));

        if (isLatLon){
            var n = WPmap.toInput.value.split(",");
            WPmap.destination = new google.maps.LatLng(n[0], n[1]);
            WPmap.setupMap();
        }
        else {

            geocoder = new google.maps.Geocoder();
            geocoder.geocode( { 'address': WPmap.toInput.value}, function(results, status){

                WPmap.destination = results[0].geometry.location;
                WPmap.setupMap();
            });
        }
    },

    /* Initialize the map */
    setupMap : function() {

        //get the content
        var infoWindowContent = WPmap.mapContainer.getAttribute('data-map-infowindow');



		if(mapa_data.initial_map_type == 'SATELLITE'){
			WPmap.map = new google.maps.Map(WPmap.mapContainer, {
				zoom:parseInt(mapa_data.zoom),     //ensure it comes through as an Integer
				center:WPmap.destination,
				mapTypeId:google.maps.MapTypeId.SATELLITE // style of map: HYBRID, ROADMAP, SATELLITE, TERRAIN
			});
		} else if(mapa_data.initial_map_type == 'ROADMAP'){
			WPmap.map = new google.maps.Map(WPmap.mapContainer, {
				zoom:parseInt(mapa_data.zoom),     //ensure it comes through as an Integer
				center:WPmap.destination,
				mapTypeId:google.maps.MapTypeId.ROADMAP // style of map: HYBRID, ROADMAP, SATELLITE, TERRAIN
			});
		} else if(mapa_data.initial_map_type == 'HYBRID'){
			WPmap.map = new google.maps.Map(WPmap.mapContainer, {
				zoom:parseInt(mapa_data.zoom),     //ensure it comes through as an Integer
				center:WPmap.destination,
				mapTypeId:google.maps.MapTypeId.HYBRID // style of map: HYBRID, ROADMAP, SATELLITE, TERRAIN
			});
		} else if(mapa_data.initial_map_type == 'TERRAIN'){
			WPmap.map = new google.maps.Map(WPmap.mapContainer, {
				zoom:parseInt(mapa_data.zoom),     //ensure it comes through as an Integer
				center:WPmap.destination,
				mapTypeId:google.maps.MapTypeId.TERRAIN // style of map: HYBRID, ROADMAP, SATELLITE, TERRAIN
			});
		} else {
			alert('EROOR');
		}


        marker = new google.maps.Marker({
            map:WPmap.map,
            position:WPmap.destination,
            draggable:false
        });

        //set the infowindow content
        infoWindow = new google.maps.InfoWindow({
            content:mapa_data.infobox_text
        });
        infoWindow.open(WPmap.map, marker);

    },

    getSelectedUnitSystem:function () {
        return WPmap.unitInput.options[WPmap.unitInput.selectedIndex].value == 'metric' ?
            google.maps.DirectionsUnitSystem.METRIC :
            google.maps.DirectionsUnitSystem.IMPERIAL;
    },

    /**
     * Get the directions
     * Check if the user has selected a Geo option & use that instead
     */
    getDirections:function (request) {

        //Get the postcode that was entered
        var fromStr = WPmap.fromInput.value;

        var dirRequest = {
            origin      : fromStr,
            destination : WPmap.destination,
            travelMode  : google.maps.DirectionsTravelMode.DRIVING,
            unitSystem  : WPmap.getSelectedUnitSystem()
        };

        //check if user clicked 'use current location'
        if (request == 'geo'){
            var geoLatLng = new google.maps.LatLng( WPmap.geoLat , WPmap.geoLon );
            dirRequest.origin = geoLatLng;
        }

        WPmap.dirService.route(dirRequest, WPmap.showDirections);
    },

    /**
     * Output the Directions into the page.
     */
    showDirections:function (dirResult, dirStatus) {
        if (dirStatus != google.maps.DirectionsStatus.OK) {
            switch (dirStatus){
                case "ZERO_RESULTS" : alert ('Sorry, we can\'t provide directions to that address (you maybe too far away, are you in the same country as us?) Please try again.')
                    break;
                case "NOT_FOUND" : alert('Sorry we didn\'t understand the address you entered - Please try again.');
                    break;
                default : alert('Sorry, there was a problem generating the directions. Please try again.')
            }
            return;
        }
        // Show directions
        WPmap.dirRenderer.setMap(WPmap.map);
        WPmap.dirRenderer.setPanel(WPmap.dirContainer);
        WPmap.dirRenderer.setDirections(dirResult);
    },

    init:function () {

        if (WPmap.geoLoc = WPmap.getGeo()){
            //things to do if the browser supports GeoLocation.
            WPmap.getGeoCoords();
        }

        WPmap.getDestination();

        //listen for when Directions are requested
        google.maps.event.addListener(WPmap.dirRenderer, 'directions_changed', function () {

            infoWindow.close();         //close the first infoWindow
            marker.setVisible(false);   //remove the first marker

            //setup strings to be used.
            var distanceString = WPmap.dirRenderer.directions.routes[0].legs[0].distance.text;

            //set the content of the infoWindow before we open it again.
            infoWindow.setContent( mapa_data.msg_ok1 + distanceString + mapa_data.msg_ok2);

            //re-open the infoWindow
            infoWindow.open(WPmap.map, marker);
            setTimeout(function () {
                infoWindow.close()
            }, 8000); //close it after 8 seconds.

        });
    }//init
};

google.maps.event.addDomListener(window, 'load', WPmap.init);


/* Function to easily remove any class from an element. */
HTMLElement.prototype.removeClass = function(remove) {
    var newClassName = "";
    var i;
    var classes = this.className.split(" ");
    for(i = 0; i < classes.length; i++) {
        if(classes[i] !== remove) {
            newClassName += classes[i] + " ";
        }
    }
    this.className = newClassName;
}