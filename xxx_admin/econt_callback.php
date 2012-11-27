<?php
/**
 * zencart mod
 * SIMEON MLADENOV
*/

 global $db;
require('includes/application_top.php');
//$lang_ses = $db->Execute("select * from languages where languages_id = '" . (int)$_SESSION['languages_id'] ."'");
         //$my_lang = $lang_ses->fields['directory'];
include_once(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/shipping/econt.php');
include_once('includes/modules/shipping/econt/functions.php');

if((isset($_REQUEST['action'])) && ($_REQUEST['action'] != "")) {
	$action = $_REQUEST['action'];
	
	switch($action) {
		
		case 'set_shop_info':
			$eol_username = zen_db_prepare_input($_REQUEST['eol_username']);
			$eol_password = zen_db_prepare_input($_REQUEST['eol_password']);
			$deliver_to = zen_db_prepare_input($_REQUEST['deliver_to']);
			$validate_full_client_address = zen_db_prepare_input($_REQUEST['validate_full_client_address']);
			$params = array('eol_username' => $eol_username, 'eol_password' => $eol_password, 'deliver_to' => $deliver_to, 'validate_full_client_address' => $validate_full_client_address);
			
			$out = '1';
			foreach($params as $key => $val) {
				if(!$db->Execute("UPDATE ".MODULE_SHIPPING_ECONT_DB_SHOP_INFO." SET param_value = '".$val."' WHERE param_name = '".$key."'")) {
					$out = '0';
				}
			}
			echo $out;
			break;
			
		case 'get_shop_info':
			$row_shop_info = $db->Execute("SELECT * FROM ".MODULE_SHIPPING_ECONT_DB_SHOP_INFO."");
			while(!$row_shop_info->EOF) {
				$out[$row_shop_info->fields['param_name']] = $row_shop_info->fields['param_value'];
				$row_shop_info->MoveNext();
			}
			echo '{"shopinfo":'.json_encode($out).'}';
			break;
		
		/** UPDATING CITIES, REGIONS, QUARTERS, STREETS BOF **/
		case 'cities_update':
			header('content-type:text/html ; charset=utf-8');
			set_time_limit(10000);
			$out = array();
			$out['msg_error'] = '';
			
			$row_shop_info = $db->Execute("SELECT * FROM ".MODULE_SHIPPING_ECONT_DB_SHOP_INFO.";");
			while(!$row_shop_info->EOF) {
				switch($row_shop_info->fields['param_name']) {
					case 'eecont_url':
						$eecont_url = $row_shop_info->fields['param_value'];
						break;
					case 'eol_password':
						$eol_password = $row_shop_info->fields['param_value'];
						break;
					case 'eol_username':
						$eol_username = $row_shop_info->fields['param_value'];
						break;
				}
				$row_shop_info->MoveNext();
			}
			
			if($eol_password && $eol_username) {
				$nMaxQueryRows = 1000;
				
				/* POPULATE DB TABLE econt_cities_table BOF */
				$sXMLResponseCities = addressInfoSendRequest('cities', $eecont_url, $eol_username, $eol_password);
				if($sXMLResponseCities === false) {
					$out['msg_error'] = MODULE_SHIPPING_ECONT_ADDRESS_UPDATE_ERROR_CITIES_TEXT;
					echo '{"callback":'.json_encode($out).'}';
					exit;
				} else {
					$db->Execute("DELETE FROM ".MODULE_SHIPPING_ECONT_DB_CITIES_TABLE."");
					$aRowsCities = array();
					#read the response
					$oResponseCities = new SimpleXMLElement($sXMLResponseCities);
					foreach ($oResponseCities->cities->e as $oShipmentCities) {
						$aFieldsCities = array(
							"'".zen_db_prepare_input($oShipmentCities->id)."'",
							"'".zen_db_prepare_input($oShipmentCities->post_code)."'",
							"'".zen_db_prepare_input($oShipmentCities->id_zone)."'",
							"'".zen_db_prepare_input($oShipmentCities->name)."'",
							"'".zen_db_prepare_input($oShipmentCities->name_en)."'",
							"'".zen_db_prepare_input($oShipmentCities->id_country)."'",
							"'".zen_db_prepare_input($oShipmentCities->id_office)."'"
						);
						$aRowsCities[] = '('.implode(',', $aFieldsCities).')';
						
						if(sizeof($aRowsCities) == $nMaxQueryRows) {
							$sQueryCities = "INSERT INTO ".MODULE_SHIPPING_ECONT_DB_CITIES_TABLE." (id, post_code, id_zone, name, name_en, id_country, id_office) VALUES ";
							$sQueryCities .= implode(", ", $aRowsCities);
							$db->Execute($sQueryCities);
							if(mysql_error()) {
								$out['msg_error'] = MODULE_SHIPPING_ECONT_ADDRESS_UPDATE_ERROR_CITIES_INSERT_TEXT;
								echo '{"callback":'.json_encode($out).'}';
								exit;
							}
							$aRowsCities = array();
						}
						//break;
					}
					if(sizeof($aRowsCities) > 0) {
						$sQueryCities = "INSERT INTO ".MODULE_SHIPPING_ECONT_DB_CITIES_TABLE." (id, post_code, id_zone, name, name_en, id_country, id_office) VALUES ";
						$sQueryCities .= implode(", ",$aRowsCities);
						$db->Execute($sQueryCities);
						if(mysql_error()) {
							$out['msg_error'] = MODULE_SHIPPING_ECONT_ADDRESS_UPDATE_ERROR_CITIES_INSERT_TEXT;
							echo '{"callback":'.json_encode($out).'}';
							exit;
						}
						$aRowsCities = array();
					}

					//update ref table for offices
					$db->Execute("DELETE FROM ".MODULE_SHIPPING_ECONT_DB_CITIES_OFFICES_TABLE."");
					foreach ($oResponseCities->cities->e as $oShipmentCities) {
						foreach ($oShipmentCities->attach_offices->children() as $oShipmentType) {
							$sShipmentType = $oShipmentType->getName();
							foreach ($oShipmentType->children() as $oDeliveryType) {
								$sDeliveryType = $oDeliveryType->getName();
								foreach ($oDeliveryType as $sOfficeCode) {
									$aFieldsCitiesOffices = array(
										"'".zen_db_prepare_input((string) $oShipmentCities->id)."'",
										"'".zen_db_prepare_input((string) $sOfficeCode)."'",
										"'".zen_db_prepare_input($sShipmentType)."'",
										"'".zen_db_prepare_input($sDeliveryType)."'"
									);
									$aRowsCitiesOffices[] = '('.implode(',',$aFieldsCitiesOffices).')';
								}
							}
						}
						
						if(sizeof($aRowsCitiesOffices) > $nMaxQueryRows) {
							$sQueryCitiesOffices = "INSERT INTO ".MODULE_SHIPPING_ECONT_DB_CITIES_OFFICES_TABLE." (id_city, office_code, shipment_type, delivery_type) VALUES ";
							$sQueryCitiesOffices .= implode(", ",$aRowsCitiesOffices);				
							
							$db->Execute($sQueryCitiesOffices);
							if(mysql_error()) {
								$out['msg_error'] = 'error';
								echo '{"callback":'.json_encode($out).'}';
								exit;
							}
							$aRowsCitiesOffices = array();
						}
					}
					if(sizeof($aRowsCitiesOffices) > 0) {
						$sQueryCitiesOffices = "INSERT INTO ".MODULE_SHIPPING_ECONT_DB_CITIES_OFFICES_TABLE." (id_city, office_code, shipment_type, delivery_type) VALUES ";
						$sQueryCitiesOffices .= implode(", ",$aRowsCitiesOffices);				
						
						$db->Execute($sQueryCitiesOffices);
						if(mysql_error()) {
							$out['msg_error'] = 'error';
							echo '{"callback":'.json_encode($out).'}';
							exit;
						}
						$aRowsCitiesOffices = array();
					}


				}
				/* POPULATE DB TABLE econt_cities_table EOF */
				
				/* POPULATE DB TABLE econt_cities_regions_table BOF */
				$sXMLResponseRegions = addressInfoSendRequest('cities_regions', $eecont_url, $eol_username, $eol_password);
				if($sXMLResponseRegions === false) {
					$out['msg_error'] = MODULE_SHIPPING_ECONT_ADDRESS_UPDATE_ERROR_REGIONS_TEXT;
					echo '{"callback":'.json_encode($out).'}';
					exit;
				} else {
					$db->Execute("DELETE FROM ".MODULE_SHIPPING_ECONT_DB_CITIES_REGIONS_TABLE."");
					$aRowsRegions = array();
					#read the response
					$oResponseRegions = new SimpleXMLElement($sXMLResponseRegions);
					foreach ($oResponseRegions->cities_regions->e as $oShipmentRegions) {
						$aFieldsRegions = array(
							"'".zen_db_prepare_input($oShipmentRegions->id)."'",
							"'".zen_db_prepare_input($oShipmentRegions->id_city)."'",
							"'".zen_db_prepare_input($oShipmentRegions->name)."'",
							"'".zen_db_prepare_input($oShipmentRegions->code)."'",
						);
						$aRowsRegions[] = '('.implode(',', $aFieldsRegions).')';
						
						if(sizeof($aRowsRegions) == $nMaxQueryRows) {
							$sQueryRegions = "INSERT INTO ".MODULE_SHIPPING_ECONT_DB_CITIES_REGIONS_TABLE." (id, id_city, name, code) VALUES ";
							$sQueryRegions .= implode(", ", $aRowsRegions);
							$db->Execute($sQueryRegions);
							if(mysql_error()) {
								$out['msg_error'] = MODULE_SHIPPING_ECONT_ADDRESS_UPDATE_ERROR_REGIONS_INSERT_TEXT;
								echo '{"callback":'.json_encode($out).'}';
								exit;
							}
							$aRowsRegions = array();
						}
					}
					if(sizeof($aRowsRegions) > 0) {
						$sQueryRegions = "INSERT INTO ".MODULE_SHIPPING_ECONT_DB_CITIES_REGIONS_TABLE." (id, id_city, name, code) VALUES ";
						$sQueryRegions .= implode(", ",$aRowsRegions);
						$db->Execute($sQueryRegions);
						if(mysql_error()) {
							$out['msg_error'] = MODULE_SHIPPING_ECONT_ADDRESS_UPDATE_ERROR_REGIONS_INSERT_TEXT;
							echo '{"callback":'.json_encode($out).'}';
							exit;
						}
						$aRowsRegions = array();
					}
				}
				/* POPULATE DB TABLE econt_cities_regions_table EOF */
				
				/* POPULATE DB TABLE econt_cities_quarters_table BOF */
				$sXMLResponseQuarters = addressInfoSendRequest('cities_quarters', $eecont_url, $eol_username, $eol_password);
				if($sXMLResponseQuarters === false) {
					$out['msg_error'] = MODULE_SHIPPING_ECONT_ADDRESS_UPDATE_ERROR_QUARTERS_TEXT;
					echo '{"callback":'.json_encode($out).'}';
					exit;
				} else {
					$db->Execute("DELETE FROM ".MODULE_SHIPPING_ECONT_DB_CITIES_QUARTERS_TABLE."");
					$aRowsQuarters = array();
					#read the response
					$oResponseQuarters = new SimpleXMLElement($sXMLResponseQuarters);
					foreach ($oResponseQuarters->cities_quarters->e as $oShipmentQuarters) {
						$aFieldsQuarters = array(
							"'".zen_db_prepare_input($oShipmentQuarters->id)."'",
							"'".zen_db_prepare_input($oShipmentQuarters->id_city)."'",
							"'".zen_db_prepare_input($oShipmentQuarters->name)."'",
							"'".zen_db_prepare_input($oShipmentQuarters->name_en)."'",
						);
						$aRowsQuarters[] = '('.implode(',', $aFieldsQuarters).')';
						
						if(sizeof($aRowsQuarters) == $nMaxQueryRows) {
							$sQueryQuarters = "INSERT INTO ".MODULE_SHIPPING_ECONT_DB_CITIES_QUARTERS_TABLE." (id, id_city, quarter_name, quarter_name_en) VALUES ";
							$sQueryQuarters .= implode(", ", $aRowsQuarters);
							$db->Execute($sQueryQuarters);
							if(mysql_error()) {
								$out['msg_error'] = MODULE_SHIPPING_ECONT_ADDRESS_UPDATE_ERROR_QUARTERS_INSERT_TEXT;
								echo '{"callback":'.json_encode($out).'}';
								exit;
							}
							$aRowsQuarters = array();
						}
						//break;
					}
					if(sizeof($aRowsQuarters) > 0) {
						$sQueryQuarters = "INSERT INTO ".MODULE_SHIPPING_ECONT_DB_CITIES_QUARTERS_TABLE." (id, id_city, quarter_name, quarter_name_en) VALUES ";
						$sQueryQuarters .= implode(", ",$aRowsQuarters);
						$db->Execute($sQueryQuarters);
						if(mysql_error()) {
							$out['msg_error'] = MODULE_SHIPPING_ECONT_ADDRESS_UPDATE_ERROR_QUARTERS_INSERT_TEXT;
							echo '{"callback":'.json_encode($out).'}';
							exit;
						}
						$aRowsQuarters = array();
					}
				}
				/* POPULATE DB TABLE econt_cities_quarters_table EOF */
				
				/* POPULATE DB TABLE econt_cities_street_table BOF */
				$sXMLResponseStreet = addressInfoSendRequest('cities_streets', $eecont_url, $eol_username, $eol_password);
				if($sXMLResponseStreet === false) {
					$out['msg_error'] = MODULE_SHIPPING_ECONT_ADDRESS_UPDATE_ERROR_STREETS_TEXT;
					echo '{"callback":'.json_encode($out).'}';
					exit;
				} else {
					$db->Execute("DELETE FROM ".MODULE_SHIPPING_ECONT_DB_CITIES_STREETS_TABLE."");
					$aRowsStreet = array();
					#read the response
					$oResponseStreet = new SimpleXMLElement($sXMLResponseStreet);
					foreach ($oResponseStreet->cities_street->e as $oShipmentStreet) {
						$aFieldsStreet = array(
							"'".zen_db_prepare_input($oShipmentStreet->id)."'",
							"'".zen_db_prepare_input($oShipmentStreet->id_city)."'",
							"'".zen_db_prepare_input($oShipmentStreet->name)."'",
							"'".zen_db_prepare_input($oShipmentStreet->name_en)."'",
						);
						$aRowsStreet[] = '('.implode(',', $aFieldsStreet).')';
						
						if(sizeof($aRowsStreet) == $nMaxQueryRows) {
							$sQueryStreet = "INSERT INTO ".MODULE_SHIPPING_ECONT_DB_CITIES_STREETS_TABLE." (id, id_city, street_name, street_name_en) VALUES ";
							$sQueryStreet .= implode(", ", $aRowsStreet);
							$db->Execute($sQueryStreet);
							if(mysql_error()) {
								$out['msg_error'] = MODULE_SHIPPING_ECONT_ADDRESS_UPDATE_ERROR_STREETS_INSERT_TEXT;
								echo '{"callback":'.json_encode($out).'}';
								exit;
							}
							$aRowsStreet = array();
						}
						//break;
					}
					if(sizeof($aRowsStreet) > 0) {
						$sQueryStreet = "INSERT INTO ".MODULE_SHIPPING_ECONT_DB_CITIES_STREETS_TABLE." (id, id_city, street_name, street_name_en) VALUES ";
						$sQueryStreet .= implode(", ",$aRowsStreet);
						$db->Execute($sQueryStreet);
						if(mysql_error()) {
							$out['msg_error'] = MODULE_SHIPPING_ECONT_ADDRESS_UPDATE_ERROR_STREETS_INSERT_TEXT;
							echo '{"callback":'.json_encode($out).'}';
							exit;
						}
						$aRowsStreet = array();
					}
				}
				/* POPULATE DB TABLE econt_cities_street_table EOF */
				
				/* POPULATE DB TABLE econt_offices_table BOF */
				$sXMLResponseOffices = addressInfoSendRequest('offices', $eecont_url, $eol_username, $eol_password);
				if($sXMLResponseOffices === false) {
					$out['msg_error'] = MODULE_SHIPPING_ECONT_ADDRESS_UPDATE_ERROR_OFFICES_TEXT;
					echo '{"callback":'.json_encode($out).'}';
					exit;
				} else {
					$db->Execute("DELETE FROM ".MODULE_SHIPPING_ECONT_DB_OFFICES_TABLE."");
					$aRowsOffices = array();
					#read the response
					$oResponseOffices = new SimpleXMLElement($sXMLResponseOffices);
					foreach ($oResponseOffices->offices->e as $oShipmentOffices) {
						$aFieldsOffices = array(
							"'".zen_db_prepare_input($oShipmentOffices->id)."'",
							"'".zen_db_prepare_input($oShipmentOffices->id_city)."'",
							"'".zen_db_prepare_input($oShipmentOffices->office_code)."'",
							"'".zen_db_prepare_input($oShipmentOffices->name)."'",
							"'".zen_db_prepare_input($oShipmentOffices->name_en)."'",
							"'".zen_db_prepare_input($oShipmentOffices->phone)."'",
							"'".zen_db_prepare_input($oShipmentOffices->address)."'",
							"'".zen_db_prepare_input($oShipmentOffices->address_en)."'",
							"'".zen_db_prepare_input($oShipmentOffices->work_begin)."'",
							"'".zen_db_prepare_input($oShipmentOffices->work_begin_saturday)."'",
							"'".zen_db_prepare_input($oShipmentOffices->work_end)."'",
							"'".zen_db_prepare_input($oShipmentOffices->work_end_saturday)."'",
							"'".zen_db_prepare_input($oShipmentOffices->time_priority)."'",
						);
						$aRowsOffices[] = '('.implode(',', $aFieldsOffices).')';
						
						if(sizeof($aRowsOffices) == $nMaxQueryRows) {
							$sQueryOffices = "INSERT INTO ".MODULE_SHIPPING_ECONT_DB_OFFICES_TABLE." (id, id_city, code, name, name_en, phone, address, address_en, work_begin, work_begin_saturday, work_end, work_end_saturday, time_priority) VALUES ";
							$sQueryOffices .= implode(", ", $aRowsOffices);
							$db->Execute($sQueryOffices);
							if(mysql_error()) {
								$out['msg_error'] = MODULE_SHIPPING_ECONT_ADDRESS_UPDATE_ERROR_OFFICES_INSERT_TEXT;
								echo '{"callback":'.json_encode($out).'}';
								exit;
							}
							$aRowsOffices = array();
						}
						//break;
					}
					if(sizeof($aRowsOffices) > 0) {
						$sQueryOffices = "INSERT INTO ".MODULE_SHIPPING_ECONT_DB_OFFICES_TABLE." (id, id_city, code, name, name_en, phone, address, address_en, work_begin, work_begin_saturday, work_end, work_end_saturday, time_priority) VALUES ";
						$sQueryOffices .= implode(", ",$aRowsOffices);
						$db->Execute($sQueryOffices);
						if(mysql_error()) {
							$out['msg_error'] = MODULE_SHIPPING_ECONT_ADDRESS_UPDATE_ERROR_OFFICES_INSERT_TEXT;
							echo '{"callback":'.json_encode($out).'}';
							exit;
						}
						$aRowsOffices = array();
					}
				}
				/* POPULATE DB TABLE econt_offices_table EOF */
				
			} else {
				$out['msg_error'] = MODULE_SHIPPING_ECONT_ADDRESS_UPDATE_ERROR_USER_PASSWORD_TEXT;
				echo '{"callback":'.json_encode($out).'}';
				exit;
			}
			echo '{"callback":'.json_encode($out).'}';
			break;
		/** UPDATING CITIES, REGIONS, QUARTERS, STREETS EOF **/
		
		/** SENDERS CONFIG GET LIST BOF **/
		case 'get_sender_config':
			$out = array();
			$out["owner"] = STORE_OWNER . ' ('.STORE_NAME.')';
			
			$row_vendor_info = $db->Execute("SELECT * FROM ".MODULE_SHIPPING_ECONT_DB_VENDOR_INFO." WHERE vendor_id = 1");
			//$row_vendor_info = zen_db_fetch_array($vendor_info);

			$out["vendor_sname"] 			= $row_vendor_info->fields["vendor_sname"];
			$out["vendor_contact_name"] 	= $row_vendor_info->fields["vendor_contact_name"];
			$out["vendor_contact_phone"] 	= $row_vendor_info->fields["vendor_contact_phone"];
			$out["vendor_city"] 			= $row_vendor_info->fields["vendor_city"];
			$out["vendor_zip"] 				= $row_vendor_info->fields["vendor_zip"];
			$out["vendor_quarter"] 			= $row_vendor_info->fields["vendor_quarter"];
			$out["vendor_street"] 			= $row_vendor_info->fields["vendor_street"];
			$out["vendor_street_num"] 		= $row_vendor_info->fields["vendor_street_num"];
			$out["vendor_street_bl"] 		= $row_vendor_info->fields["vendor_street_bl"];
			$out["vendor_street_vh"] 		= $row_vendor_info->fields["vendor_street_vh"];
			$out["vendor_street_et"] 		= $row_vendor_info->fields["vendor_street_et"];
			$out["vendor_street_ap"] 		= $row_vendor_info->fields["vendor_street_ap"];
			$out["vendor_street_other"] 	= $row_vendor_info->fields["vendor_street_other"];
			
			$out["vendor_deliver_from"] 	= $row_vendor_info->fields["vendor_deliver_from"] == 'DOOR' ? 'DOOR' : 'OFFICE';
			$out["vendor_oc_min_amount "] 	= $row_vendor_info->fields["vendor_oc_min_amount"] < 0 ? -1 : $row_vendor_info->fields["vendor_oc_min_amount"];
			switch($row_vendor_info->fields["vendor_enable_dc"]) {
				case 'OFF':
					$vendorenabledc = 'OFF';
					break;
				case 'ON':
					$vendorenabledc = 'ON';
					break;
				case 'PLUS':
					$vendorenabledc = 'PLUS';
					break;
			}
			$out["vendor_enable_dc"] 				= $vendorenabledc;
			$out["vendor_payment_method"] 			= $row_vendor_info->fields["vendor_payment_method"] == 'CREDIT' ? 'CREDIT' : 'CASH';
			$out["vendor_credit_num"] 				= $row_vendor_info->fields["vendor_credit_num"];
			$out["vendor_cd_agreement_num"] 		= $row_vendor_info->fields["vendor_cd_agreement_num"];
			$out["vendor_share_sum"] 				= $row_vendor_info->fields["vendor_share_sum"];
			$out["vendor_enable_oc"] 				= $row_vendor_info->fields["vendor_enable_oc"];
			$out["vendor_priority_hour"] 			= $row_vendor_info->fields["vendor_priority_hour"];
			$out["vendor_pay_after_accept"] 		= $row_vendor_info->fields["vendor_pay_after_accept"];
			$out["vendor_invoice_before_pay_CD"]	= $row_vendor_info->fields["vendor_invoice_before_pay_CD"];
			$out["vendor_instruction_returns"]		= $row_vendor_info->fields["vendor_instruction_returns"];
			$out["vendor_sms_no"] 					= $row_vendor_info->fields["vendor_sms_no"];
			
			echo '{"config":'.json_encode($out).'}';
			break;
		/** SENDERS CONFIG GET LIST EOF **/
		
		/** SENDERS CONFIG SET LIST BOF **/
		case 'set_sender_config':
			$out = array('0');

			$vendor_id 						= zen_db_prepare_input($_REQUEST['vendor_id']);
			$vendor_sname 					= zen_db_prepare_input($_REQUEST['vendor_sname']);
			$vendor_deliver_from 			= zen_db_prepare_input($_REQUEST['vendor_deliver_from']);
			$vendor_enable_dc 				= zen_db_prepare_input($_REQUEST['vendor_enable_dc']);
			$vendor_payment_method 			= zen_db_prepare_input($_REQUEST['vendor_payment_method']);
			$vendor_credit_num 				= zen_db_prepare_input($_REQUEST['vendor_credit_num']);
			$vendor_cd_agreement_num 		= zen_db_prepare_input($_REQUEST['vendor_cd_agreement_num']);
			$vendor_share_sum 				= zen_db_prepare_input($_REQUEST['vendor_share_sum']);
			$vendor_enable_oc 				= zen_db_prepare_input($_REQUEST['vendor_enable_oc']);
			$vendor_priority_hour 			= zen_db_prepare_input($_REQUEST['vendor_priority_hour']);
			$vendor_pay_after_accept 		= zen_db_prepare_input($_REQUEST['vendor_pay_after_accept']);
			$vendor_invoice_before_pay_CD	= zen_db_prepare_input($_REQUEST['vendor_invoice_before_pay_CD']);
			$vendor_instruction_returns		= zen_db_prepare_input($_REQUEST['vendor_instruction_returns']);
			$vendor_sms_no 					= zen_db_prepare_input($_REQUEST['vendor_sms_no']);
			$vendor_country 				= zen_db_prepare_input($_REQUEST['vendor_country']);
			
			$vendorsharesum = $vendor_share_sum==''?'100':$vendor_share_sum;
			if($vendor_id) {
				if($db->Execute("UPDATE ".MODULE_SHIPPING_ECONT_DB_VENDOR_INFO." SET 
						vendor_sname 					= '".$vendor_sname."',
						vendor_deliver_from 			= '".$vendor_deliver_from."',
						vendor_enable_dc				= '".$vendor_enable_dc."',
						vendor_payment_method			= '".$vendor_payment_method."',
						vendor_credit_num				= '".$vendor_credit_num."',
						vendor_cd_agreement_num			= '".$vendor_cd_agreement_num."',
						vendor_share_sum				= '".$vendorsharesum."',
						vendor_enable_oc				= '".$vendor_enable_oc."',
						vendor_priority_hour			= '".$vendor_priority_hour."',
						vendor_pay_after_accept 		= '".$vendor_pay_after_accept."',
						vendor_invoice_before_pay_CD	= '".$vendor_invoice_before_pay_CD."',
						vendor_instruction_returns		= '".$vendor_instruction_returns."',
						vendor_sms_no					= '".$vendor_sms_no."',
						vendor_country					= '".$vendor_country."'
					WHERE vendor_id = ".$vendor_id."")) {
					$out = array('1');
				}
			}
			
			echo '{"config":'.json_encode($out).'}';
			break;
		/** SENDERS CONFIG GET LIST EOF **/
		
		
		/** SENDERS CONFIG UPDATE PROFILE BOF **/
		case 'set_sender_profile':
			header('content-type:text/html ; charset=utf-8');
			set_time_limit(2500);
			$out = array();
			$out['msg_error'] = '';
			$vendor_sname = zen_db_prepare_input($_REQUEST['vendor_sname']);
			
			$row_shop_info = $db->Execute("SELECT * FROM ".MODULE_SHIPPING_ECONT_DB_SHOP_INFO.";");
			while(!$row_shop_info->EOF) {
				switch($row_shop_info->fields['param_name']) {
					case 'eecont_url':
						$eecont_url = $row_shop_info->fields['param_value'];
						break;
					case 'eol_password':
						$eol_password = $row_shop_info->fields['param_value'];
						break;
					case 'eol_username':
						$eol_username = $row_shop_info->fields['param_value'];
						break;
				}
				$row_shop_info->MoveNext();
			}
			
			if($eol_password && $eol_username) {
				$nMaxQueryRows = 100;
				
				/* POPULATE DB TABLE econt_cities_table BOF */
				$sXMLResponseProfiles = profileRequest('profile', $eecont_url, $eol_username, $eol_password);
				if($sXMLResponseProfiles === false) {
					$out['msg_error'] = MODULE_SHIPPING_ECONT_ADDRESS_UPDATE_ERROR_CITIES_TEXT;
					echo '{"callback":'.json_encode($out).'}';
					exit;
				} else {
					$aRowsProfiles = array();
					#read the response
					$oResponseProfiles = new SimpleXMLElement($sXMLResponseProfiles);
					
					$vendor_credit_num 		= zen_db_prepare_input($oResponseProfiles->client_info->key_word);
					$vendor_contact_name 	= zen_db_prepare_input($oResponseProfiles->client_info->mol);
					$vendor_contact_phone 	= zen_db_prepare_input($oResponseProfiles->client_info->business_phone);
					
					foreach ($oResponseProfiles->addresses->e as $oShipmentAddresses) {
						$vendor_zip 			= zen_db_prepare_input($oShipmentAddresses->city_post_code);
						$vendor_city 			= zen_db_prepare_input($oShipmentAddresses->city);
						$vendor_quarter 		= zen_db_prepare_input($oShipmentAddresses->quarter);
						$vendor_street 			= zen_db_prepare_input($oShipmentAddresses->street);
						$vendor_street_num 		= zen_db_prepare_input($oShipmentAddresses->street_num);
						$vendor_street_other 	= zen_db_prepare_input($oShipmentAddresses->other);
						break;
					}
					
					$sql_update = $db->Execute("UPDATE ".MODULE_SHIPPING_ECONT_DB_VENDOR_INFO." 
						SET vendor_sname			= '".$vendor_sname."',
							vendor_contact_name 	= '".$vendor_contact_name."',
							vendor_contact_phone 	= '".$vendor_contact_phone."',
							vendor_zip				= '".$vendor_zip."',
							vendor_city				= '".$vendor_city."',
							vendor_quarter 			= '".$vendor_quarter."',
							vendor_street 			= '".$vendor_street."',
							vendor_street_num 		= '".$vendor_street_num."',
							vendor_street_other		= '".$vendor_street_other."',
							vendor_credit_num		= '".$vendor_credit_num."'
						WHERE vendor_id = 1");
					
					$row_vendor_info = $db->Execute("SELECT * FROM ".MODULE_SHIPPING_ECONT_DB_VENDOR_INFO." WHERE vendor_id = 1");
					//$row_vendor_info = zen_db_fetch_array($vendor_info);
					
					$out["vendor_contact_name"] 	= $row_vendor_info->fields["vendor_contact_name"];
					$out["vendor_contact_phone"] 	= $row_vendor_info->fields["vendor_contact_phone"];
					$out["vendor_zip"] 				= $row_vendor_info->fields["vendor_zip"];
					$out["vendor_city"] 			= $row_vendor_info->fields["vendor_city"];
					$out["vendor_quarter"] 			= $row_vendor_info->fields["vendor_quarter"];
					$out["vendor_street"] 			= $row_vendor_info->fields["vendor_street"];
					$out["vendor_street_num"] 		= $row_vendor_info->fields["vendor_street_num"];
					$out["vendor_street_other"] 	= $row_vendor_info->fields["vendor_street_other"];
					$out["vendor_credit_num"] 		= $row_vendor_info->fields["vendor_credit_num"];
				}
			}

			echo '{"profile":'.json_encode($out).'}';
			break;
		/** SENDERS CONFIG UPDATE PROFILE EOF **/
		
		
		/** AUTOCOMPLETE GET CITIES BOF **/
		case 'get_suggest_city':
			$out = array();

			$requested = zen_db_prepare_input($_REQUEST['city_startswith']);
			$requested_postcode = "";
			if((isset($_REQUEST['postcode_startswith'])) && ($_REQUEST['postcode_startswith'] != "")) {
				$requested_postcode = ' WHERE post_code = "'.$_REQUEST['postcode_startswith'].'" ';
			} else {
				$requested_postcode = " WHERE (name_en LIKE '".$requested."%' OR name LIKE '".$requested."%') ";
			}

			$row_cities = $db->Execute("SELECT id, post_code, name, name_en FROM ".MODULE_SHIPPING_ECONT_DB_CITIES_TABLE." ".$requested_postcode." ORDER BY name");
			while(!$row_cities->EOF) {
				$out[] = array('cityid' => $row_cities->fields['id'], 'zip' => $row_cities->fields['post_code'], 'name' => $row_cities->fields['name']);
				$row_cities->MoveNext();
			}
			echo '{"cities":'.json_encode($out).'}';
			
			break;
		/** AUTOCOMPLETE GET CITIES EOF **/
		
		/** AUTOCOMPLETE GET POSTCODE BOF **/
		case 'get_suggest_postcode':
			
			$out = array();
			$requested_postcode = $_REQUEST['postcode_startswith'];

			$requested_city = "";
			if((isset($_REQUEST['city_startswith'])) && ($_REQUEST['city_startswith'] != "")) {
				$requested_city = ' AND name = "'.$_REQUEST['city_startswith'].'" ';
			}
			$row_postcode = $db->Execute("SELECT id, post_code, name, name_en FROM ".MODULE_SHIPPING_ECONT_DB_CITIES_TABLE." WHERE (post_code LIKE '".$requested_postcode."%') ".$requested_city." ORDER BY post_code ASC");
			while(!$row_postcode->EOF) {
				$out[] = array('cityid' => $row_postcode->fields['id'], 'code' => $row_postcode->fields['post_code']);
				$row_postcode->MoveNext();
			}
			echo '{"postcode":'.json_encode($out).'}';
			
			break;
		/** AUTOCOMPLETE GET POSTCODE EOF **/
		
		/** AUTOCOMPLETE GET STREET BOF **/
		case 'get_suggest_street' :
			
			$out = array();
			
			$requested = $_REQUEST['street_startswith'];
			$cityid = $_REQUEST['cityid'];
			$street_exact = $_REQUEST['street_exact'];
			if($street_exact == '1') {
				$requested_val = " '".$requested."' ";
			} else {
				$requested_val = " '%".$requested."%' ";
			}
			$row_street = $db->Execute("SELECT id, street_name FROM ".MODULE_SHIPPING_ECONT_DB_CITIES_STREETS_TABLE." WHERE (street_name LIKE ".$requested_val." OR street_name_en LIKE ".$requested_val.") AND id_city = '".$cityid."' ORDER BY street_name ASC");
			while(!$row_street->EOF) {
				$out[] = array('streetid' => $row_street->fields['id'], 'street_name' => $row_street->fields['street_name']);
				$row_street->MoveNext();
			}
			echo '{"street":'.json_encode($out).'}';
			
			break;
		/** AUTOCOMPLETE GET STREET EOF **/
		
		/** AUTOCOMPLETE GET SUBURB BOF **/
		case 'get_suggest_suburb' :
			
			$out = array();
			
			$requested = $_REQUEST['suburb_startswith'];
			$cityid = $_REQUEST['cityid'];
			$row_suburb = $db->Execute("SELECT id, quarter_name FROM ".MODULE_SHIPPING_ECONT_DB_CITIES_QUARTERS_TABLE." WHERE (quarter_name LIKE '%".$requested."%' OR quarter_name_en LIKE '%".$requested."%') AND id_city = '".$cityid."' ORDER BY quarter_name ASC");
			if($row_suburb->RecordCount()) {
				while(!$row_suburb->EOF) {
					$out[] = array('suburbid' => $row_suburb->fields['id'], 'suburb_name' => $row_suburb->fields['quarter_name']);
					$row_suburb->MoveNext();
				}
				echo '{"suburb":'.json_encode($out).'}';
			} else {
				$out = array('suburb_name' => '0');
				echo '{"suburb":'.json_encode($out).'}';
			}
			
			break;
		/** AUTOCOMPLETE GET SUBURB EOF **/
case 'request-courier-single':
			$out = array();
			
			$econtorderid = $_REQUEST['econtorderid'];
			
			$blank_no = $db->Execute("SELECT * FROM ".MODULE_SHIPPING_ECONT_DB_LOADINGS." WHERE order_id = ".(int)$econtorderid."");
			if($blank_no->RecordCount()) {
				
				$out = array('pdf' => '<a href="'.$blank_no->fields['blank_no'].'" target="_blank">'.MODULE_SHIPPING_ECONT_REQUESTCOURIER_LINK_PDF_LOADING_TEXT.'</a>');

			} else {
				
				$sender = $db->Execute("SELECT * FROM ".MODULE_SHIPPING_ECONT_DB_VENDOR_INFO." WHERE vendor_id = 1");
				$aLoadingInfo['sender'] = array(
					'city'			=> $sender->fields['vendor_city'],
					'post_code'		=> $sender->fields['vendor_zip'],
					'name'			=> $sender->fields['vendor_sname'],
					'name_person'	=> $sender->fields['vendor_contact_name'],
					'quarter'		=> $sender->fields['vendor_quarter'],
					'street'		=> $sender->fields['vendor_street'],
					'street_num'	=> $sender->fields['vendor_street_num'],
					'street_bl'		=> $sender->fields['vendor_street_bl'],
					'street_vh'		=> $sender->fields['vendor_street_vh'],
					'street_et'		=> $sender->fields['vendor_street_et'],
					'street_ap'		=> $sender->fields['vendor_street_ap'],
					'street_other'	=> $sender->fields['vendor_street_other'],
					'phone_num'		=> $sender->fields['vendor_contact_phone'],
					'share_prc'     => $sender->fields['vendor_share_sum'] 
				);
				
				$receiver = $db->Execute("SELECT * FROM ".TABLE_ORDERS." WHERE orders_id = ".(int)$econtorderid."");
				$receiver_street_array = explode("|", $receiver->fields['delivery_street_address']);
				$receiver_street_name = $receiver_street_array[0];
				$receiver_street_number = $receiver_street_array[1];
				
				
				$receiver_suburb_array = explode("|", $receiver->fields['delivery_suburb']);
				$receiver_suburb = $receiver_suburb_array[0];
				$receiver_suburb_else = $receiver_suburb_array[1];
				
				
				
				$office_code = '';
				$econt_orders_query = $db->Execute("SELECT * FROM ".MODULE_SHIPPING_ECONT_DB_ORDERS_INFO." WHERE order_id = ".(int)$econtorderid."");
				
				if($sender->fields['vendor_deliver_from'] != 'DOOR') {
					#customer delivery to
					if( $econt_orders_query->fields['office'] ) {
						$tariff_code = '2';
						$tariff_sub_code = 'OFFICE_OFFICE';
						$office_code = $econt_orders_query->fields['office'];
					} else {
						$tariff_code ='3';
						$tariff_sub_code ='OFFICE_DOOR';
					}
				} else {
					#customer delivery to
					if( $econt_orders_query->fields['office'] ) {
						$tariff_code ='3';
						$tariff_sub_code ='DOOR_OFFICE';
						$office_code = $econt_orders_query->fields['office'];
					} else {
						
						if($econt_orders_query->fields['econt_exp'] == ''){
						$tariff_code ='4';
						$tariff_sub_code ='DOOR_DOOR';}
						else{$tariff_code ='1';
						$tariff_sub_code ='DOOR_DOOR';}
						
					}
				}
				              
								
				$aLoadingInfo['receiver'] = array(
					'city'			=> $receiver->fields['delivery_city'],
					'post_code'		=> $receiver->fields['delivery_postcode'],
					'name'			=> $receiver->fields['delivery_name'],
					'name_person'	=> $receiver->fields['delivery_name'],
					'quarter'		=> $receiver_suburb,
					'street'		=> $receiver_street_name,
					'street_num'	=> $receiver_street_number,
					'street_bl'		=> '',
					'street_vh'		=> '',
					'street_et'		=> '',
					'street_ap'		=> '',
					'street_other'	=> $receiver_suburb_else,
					'phone_num'		=> $receiver->fields['customers_telephone'],
					'office_code'	=> $office_code,
					'sms_no'		=> $sender->fields['vendor_sms_no']
				);
				
				
				$order_count = 0;
				$order_weight = 0;
				$order_total = 0;
				$order_products = $db->Execute("SELECT * FROM ".TABLE_ORDERS_PRODUCTS." WHERE orders_id = ".(int)$econtorderid."");
				while(!$order_products->EOF) {
					$order_products_description .= $order_products->fields['products_model'].' x '.$order_products->fields['products_quantity'].'; ';
					$product_weight = array();
					$product_weight = $db->Execute("SELECT products_weight FROM ".TABLE_PRODUCTS." WHERE products_id = ".(int)$order_products->fields['products_id']."");
					
					$order_weight += ((float)$product_weight->fields['products_weight'] * (int)$order_products->fields['products_quantity']);
					$order_total += ((float)$order_products->fields['final_price'] * (int)$order_products->fields['products_quantity']);
					$order_count +=  (int)$order_products->fields['products_quantity'];
					$order_products->MoveNext();
				}
				$aLoadingInfo['shipment'] = array(
					'shipment_type'		=> 'PACK',
					'description'		=> $order_products_description,
					'pack_count'		=> $order_count==0 ? 1 : $order_count,
					'weight'			=> $order_weight!=0 ? $order_weight : 1
				);
				
				
				$row_order_total = $db->Execute("SELECT * FROM ".TABLE_ORDERS_TOTAL." WHERE orders_id = ".(int)$econtorderid."");
				
				$ot_shipping = 0;
				$ot_tax = 0;
				$order_total_share_sum = 0;
				while(!$row_order_total->EOF) {
					switch($row_order_total->fields['class']) {
						case 'ot_shipping':
							$ot_shipping = (float)$row_order_total->fields['value'];
							break;
						case 'ot_tax':
							$ot_tax = (float)$row_order_total->fields['value'];
							break;
						case 'ot_subtotal':
							$ot_subtotal = (float)$row_order_total->fields['value'];
							break;
						case 'ot_total':
							$ot_total = (float)$row_order_total->fields['value'];
							break;	
					}
					$row_order_total->MoveNext();
				}
				$order_total_share_sum = $ot_shipping + $ot_tax;
				
				$row_order_totales = $db->Execute("SELECT `value` FROM ".TABLE_ORDERS_TOTAL." WHERE orders_id = ".(int)$econtorderid." AND `class` ='ot_total'");
				
				$aLoadingInfo['payment'] = array();
				#payment side
				//$aLoadingInfo['payment']['side'] 	= 'RECEIVER';

				$aLoadingInfo['payment']['side'] = 'SENDER';
				$aLoadingInfo['payment']['method'] = $sender->fields['vendor_payment_method'];
				if($sender->fields['vendor_payment_method'] != 'CASH'){
					$aLoadingInfo['payment']['key_word'] = $sender->fields['vendor_credit_num'];
				}
				//$aLoadingInfo['payment']['receiver_share_sum'] =(float)$econt_orders_query->fields['vendor_discount'];
				//$aLoadingInfo['payment']['receiver_share_sum'] =(float)$order_total_share_sum;
				$aLoadingInfo['payment']['share_percent'] = $sender->fields['vendor_share_sum'];
				
				#services
				$aLoadingInfo['services'] = array();
				#DC
				if($sender->fields['vendor_enable_dc'] == 'PLUS') {
					$aLoadingInfo['services']['dc'] = 'OFF';
					$aLoadingInfo['services']['dc_cp'] = 'ON';
				} else {
					$aLoadingInfo['services']['dc'] = $sender->fields['vendor_enable_dc'];
				}
				
				#OC
				$service_oc = $db->Execute("SELECT oc FROM ".MODULE_SHIPPING_ECONT_DB_ORDERS_INFO." WHERE order_id = ".(int)$econtorderid."");
				//$service_oc = zen_db_fetch_array($service_oc_query);
				$aLoadingInfo['services']['oc'] = $service_oc->fields['oc'] == 1 ? $ot_subtotal : 0;
				
				# Priority
				$service_priority = $db->Execute("SELECT * FROM ".MODULE_SHIPPING_ECONT_DB_ORDERS_INFO." WHERE order_id = ".(int)$econtorderid."");
				//$service_priority = zen_db_fetch_array($service_priority_query);
				if($service_priority->fields['p_hour']) {
					$aLoadingInfo['services']['p'] = $service_priority->fields['p_hour'];
					$aLoadingInfo['services']['p_type'] = $service_priority->fields['p_type'];
				}
				
				#EXPRESS COURIER
				$service_express = $db->Execute("SELECT * FROM ".MODULE_SHIPPING_ECONT_DB_ORDERS_INFO." WHERE order_id = ".(int)$econtorderid."");
				if($tariff_code =='1'){
					//print_r($service_express->field['econt_exp']);
					//$aLoadingInfo['services']['e'] = 'ON';
					//$tariff_code ='1';
					
					switch ($service_express->fields['econt_exp']) {
                             case 'e1':
                               $aLoadingInfo['services']['e1'] = 'ON';
                               break;
                             case 'e2':
                               $aLoadingInfo['services']['e2'] = 'ON';
                               break;
                             case 'e3':
                               $aLoadingInfo['services']['e3'] = 'ON';
                               break;
                         }
					
					
					//if($service_express->field['econt_exp'] == 'e1'){$aLoadingInfo['services']['e1'] = 'ON';}
					//if($service_express->field['econt_exp'] == 'e2'){$aLoadingInfo['services']['e2'] = 'ON';}
					//if($service_express->field['econt_exp'] == 'e3'){$aLoadingInfo['services']['e3'] = 'ON';}
					
					}
				//$aLoadingInfo['services']['e1'] = 'ON';
				
				
				#CD
				$ms = 100.00;
				if($service_priority->fields['econt_cd'] == '1') {
					$aLoadingInfo['services']['cd'] = (float)$ot_subtotal;//$order_total;//$ot_subtotal;
					$aLoadingInfo['services']['cd_agreement_num'] = $sender->fields['vendor_cd_agreement_num'];
				}
				
				//print_r($aLoadingInfo);
				$aLoadings[] = $aLoadingInfo;
				
				$aLoadingsToSend = array();
					
				$sVendorDeliverFrom = $sender->fields['vendor_deliver_from'];
				$aLoadingInfo['shipment']['tariff_code'] = $tariff_code;
				$aLoadingInfo['shipment']['tariff_sub_code'] = $tariff_sub_code;
				switch($sender->fields['vendor_pay_after_accept']) {
					case '0':
						break;
					case '1':
						$aLoadingInfo['shipment']['pay_after_accept'] = $sender->fields['vendor_pay_after_accept'];
						$aLoadingInfo['shipment']['instruction_returns'] = $sender->fields['vendor_instruction_returns'];
						break;
					case '2':
						$aLoadingInfo['shipment']['pay_after_accept'] = '0';
						$aLoadingInfo['shipment']['pay_after_test'] = '1';
						$aLoadingInfo['shipment']['instruction_returns'] = $sender->fields['vendor_instruction_returns'];
						break;
				}
				
				$aLoadingInfo['shipment']['invoice_before_pay_CD'] = $sender->fields['vendor_invoice_before_pay_CD'];
				//print_r($aLoadingInfo);
				$aLoadingsToSend[] = $aLoadingInfo;
				//print_r($aLoadingsToSend);
				$row_shop_info = $db->Execute("SELECT * FROM ".MODULE_SHIPPING_ECONT_DB_SHOP_INFO.";");
				while(!$row_shop_info->EOF) {
					switch($row_shop_info->fields['param_name']) {
						case 'eecont_url':
							$eecont_url = $row_shop_info->fields['param_value'];
							break;
						case 'eol_password':
							$eol_password = $row_shop_info->fields['param_value'];
							break;
						case 'eol_username':
							$eol_username = $row_shop_info->fields['param_value'];
							break;
					}
					$row_shop_info->MoveNext();
				}
				
				
				
				$sXMLResponseShipping = calculateShipping($eecont_url, $eol_username, $eol_password, $aLoadingsToSend, '0', '0');
				$oResponse = new SimpleXMLElement($sXMLResponseShipping);
               //print_r($oResponse);
				if($oResponse->result->e->error != "") {
					$cityid = $db->Execute("SELECT id, post_code, name, name_en FROM ".MODULE_SHIPPING_ECONT_DB_CITIES_TABLE."  WHERE name = '".$receiver->fields['delivery_city']."'");
					if($cityid->RecordCount()) {
						$streetid = $db->Execute("SELECT id, street_name FROM ".MODULE_SHIPPING_ECONT_DB_CITIES_STREETS_TABLE." WHERE street_name = '".$receiver_street_name."' AND id_city = '".$cityid->fields['id']."'");
						if($streetid->RecordCount()){ $streetid = $db->Execute("SELECT id, street_name FROM ".MODULE_SHIPPING_ECONT_DB_CITIES_STREETS_TABLE." WHERE street_name = '".$receiver_street_name."' AND id_city = '".$cityid->fields['id']."'");}
						else {$streetid->fields['id'] = '';}
					} else {
						$cityid->fields['id'] = '';
						$streetid->fields['id'] = '';
					}
					
					$out = array('address' => 'error', 
						'error' 		=> (string)$oResponse->result->e->error,
						'name'			=> $receiver->fields['delivery_name'],
						'phone_num'		=> $receiver->fields['customers_telephone'],
						'city'			=> $receiver->fields['delivery_city'],
						'postcode'		=> $receiver->fields['delivery_postcode'],
						'quarter'		=> $receiver_suburb,
						'street'		=> $receiver_street_name,
						'street_num'	=> $receiver_street_number,
						'street_other'	=> $receiver_suburb_else,
						'orderid'		=> $econtorderid,
						'cityid'		=> $cityid->fields['id'],
						'streetid'		=> $streetid->fields['id']
					);

					echo '{"courier":'.json_encode($out).'}';
					exit;
				}				
				
				
				
				foreach($oResponse->result->e as $result) {
					if($result->error == "") {
						$loading_num = $result->loading_num;
						$delivery_date = $result->delivery_date;
						//$main_cd = (float)$result->loading_price->CD*((int)$aLoadingInfo['sender']['share_prc']/100);
						$main_cd = $order_total;
					}
				}
				if((isset($loading_num)) && ($loading_num != "")) {
					$blank_yes = 'http://www.econt.com/e-econt/api/api_pdf_shipment.php?user='.$eol_username.'&print_media=template&nums[]='.$loading_num;
					$blank_no = 'http://www.econt.com/e-econt/api/api_pdf_shipment.php?user='.$eol_username.'&print_media=double&nums[]='.$loading_num;
					if(isset($office_code)){$main_store = $office_code;}
					else{$main_store = MODULE_SHIPPING_ECONT_ICON_LOADING_INFO_DOOR;}
					
					$loading_query = $db->Execute("INSERT INTO ".MODULE_SHIPPING_ECONT_DB_LOADINGS." 
							(
								order_id, 
								vendor_id,
								loading_num,
								receiver_person,
								storage,
								cd_get_sum,
								updated_time,
								delivery_date,
								blank_yes,
								blank_no
							)
						VALUES(
							".(int)$receiver->fields['orders_id'].",
							".(int)$sender->fields['vendor_id'].",
							'".$loading_num."',
							'".$receiver->fields['delivery_name']."',
							'".$main_store."',
							".$main_cd.",
							NOW(),
							'".$delivery_date."',
							'".$blank_yes."',
							'".$blank_no."'
						)");
					$out = array('address' => '', 'pdf' => '<a href="'.$blank_no.'" target="_blank">'.MODULE_SHIPPING_ECONT_REQUESTCOURIER_LINK_PDF_LOADING_TEXT.'</a>');
				}
				
			}
			
			
			
			
			
			
			
			
			
			
			
			
			
			echo '{"courier":'.json_encode($out).'}';
		break;
		/** REQUEST COURIER SINGLE ORDER BOF **/
		
		/** REQUEST COURIER SINGLE ORDER EOF **/
		
		
		/** SET RECEIVER ADDRESS BOF **/
		case 'set_receiver_address':
			$out = array();
			$econtorderid = zen_db_prepare_input($_REQUEST['econtorderid']);
			$receiver_name = zen_db_prepare_input($_REQUEST['receiver_name']);
			$receiver_contact_phone = zen_db_prepare_input($_REQUEST['receiver_contact_phone']);
			$receiver_city = zen_db_prepare_input($_REQUEST['receiver_city']);
			$receiver_zip = zen_db_prepare_input($_REQUEST['receiver_zip']);
			$receiver_quarter = zen_db_prepare_input($_REQUEST['receiver_quarter']);
			$receiver_street = zen_db_prepare_input($_REQUEST['receiver_street']);
			$receiver_street_num = zen_db_prepare_input($_REQUEST['receiver_street_num']); 
			$receiver_quarter_other = zen_db_prepare_input($_REQUEST['receiver_quarter_other']);
			
			if($receiver_street != "") {
				$street = ", delivery_street_address= '".$receiver_street."|".$receiver_street_num."'";
				$street_address_book = ", entry_street_address= '".$receiver_street."|".$receiver_street_num."'";
			}
			
			if($receiver_quarter != "") {
				$suburb = ", delivery_suburb = '".$receiver_quarter."|".$receiver_quarter_other."' ";
				$suburb_address_book = ", entry_suburb = '".$receiver_quarter."|".$receiver_quarter_other."' ";
			}
			
			
			if($econtorderid) {
				$wrong_address = $db->Execute("SELECT * FROM ".TABLE_ORDERS." WHERE orders_id = ".(int)$econtorderid."");
				$default_address_book = $db->Execute("SELECT * FROM ".TABLE_CUSTOMERS." WHERE customers_id = ".(int)$wrong_address->fields['customers_id']."");

				$address_book = $db->Execute("SELECT * FROM ".TABLE_ADDRESS_BOOK." WHERE customers_id = ".(int)$wrong_address->fields['customers_id']." AND address_book_id = ".(int)$default_address_book->fields['customers_default_address_id']."");
				if($address_book->RecordCount()) {
					//$address_book = zen_db_fetch_array($address_checkpoint_query);
					
					$db->Execute("UPDATE ".TABLE_ADDRESS_BOOK." SET entry_city = '".$receiver_city."', entry_postcode = '".$receiver_zip."' ".$street_address_book." ".$suburb_address_book." WHERE address_book_id = ".(int)$address_book->fields['address_book_id']."");
				}
				
				$db->Execute("UPDATE ".TABLE_ORDERS." SET delivery_city = '".$receiver_city."', delivery_postcode = '".$receiver_zip."' ".$street." ".$suburb."  WHERE orders_id = ".(int)$econtorderid."");
				$out = array("status" => "1");
				echo '{"setreceiveraddress":'.json_encode($out).'}';
			}
			
			break;
		/** SET RECEIVER ADDRESS EOF **/
		
		
		/** DELETE LOADING BOF **/
		case 'delete_loading':
			$out = array('status' => '1');
			$loadingid = zen_db_prepare_input($_REQUEST['loadingid']);
			$db->Execute("DELETE FROM ".MODULE_SHIPPING_ECONT_DB_LOADINGS." WHERE loading_id = ".(int)$loadingid.";");
			echo '{"loadingdelete":'.json_encode($out).'}';
			break;
		/** DELETE LOADING EOF **/
		
		default:
			break;
	}
}

?>