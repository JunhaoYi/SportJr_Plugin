jQuery(document).ready(function(){
	
	// activity search button event
	jQuery('#search-btn').click(function() {
		var sub = jQuery("#s-suburb").val();
		var cat = jQuery('#search-list').val();
		var msg = sub + ',' + cat;

		var data = {
			action: 'map_response',
			post_var: msg
		};
		jQuery.post(custom_ajax_script.ajaxurl, data, function(response) {
			var rawdata = JSON.parse(response);
			blockGen(rawdata[0]);
			setCenter(rawdata[1]);
		});
	});

	// event search button event
	jQuery('#event_search').click(function() {
		var sub = jQuery.trim(jQuery('#event_key').val());
		var cuisine = jQuery.trim(jQuery('#event_postcode').val());
		var rating = jQuery('#event_sort').val();
		//var order = jQuery('#event_order').val();
		//if (sub != '' )
		if (true ){
			if(sub === ''){
				sub = "sport";
			}
			document.getElementById('cardcontainer').innerHTML = '';
			jQuery(".event-tips").hide();
			jQuery(".event-error").hide();
			jQuery('.event-inputerror').hide();
			jQuery(".event-showmore").hide();
			jQuery(".preload").show();
			var msg = sub + ',' + cuisine + ',' + rating;
			var data = {
				action: 'event_response',
				post_var: msg
			};
			jQuery.post(custom_ajax_script.ajaxurl, data, function(response) {
				if (jQuery.trim(response) == 'Error'){
					jQuery(".preload").hide();
					jQuery(".event-error").html("Sorry, no results for " + sub);
					jQuery(".event-error").show();
				}
				else {
					var rawdata = JSON.parse(response);
					var eventdata = rawdata._embedded.events;
					cardGen(eventdata);
				}
			});
		}
		else {
			document.getElementById('cardcontainer').innerHTML = '';
			jQuery(".event-showmore").hide();
			jQuery('.event-inputerror').show();
		}
	});

})