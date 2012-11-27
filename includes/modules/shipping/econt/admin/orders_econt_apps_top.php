<?php
/**
 * zencart mod
 * SIMEON MLADENOV
*/
?>
<link rel="stylesheet" type="text/css" href="../includes/modules/shipping/econt/javascript/jquery/jquery.ui.all.css" />
<script language="javascript" src="../includes/modules/shipping/econt/javascript/jquery/jquery-1.4.3.js"></script>
<script language="javascript" src="../includes/modules/shipping/econt/javascript/jquery/jquery-ui-1.8.6.custom.min.js"></script>
<script language="javascript" src="../includes/modules/shipping/econt/javascript/jquery/jquery.bgiframe-2.1.2.js"></script>
<script language="javascript" src="../includes/modules/shipping/econt/javascript/jquery/ui/jquery.ui.core.js"></script>
<script language="javascript" src="../includes/modules/shipping/econt/javascript/jquery/ui/jquery.ui.widget.js"></script>
<script language="javascript" src="../includes/modules/shipping/econt/javascript/jquery/ui/jquery.ui.position.js"></script>
<script language="javascript" src="../includes/modules/shipping/econt/javascript/jquery/ui/jquery.ui.mouse.js"></script>
<script language="javascript" src="../includes/modules/shipping/econt/javascript/jquery/ui/jquery.ui.button.js"></script>
<script language="javascript" src="../includes/modules/shipping/econt/javascript/jquery/ui/jquery.ui.draggable.js"></script>
<script language="javascript" src="../includes/modules/shipping/econt/javascript/jquery/ui/jquery.ui.resizable.js"></script>
<script language="javascript" src="../includes/modules/shipping/econt/javascript/jquery/ui/jquery.ui.dialog.js"></script>
<script language="javascript" src="../includes/modules/shipping/econt/javascript/jquery/ui/jquery.effects.core.js"></script>
<script language="javascript" src="includes/modules/shipping/econt/javascript/jquery/ui/jquery.ui.autocomplete.js"></script>
<link rel="stylesheet" type="text/css" href="includes/modules/shipping/econt/javascript/jquery/jquery.autocomplete-style.css" />

