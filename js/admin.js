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

jQuery('#juxtalearn_hub_trickytopic_id').change(function(){
 var tt_id = jQuery(this).val();
 inheritTrickyTopicVals(tt_id);
});

// http://stackoverflow.com/a/3855394/1027723
var qs = (function(a) {
    if (a == "") return {};
    var b = {};
    for (var i = 0; i < a.length; ++i)
    {
        var p=a[i].split('=');
        if (p.length != 2) continue;
        b[p[0]] = decodeURIComponent(p[1].replace(/\+/g, " "));
    }
    return b;
})(window.location.search.substr(1).split('&'));

jQuery( document ).ready(function($) {
    if(qs['tt_id']){
		inheritTrickyTopicVals(qs['tt_id']);
	}                                                   
	$('.tax_term').hide();
	$('div[class$="desc"]').show();
	$('.tabs-text').show();
	$( "#juxtalearn_hub_tax_tabs" ).show();
});

jQuery(function($) {
	//if (!$('').tabs) { return window.console && console.log("No 'tabs'"); }

	$('').tabs && $( "#juxtalearn_hub_tax_tabs" ).tabs({
            activate: function(event,ui){ 
							$('.tax_term').hide();                                                     
                            $('.tax_term').hide();
							$('div[class$="desc"]').show();                                                  
                    }                                                                          
         }).css("min-height", "150px");;
	$('#juxtalearn_hub_tax_tabs').find('label').hover(function(){
			$('.tax_term').hide();
			$(this).css('text-decoration', 'underline');
			var labelName = $(this).attr('for');
			$('.tax_term.'+labelName).fadeIn(500);
		}, function(){
			$('.tax_term').hide();
			$('div[class$="desc"]').fadeIn(500); 
			$(this).css('text-decoration','');
	});

	//NDF: Stumbling Block tags auto-suggest/ autocomplete [#5].
	//See: /wp-admin/js/post.js; /wp-includes/js/jquery/suggest.js

	//http://stackoverflow.com/questions/31044/is-there-an-exists-function-for-jquery
	jQuery.fn.exists = function () { return this.length > 0; }
	jQuery.fn.values = function () {
		/*var vals = [];
		$(this).each(function (i, el) {
			vals.push( $(el).val() );
		});
		return vals;*/
		return $(this).map(function (i, el) {
		  return $(el).val();
		}).get();
	}

	var custom_el = $('#juxtalearn_hub_sb_custom');
	if (custom_el.exists()) {
		var ajaxtag = $('div.ajaxtag');
		$('input.tagadd-cust', ajaxtag).click(function () {
			var custom_par = $(this).closest('.tagsdiv'), //$('#juxtalearn_hub_sb')
				nm = '__tax_input[juxtalearn_hub_sb][]',
				newtags = $('input.newtag', custom_par).val().replace(/, ?$/, ''),
				tags = $('input', custom_el),
				tagsval = tags.values(),
				checked = tags.filter(':checked').values();

			//console.log('>>NDF: ', newtags, tagsval, checked);

			$('input.newtag', custom_par).val("");

			var tags_r = newtags.split(',');
			for (var it in tags_r) {
				var tag = $.trim(tags_r[it]);

				if (""== tag || $.inArray(tag, checked) > -1) {
					// Tag already ticked - do nothing.
					window.console && console.log('Do nothing!');
				}
				else if ($.inArray(tag, tagsval) > -1) {
					tags.filter('[value = "'+ tag +'"]').attr('checked', '');
				}
				else {
					custom_el.append(
					'<label><input type="checkbox" checked name="'+ nm +'" value="'+
					tag +'"/>'+ tag +'</label> ');
				}
			}
		});
	}
});

function inheritTrickyTopicVals(tt_id){
	var $ = jQuery;
	$.ajax({
		url: ajaxurl,
		type: 'POST',
		async: true,
		cache: false,
		dataType: 'json',
		data: {
			action: 'juxtalearn_hub_tricky_topic_details',
			tt_id: tt_id,
		},
		success: function( $data ){
			
			$('#juxtalearn_hub_country').val($data.juxtalearn_hub_country[0]);
			$('#juxtalearn_hub_location_id_field').val($data.juxtalearn_hub_location_id_field);
			$('#juxtalearn_hub_location_id').val($data.juxtalearn_hub_location_id);
		//NDF: Part 2: Stumbling Block tags auto-suggest/ autocomplete [#5].
		//if ('check' === $('#custom-sb-meta-box-type').val()) {
		var custom_el = $('#juxtalearn_hub_sb_custom');
		if (custom_el.exists()) {
			//console.log($data.juxtalearn_hub_sb);
			var checks = '';
			for (var it in $data.juxtalearn_hub_sb) {
				var sb = $data.juxtalearn_hub_sb[it];
				var nm = '__tax_input[juxtalearn_hub_sb][]';
				checks += '<label><input type="checkbox" name="'+ nm +'" value="'+
						sb +'"/>'+ sb +'</label> ';
			}
			custom_el.html(checks);
		} else {
		// Martin's original.
			$('#tax-input-juxtalearn_hub_sb').val('');
			$('#juxtalearn_hub_sb .tagchecklist').empty();
			$('#new-tag-juxtalearn_hub_sb').val($data.juxtalearn_hub_sb.join(","));
			tagBox.flushTags($('#new-tag-juxtalearn_hub_sb').closest('.tagsdiv'));
		}
			$('#juxtalearn_hub_trickytopic_id').val(tt_id).focus();
		}
	});
}
function cleanTags(el, a, f) {
		var $ = jQuery;
		var tagsval, newtags, text,
			tags = $('.the-tags', el),
			newtag = $('input.newtag', el),
			comma = postL10n.comma;
		a = a || false;

		text = a ? $(a).text() : newtag.val();
		tagsval = tags.val();
		newtags = tagsval ? tagsval + comma + text : text;

		newtags = tagBox.clean( newtags );
		newtags = array_unique_noempty( newtags.split(comma) ).join(comma);
		tags.val(newtags);
		tagBox.quickClicks(el);

		if ( !a )
			newtag.val('');
		if ( 'undefined' == typeof(f) )
			newtag.focus();

		return false;
	}

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