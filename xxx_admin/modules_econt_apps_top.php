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

<script language="javascript" type="text/javascript">
$(function() {

	/** BUTTON SET/UPDATE E-Econt CUSTOMER INFO BOF **/
	// a workaround for a flaw in the demo system (http://dev.jqueryui.com/ticket/4375), ignore!
	$("#dialog:ui-dialog").dialog( "destroy" );

	var eol_username = $("#eol_username"),
	eol_password = $("#eol_password"),
	deliver_to = $("#deliver_to"),
	validate_full_client_address = $("#validate_full_client_address"),	
	allFields = $([]).add(eol_username).add(eol_password).add(deliver_to).add(validate_full_client_address),
	tips = $( ".validateTips" );
	
	function updateTips( t ) {
		tips
		.text( t )
		.addClass( "ui-state-highlight" );
		setTimeout(function() {
			tips.removeClass( "ui-state-highlight", 1500 );
		}, 
		500 );
	}
	
	function checkLength( o, n, min, max ) {
		if ( o.val().length > max || o.val().length < min ) {
			o.addClass( "ui-state-error" );
			updateTips( "Length of " + n + " must be between " + min + " and " + max + "." );
			return false;
		} else {
			return true;
		}
	}
	
	function checkRegexp( o, regexp, n ) {
		if ( !( regexp.test( o.val() ) ) ) {
			o.addClass( "ui-state-error" );
			updateTips( n );
			return false;
		} else {
			return true;
		}
	}
	
	$("#dialog-shopconfig-form").dialog({
		autoOpen: false,
		resizable: false,
		height: 340,
		width: 580,
		modal: true,
		buttons: {
			"<?php echo MODULE_SHIPPING_ECONT_CONFIG_SHOP_BUTTON_SAVE_TEXT; ?>": function() {
				var bValid = true;
				allFields.removeClass( "ui-state-error" );
				if ( bValid ) {
					$.ajax({
						type: "POST",
						url: "econt_callback.php",
						data: "action=set_shop_info&eol_username="+eol_username.val()+"&eol_password="+eol_password.val()+"&deliver_to="+deliver_to.val()+"&validate_full_client_address="+validate_full_client_address.val(),
						success: function(msg){
							if(msg == '1') {
								alert( "<?php echo MODULE_SHIPPING_ECONT_CONFIG_SHOP_SAVE_SUCCESS_TEXT; ?>" );
							}
						}
					});
					$( this ).dialog( "close" );
				}
			},
			"<?php echo MODULE_SHIPPING_ECONT_CONFIG_SHOP_BUTTON_CANCEL_TEXT; ?>": function() {
				$( this ).dialog( "close" );
			}
		},
		close: function() {
			allFields.val( "" ).removeClass( "ui-state-error" );
		}
	});
	
	$("#shopconfig-form")
	.button()
	.click(function() {
		$.ajax({
			dataType: 'json',
			url: "econt_callback.php",
			data: "action=get_shop_info",
			success: function(data){
				eol_password.val(data.shopinfo["eol_password"]);
				eol_username.val(data.shopinfo["eol_username"]);
				if(data.shopinfo["deliver_to"] == 'both') {
					$( "#deliver_to option[value='both']" ).attr('selected', 'selected');
				} else if (data.shopinfo["deliver_to"] == 'door') {
					$( "#deliver_to option[value='door']" ).attr('selected', 'selected');
				} else if (data.shopinfo["deliver_to"] == 'office') {
					$( "#deliver_to option[value='office']" ).attr('selected', 'selected');
				}
				
				if(data.shopinfo["validate_full_client_address"] == 'yes') {
					$( "#validate_full_client_address option[value='yes']" ).attr('selected', 'selected');
				} else if (data.shopinfo["validate_full_client_address"] == 'cityzip') {
					$( "#validate_full_client_address option[value='cityzip']" ).attr('selected', 'selected');
				} else if (data.shopinfo["validate_full_client_address"] == 'no') {
					$( "#validate_full_client_address option[value='no']" ).attr('selected', 'selected');
				}
			}
		});
		$("#dialog-shopconfig-form").dialog("open");
	});
	/** BUTTON SET/UPDATE E-Econt CUSTOMER INFO EOF **/


	/** BUTTON CITIES UPDATE BOF **/
	$( "#dialog-cities-update" ).dialog({
		autoOpen: false,
		resizable: false,
		width: 350,
		height: 170,
		modal: true,
		buttons: {
			"<?php echo MODULE_SHIPPING_ECONT_CITIES_UPDATE_BUTTON_START_TEXT; ?>": function() {
				$( "#progressbar" ).progressbar({ value: 100 });
				$( "#dialog-cities-update" ).dialog( "<?php echo MODULE_SHIPPING_ECONT_CITIES_UPDATE_BUTTON_START_TEXT; ?>", "disabled", true );
				$.ajax({
					type: "POST",
					url: "econt_callback.php",
					data: "action=cities_update",
					dataType: 'json',
					global: false,
					async: false,
					success: function(data){
						$( "#progressbar" ).progressbar("destroy");
						$( "#dialog-cities-update" ).dialog( "close" );
						if(data.callback['msg_error'] != '') {
							alert( data.callback['msg_error'] );
						} else if(data.callback['msg_error'] == '') {
							alert( "<?php echo MODULE_SHIPPING_ECONT_CITIES_UPDATE_SUCCESS_TEXT; ?>" );
						}
					}
				});
			},
			"<?php echo MODULE_SHIPPING_ECONT_CITIES_UPDATE_BUTTON_CANCEL_TEXT; ?>": function() {
				$( this ).dialog( "close" );
			}
		}
	});
	
	$("#dialog:ui-dialog").dialog("destroy");
	$("#cities-update")
	.button()
	.click(function() {
		$("#dialog-cities-update").dialog("open");
	});
	/** BUTTON CITIES UPDATE EOF **/
	
	
	/** BUTTON SENDER CONFIG BOF **/
	var vendor_sname 				= $("#vendor_sname"),
	vendor_contact_name 			= $("#vendor_contact_name"),
	vendor_contact_phone 			= $("#vendor_contact_phone"),
	vendor_deliver_from 			= $("#vendor_deliver_from"),
	vendor_enable_dc 				= $("#vendor_enable_dc"),
	vendor_payment_method 			= $("#vendor_payment_method"),
	vendor_credit_num 				= $("#vendor_credit_num"),
	vendor_cd_agreement_num			= $("#vendor_cd_agreement_num"),
	vendor_share_sum				= $("#vendor_share_sum"),
	vendor_enable_oc				= $("#vendor_enable_oc"),
	vendor_priority_hour			= $("#vendor_priority_hour"),
	vendor_pay_after_accept			= $("#vendor_pay_after_accept"),
	vendor_invoice_before_pay_CD	= $("#vendor_invoice_before_pay_CD"),
	vendor_instruction_returns		= $("#vendor_instruction_returns"),
	vendor_sms_no					= $("#vendor_sms_no"),
	vendor_country					= $("#vendor_country"),
	vendor_zip						= $("#vendor_zip"),
	vendor_city						= $("#vendor_city"),
	vendor_quarter					= $("#vendor_quarter"),
	vendor_street					= $("#vendor_street"),
	vendor_street_num				= $("#vendor_street_num"),
	vendor_street_other				= $("#vendor_street_other"),
	configFields 					= $([]).add(vendor_contact_phone),
	configTips 						= $(".validateTips");
	
	$( "#dialog:ui-dialog" ).dialog( "destroy" );
	var sender_owner = $("#sender-owner");
	$( "#sender-config" )
	.button()
	.click(function() {
		$( "#progressbar_config" ).progressbar({ value: 100 });
		$.ajax({
			dataType: 'json',
			url: "econt_callback.php",
			data: "action=get_sender_config",
			success: function(data){
				$( "#progressbar_config" ).progressbar("destroy");
				sender_owner.text(data.config[			"owner"]);
				vendor_sname.val(data.config[			"vendor_sname"]);
				vendor_contact_name.val(data.config[	"vendor_contact_name"]);
				vendor_contact_phone.val(data.config[	"vendor_contact_phone"]);
				vendor_zip.val(data.config[				"vendor_zip"]);
				vendor_city.val(data.config[			"vendor_city"]);
				vendor_quarter.val(data.config[			"vendor_quarter"]);
				vendor_street.val(data.config[			"vendor_street"]);
				vendor_street_num.val(data.config[		"vendor_street_num"]);
				vendor_street_other.text(data.config[	"vendor_street_other"]);
				
				if(data.config["vendor_deliver_from"] == 'OFFICE') {
					$("#vendor_deliver_from option[value='OFFICE']").attr('selected', 'selected');
				} else if (data.config["vendor_deliver_from"] == 'DOOR') {
					$("#vendor_deliver_from option[value='DOOR']").attr('selected', 'selected');
				}
				
				if(data.config["vendor_oc_min_amount"] < 0) {
					$("#vendor_oc_min_amount option[value='-1']").attr('selected', 'selected');
				}
				
				if(data.config["vendor_enable_dc"] == 'ON') {
					$("#vendor_enable_dc option[value='ON']").attr('selected', 'selected');
				} else if (data.config["vendor_enable_dc"] == 'OFF') {
					$("#vendor_enable_dc option[value='OFF']").attr('selected', 'selected');
				} else if (data.config["vendor_enable_dc"] == 'PLUS') {
					$("#vendor_enable_dc option[value='PLUS']").attr('selected', 'selected');
				}
				
				if(data.config["vendor_payment_method"] == 'CREDIT') {
					$("#vendor_payment_method option[value='CREDIT']").attr('selected', 'selected');
				} else if (data.config["vendor_payment_method"] == 'CASH') {
					$("#vendor_payment_method option[value='CASH']").attr('selected', 'selected');
				}
				
				vendor_credit_num.val(data.config["vendor_credit_num"]);
				vendor_cd_agreement_num.val(data.config["vendor_cd_agreement_num"]);
				vendor_share_sum.val(data.config["vendor_share_sum"]);
				
				if(data.config["vendor_enable_oc"] == '0') {
					$("#vendor_enable_oc option[value='0']").attr('selected', 'selected');
				} else if (data.config["vendor_enable_oc"] == '1') {
					$("#vendor_enable_oc option[value='1']").attr('selected', 'selected');
				} else if (data.config["vendor_enable_oc"] == '2') {
					$("#vendor_enable_oc option[value='2']").attr('selected', 'selected');
				}
				
				if(data.config["vendor_priority_hour"] == '0') {
					$("#vendor_priority_hour option[value='0']").attr('selected', 'selected');
				} else if (data.config["vendor_priority_hour"] == '1') {
					$("#vendor_priority_hour option[value='1']").attr('selected', 'selected');
				}
				
				if(data.config["vendor_pay_after_accept"] == '0') {
					$("#vendor_pay_after_accept option[value='0']").attr('selected', 'selected');
				} else if (data.config["vendor_pay_after_accept"] == '1') {
					$("#vendor_pay_after_accept option[value='1']").attr('selected', 'selected');
				} else if (data.config["vendor_pay_after_accept"] == '2') {
					$("#vendor_pay_after_accept option[value='2']").attr('selected', 'selected');
				}
				
				
				if(data.config["vendor_invoice_before_pay_CD"] == '0') {
					$("#vendor_invoice_before_pay_CD option[value='0']").attr('selected', 'selected');
				} else if (data.config["vendor_invoice_before_pay_CD"] == '1') {
					$("#vendor_invoice_before_pay_CD option[value='1']").attr('selected', 'selected');
				}
				
				if(data.config["vendor_instruction_returns"] == '0') {
					$("#vendor_instruction_returns option[value='0']").attr('selected', 'selected');
				} else if (data.config["vendor_instruction_returns"] == 'shipping_returns') {
					$("#vendor_instruction_returns option[value='shipping_returns']").attr('selected', 'selected');
				} else if (data.config["vendor_instruction_returns"] == 'returns') {
					$("#vendor_instruction_returns option[value='returns']").attr('selected', 'selected');
				}
				
				vendor_sms_no.val(data.config["vendor_sms_no"]);
			}
		});
		$( "#dialog-sender-config" ).dialog( "open" );
	});
	
	$( "#dialog-sender-config" ).dialog({
		autoOpen: false,
		resizable: false,
		width: 720,
		height: 665,
		modal: true,
		buttons: {
			"<?php echo MODULE_SHIPPING_ECONT_SENDER_CONFIG_BUTTON_UPDATE_PROFILE_TEXT; ?>": function() {
				$("#progressbar_config").progressbar({ value: 100 });
				$.ajax({
					dataType: 'json',
					url: "econt_callback.php",
					data: {
						action: 		"set_sender_profile",
						vendor_sname:	vendor_sname.val()
					},
					success: function(data){
						vendor_contact_name.val(data.profile[	"vendor_contact_name"]);
						vendor_contact_phone.val(data.profile[	"vendor_contact_phone"]);
						vendor_zip.val(data.profile[			"vendor_zip"]);
						vendor_city.val(data.profile[			"vendor_city"]);
						vendor_quarter.val(data.profile[		"vendor_quarter"]);
						vendor_street.val(data.profile[			"vendor_street"]);
						vendor_street_num.val(data.profile[		"vendor_street_num"]);
						vendor_credit_num.val(data.profile[		"vendor_credit_num"]);
						vendor_street_other.text(data.profile[	"vendor_street_other"]);
						$("#progressbar_config").progressbar("destroy");
					}
				});
			},
			"<?php echo MODULE_SHIPPING_ECONT_SENDER_CONFIG_BUTTON_UPDATE_TEXT; ?>": function() {
				$("#progressbar_config").progressbar({ value: 100 });
				$.ajax({
					dataType: 'json',
					url: "econt_callback.php",
					data: {
						action: 							"set_sender_config",
						vendor_id: 							1,
						vendor_sname:						vendor_sname.val(),
						vendor_deliver_from: 				vendor_deliver_from.val(),
						vendor_enable_dc: 					vendor_enable_dc.val(),
						vendor_payment_method: 				vendor_payment_method.val(),
						vendor_credit_num: 					vendor_credit_num.val(),
						vendor_cd_agreement_num:			vendor_cd_agreement_num.val(),
						vendor_share_sum:					vendor_share_sum.val(),
						vendor_enable_oc: 					vendor_enable_oc.val(),
						vendor_priority_hour: 				vendor_priority_hour.val(),
						vendor_pay_after_accept: 			vendor_pay_after_accept.val(),
						vendor_invoice_before_pay_CD: 		vendor_invoice_before_pay_CD.val(),
						vendor_instruction_returns: 		vendor_instruction_returns.val(),
						vendor_sms_no: 						vendor_sms_no.val(),
						vendor_country:						vendor_country.val()
					},
					success: function(data){
						$("#progressbar_config").progressbar("destroy");
					}
				});
			}
		}
	});
	/** BUTTON SENDER CONFIG EOF **/
	
	
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

#dialog-shopconfig-form { height: 235px !important; }
#dialog-cities-update { height: 75px !important; }
#dialog-sender-config {
	height: 660px !important;
	font-family: Arial, Verdana, sans-serif !important;
	font-size: 11px !important;
	font-variant: normal !important;
}
#dialog-sender-config input, textarea { border: 1px solid #CCCCCC; }

#dialog-sender-config fieldset {
	width: 1080px;
	border: 1px solid #CCCCCC;
}
#dialog-sender-config legend {
	font-size: 14px;
	color: #717C80;
}
#dialog-sender-config .sender-config-form td {
	font-size: 12px;
}
#sender-owner { padding-left: 25px; }

div#progressbar_config { width: 97%; position: absolute; top: 10px; left: 10px; z-index: 1; }
</style>