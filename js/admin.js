// JavaScript Document
/*
Modified from:

Plugin Name: Authors Autocomplete Meta Box
Plugin URI: http://wordpress.org/plugins/authors-autocomplete-meta-box
Description: Replaces the default WordPress Author meta box (that has an author dropdown) with a meta box that allows you to select the author via Autocomplete.
Version: 1.1
Author: Rachel Carden
Author URI: http://www.rachelcarden.com
*/

var errorDNA = "The location does not exist. <a href='post-new.php?post_type=location' target='_blank'>Add New Location if required</a>";
var map = map || "";
var marker = marker || "";

jQuery.noConflict()(function(){
	jQuery.ui.autocomplete.prototype._resizeMenu = function () {
	  var ul = this.menu.element;
	  ul.outerWidth(this.element.outerWidth());
	}
	jQuery( 'input#juxtalearn_hub_location_id_field' ).each( function() {
	
		var $juxtalearn_hub_location_id_field = jQuery( 'input#juxtalearn_hub_location_id_field' );	

		
		// autocomplete new tags
		if ( $juxtalearn_hub_location_id_field.size() > 0 ) {
			$juxtalearn_hub_location_id_field.autocomplete({
				delay: 100,
				minLength: 1,
				appendTo: '#menu-container',
				source: function( $request, $response ){
					jQuery.ajax({
						url: ajaxurl,
						type: 'POST',
						async: true,
						cache: false,
						dataType: 'json',
						data: {
							action: 'juxtalearn_hub_location_callback',
							juxtalearn_hub_location_search_term: $request.term,
						},
						success: function( $data ){
							$response( jQuery.map( $data, function( $item ) {
								return {
									location_id: $item.location_id,
									address: $item.address,
									value: $item.label,
									label: $item.label,
								};
							}));
						}
					});
				},
				search: function( $event, $ui ) {
					autocomplete_eh_remove_error_message();
				},
				select: function( $event, $ui ) {
				
					// stop the loading spinner
					autocomplete_eh_stop_loading_spinner();
				
					// make sure any errors are removed
					autocomplete_eh_remove_error_message();
					
					// change the saved post author
					autocomplete_eh_change_location( $ui.item.location_id, $ui.item.label  );
					
				},
				response: function( $event, $ui ) {
					autocomplete_eh_stop_loading_spinner();
				},
				focus: function( $event, $ui ) {
					autocomplete_eh_stop_loading_spinner();
				},
				close: function( $event, $ui ) {
					autocomplete_eh_stop_loading_spinner();
				},
				change: function( $event, $ui ) {
					// stop the loading spinner
					autocomplete_eh_stop_loading_spinner();
					
					// remove any existing message
					autocomplete_eh_remove_error_message();
					
					// get the saved author display name. we'll need it later.
					if ($ui.item != null)
						autocomplete_eh_change_location( $ui.item.location_id, $ui.item.label );
					else 
						autocomplete_eh_add_error_message( errorDNA );
				}

			}).data( "ui-autocomplete" )._renderItem = function( $ul, $item ) {
				return jQuery( '<li>' ).append( '<a><strong>' + $item.label + '</strong><br />Address: <em>' + $item.address + '</em></a>' ).appendTo( $ul );
			};
	    }		
	});
});

function autocomplete_eh_stop_loading_spinner() {
	jQuery( 'input#juxtalearn_hub_location_id_field' ).removeClass( 'ui-autocomplete-loading' );
}

function autocomplete_eh_remove_error_message() {
	jQuery( '#autocomplete_eh_error_message' ).remove();
}

function autocomplete_eh_add_error_message( $message ) {

	// remove any existing error message
	autocomplete_eh_remove_error_message();
	//jQuery( '#pronamicMapHolder' ).empty();
	// add a new error message
	var $autocomplete_eh_error_message = jQuery( '<div id="autocomplete_eh_error_message">' + $message + '</div>' );
	jQuery( '#juxtalearn_hub_location_id' ).after( $autocomplete_eh_error_message );
	
}

function autocomplete_eh_change_location(id, label){
	var $juxtalearn_hub_location_id_field = jQuery( 'input#juxtalearn_hub_location_id_field' );	
	var $juxtalearn_hub_location_id = jQuery( 'input#juxtalearn_hub_location_id' );
	
	$juxtalearn_hub_location_id_field.val(label);
	$juxtalearn_hub_location_id.val(id);

		
	var $saved_location_id = $juxtalearn_hub_location_id.val();
	
	var $entered_user_value = $juxtalearn_hub_location_id_field.val();

	// see if the user exists
	jQuery.ajax({
		url: ajaxurl,
		type: 'POST',
		async: true,
		cache: false,
		dataType: 'json',
		data: {
			action: 'juxtalearn_hub_if_location_exists_by_value',
			autocomplete_eh_location_value: $entered_user_value,
			autocomplete_eh_location_id: $saved_location_id
		},
		success: function( $location ){
			
			// if the user exists
			if ( $location.valid ) {
				jQuery( '#MapHolder' ).show();
				if (jQuery('#MapHolder .leaflet-map-pane').length === 0){
					map = L.map("MapHolder").setView([$location.lat, $location.lng], $location.zoom);
					L.tileLayer("http://{s}.tile.osm.org/{z}/{x}/{y}.png", {attribution: "&copy; <a href=\"http://osm.org/copyright\">OpenStreetMap</a> contributors"}).addTo(map);
					marker = L.marker([$location.lat, $location.lng]).addTo(map);
				} else {
					map.setView([$location.lat, $location.lng], $location.zoom);
					marker.setLatLng([$location.lat, $location.lng]).update();	
				}
				
				//if ($location.lat)
					//jQuery( '#pgm-lat-field' ).val($location.lat);
				
				//if ($location.lng)
					//jQuery( '#pgm-lng-field' ).val($location.lng);
					
				if ($location.country)
					jQuery( '#juxtalearn_hub_country' ).val($location.country);
				else
					jQuery( '#juxtalearn_hub_country' ).val('');
				
			} else if ( $location.notamatch ||  $location.noid) {
				jQuery( '#MapHolder' ).hide();
				autocomplete_eh_add_error_message( errorDNA );
			} 			
		}
	});
}

//jQuery(document).ready(function($) {
	jQuery("#pgm-reverse-geocode-button").on('click' ,function() {
		jQuery("#pronamic-google-maps-meta-box").data('pgm-meta-box').reverseGeocode = function() {
			var $ = jQuery;
		    var geocoder = new google.maps.Geocoder();
			var fields = {};
			fields.latitude = $("#pgm-lat-field");
			fields.longitude = $("#pgm-lng-field");
			fields.address = $("#pgm-address-field");
			var location =  new google.maps.LatLng(fields.latitude.val(), fields.longitude.val());

			geocoder.geocode({"latLng": location} , function(results, status) {
				if(status == google.maps.GeocoderStatus.OK) {
					if(results[0]) {
						var address = results[0].formatted_address;
						fields.address.val(address);
						var arrAddress = results[0].address_components;
						$.each(arrAddress, function (i, address_component) {
							if (address_component.types[0] == "country"){ 
								$("#juxtalearn_hub_country").val(address_component.short_name.toLowerCase());
        						console.log("country:"+address_component.short_name.toLowerCase()); 
								return false;
							}
						});
					}
				} else {
					alert(status);
				}
			});
		};
		return false;
	});
	
//});