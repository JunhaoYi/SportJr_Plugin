<?php 

// activity query handler 
function getInfo() {
	if ( isset( $_POST["post_var"] ) ) {
		$arr = explode(",",$_POST["post_var"]);
		$sub = trim($arr[0]);
		$cat = trim($arr[1]);
		$results;
		$geo;
		global $wpdb;
		if (trim($sub) != '') {
			if ($cat == 'dance')
				{$results = $wpdb->get_results( "SELECT * FROM dance where TRIM(UPPER(Suburb)) = '".strtoupper($sub)."'");}
			else if ($cat == 'tennis')
				{$results = $wpdb->get_results( "SELECT * FROM tennis where TRIM(UPPER(Suburb)) = '".strtoupper($sub)."'");}
			else if ($cat == 'footy')
				{$results = $wpdb->get_results( "SELECT * FROM footy where TRIM(UPPER(Suburb)) = '".strtoupper($sub)."'");}
			else if ($cat == 'cricket')
				{$results = $wpdb->get_results( "SELECT * FROM cricket where TRIM(UPPER(Suburb)) = '".strtoupper($sub)."'");}
			else if ($cat == 'soccer')
				{$results = $wpdb->get_results( "SELECT * FROM soccer where TRIM(UPPER(Suburb)) = '".strtoupper($sub)."'");}
			else if ($cat == 'gym')
				{$results = $wpdb->get_results( "SELECT * FROM gym where TRIM(UPPER(Suburb)) = '".strtoupper($sub)."'");}
			else if ($cat == 'hockey')
				{$results = $wpdb->get_results( "SELECT * FROM hockey where TRIM(UPPER(Suburb)) = '".strtoupper($sub)."'");}
			else if ($cat == 'netball')
				{$results = $wpdb->get_results( "SELECT * FROM netball where TRIM(UPPER(Suburb)) = '".strtoupper($sub)."'");}
			else if ($cat == 'athletics')
				{$results = $wpdb->get_results( "SELECT * FROM athletics where TRIM(UPPER(Suburb)) = '".strtoupper($sub)."'");}
			else if ($cat == 'basketball')
				{$results = $wpdb->get_results( "SELECT * FROM basketball where TRIM(UPPER(Suburb)) = '".strtoupper($sub)."'");}
			else if ($cat == 'marts')
				{$results = $wpdb->get_results( "SELECT * FROM marts where TRIM(UPPER(Suburb)) = '".strtoupper($sub)."'");}
			else if ($cat == 'swim')
				{$results = $wpdb->get_results( "SELECT * FROM swim where TRIM(UPPER(Suburb)) = '".strtoupper($sub)."'");}
			else{
				{$results = $wpdb->get_results( "SELECT * FROM sport where TRIM(UPPER(Suburb)) = '".strtoupper($sub)."'");}
			}
			
			
			$geo = $wpdb->get_results( "SELECT * FROM geo where TRIM(UPPER(Suburbs)) = '".strtoupper($sub)."'");
		}
		else {
			if ($cat == 'dance')
				{$results = $wpdb->get_results( "SELECT * FROM dance");}
			else if ($cat == 'tennis')
				{$results = $wpdb->get_results( "SELECT * FROM tennis");}
			else if ($cat == 'footy')
				{$results = $wpdb->get_results( "SELECT * FROM footy");}
			else if ($cat == 'cricket')
				{$results = $wpdb->get_results( "SELECT * FROM cricket");}
			else if ($cat == 'soccer')
				{$results = $wpdb->get_results( "SELECT * FROM soccer");}
			else if ($cat == 'gym')
				{$results = $wpdb->get_results( "SELECT * FROM gym");}
			else if ($cat == 'hockey')
				{$results = $wpdb->get_results( "SELECT * FROM hockey");}
			else if ($cat == 'netball')
				{$results = $wpdb->get_results( "SELECT * FROM netball");}
			else if ($cat == 'athletics')
				{$results = $wpdb->get_results( "SELECT * FROM athletics");}
			else if ($cat == 'basketball')
				{$results = $wpdb->get_results( "SELECT * FROM basketball");}
			else if ($cat == 'marts')
				{$results = $wpdb->get_results( "SELECT * FROM marts");}
			else if ($cat == 'swim')
				{$results = $wpdb->get_results( "SELECT * FROM swim");}
			else{
				{$results = $wpdb->get_results( "SELECT * FROM sport");}
			}
			
		}
		$product = array($results, $geo);
		echo json_encode($product);
		die();
	}
}
add_action('wp_ajax_map_response', 'getInfo');
add_action('wp_ajax_nopriv_map_response', 'getInfo');

// event query handler
function getEvent() {
	if (isset( $_POST["post_var"] )) {
		$arr = explode(",",$_POST["post_var"]);
		$keyword = $arr[0];
		$postcode = $arr[1];
		$order1 = $arr[2];
		$order2 = $arr[3];
		$order = $order1.','.$order2;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://app.ticketmaster.com/discovery/v2/events?apikey=web5wRD0ahMMi14pAEfv4bzBa7q1368C&keyword=".$keyword."&postalCode=".$postcode."&sort=".$order."&countryCode=AU&stateCode=VIC&segmentName=sports");
		//curl_setopt($ch, CURLOPT_URL, "https://app.ticketmaster.com/discovery/v2/events?apikey=web5wRD0ahMMi14pAEfv4bzBa7q1368C&keyword=Miscellaneous&postalCode=3000&sort=name,asc&countryCode=AU&stateCode=VIC&segmentName=sports");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		$result = curl_exec($ch);

		if (curl_errno($ch)) {
			echo 'Error';
		}
		curl_close ($ch);
		$eventdata = json_decode($result);
		$eventsum = $eventdata->_embedded->events;
		if (count($eventsum) == 0) {
			echo "Error"; 
		}
		else{
			echo $result;
		}
		
		die();
	}
}


add_action('wp_ajax_event_response', 'getEvent');
add_action('wp_ajax_nopriv_event_response', 'getEvent');