<script language="javascript" type="text/javascript">
$(function() {
	$("#dialog:ui-dialog").dialog( "destroy" );
	
	var receiver_name 		= $("#receiver_name"),
	receiver_contact_phone 	= $("#receiver_contact_phone"),
	receiver_city 			= $("#receiver_city"),
	receiver_zip 			= $("#receiver_zip"),
	receiver_quarter 		= $("#receiver_quarter"),
	receiver_street 		= $("#receiver_street"),
	receiver_street_num 	= $("#receiver_street_num"),
	receiver_quarter_other 	= $("#receiver_quarter_other"),
	econtorderid			= $("#econtorderid");
	cityid					= $("#cityid");
	streetid				= $("#streetid");
	/** BUTTON REQUEST COURIER BOF **/
	$("#dialog-requestcourier").dialog({
		autoOpen: false,
		resizable: false,
		width: 350,
		height: 170,
		modal: true,
		buttons: {
			"<?php echo MODULE_SHIPPING_ECONT_REQUESTCOURIER_BUTTON_START_TEXT; ?>": function() {
				$( "#progressbar" ).progressbar({ value: 100 });
				$.ajax({
					dataType: 'json',
					url: "econt_callback.php",
					data: {
						action:			"request-courier-single",
						econtorderid: 	document.getElementById('econtorderid').value
					},
					success: function(data){
						$( "#progressbar" ).progressbar("destroy");
						if(data.courier['address'] == 'error') {
							$("#dialog-requestcourier").dialog("close");
							$("#dialog-receiveraddress").dialog("open");
							
							$("#progressbar_receiveraddress").progressbar({ value: 100 });
							
							$("#error-receiveraddress").text(data.courier['error']);
							document.getElementById("dialog-receiveraddress").title = data.courier['error'];
							receiver_name.val(data.courier[				"name"]);
							receiver_contact_phone.val(data.courier[	"phone_num"]);
							receiver_city.val(data.courier[				"city"]);
							receiver_zip.val(data.courier[				"postcode"]);
							receiver_quarter.val(data.courier[			"quarter"]);
							receiver_street.val(data.courier[			"street"]);
							receiver_street_num.val(data.courier[		"street_num"]);
							receiver_quarter_other.val(data.courier[	"street_other"]);
							econtorderid.val(data.courier[				"orderid"]);
							cityid.val(data.courier[					"cityid"]);
							streetid.val(data.courier[					"streetid"]);
							
							$("#progressbar_receiveraddress").progressbar("destroy");
						} else if(data.courier['pdf'] != "") {
							$("#pdflink").html(data.courier['pdf']);
							$( "#dialog-requestcourier" ).dialog( "<?php echo MODULE_SHIPPING_ECONT_REQUESTCOURIER_BUTTON_START_TEXT; ?>", "disabled", true );
						}
					}
				});
			}
		}
	});
	
	$("#dialog:ui-dialog").dialog("destroy");
	$("#requestcourier-form")
	.button()
	.click(function() {
		$("#dialog-requestcourier").dialog("open");
	});
	
	
	
	$("#dialog-loadingurl").dialog({
		autoOpen: false,
		resizable: false,
		width: 400,
		height: 540,
		modal: true,
		buttons: {
			"<?php echo MODULE_SHIPPING_ECONT_REQUESTCOURIER_BUTTON_CLOSE_TEXT; ?>": function() {
				$( this ).dialog( "close" );
			} 
		}
	});
	$("#loadingurl-form")
	.button()
	.click(function() {
		$("#dialog-loadingurl").dialog("open");
	});
	/** BUTTON REQUEST COURIER EOF **/
	
	
	/** FORM RECEIVER ADDRESS BOF **/
	$("#dialog-receiveraddress").dialog({
		autoOpen: false,
		resizable: false,
		width: 460,
		height: 665,
		modal: true,
		buttons: {
			"<?php echo MODULE_SHIPPING_ECONT_REQUESTCOURIER_BUTTON_SAVE_TEXT; ?>": function() {
				$("#progressbar_receiveraddress").progressbar({ value: 100 });
				$.ajax({
					dataType: 'json',
					url: "econt_callback.php",
					data: {
						action: 				"set_receiver_address",
						econtorderid: 			econtorderid.val(),
						receiver_name: 			receiver_name.val(),
						receiver_contact_phone: receiver_contact_phone.val(),
						receiver_city: 			receiver_city.val(),
						receiver_zip: 			receiver_zip.val(),
						receiver_quarter:	 	receiver_quarter.val(),
						receiver_street: 		receiver_street.val(),
						receiver_street_num: 	receiver_street_num.val(),
						receiver_quarter_other: receiver_quarter_other.val()
					},
					success: function(data){
						if(data.setreceiveraddress["status"] == '1') {
							$("#progressbar_config").progressbar("destroy");
						}
					}
				});
				$(this).dialog("close");
			}
		}
	});
	
	$("#receiveraddress-form")
	.button()
	.click(function() {
		$("#dialog-receiveraddress").dialog("open");
	});
	/** FORM RECEIVER ADDRESS EOF **/
	
	/** AUTOCOMPLETE GET CITIES BOF **/
	var availableCities = new Array();
	$("#receiver_city").autocomplete({
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
					$("#receiver_zip").val(availablePostCode[x]);
				}
			});
		}
	});
	/** AUTOCOMPLETE GET CITIES EOF **/
	
	/** AUTOCOMPLETE GET POSTCODE BOF **/
	var availablePostCode = new Array();
	$("#receiver_zip").autocomplete({
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
					$("#receiver_city").val(availableCities[x]);
				}
			});
		}
	});
	/** AUTOCOMPLETE GET POSTCODE EOF **/
	
	/** AUTOCOMPLETE GET STREET BOF **/
	var availableStreet = new Array();
	$("#receiver_street").autocomplete({
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
			document.getElementById('receiver_street_num').focus();
		}
	});
	/** AUTOCOMPLETE GET STREET EOF **/
	
	
	/** AUTOCOMPLETE GET SUBURB BOF **/
	var availableSuburb = new Array();
	$("#receiver_quarter").autocomplete({
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
			document.getElementById('receiver_quarter_other').focus();
		}
	});
	/** AUTOCOMPLETE GET SUBURB EOF **/
	
	$("#delete-loading").click(function() {
		if(confirm("<?php echo MODULE_SHIPPING_ORDERS_LOADINGINFO_DELETE_LOADING_CONFIRM_TEXT; ?>")) {
			$.ajax({
				dataType: 'json',
				url: "econt_callback.php",
				data: {
					action: "delete_loading",
					loadingid: document.getElementById('loadingid').value
				},
				success: function( data ) {
					if(!data) {
						alert();
					} else if(data.loadingdelete['status'] == '1') {
						$("#dialog-loadingurl").dialog("close");
					}
				},
				error:function (xhr, ajaxOptions, thrownError){
                    if(xhr.status == 200) {
                    	alert("<?php echo MODULE_SHIPPING_ORDERS_LOADINGINFO_DELETE_LOADING_ERROR_TEXT; ?>");
                    	$("#delete-loading-error").html(thrownError);
                    }
                }
			});
		}
	});
});
</script>

