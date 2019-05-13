 <?php
 /**

 * Plugin Name: Iteration3.1
 * Description: Facility page and Event Page. 
 * Version: 1.0.4 (May 9th,2019)
 * Author: Ethan (Junhao Yi)
 * License: GPL2
 */

 // Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ajax hooking */ 
function enqueueAjaxScript() {
	wp_enqueue_script('custom-ajax', plugins_url('js/ajax.js', __FILE__), array('jquery'));
	$script_data = array(
        'ajaxurl' => admin_url( 'admin-ajax.php' )
    );
    wp_localize_script(
        'custom-ajax',
        'custom_ajax_script',
        $script_data
    );
}
add_action('wp_enqueue_scripts', 'enqueueAjaxScript');
include(plugin_dir_path( __FILE__ ) . 'db/db.php');

function enqueueCommunityScript() {
	wp_enqueue_style('community-style', plugins_url('css/community.css', __FILE__));
	wp_enqueue_style('autocomplete-style', plugins_url('css/autocomplete.css', __FILE__));
	wp_enqueue_style('nice-select-style', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-nice-select/1.1.0/css/nice-select.min.css');
	wp_enqueue_script('nice-select-script', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-nice-select/1.1.0/js/jquery.nice-select.min.js');
	wp_enqueue_script('autocomplete-script', plugins_url('js/autocomplete.js', __FILE__));

}
add_action('wp_enqueue_scripts', 'enqueueCommunityScript');
function enqueueActivityScript() {
	wp_enqueue_style('activity-style', plugins_url('css/activity.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'enqueueActivityScript');
function enqueueeventScript() {
	wp_enqueue_style('event-style', plugins_url('css/event.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'enqueueeventScript');

$sub = array();
$pop = array();
$test = '';


/* initiate content block for first loading*/
function genBlock($results, $type) {
	if (count($results) != 0){
		foreach ($results as $rows) {
			echo '<div class="content-block"><button value="'.$rows->Latitude.' '.$rows->Longitude.'">';
			if ($type != 'none') {
				echo '<div class="block-type">'.$type.'</div>';
			}
			echo '<div class="content-title"><h3>'.$rows->Name.'</h3></div>';
			echo '<div class="content-description">';
			echo '<p><i class="fas fa-map-marker-alt"></i>  '.$rows->Address.', '.$rows->Suburb.'<br>';
			//echo '<p><i class="fas fa-map-marker-alt"></i>  '.$rows->Suburb.'<br>';
			echo ' <i class="fas fa-info"></i> '.$rows->SportsPlayed.'<br>';
			echo '<i class="fas fa-globe"></i><a href="https://www.google.com/maps/dir/Current+Location/'.$rows->Latitude.','.$rows->Longitude.'" target="_blank"><span style="color:blue"> Get directions</span></a>';
			echo '</p></div>';
			echo '</button></div>';
		}
	}
}

/* activity page module */
function activityGen() {
	global $wpdb;
	$results;
	$total_results = array();
	// store suburb list for autocompletion
	$sublist = $wpdb->get_results( "SELECT DISTINCT Suburb FROM sport");
	?>
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">
	<div class="activity-container">
	 	<div class="search-container" id="search-container">
			<div class="content-search">
				<form method="post" autocomplete="off">
					<div class="search-box">
						<legend>
								Enter Suburb Name:
							</legend>
						<div class="search-txt autocomplete">
						<input id="s-suburb" type="text" name="s-suburb" placeholder="E.g: Caulfield" value="<?php echo (isset($_GET['my_variable'])?$_GET['my_variable']:'') ?>">
						</div>
						<select name="s-category" id="search-list">
							<option value="sport">All Sports</option>
							<option value="soccer">Soccer</option>
							<option value="dance">Dance</option>
							<option value="cricket">Cricket</option>
							<option value="tennis">Tennis</option>
							<option value="footy">Footy</option>
							<option value="gym">Gymnastics</option>
							<option value="hockey">Hockey</option>
							<option value="netball">Netball</option>
							<option value="athletics">Athletics</option>
							<option value="basketball">Basketball</option>
							<option value="marts">Martial Arts</option>
							<option value="swim">Swimming</option>
						</select>
						<input type="button" name="s-submit" id="search-btn" value="Show">
					</div>
				</form>
			</div>	
		</div>
	 	<div class="side-container" id="side-container">
	 		
	 		<div class="content-wrap">
				<div class="content-display" id="content-display">
					<?php 	
					if (isset($_GET['my_variable']))
					{
						echo $_GET['my_variable'];
						$querylist = array('sport','soccer', 'dance', 'cricket', 'tennis', 'footy', 'gym', 'hockey', 'netball','athletics','basketball','marts','swim');
						foreach ($querylist as $row) {
							$sub_result = $wpdb->get_results( "SELECT * FROM ". $row ." where TRIM(UPPER(Suburb)) = '".strtoupper($_GET['my_variable'])."'");
							//$sub_result = $wpdb->get_results( "SELECT * FROM ". $row ." where TRIM(UPPER(Postcode)) = '".strtoupper($_GET['my_variable'])."'");
							if (count($sub_result) != 0){
								genBlock($sub_result, $row);
								$total_results = array_merge($total_results, $sub_result);
							}
						}
					}
					else {
						$total_results = $wpdb->get_results( "SELECT * FROM sports");
						genBlock($total_results, 'none');
					}
					 ?>		
				</div>
			</div>
		</div>
		
		<div class="map-container" id="map-container">
			<div id="map"></div>
		</div>
		<div class="side-ctrl-container" id="side-ctrl-container">
			<button id="side-ctrl-btn" onclick="sideCtrl()">
				<i class="fas fa-angle-left" id="ctrl-icon"></i>
			</button>
		</div>
	</div>
	<!-- google map script -->
	<script type="text/javascript">
		// jQuery(document).ready(function() {
		// 	jQuery('select').niceSelect();
		// });

		function sideCtrl() {
			document.getElementById('map-container').classList.toggle('active');
			document.getElementById('side-container').classList.toggle('active');
			document.getElementById('search-container').classList.toggle('active');
			document.getElementById('side-ctrl-container').classList.toggle('active');
			if (document.getElementById("ctrl-icon").classList.contains('fa-angle-right')) {
				document.getElementById("ctrl-icon").classList.remove('fa-angle-right');
				document.getElementById("ctrl-icon").classList.add('fa-angle-left');
			}
			else {
				document.getElementById("ctrl-icon").classList.remove('fa-angle-left');
				document.getElementById("ctrl-icon").classList.add('fa-angle-right');
			}
		}

		// dynamicaly generate black content according to query
		function blockGen(data) {
			if (data == ''){
				jQuery('#content-display').html(
					'<div class="content-block"><button>'+
					'<div class="content-title"><h3>No data</h3></div>'+
					'<div class="content-description">'+
					'<p>Sorry, no data for ' + jQuery('#s-suburb').val() + '</p></div>'+
					'</button></div>'
					);
				clearMarker();
				clearCluster();
			}
			else {
				clearCluster();
				clearMarker(); // clear map marker;
				document.getElementById('content-display').innerHTML = ''; // clear content
				for (var i = 0; i < data.length; i++) {
					var block = document.createElement('div'); // create new block
					var blockbtn = document.createElement('button');
					block.classList.add('content-block');
					blockbtn.value = data[i].Latitude + ' ' + data[i].Longitude;
					blockbtn.classList.add('content-block-btn');
					blockbtn.innerHTML = '<div class="content-title"><h3>'+data[i].Name+'</h3></div>'+
						'<div class="content-description">'+
						'<p><i class="fas fa-map-marker-alt"></i>'+' '+data[i].Address+', '+data[i].Suburb+'</p>'+
						'<p><i class="fas fa-info"></i>'+'  '+data[i].SportsPlayed+'</p>'+
						'<p><i class="fas fa-globe"></i><a href="https://www.google.com/maps/dir/Current+Location/'+data[i].Latitude+','+data[i].Longitude+'" target="_blank"><span style="color:blue"> Get directions</span></a></p>'+
						'</div>';
					block.appendChild(blockbtn);
					document.getElementById('content-display').appendChild(block);

					addMarker({lat:data[i].Latitude, lng:data[i].Longitude, name:data[i].Name, addr:data[i].Address, sub:data[i].Suburb});
					if (i == 5000){
						break;
					}
				}
				addCluster();
			}
		}
		
		var map;
		var markers = [];
		var markerCluster;

		// block listener for map center change
		jQuery('#content-display').on('click', '.'+'content-block-btn', function() {
			var rawpos = jQuery(this).val();
			var pos = rawpos.split(" ");
			map.panTo({lat: parseFloat(pos[0]), lng: parseFloat(pos[1])});
		});

		// set the view port to selected marker on the map
		jQuery(".content-display button").click(function(){
			var rawpos = jQuery(this).val();
			var pos = rawpos.split(" ");
			map.panTo({lat: parseFloat(pos[0]), lng: parseFloat(pos[1])});
		});


		function addInfowindow(marker, pos) {
			// add event listener to zoom when click the marker
			google.maps.event.addListener(marker,'click',function() {
				var infowindow = new google.maps.InfoWindow({
				content: '<div class="cus-InfoWindow">' + 
						'<h3 id="cus-info-title">'+pos.name+'</h3><br>' + 
						'<p><i class="fas fa-map-marker-alt"></i>'+pos.sub+'</p></br>' +
						'<p><i class="fas fa-map-marker-alt"></i><a href="https://www.google.com/maps/dir/Current+Location/'+pos.lat+','+pos.lng+'" target="_blank"><span style="color:blue;"> Get directions </span></a><br>'+
							
							'</p></div>'
				});
				infowindow.open(map,marker);
				map.panTo(marker.getPosition());
				map.setZoom(16);
				google.maps.event.addListener(map, 'click', function() {
			    	infowindow.close();
				});
			});
		}

		function addMarker(pos) {
			// add a marker for geo location
			marker = new google.maps.Marker({
				position: new google.maps.LatLng(pos.lat, pos.lng),
				map: map,
				animation: google.maps.Animation.DROP,
				title: pos.name
			});
			// add info window
			addInfowindow(marker, pos);
			markers.push(marker);
		}

		function clearMarker() {
			// clear map markers
			for (var i = 0; i < markers.length; i++) {
				markers[i].setMap(null);
			}
			markers = [];
		}

		function addCluster() {
			markerCluster = new MarkerClusterer(map, markers,
            {imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m',maxZoom: 14});
		}

		function clearCluster() {
			markerCluster.clearMarkers();
		}

		function initMap() {
			var pos = getCenter();
			map = new google.maps.Map(document.getElementById('map'), {
			  zoom: pos.zoom,
			  center: {lat: pos.lat, lng: pos.lng},
			  mapTypeId: 'roadmap',
			  disableDefaultUI: true,
			  zoomControl: true,
			  fullscreenControl: true
			});
			initMarker();
			addCluster();
		}

		// change map center each for each query
		function setCenter(data) {
			if (data != null){
				map.panTo({lat: parseFloat(data[0].latitude), lng: parseFloat(data[0].longitude)});
				map.setZoom(9);
			}
			else {
				map.panTo({lat: -37.814, lng: 144.96332});
				map.setZoom(12);
			}
		}

		function initMarker() {
			<?php $count = 0; ?>
			<?php foreach ($total_results as $rows) {
				if ($count == 5000) {break;}
				// add marker for each location
				echo 'addMarker({lat:'.$rows->Latitude.',lng:'.$rows->Longitude.',name:"'.$rows->Name.'",addr:"'.$rows->Address.'",sub:"'.$rows->Suburb.'"});';
	      		$count++;
			} ?>
		}

		// get view port to the selected suburb  
		function getCenter(data) {
			<?php
			$center = array();
			if (!isset($_GET['my_variable'])) {
				$center = array('lat'=>-37.814, 'lng'=>144.96332, 'zoom'=>12);
			}
			else {
				global $wpdb;
				$geo = $wpdb->get_results( "SELECT * FROM geo where TRIM(UPPER(Suburbs)) = '".strtoupper($_GET['my_variable'])."'");
				foreach ($geo as $row) {
					$center['lat'] = $row->latitude;
					$center['lng'] = $row->longitude;
					$center['zoom'] = 14;
				}
			}?>
			return {lat:<?php echo $center['lat'] ?>, lng:<?php echo $center['lng'] ?>, zoom:<?php echo $center['zoom'] ?>};
		}
		
	</script>
	<script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js">
    </script>
	<script async defer
		        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAamXiqL6AsLUQSEpSdBps9uPgwo8i1oNs&libraries=visualization&callback=initMap">
	</script>
	<!-- autocompletion script -->
	<script>
		/*An array containing all the country names in the world:*/
		var subs = [<?php foreach ($sublist as $row) {
			echo '"'.$row->Suburb.'",';
		} ?>];

		/*initiate the autocomplete function on the "sub-input" element, and pass along the countries array as possible autocomplete values:*/
		autocomplete(document.getElementById("s-suburb"), subs);
	</script>
	<?php  
}

// event page module
function eventGen() {
	global $wpdb;
	$sublist = $wpdb->get_results( "SELECT DISTINCT Sports FROM  autofill");
	?>
	<div class="event-userinput">
		<form autocomplete="off">
			<div class="autocomplete event-keywrap">
				<legend>Please enter a Sport name</legend>
				<input type="text" id="event_key" placeholder="E.g. Hockey" >
			</div>
			<div class="autocomplete event-postwrap">
				<legend>
				Enter Postcode (Optional)
				</legend>
				<input type="text" id="event_postcode" placeholder="Postcode" >
			</div>
			<input type="button" id="event_search" value="search">
			
		</form>
		<div class="sortwrap" style="width: 100%;margin-top: 10px;">
			Sort by 
			<select id="event_sort" style="margin-right: 10px;">
				<option value="date,asc">Date(Ascending)</option>
				<option value="date,desc">Date(Descending)</option>

			</select>
		</div>
	</div>
	<div style="width:100%;">
		<hr style="border-color: black;background-color: black">
	</div>
	<div class="eventcontainer" id="eventcontainer">
		<div class="preload">
			<img src="<?php echo plugin_dir_url( __FILE__ ) . 'img/giphy.gif'; ?>">
		</div>
		<div class="cardcontainer" id='cardcontainer'></div>
		<div class="event-tips" style="color: black;">Input a Sport to search or click the Search button directly to get all sports events.</div>
		<div class="cardcontainer" id='cardcontainer'></div>
		<div class="event-error" style="display: none; color: black;"></div>
		<div class="event-showmore" style="">
			<button onclick="showmore();">Show more</button>
		</div>
		<div class="event-inputerror" style="display: none;color: red;">**Please input a <b>SPORT</b></div>
	</div>
	<script type="text/javascript">
		var imgLoc = "<?php echo plugin_dir_url( __FILE__ ) . 'img/'; ?>"; // default img location
		var loadinterval = 6; // data increment
		var loadlen; // which part of data to be load
		var wholedata; // store received data

		// first data loading from server
		function cardGen(data) {
			wholedata = data;
			datalen = data.length;
			loadlen = loadinterval;
			if (loadlen > datalen) {
				loadlen = datalen;
			}
			for (var i = 0; i < loadlen; i++) {
				createCard(data[i]);
			}
			jQuery(".preload").hide();
			if (loadlen < datalen){
				jQuery(".event-showmore").show();
			}
		}

		// create each card
		function createCard(data) {
			var block = document.createElement('div'); // create new block
			var cardfront = document.createElement('div');
			var cardback = document.createElement('div');
			var rate = document.createElement('span');
			block.classList.add('event-card');
			cardfront.classList.add('event-cardfront');
			cardback.classList.add('event-cardback');
			rate.classList.add('event-rate');

			// front side
			if (data.images[0].url != ''){
				cardfront.style.background = 'linear-gradient(rgba(0, 0, 0, 0.2), rgba(0, 0, 0, 0.2)), url('+data.images[0].url+')';
				cardfront.style.backgroundSize = 'cover';
			}
			else {
				cardfront.style.background = 'linear-gradient(rgba(0, 0, 0, 0.2), rgba(0, 0, 0, 0.2)), url('+imgLoc+'/sport.jpg)';
				cardfront.style.backgroundSize = 'cover';
			}
			cardfront.innerHTML = '<div class="frontcontent"><h1>'+data.name+'</h1></div>'+
				'</div></div>';

			// back side
			cardback.innerHTML = '<div class="backcontent">'+
				'<h1>'+data.name+'</h1>'+
				'<div class="backmid"><span class="type">DATE:</span><span class="value">'+data.dates.start.localDate+'</span>'+
				'<span class="type">TIME:</span><span class="value">'+data.dates.start.localTime+'</span>'+
				'<span class="type">TICKET:</span><span class="value">'+data.dates.status.code+'</span>'+
				'<span class="type">ADDRESS:</span><span class="value">'+data._embedded.venues[0].address.line1+'</span></div>'+
				'<a href="'+data.url+'" target="_blank">Visit event page</a></div>';
			
			
			rate.innerHTML = data.dates.start.localDate;
			cardfront.appendChild(rate);
			block.appendChild(cardfront);
			block.appendChild(cardback);
			document.getElementById('cardcontainer').appendChild(block);
		}

		// show more result
		function showmore() {
			for (var i = loadlen; i < datalen; i++ ) {
				createCard(wholedata[i]);
			}
			loadlen += loadinterval;
			if (loadlen < datalen){
				
			}
			else {
				jQuery(".event-showmore").hide();
			}
		}
	</script>
	<script type="text/javascript">
		var sports = [<?php foreach ($sublist as $row) {
			echo '"'.$row->Sports.'",';
		} ?>];
		
		autocomplete(document.getElementById("event_key"), sports);
		//autocomplete(document.getElementById("event_postcode"), cuisines);
	</script>
	<?php  
}


add_shortcode("activity-generator", "activityGen");
add_shortcode("event-generator", "eventGen");
