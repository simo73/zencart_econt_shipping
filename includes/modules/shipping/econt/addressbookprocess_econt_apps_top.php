<?php
/**
 * zencart mod
 * SIMEON MLADENOV
*/
?>

<link rel="stylesheet" type="text/css" href="includes/modules/shipping/econt/javascript/jquery/jquery.ui.all.css" />
<script language="javascript" src="includes/modules/shipping/econt/javascript/jquery/jquery-1.4.3.js"></script>
<script language="javascript" src="includes/modules/shipping/econt/javascript/jquery/ui/jquery.ui.core.js"></script>
<script language="javascript" src="includes/modules/shipping/econt/javascript/jquery/ui/jquery.ui.widget.js"></script>
<script language="javascript" src="includes/modules/shipping/econt/javascript/jquery/ui/jquery.ui.position.js"></script>
<script language="javascript" src="includes/modules/shipping/econt/javascript/jquery/ui/jquery.ui.autocomplete.js"></script>
<link rel="stylesheet" type="text/css" href="includes/modules/shipping/econt/javascript/jquery/jquery.autocomplete-style.css" />

<script language="javascript" type="text/javascript">
$(function() {

	/** AUTOCOMPLETE GET CITIES BOF **/
	var availableCities = new Array();
	$("#city_suggest").autocomplete({
		source: function( request, response ) {
			$.ajax({
				dataType: 'json',
				url: "econt_callback.php",
				data: {
					action: "get_suggest_city",
					city_startswith: request.term
				},
				success: function( data ) {
					availableCities = new Array();
					for(x in data.cities) {
						availableCities[x] = data.cities[x]["name"];
					}
					response(availableCities);
				}
			});
		},
		minLength: 3,
		select: function( event, ui ) {
			$.ajax({
				dataType: 'json',
				url: "econt_callback.php",
				data: {
					action: "get_suggest_postcode",
					city_startswith: ui.item.label
				},
				success: function( data ) {
					for(x in data.postcode) {
						availablePostCode[x] = data.postcode[x]["code"];
						document.getElementById('cityid').value = data.postcode[x]["cityid"];
					}
					$("#postcode_suggest").val(availablePostCode[x]);
					document.getElementById('street_suggest').disabled = false;
					document.getElementById('street_suggest').value = '';
					document.getElementById('street_suggest').focus();
					document.getElementById('street_number').disabled = false;
					document.getElementById('street_number').value = '';
					document.getElementById('suburb_suggest').disabled = false;
					document.getElementById('suburb_suggest').value = '';
					document.getElementById('suburb_else').disabled = false;
					document.getElementById('suburb_else').value = '';
				}
			});
		}
	});
	/** AUTOCOMPLETE GET CITIES EOF **/
	
	/** AUTOCOMPLETE GET POSTCODE BOF **/
	var availablePostCode = new Array();
	$("#postcode_suggest").autocomplete({
		source: function( request, response ) {
			$.ajax({
				dataType: 'json',
				url: "econt_callback.php",
				data: {
					action: "get_suggest_postcode",
					postcode_startswith: request.term
				},
				success: function( data ) {
					availablePostCode = new Array();
					for(var x in data.postcode) {
						availablePostCode[x] = data.postcode[x]["code"];
					}
					response(availablePostCode);
				}
			});
		},
		minLength: 2,
		select: function( event, ui ) {
			$.ajax({
				dataType: 'json',
				url: "econt_callback.php",
				data: {
					action: "get_suggest_city",
					postcode_startswith: ui.item.label
				},
				success: function( data ) {
					for(x in data.cities) {
						availableCities[x] = data.cities[x]["name"];
						document.getElementById('cityid').value = data.cities[x]["cityid"];
					}
					$("#city_suggest").val(availableCities[x]);
					document.getElementById('street_suggest').disabled = false;
					document.getElementById('street_suggest').value = '';
					document.getElementById('street_suggest').focus();
					document.getElementById('street_number').disabled = false;
					document.getElementById('street_number').value = '';
					document.getElementById('suburb_suggest').disabled = false;
					document.getElementById('suburb_suggest').value = '';
					document.getElementById('suburb_else').disabled = false;
					document.getElementById('suburb_else').value = '';
				}
			});
		}
	});
	/** AUTOCOMPLETE GET POSTCODE EOF **/
	
	/** AUTOCOMPLETE GET STREET BOF **/
	var availableStreet = new Array();
	$("#street_suggest").autocomplete({
		source: function( request, response ) {
			$.ajax({
				dataType: 'json',
				url: "econt_callback.php",
				data: {
					action: "get_suggest_street",
					street_startswith: request.term,
					cityid: document.getElementById('cityid').value
				},
				success: function( data ) {
					availableStreet = new Array();
					for(x in data.street) {
						availableStreet[x] = data.street[x]["street_name"];
					}
					response(availableStreet);
				}
			});
		},
		minLength: 3,
		select: function( event, ui ) {
			$.ajax({
				dataType: 'json',
				url: "econt_callback.php",
				data: {
					action: "get_suggest_street",
					street_startswith: ui.item.value,
					street_exact: '1',
					cityid: document.getElementById('cityid').value
				},
				success: function( data ) {
					for(x in data.street) {
						document.getElementById('streetid').value = data.street[x]["streetid"];
					}
				}
			});
			document.getElementById('street_number').focus();
		}
	});
	/** AUTOCOMPLETE GET STREET EOF **/
	
	
	/** AUTOCOMPLETE GET SUBURB BOF **/
	var availableSuburb = new Array();
	$("#suburb_suggest").autocomplete({
		source: function( request, response ) {
			$.ajax({
				dataType: 'json',
				url: "econt_callback.php",
				data: {
					action: "get_suggest_suburb",
					suburb_startswith: request.term,
					cityid: document.getElementById('cityid').value
				},
				success: function( data ) {
					if(data.suburb) {
						availableSuburb = new Array();
						for(x in data.suburb) {
							availableSuburb[x] = data.suburb[x]["suburb_name"];
						}
						response(availableSuburb);
					}
				}
			});
		},
		minLength: 2,
		select: function( event, ui ) {
			document.getElementById('suburb_else').focus();
		}
	});
	/** AUTOCOMPLETE GET SUBURB EOF **/
});

</script>