<style type="text/css">
body { font-size: 62.5%; }
label, input { display:block; }
input.text { margin-bottom:12px; width:95%; padding: .4em; }
fieldset { padding:0; border:0; margin-top:5px; }
h1 { font-size: .2em; margin: .6em 0; }
div#users-contain { width: 350px; margin: 20px 0; }
div#users-contain table { margin: 1em 0; border-collapse: collapse; width: 100%; }
div#users-contain table td, div#users-contain table th { border: 1px solid #eee; padding: .6em 10px; text-align: left; }
.ui-dialog .ui-state-error { padding: .3em; }
.validateTips { border: 1px solid transparent; padding: 0.3em; }
.ui-progressbar-value { background-image: url(../images/infobox/pbar-ani.gif); }

#dialog-cities-update { height: 75px !important; }
div#loadingurl-form {
	width: 70px;
	height: 50px;
}
div#requestcourier-form {
	width: 70px;
	height: 50px;
}
#requestcourier-form img { }
div#requestcourier-form-non {float: left; position: relative; width: 20px; height: 20px; padding-right: 10px;}
#requestcourier-form-non img {width: 20px; height: 20px;}
#dialog-receiveraddress { height: 455px !important; }

#dialog-loadingurl {
	width: 380px;
	height: 350px;
}
#dialog-loadingurl input, textarea { border: 1px solid #CCCCCC; }

#dialog-loadingurl fieldset {
	width: 380px;
	height: 440px;
	border: 1px solid #CCCCCC;
}
#dialog-loadingurl legend {
	font-size: 14px;
	color: #717C80;
}
#dialog-loadingurl .sender-config-form td {
	font-size: 11px;
	text-align: center;
}

#dialog-loadingurl table { }

#delete-loading:hover {
	cursor: pointer;
	text-decoration: underline;
}


.t_border {
	border : solid;
	border-width : 1px;
	border-color : #CCCCCC;
}

.t_b_border {
	border-bottom : solid;
	border-bottom-width : 1px;
	border-color : #CCCCCC;
}

.t_t_border {
	border-top : solid;
	border-top-width : 1px;
	border-color : #CCCCCC;
}

.t_l_border {
	border-left : solid;
	border-left-width : 1px;
	border-color : #CCCCCC;
}

.t_r_border {
	border-right : solid;
	border-right-width : 1px;
	border-color : #CCCCCC;
}

.t_rb_border {
	border-bottom : solid;
	border-bottom-width : 1px;
	border-right : solid;
	border-right-width : 1px;
	border-color : #CCCCCC;
}

.t_tb_border {
	border-bottom : solid;
	border-bottom-width : 1px;
	border-top : solid;
	border-top-width : 1px;
	border-color : #CCCCCC;
}

.t_bl_border {
	border-bottom : solid;
	border-bottom-width : 1px;
	border-left : solid;
	border-left-width : 1px;
	border-color : #CCCCCC;
}

.t_tl_border {
	border-top : solid;
	border-top-width : 1px;
	border-left : solid;
	border-left-width : 1px;
	border-color : #CCCCCC;
}

.t_tr_border {
	border-top : solid;
	border-top-width : 1px;
	border-right : solid;
	border-right-width : 1px;
	border-color : #CCCCCC;
}

.t_tbl_border {
	border-bottom : solid;
	border-bottom-width : 1px;
	border-left : solid;
	border-left-width : 1px;
	border-top : solid;
	border-top-width : 1px;
	border-color : #CCCCCC;
}

.t_trb_border {
	border-bottom : solid;
	border-bottom-width : 1px;
	border-right : solid;
	border-right-width : 1px;
	border-top : solid;
	border-top-width : 1px;
	border-color : #CCCCCC;
}
</style>