<?php
/**
 * zencart mod
 * SIMEON MLADENOV
*/
function updateLoadingRequest($sRequestType, $eecont_url, $eol_username, $eol_password, $params) {
	$oRequest = new DOMDocument('1.0','UTF-8');
	$oElRequest = $oRequest->createElement('request');
		#client
		$oElClient = $oRequest->createElement('client');
			$oElUsername=$oRequest->createElement('username');
			$oElUsername->appendChild($oRequest->createTextNode($eol_username));
			$oElClient->appendChild($oElUsername);
			$oElPassword=$oRequest->createElement('password');
			$oElPassword->appendChild($oRequest->createTextNode($eol_password));
			$oElClient->appendChild($oElPassword);
		$oElRequest->appendChild($oElClient);
		$oElRequest->appendChild($oRequest->createElement('request_type',$sRequestType));
		
		$oElShipments = $oRequest->createElement('shipments');
		foreach ($params as $nLoadingNum => $aLoading) {
			$oElNum = $oRequest->createElement('num',$nLoadingNum);
			$oElShipments->appendChild($oElNum);
		}
		$oElRequest->appendChild($oElShipments);
	
	$oRequest->appendChild($oElRequest);
	
	$sXMLRequest = $oRequest->saveXML();
	
	$sTmpFileName=tempnam(sys_get_temp_dir(),'address_request_');
	file_put_contents($sTmpFileName, $sXMLRequest);
	
	$upload_url = $eecont_url."/xml_service_tool.php";
	$aPostData = array( 'file' => '@'.$sTmpFileName);
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $upload_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS,$aPostData);
	$sXMLResponse = curl_exec($ch);
	unlink($sTmpFileName);
	return $sXMLResponse;
}

function addressInfoSendRequest($sRequestType, $eecont_url, $eol_username, $eol_password) {
	$oRequest = new DOMDocument('1.0','UTF-8');
	$oElRequest = $oRequest->createElement('request');
		#client
		$oElClient = $oRequest->createElement('client');
			$oElUsername=$oRequest->createElement('username');
			$oElUsername->appendChild($oRequest->createTextNode($eol_username));
			$oElClient->appendChild($oElUsername);
			$oElPassword=$oRequest->createElement('password');
			$oElPassword->appendChild($oRequest->createTextNode($eol_password));
			$oElClient->appendChild($oElPassword);
		$oElRequest->appendChild($oElClient);
		$oElRequest->appendChild($oRequest->createElement('request_type',$sRequestType));
	$oRequest->appendChild($oElRequest);
	
	$sXMLRequest = $oRequest->saveXML();
	
	$sTmpFileName=tempnam(sys_get_temp_dir(),'address_request_');
	file_put_contents($sTmpFileName, $sXMLRequest);
	
	$upload_url = $eecont_url."/xml_service_tool.php";
	$aPostData = array( 'file' => '@'.$sTmpFileName);
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $upload_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS,$aPostData);
	$sXMLResponse = curl_exec($ch);
	unlink($sTmpFileName);
	return $sXMLResponse;
}


function profileRequest($sRequestType, $eecont_url, $eol_username, $eol_password) {
	$oRequest = new DOMDocument('1.0','UTF-8');
	$oElRequest = $oRequest->createElement('request');
		#client
		$oElClient = $oRequest->createElement('client');
			$oElUsername=$oRequest->createElement('username');
			$oElUsername->appendChild($oRequest->createTextNode($eol_username));
			$oElClient->appendChild($oElUsername);
			$oElPassword=$oRequest->createElement('password');
			$oElPassword->appendChild($oRequest->createTextNode($eol_password));
			$oElClient->appendChild($oElPassword);
		$oElRequest->appendChild($oElClient);
		$oElRequest->appendChild($oRequest->createElement('request_type',$sRequestType));
	$oRequest->appendChild($oElRequest);
	
	$sXMLRequest = $oRequest->saveXML();
	
	$sTmpFileName=tempnam(sys_get_temp_dir(),'address_request_');
	file_put_contents($sTmpFileName, $sXMLRequest);
	
	$upload_url = $eecont_url."/xml_service_tool.php";
	$aPostData = array( 'file' => '@'.$sTmpFileName);
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $upload_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS,$aPostData);
	$sXMLResponse = curl_exec($ch);
	unlink($sTmpFileName);
	return $sXMLResponse;
}

function calculateShipping($eecont_url, $eol_username, $eol_password, $aLoadingsToSend, $validate='1', $only_calculate='1') {
	
	#build request
	$oDoc = new DOMDocument('1.0','UTF-8');
	$oDoc->encoding = "UTF-8";
	$oDoc->formatOutput = true;
	
	#parcels
	$oElParcels = $oDoc->createElement('parcels');
	
		#system
		$oElSystem = $oDoc->createElement('system');
			$oElSystem->appendChild($oDoc->createElement('validate'		  ,$validate));
			$oElSystem->appendChild($oDoc->createElement('response_type'  ,'XML'));
			$oElSystem->appendChild($oDoc->createElement('only_calculate' ,$only_calculate));
		$oElParcels->appendChild($oElSystem);
		#/system
		
		#client
		$oElClient = $oDoc->createElement('client');
			$oElUsername=$oDoc->createElement('username');
			$oElUsername->appendChild($oDoc->createTextNode($eol_username));
			$oElClient->appendChild($oElUsername);
			
			$oElPassword=$oDoc->createElement('password');
			$oElPassword->appendChild($oDoc->createTextNode($eol_password));
			$oElClient->appendChild($oElPassword);

		$oElParcels->appendChild($oElClient);
		#/client
		
		#loadings
		$oElLoadings = buildLoadingsXML($aLoadingsToSend,$oDoc);
		$oElParcels->appendChild($oElLoadings);
		#/loadings
		
	$oDoc->appendChild($oElParcels);
	#/parcels
	
	$sXMLRequest=$oDoc->saveXML();
	
	#send the request
	$sTmpFileName=tempnam(sys_get_temp_dir(),'price_request_');
	file_put_contents($sTmpFileName,$sXMLRequest);
	
	$upload_url = $eecont_url."/xml_parcel_import.php";
	
	$aPostData = array( 'file' => '@'.$sTmpFileName);
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $upload_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS,$aPostData);
	$sXMLResponse = curl_exec($ch);
	
	unlink($sTmpFileName);
	return $sXMLResponse;
}

function buildLoadingsXML($aLoadings,$oDoc) {
	$nMaxDescrLength = 70;
	$oElLoadings = $oDoc->createElement('loadings');
	foreach ($aLoadings as $aLoadingInfo) {
		$oElRow = $oDoc->createElement('row');
		#sender
		$oElSender = $oDoc->createElement('sender');
		foreach ($aLoadingInfo['sender'] as $sField => $sValue) {
			$oEl = $oDoc->createElement($sField);
			$oEl->appendChild($oDoc->createTextNode($sValue));
			$oElSender->appendChild($oEl);
		}
		
		$oElRow->appendChild($oElSender);
		
		#receiver
		$oElReceiver = $oDoc->createElement('receiver');
		foreach ($aLoadingInfo['receiver'] as $sField => $sValue) {
			$oEl = $oDoc->createElement($sField);
			$oEl->appendChild($oDoc->createTextNode($sValue));
			$oElReceiver->appendChild($oEl);
		}
		
		$oElRow->appendChild($oElReceiver);
		#shipment
		$oElShipment = $oDoc->createElement('shipment');
		foreach ($aLoadingInfo['shipment'] as $sField => $sValue) {
			#truncate package description
			if($sField == 'description') {
				if(strlen(utf8_decode($sValue)) > $nMaxDescrLength) {
					$sValue = utf8_substr($sValue, 0,$nMaxDescrLength - 3).'...';
				}
			}
			$oEl = $oDoc->createElement($sField);
			$oEl->appendChild($oDoc->createTextNode($sValue));
			$oElShipment->appendChild($oEl);
		}
		$oElRow->appendChild($oElShipment);
		
		#payment
		$oElPayment = $oDoc->createElement('payment');
		foreach ($aLoadingInfo['payment'] as $sField => $sValue) {
			$oEl = $oDoc->createElement($sField);
			$oEl->appendChild($oDoc->createTextNode($sValue));
			$oElPayment->appendChild($oEl);
		}
		$oElRow->appendChild($oElPayment);
		
		#services
		$oElServices = $oDoc->createElement('services');
		if(!empty($aLoadingInfo['services']['dc'])) {
			$oElDC = $oDoc->createElement('dc');
			$oElDC->appendChild($oDoc->createTextNode($aLoadingInfo['services']['dc']));
			$oElServices->appendChild($oElDC);
			if($aLoadingInfo['services']['dc_cp'] == 'ON') {
				$oElDCCP = $oDoc->createElement('dc_cp');
				$oElDCCP->appendChild($oDoc->createTextNode($aLoadingInfo['services']['dc_cp']));
				$oElServices->appendChild($oElDCCP);
			}
		} else {
			$oElDC = $oDoc->createElement('dc');
			$oElDC->appendChild($oDoc->createTextNode('OFF'));
			$oElServices->appendChild($oElDC);
		}
		if(!empty($aLoadingInfo['services']['oc'])) {
			$oElOC = $oDoc->createElement('oc');
			$oElOC->appendChild($oDoc->createTextNode($aLoadingInfo['services']['oc']));
			$oElServices->appendChild($oElOC);
		}
		if(!empty($aLoadingInfo['services']['cd'])) {
			$oElCD = $oDoc->createElement('cd');
			$oElCD->appendChild($oDoc->createTextNode($aLoadingInfo['services']['cd']));
			$oElCD->setAttribute('type','GET');
			$oElServices->appendChild($oElCD);
		}
		
		if(!empty($aLoadingInfo['services']['p'])) {
			$oElP = $oDoc->createElement('p');
			$oElP->appendChild($oDoc->createTextNode($aLoadingInfo['services']['p']));
			switch($aLoadingInfo['services']['p_type']) {
				case 'IN':
					$oElP->setAttribute('type','IN');
					break;
				case 'BEFORE':
					$oElP->setAttribute('type','BEFORE');
					break;
				case 'AFTER':
					$oElP->setAttribute('type','AFTER');
					break;
			}
			$oElServices->appendChild($oElP);
		}
		
		if(!empty($aLoadingInfo['services']['cd_agreement_num'])) {
			$oElCDNum = $oDoc->createElement('cd_agreement_num');
			$oElCDNum->appendChild($oDoc->createTextNode($aLoadingInfo['services']['cd_agreement_num']));
			$oElServices->appendChild($oElCDNum);
		}
		$oElRow->appendChild($oElServices);
		$oElLoadings->appendChild($oElRow);
	}
	return $oElLoadings;
}

function utf8_substr( $str, $start ) {
	preg_match_all( "/./su", $str, $ar );
	if( func_num_args() >= 3 ) {
		$end = func_get_arg( 2 );
		return join( "", array_slice( $ar[0], $start, $end ) );
	} else {
		return join( "", array_slice( $ar[0], $start ) );
	}
}

?>
<?php
/**
 * zencart mod
*/
//include_once('../' . DIR_WS_MODULES .'shipping/econt/admin/orders_econt_apps_top.php');
//include_once('shipping/econt/functions.php');
 
class econt {
    var $code, $title, $description, $icon, $enabled;

    // class constructor
    function econt() {
        global $order, $db;
        
		
  
        $this->code = 'econt';
        $this->title = MODULE_SHIPPING_ECONT_TEXT_TITLE;
        $this->description = MODULE_SHIPPING_ECONT_TEXT_DESCRIPTION;
        $this->sort_order = MODULE_SHIPPING_ECONT_SORT_ORDER;
        $this->icon = '';
        $this->tax_class = MODULE_SHIPPING_ECONT_TAX_CLASS;
        $this->enabled = ((MODULE_SHIPPING_ECONT_STATUS == 'True') ? true : false);

        if (($this->enabled == true) && ((int)MODULE_SHIPPING_ECONT_ZONE > 0)) {
            $check_flag = false;
            $check = $db->Execute("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES .
                " where geo_zone_id = '" . MODULE_SHIPPING_ECONT_ZONE .
                "' and zone_country_id = '" . $order->delivery['country']['id'] .
                "' order by zone_id");
            while (!$check->EOF) {
                if ($check->fields['zone_id'] < 1) {
                    $check_flag = true;
                    break;
                } elseif ($check->fields['zone_id'] == $order->delivery['zone_id']) {
                    $check_flag = true;
                    break;
                }
				$check->MoveNext();
            }

            if ($check_flag == false) {
                $this->enabled = false;
            }
        }
        
        $this->types = array('OFFICE_OFFICE' => MODULE_SHIPPING_ECONT_CHECKOUTSHIPPING_METHOD_NAME_OFFICE_OFFICE_TEXT,
                           'OFFICE_DOOR' => MODULE_SHIPPING_ECONT_CHECKOUTSHIPPING_METHOD_NAME_OFFICE_DOOR_TEXT,
                           'DOOR_OFFICE' => MODULE_SHIPPING_ECONT_CHECKOUTSHIPPING_METHOD_NAME_DOOR_OFFICE_TEXT,
                           'DOOR_DOOR' => MODULE_SHIPPING_ECONT_CHECKOUTSHIPPING_METHOD_NAME_DOOR_DOOR_TEXT);
    }

    // class methods
function quote($method = '') {
      global $order, $db;
      
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
				case 'deliver_to':
					$deliver_to = $row_shop_info->fields['param_value'];
					break;
			}
			$row_shop_info->MoveNext();
		}
	  
	  
        $vendor_info = $db->Execute("SELECT * FROM ".MODULE_SHIPPING_ECONT_DB_VENDOR_INFO." WHERE vendor_id = 1");
        $aLoadingInfo['sender'] = array(
			'city'			=> $vendor_info->fields['vendor_city'],
			'post_code'		=> $vendor_info->fields['vendor_zip'],
			'name'			=> $vendor_info->fields['vendor_sname'],
			'name_person'	=> $vendor_info->fields['vendor_contact_name'],
			'quarter'		=> $vendor_info->fields['vendor_quarter'],
			'street'		=> $vendor_info->fields['vendor_street'],
			'street_num'	=> $vendor_info->fields['vendor_street_num'],
			'street_bl'		=> $vendor_info->fields['vendor_street_bl'],
			'street_vh'		=> $vendor_info->fields['vendor_street_vh'],
			'street_et'		=> $vendor_info->fields['vendor_street_et'],
			'street_ap'		=> $vendor_info->fields['vendor_street_ap'],
			'street_other'	=> $vendor_info->fields['vendor_street_other'],
			'phone_num'		=> $vendor_info->fields['vendor_contact_phone']
		);
		
	  
		$receiver_customer_row = $db->Execute("SELECT * FROM ".TABLE_ADDRESS_BOOK." WHERE address_book_id = '".$_SESSION['sendto']."'");
		$receiver_stree_array = explode("|", $receiver_customer_row->fields['entry_street_address']);
		$receiver_stree_name = $receiver_stree_array[0];
		$receiver_stree_number = $receiver_stree_array[1];
		
	 
	  if($receiver_customer_row->fields['entry_suburb']<>''){
	    $receiver_suburb_array = explode("|", $receiver_customer_row->fields['entry_suburb']);
		$receiver_suburb = $receiver_suburb_array[0];
		$receiver_suburb_else = $receiver_suburb_array[1];
	   }
	   
	   $receiver_entry_city_check = $db->Execute("SELECT * FROM ".MODULE_SHIPPING_ECONT_DB_CITIES_TABLE." WHERE name = '".$receiver_customer_row->fields['entry_city']."'");
	   if(!$receiver_entry_city_check->RecordCount()) {
			$receiver_entry_post_check = $db->Execute("SELECT * FROM ".MODULE_SHIPPING_ECONT_DB_CITIES_TABLE." WHERE post_code = '".$receiver_customer_row->fields['entry_postcode']."'");
			if($receiver_entry_post_check->RecordCount()) {
				$receiver_entry_post = $receiver_entry_post_check;
				$receiver_customer_row->fields['entry_city'] = $receiver_entry_post->fields['name'];
			} else {
				if(substr($receiver_customer_row->fields['entry_postcode'], 0, 1) == '1') {
					$receiver_entry_post_1000 = $db->Execute("SELECT * FROM ".MODULE_SHIPPING_ECONT_DB_CITIES_TABLE." WHERE post_code = '1000'");
					$receiver_customer_row->fields['entry_city'] = $receiver_entry_post_1000->fields['name'];
					$receiver_customer_row->fields['entry_postcode'] = $receiver_entry_post_1000->fields['post_code'];
				} else {
					$receiver_customer_0_entry_postcode = substr($receiver_customer_row->fields['entry_postcode'], 0, 3).'0';
					$receiver_customer_00_entry_postcode = substr($receiver_customer_row->fields['entry_postcode'], 0, 2).'00';
					$receiver_customer_000_entry_postcode = substr($receiver_customer_row->fields['entry_postcode'], 0, 1).'000';
					
					$receiver_entry_post_0_check = $db->Execute("SELECT * FROM ".MODULE_SHIPPING_ECONT_DB_CITIES_TABLE." WHERE post_code = '".$receiver_customer_0_entry_postcode."'");
					if($receiver_entry_post_0_check->RecordCount()) {
						$receiver_entry_post = $receiver_entry_post_0_check;
						$receiver_customer_row->fields['entry_city'] = $receiver_entry_post->fields['name'];
						$receiver_customer_row->fields['entry_postcode'] = $receiver_entry_post->fields['post_code'];
					} else {
						$receiver_entry_post_00_check = $db->Execute("SELECT * FROM ".MODULE_SHIPPING_ECONT_DB_CITIES_TABLE." WHERE post_code = '".$receiver_customer_00_entry_postcode."'");
						if($receiver_entry_post_00_check->RecordCount()) {
							$receiver_entry_post = $receiver_entry_post_00_check;
							$receiver_customer_row->fields['entry_city'] = $receiver_entry_post->fields['name'];
							$receiver_customer_row->fields['entry_postcode'] = $receiver_entry_post->fields['post_code'];
						} else {
							$receiver_entry_post_000_check = $db->Execute("SELECT * FROM ".MODULE_SHIPPING_ECONT_DB_CITIES_TABLE." WHERE post_code = '".$receiver_customer_000_entry_postcode."'");
							if($receiver_entry_post_000_check->RecordCount()) {
								$receiver_entry_post = $receiver_entry_post_000_check;
								$receiver_customer_row->fields['entry_city'] = $receiver_entry_post->fields['name'];
								$receiver_customer_row->fields['entry_postcode'] = $receiver_entry_post->fields['post_code'];
							}
						}
					}
				}
			}
		}
	   
	   
	   
	   $aLoadingInfo['receiver'] = array(
			'city'			=> $receiver_customer_row->fields['entry_city'],
			'post_code'		=> $receiver_customer_row->fields['entry_postcode'],
			'name'			=> $order->customer['firstname'].' '.$order->customer['lastname'],
			'name_person'	=> $order->customer['firstname'].' '.$order->customer['lastname'],
			'quarter'		=> $receiver_suburb,
			'street'		=> $receiver_stree_name,
			'street_num'	=> $receiver_stree_number,
			'street_bl'		=> '',
			'street_vh'		=> '',
			'street_et'		=> '',
			'street_ap'		=> '',
			'street_other'	=> $receiver_suburb_else,
			'phone_num'		=> $order->customer['telephone']
		);
	   
	   
	    $pack_count = 0;
        $pack_description = "";
        $pack_weight = 0;
        $final_price = 0;
        foreach($order->products as $cart_product) {
        	$pack_count += (int)$cart_product['qty'];
        	$pack_description .= $cart_product['name'].'('.$cart_product['model'].') ';
        	$pack_weight += (float)$cart_product['weight'] * (int)$cart_product['qty'];
        	$final_price += (float)$cart_product['final_price'] * (int)$cart_product['qty'];
        }
        //$pack_weight = ($pack_weight == 0) ? 1 : $pack_weight;
        
		$aLoadingInfo['shipment'] = array(
			'shipment_type'		=> 'PACK',
			'description'		=> $pack_description,
			'pack_count'		=> $pack_count,
			'weight'			=> $pack_weight
		);
	   
	   
	   //echo $aLoadingInfo['shipment']['description'];
	   
	   		$aLoadingInfo['payment'] = array();
		#payment side
		          $aLoadingInfo['payment']['side'] 	= 'SENDER';
				  $aLoadingInfo['payment']['share_percent'] =(int)$vendor_info->fields['vendor_share_sum'];
			      $aLoadingInfo['payment']['method'] = $vendor_info->fields['vendor_payment_method'];
				  $aLoadingInfo['payment']['key_word'] = $vendor_info->fields['vendor_credit_num'];
			
		//$aLoadingInfo['payment']['key_word'] = $vendor_info->fields['vendor_cd_agreement_num'];
		//$aLoadingInfo['payment']['receiver_share_sum'] = $vendor_info->fields['vendor_share_sum'];;
	
		#services
		$aLoadingInfo['services'] = array();
		#DC 
		if($vendor_info->fields['vendor_enable_dc'] == 'PLUS') {
			$aLoadingInfo['services']['dc'] = 'OFF';
			$aLoadingInfo['services']['dc_cp'] = 'ON';
		} else {
			$aLoadingInfo['services']['dc'] = $vendor_info->fields['vendor_enable_dc'];
		}
		
		#OC
		$aLoadingInfo['services']['p'] = '10:00';
		$aLoadingInfo['services']['p_type'] = 'IN';
		

		$aLoadingInfo['services']['oc'] = $final_price;
		
		#CD
		$aLoadingInfo['services']['cd'] = $final_price != 0 ? $final_price : 0;
		
		$aLoadingInfo['services']['cd_agreement_num'] = $vendor_info->fields['vendor_cd_agreement_num'];
		
		$aLoadingInfo['services']['cd_agreement_num'] = $vendor_info->fields['vendor_cd_agreement_num'];
		
		if($aLoadingInfo['sender']['city'] == $aLoadingInfo['receiver']['city']){
				$aLoadingInfo['services']['e1'] = 'ON';}
				//$aLoadingInfo['services']['e1'] = 'ON';
				//$aLoadingInfo['services']['e2'] = 'ON';	$_SESSION['proba']='ON';				
				//}
		
		$aLoadings['1'] = $aLoadingInfo;
		//print_r($aLoadingInfo);
		$aLoadingsToSend = array();
		
		for($iToDoor = 0; $iToDoor <=1; $iToDoor++) {
			if($deliver_to == 'office' && $iToDoor == 1) break;
			if($deliver_to == 'door' && $iToDoor == 0) continue;
			foreach ($aLoadings as $nVendorId => $aLoadingInfo) {
				$sVendorDeliverFrom = $vendor_info->fields['vendor_deliver_from'];
				
				if($sVendorDeliverFrom != 'DOOR') {
					#customer delivery to
					if( !$iToDoor ) {
						$aLoadingInfo['shipment']['tariff_code']='2';
						$aLoadingInfo['shipment']['tariff_sub_code']='OFFICE_OFFICE';
					} else {
						$aLoadingInfo['shipment']['tariff_code']='3';
						$aLoadingInfo['shipment']['tariff_sub_code']='OFFICE_DOOR';
					}
					
				} else {
					#customer delivery to
					if( !$iToDoor ) {
						$aLoadingInfo['shipment']['tariff_code']='3';
						$aLoadingInfo['shipment']['tariff_sub_code']='DOOR_OFFICE';
					} else {
						
						if($aLoadingInfo['receiver']['city'] == $aLoadingInfo['sender']['city']){
								$aLoadingInfo['shipment']['tariff_code']='1';
						        $aLoadingInfo['shipment']['tariff_sub_code']='DOOR_DOOR';
						}
						else{
						$aLoadingInfo['shipment']['tariff_code']='4';
						$aLoadingInfo['shipment']['tariff_sub_code']='DOOR_DOOR';}
					}
					
				}
				$aLoadingsToSend[] = $aLoadingInfo;
			}
		}
		
		
		
		$sXMLResponseShipping = calculateShipping($eecont_url, $eol_username, $eol_password, $aLoadingsToSend);
		$oResponse = new SimpleXMLElement($sXMLResponseShipping);
        //print_r($oResponse);
		$methods = array();
		
		$office_select = '<select name="office_select">'."\n";
		//$modArray = array();
		$receiver_city_id = $db->Execute("SELECT id FROM ".MODULE_SHIPPING_ECONT_DB_CITIES_TABLE." WHERE name = '".$receiver_customer_row->fields['entry_city']."'");
		if($receiver_city_id->fields['id']) {
			$office_option = $db->Execute("SELECT o.code, o.name AS name FROM ".MODULE_SHIPPING_ECONT_DB_OFFICES_TABLE." o JOIN ".MODULE_SHIPPING_ECONT_DB_CITIES_OFFICES_TABLE." co ON o.code = co.office_code WHERE 1 AND shipment_type = 'courier_shipments' AND delivery_type = 'to_office' AND co.id_city = ".$receiver_city_id->fields['id']."");
			while(!$office_option->EOF) {
				
				 //$modArray[] = array('value' => $office_option->fields['code'], 'text' => $office_option->fields['name']);
				$office_select .= '<option value="'.$office_option->fields['code'].'">'.$office_option->fields['name'].'</option>'."\n";
				$office_option->MoveNext();
	}
				} else {
			$office_select .= '<option></option>';
		}
		$office_select .= '</select>'."\n";
		
		

		
		
		switch($vendor_info->fields['vendor_enable_oc']) {
			case '0':
				$shipping_oc_select = '';$step=0;
				break;
			case '1':
				$shipping_oc_select = '<br />&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="vendor_enable_oc" /><label>'.MODULE_SHIPPING_ECONT_CHECKOUTSHIPPING_METHOD_OC_TEXT.'</label>'.'';$step=1;
				break;	
			case '2':
				$shipping_oc_select ='<br />&nbsp;&nbsp;&nbsp;&nbsp;<input type="hidden" name="vendor_enable_oc" value="on" /><label>'.MODULE_SHIPPING_ECONT_CHECKOUTSHIPPING_METHOD_OC_TEXT_2.'</label>'.'';$step=2;
				break;
			
		}
		$_SESSION['check_oc']=$step;
	
		$priority_hour = '';
		if($vendor_info->fields['vendor_priority_hour'] == '1') {
			$priority_hour = '<br />&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="vendor_priority_hour" />'.MODULE_SHIPPING_ECONT_CHECKOUTSHIPPING_PRIORITY_HOUR_TEXT.' (+'.MODULE_SHIPPING_ECONT_CHECKOUTSHIPPING_PRIORITY_HOUR_TEXT.')&nbsp;';
			$priority_hour .= '<select name="econt_priority_type"><option value="BEFORE">'.MODULE_SHIPPING_ECONT_CHECKOUTSHIPPING_PRIORITY_BEFORE_TEXT.'</option>'."\n".'<option value="AFTER">'.MODULE_SHIPPING_ECONT_CHECKOUTSHIPPING_PRIORITY_AFTER_TEXT.'</option>'."\n".'<option value="IN">'.MODULE_SHIPPING_ECONT_CHECKOUTSHIPPING_PRIORITY_IN_TEXT.'</option></select>';
			 $priority_hour .= '<input type="text" name="econt_priority_text" value="10:00" maxlength="5" size="5" onclick="if(this.value == \'00:00\') this.value==\'\'" />';
		}
		
		if($aLoadingInfo['sender']['city'] == $aLoadingInfo['receiver']['city']){

			$express_curier = '<br />'.'&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="customer_express" value="1"/><strong>Експресен градски куриер</strong>';	
			
			$express_curier .= '<br />'.'&nbsp;&nbsp;&nbsp;&nbsp;<select name="ee"><option value="e1">'.'до 60 мин(+7.20 лв)'.'</option>'."\n".'<option value="e2">'.'до 90 мин(+6.00 лв)'.'</option>'."\n".'<option value="e3">'.'до 120 мин(+4.80 лв)'.'</option></select>';
			
			//$express_curier .= '<br /><input type="checkbox" name="express_e" /><input type="hidden" name="e1" value="on" />'.'до 60 мин(+7.20 лв)';
			//$express_curier .= '<br /><input type="checkbox" name="express_e1" />'.'до 90 мин(+6.00 лв)';
			//$express_curier .= '<br /><input type="checkbox" name="express_e2" />'.'до 120 мин(+4.80 лв)';	
		}
		
		if($vendor_info->fields['vendor_pay_after_accept'] == '0') {$_SESSION['pay_after_accept'] ='0';}
		if($vendor_info->fields['vendor_pay_after_accept'] == '1') {$_SESSION['pay_after_accept'] ='1';}
		if($vendor_info->fields['vendor_pay_after_accept'] == '2') {$_SESSION['pay_after_accept'] ='2';}
		
		if($vendor_info->fields['vendor_invoice_before_pay_CD'] == '0') {$_SESSION['invoice_before_pay_CD'] ='0';}
		else {$_SESSION['invoice_before_pay_CD'] ='1';}
		
		if($vendor_info->fields['vendor_instruction_returns'] == '0') {$_SESSION['instruction_returns'] ='0';}
		if($vendor_info->fields['vendor_instruction_returns'] == 'shipping_returns') {$_SESSION['instruction_returns'] ='1';}
		if($vendor_info->fields['vendor_instruction_returns'] == 'returns') {$_SESSION['instruction_returns'] ='2';}
	
		if($sVendorDeliverFrom != 'DOOR') {
			$methods_name = array(MODULE_SHIPPING_ECONT_CHECKOUTSHIPPING_METHOD_NAME_OFFICE_OFFICE_TEXT.' '.$office_select,MODULE_SHIPPING_ECONT_CHECKOUTSHIPPING_METHOD_NAME_DOOR_DOOR_TEXT);
			$methods_name_oc = array($shipping_oc_select, $shipping_oc_select);
			$methods_name_priority = array( "", $priority_hour);
			$methods_pay_after_accept = array($pay_after_accept, $pay_after_accept);
			$methods_invoice_before_pay_CD = array($invoice_before_pay_CD, $invoice_before_pay_CD);
			$methods_instruction_returns = array($instruction_returns, $instruction_returns);
			$methods_id = array('OFFICE_OFFICE', 'OFFICE_DOOR');
		} else { 
			$methods_name = array(MODULE_SHIPPING_ECONT_CHECKOUTSHIPPING_METHOD_NAME_DOOR_OFFICE_TEXT.' '.$office_select, MODULE_SHIPPING_ECONT_CHECKOUTSHIPPING_METHOD_NAME_DOOR_DOOR_TEXT);
			$methods_name_oc = array($shipping_oc_select, $shipping_oc_select);
			$methods_name_priority = array("", $priority_hour);
			$methods_pay_after_accept = array($pay_after_accept, $pay_after_accept);
			$methods_invoice_before_pay_CD = array($invoice_before_pay_CD, $invoice_before_pay_CD);
			$methods_instruction_returns = array($instruction_returns, $instruction_returns);
			$methods_express_curier = array("", $express_curier);
			$methods_id = array('DOOR_OFFICE', 'DOOR_DOOR');
		}
		
		$this->quotes = array(
			'id' => $this->code,
			'module' => MODULE_SHIPPING_ECONT_TEXT_TITLE
		);
		//$methods = array();
		$xxx = 0;
		$iii = 0;
		foreach($oResponse->result->e as $result) {
			if($deliver_to == 'office' && $xxx == 1) {break;}
			if($deliver_to == 'door' && $xxx == 0) {$iii++; $xxx++;}
			
			
			
			switch($step) {
			case '0':
				$sum_ship =(((float)$result->loading_price->C + ((float)$result->loading_price->DC + (float)$result->loading_price->{'DC-CP'})) * ((int)$vendor_info->fields['vendor_share_sum']/100));
				break;
			case '1':
				$sum_ship =(((float)$result->loading_price->C + ((float)$result->loading_price->DC + (float)$result->loading_price->{'DC-CP'})) * ((int)$vendor_info->fields['vendor_share_sum']/100));
				break;
			case '2':
				$sum_ship =(((float)$result->loading_price->C + ((float)$result->loading_price->DC + (float)$result->loading_price->{'DC-CP'}+(float)$result->loading_price->OC)) * ((int)$vendor_info->fields['vendor_share_sum']/100));
				break;		
				
			}
			$stak_methods_title = $methods_name[$iii] . ' ' .$methods_name_priority[$iii] . ' ' .$methods_name_oc[$iii] . ' ' . $methods_express_curier[$iii];
			
			$methods[] = array(
				'id' => $methods_id[$iii],
				'title' => $methods_name[$iii] . ' ' .$methods_name_priority[$iii] . ' ' .$methods_name_oc[$iii] . ' ' . $methods_express_curier[$iii],
				'cost' => (string)$sum_ship);
			
			$econtloadingp = ((float)$result->loading_price->P * ((int)$vendor_info->fields['vendor_share_sum']/100));
			$econtloadingoc = ((float)$result->loading_price->OC * ((int)$vendor_info->fields['vendor_share_sum']/100));
			$econtloadingcd_user = (string)((float)$result->loading_price->CD * ((int)$vendor_info->fields['vendor_share_sum']/100)).''.$result->loading_price->currency;
			$econtloadingcd = (string)((float)$result->loading_price->CD * ((int)$vendor_info->fields['vendor_share_sum']/100));
			if ($iii == 0){$_SESSION['vendor_disc_one'] = (float)$result->loading_price->{'DISC_E-ECONT'};}
			else{$_SESSION['vendor_disc_two'] = (float)$result->loading_price->{'DISC_E-ECONT'};} 
			
			if ($iii == 0){$_SESSION['reciver_share_office'] = (float)$result->loading_price->{'SHARE'}-(float)$result->loading_price->CD-(float)$result->loading_price->OC-(float)$result->loading_price->{'DISC_E-ECONT'};}
			else{$_SESSION['reciver_share_door'] = (float)$result->loading_price->{'SHARE'}-(float)$result->loading_price->CD-(float)$result->loading_price->OC-(float)$result->loading_price->{'DISC_E-ECONT'};} 
			
			$iii++;
			$xxx++;
		}

		$this->quotes['methods'] = $methods;
		
		$_SESSION['econtloadingp'] = $econtloadingp;
		$_SESSION['econtloadingoc'] = $econtloadingoc;
		$_SESSION['econtloadingcd_user'] = $econtloadingcd_user;
		$_SESSION['econtloadingcd'] = $econtloadingcd;
        if ($this->tax_class > 0) {
            $this->quotes['tax'] = zen_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
        }

        if (zen_not_null($this->icon)) $this->quotes['icon'] = zen_image($this->icon, $this->title);
        return $this->quotes;
 }

    function check() {
		global $db;
        if (!isset($this->_check)) {
            $check_query = $db->Execute("select configuration_value from " .
                TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_ECONT_STATUS'");
            $this->_check = $check_query->RecordCount();
        }
        return $this->_check;
    }

    function install() {
		global $db;
        $db->Execute("insert into " . TABLE_CONFIGURATION .
            " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Econt Express Shipping', 'MODULE_SHIPPING_ECONT_STATUS', 'True', 'Do you want to offer econt rate shipping?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
        
        $db->Execute("insert into " . TABLE_CONFIGURATION .
            " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_SHIPPING_ECONT_SORT_ORDER', '0', 'Sort order of display.', '6', '0', now())");

        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Конфигурация Еконт Магазин', 'MODULE_SHIPPING_ECONT_SHOP_INFO_SETUP_LINK', '<button id=\"shopconfig-form\"><img src=\"../images/infobox/shop_configuration.png\" /></button>', '', '6', '0', now())");
        
        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Обновяване на таблицата с адреси.', 'MODULE_SHIPPING_ECONT_SHOP_CITIES_UPDATE_LINK', '<button id=\"cities-update\"><img src=\"../images/infobox/shop_orders.png\" /></button>', '', '6', '0', now())");

        $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Конфигурация на Доставчик', 'MODULE_SHIPPING_ECONT_SHOP_SENDER_CONFIG_LINK', '<button id=\"sender-config\"><img src=\"../images/infobox/shop_vendors.png\" /></button>', '', '6', '0', now())");
        
        $db->Execute("insert into " . TABLE_CONFIGURATION .
            " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Econt DataBase Table: econt_orders', 'MODULE_SHIPPING_ECONT_DB_ORDERS_INFO', 'econt_orders', 'DataBase Table name of econt_orders', '99999', '0', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION .
            " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Econt DataBase Table: econt_vendor_info', 'MODULE_SHIPPING_ECONT_DB_VENDOR_INFO', 'econt_vendor_info', 'DataBase Table name of econt_vendor_info', '99999', '0', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION .
            " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Econt DataBase Table: econt_shop_info', 'MODULE_SHIPPING_ECONT_DB_SHOP_INFO', 'econt_shop_info', 'DataBase Table name of econt_shop_info', '99999', '0', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION .
            " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Econt DataBase Table: econt_offices_table', 'MODULE_SHIPPING_ECONT_DB_OFFICES_TABLE', 'econt_offices_table', 'DataBase Table name of econt_offices_table', '99999', '0', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION .
            " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Econt DataBase Table: econt_loadings', 'MODULE_SHIPPING_ECONT_DB_LOADINGS', 'econt_loadings', 'DataBase Table name of econt_loadings', '99999', '0', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION .
            " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Econt DataBase Table: econt_cities_table', 'MODULE_SHIPPING_ECONT_DB_CITIES_TABLE', 'econt_cities_table', 'DataBase Table name of econt_cities_table', '99999', '0', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION .
            " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Econt DataBase Table: econt_cities_streets_table', 'MODULE_SHIPPING_ECONT_DB_CITIES_STREETS_TABLE', 'econt_cities_streets_table', 'DataBase Table name of econt_cities_streets_table', '99999', '0', now())");
        $db->Execute("insert into " . TABLE_CONFIGURATION .
            " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Econt DataBase Table: econt_cities_regions_table', 'MODULE_SHIPPING_ECONT_DB_CITIES_REGIONS_TABLE', 'econt_cities_regions_table', 'DataBase Table name of econt_cities_regions_table', '99999', '0', now())");
		$db->Execute("insert into " . TABLE_CONFIGURATION .
            " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Econt DataBase Table: econt_cities_quarters_table', 'MODULE_SHIPPING_ECONT_DB_CITIES_QUARTERS_TABLE', 'econt_cities_quarters_table', 'DataBase Table name of econt_cities_quarters_table', '99999', '0', now())");
		$db->Execute("insert into " . TABLE_CONFIGURATION .
            " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Econt DataBase Table: econt_cities_offices_table', 'MODULE_SHIPPING_ECONT_DB_CITIES_OFFICES_TABLE', 'econt_cities_offices_table', 'DataBase Table name of econt_cities_offices_table', '99999', '0', now())");
  		
            
		
		// Creating DataBase table: econt_vendor_info : holding owners details.
		$db->Execute("CREATE TABLE IF NOT EXISTS `econt_vendor_info` ( `id` int(11) unsigned NOT NULL auto_increment,
			`vendor_id` int(11) unsigned NOT NULL,
			`vendor_sname` char(100) NOT NULL default '',
			`vendor_contact_name` char(50) NOT NULL default '',
			`vendor_contact_phone` char(20) NOT NULL default '',
			`vendor_country` char(50) NOT NULL default '',
			`vendor_city` char(50) NOT NULL default '',
			`vendor_zip` char(15) NOT NULL default '',
			`vendor_quarter` char(60) NOT NULL default '',
			`vendor_street` char(60) NOT NULL default '',
			`vendor_street_num` char(10) NOT NULL default '',
			`vendor_street_bl` char(10) NOT NULL default '',
			`vendor_street_vh` char(10) NOT NULL default '',
			`vendor_street_et` char(10) NOT NULL default '',
			`vendor_street_ap` char(10) NOT NULL default '',
			`vendor_street_other` char(80) NOT NULL default '',
			`vendor_deliver_from` char(10) NOT NULL default 'DOOR',
			`vendor_oc_min_amount` float NOT NULL default '-1',
			`vendor_enable_dc` char(10) NOT NULL default 'OFF',
			`vendor_payment_method` char(10) NOT NULL default 'CASH',
			`vendor_credit_num` char(20) NOT NULL default '',
			`vendor_cd_agreement_num` char(20) NOT NULL default '',
			`vendor_share_sum` int(11) NOT NULL DEFAULT '100',
			`vendor_enable_oc` enum('0','1','2') NOT NULL DEFAULT '0',
			`vendor_priority_hour` enum('0','1') NOT NULL DEFAULT '0',
			`vendor_sms_no` char(10) DEFAULT NULL,
			`vendor_pay_after_accept` enum('0','1','2') NOT NULL DEFAULT '0',
  			`vendor_instruction_returns` enum('shipping_returns','returns','0') NOT NULL DEFAULT '0',
  			`vendor_invoice_before_pay_CD` enum('0','1') NOT NULL DEFAULT '0',
			PRIMARY KEY  (`id`),
			KEY `vendorid` (`vendor_id`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='vendor configuration,info and adress';"); 
			
		// Creating DataBase table: econt_shop_info : holding owners Econt settings.
		$db->Execute("CREATE TABLE IF NOT EXISTS `econt_shop_info` (`id` int(11) unsigned NOT NULL auto_increment,
			`param_name` char(50) NOT NULL default '',
			`param_value` char(50) NOT NULL default '',
			PRIMARY KEY  (`id`),
			UNIQUE KEY `param_name` (`param_name`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='shop configuration';");
		// Populate DataBase table: econt_shop_info with standard data
		if(!$db->Execute("select * from `econt_shop_info` where `param_name` = 'deliver_to'")->RecordCount()) {
			$db->Execute("insert into `econt_shop_info` (`param_name`, `param_value`) values ('deliver_to', 'both')");
		}
		
		if(!$db->Execute("select * from `econt_shop_info` where `param_name` = 'eol_username'")->RecordCount()) {
			$db->Execute("insert into `econt_shop_info` (`param_name`, `param_value`) values ('eol_username', '')");
		}
		
		if(!$db->Execute("select * from `econt_shop_info` where `param_name` = 'eol_password'")->RecordCount()) {
			$db->Execute("insert into `econt_shop_info` (`param_name`, `param_value`) values ('eol_password', '')");
		}
		
		if(!$db->Execute("select * from `econt_shop_info` where `param_name` = 'pay_after_accept'")->RecordCount()) {
			$db->Execute("insert into `econt_shop_info` (`param_name`, `param_value`) values ('pay_after_accept', '')");
		}
		
		if(!$db->Execute("select * from `econt_shop_info` where `param_name` = 'loadings_refresh_interval'")->RecordCount()) {
			$db->Execute("insert into `econt_shop_info` (`param_name`, `param_value`) values ('loadings_refresh_interval', '600')");
		}
		
		if(!$db->Execute("select * from `econt_shop_info` where `param_name` = 'eecont_url'")->RecordCount()) {
			$db->Execute("insert into `econt_shop_info` (`param_name`, `param_value`) values ('eecont_url', 'http://www.econt.com/e-econt')");
		}
		
		if(!$db->Execute("select * from `econt_shop_info` where `param_name` = 'validate_full_client_address'")->RecordCount()) {
			$db->Execute("insert into `econt_shop_info` (`param_name`, `param_value`) values ('validate_full_client_address', 'yes')");
		}
		
		if(!$db->Execute("select * from `econt_vendor_info`")->RecordCount()) {
			$db->Execute("insert into `econt_vendor_info` (`vendor_id`) values (1)");
		}
		
		// Creating DataBase table: econt_offices : holding all Econt offices.
		$db->Execute("CREATE TABLE IF NOT EXISTS `econt_offices_table` (`id` int(4) unsigned NOT NULL auto_increment,
			`id_city` int(4) unsigned NOT NULL default '0',
			`code` int(4) unsigned NOT NULL COMMENT 'office code',
			`name` char(250) NOT NULL default '' COMMENT 'name in bg',
			`name_en` char(80) NOT NULL default '' COMMENT 'name in english',
			`phone` char(30) NOT NULL,
			`address` char(250) NOT NULL,
			`address_en` char(150) NOT NULL,
			`work_begin` time default '09:00:00',
			`work_begin_saturday` time default '09:00:00',
			`work_end` time default '18:00:00',
			`work_end_saturday` time default '13:00:00',
			`time_priority` time default '12:00:00' COMMENT 'minimum priority hour',
			PRIMARY KEY  (`id`),
			UNIQUE KEY `code` (`code`),
			KEY `id_city` (`id_city`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");
			
		// Creating DataBase table: econt_loadings : holding links to API Services, Updates.
		$db->Execute("CREATE TABLE IF NOT EXISTS `econt_loadings` (`loading_id` int(11) unsigned NOT NULL auto_increment,
			`order_id` int(11) NOT NULL,
			`vendor_id` int(11) unsigned NOT NULL,
			`loading_num` char(20) NOT NULL,
			`updated_time` datetime NOT NULL default '1970-01-01 00:00:00',
			`delivery_date` date NOT NULL COMMENT 'date to be delivered',
			`storage` char(100) NOT NULL default '',
			`is_imported` tinyint(3) unsigned NOT NULL default '0',
			`receiver_person` char(50) NOT NULL default '',
			`receiver_phone` char(50) NOT NULL default '',
			`receiver_courier` char(50) NOT NULL default '',
			`receiver_courier_phone` char(50) NOT NULL default '',
			`receiver_time` datetime NOT NULL default '0000-00-00 00:00:00',
			`cd_get_sum` float NOT NULL default '-1',
			`cd_get_time` datetime NOT NULL default '0000-00-00 00:00:00',
			`blank_yes` text NOT NULL,
			`blank_no` text NOT NULL,
			`error` char(100) NOT NULL default '',
			`error_code` char(50) NOT NULL default '',
			PRIMARY KEY  (`loading_id`),
			UNIQUE KEY `loading_num` (`loading_num`),
			KEY `order_id` (`order_id`),
			KEY `updated_time` (`updated_time`),
			KEY `vendor_id` (`vendor_id`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");
		
		// Creating DataBase table: econt_cities_table : holding all cities from Econt DB.
		$db->Execute("CREATE TABLE IF NOT EXISTS `econt_cities_table` (`id` int(4) unsigned NOT NULL,
			`id_office` int(4) unsigned NOT NULL default '0',
			`id_zone` int(4) unsigned NOT NULL default '3',
			`post_code` int(4) unsigned NOT NULL default '0',
			`name` char(250) NOT NULL default '',
			`name_en` char(160) NOT NULL default '' COMMENT 'name in english',
			`type` tinyint(1) unsigned NOT NULL default '0' COMMENT '0-grad; 1-selo; 2-kk; 3-manastir; 4-gara',
			`id_country` int(11) NOT NULL default '1033' COMMENT 'ID na darjavata v koiato se namira nas. mesto',
			`is_country` tinyint(3) NOT NULL default '0' COMMENT 'Dali e darjava',
			PRIMARY KEY  (`id`),
			KEY `code` (`post_code`),
			KEY `name` (`name`),
			KEY `id_office` (`id_office`),
			KEY `name_en` (`name_en`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
		
		// Creating DataBase table: econt_cities_streets_table : holding all streets from Econt DB.
		$db->Execute("CREATE TABLE IF NOT EXISTS `econt_cities_streets_table` (`id` int(11) NOT NULL,
			`id_city` int(4) NOT NULL default '0',
			`street_name` char(250) NOT NULL default '',
			`street_name_en` char(250) default NULL COMMENT 'name in english',
			PRIMARY KEY  (`id`),
			KEY `id_city` (`id_city`),
			KEY `streeet_name` (`street_name`),
			KEY `streeet_name_en` (`street_name_en`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
		
		// Creating DataBase table: econt_cities_regions_table : holding all zip codes by cities from Econt DB.
		$db->Execute("CREATE TABLE IF NOT EXISTS `econt_cities_regions_table` (
			`id` int(4) NOT NULL,
			`id_city` int(4) NOT NULL default '0',
			`code` char(40) NOT NULL default '',
			`name` char(250) NOT NULL default '',
			PRIMARY KEY  (`id`),
			KEY `id_city` (`id_city`),
			KEY `code` (`code`),
			KEY `name` (`name`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
		
		// Creating DataBase table: econt_cities_quarters_table : holding all quarters by cities from Econt DB.
		$db->Execute("CREATE TABLE IF NOT EXISTS `econt_cities_quarters_table` (`id` int(11) NOT NULL,
			`id_city` int(4) NOT NULL default '0',
			`quarter_name` char(250) NOT NULL,
			`quarter_name_en` char(250) default NULL COMMENT 'name in english',
			PRIMARY KEY  (`id`),
			KEY `id_city` (`id_city`),
			KEY `quarter_name_en` (`quarter_name_en`),
			KEY `quarter_name` (`quarter_name`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
		
		// Creating DataBase table: econt_cities_offices_table : holding all offices by cities from Econt DB.
		$db->Execute("CREATE TABLE IF NOT EXISTS `econt_cities_offices_table` (`id` int(10) unsigned NOT NULL auto_increment,
			`id_city` int(10) unsigned NOT NULL,
			`office_code` int(10) unsigned NOT NULL,
			`shipment_type` enum('courier_shipments','cargo_palet_shipments','cargo_expres_shipments','post_shipments') NOT NULL,
			`delivery_type` enum('to_door','from_door','to_office','from_office') NOT NULL,
			PRIMARY KEY  (`id`),
			UNIQUE KEY `all` (`id_city`,`office_code`,`shipment_type`,`delivery_type`),
			KEY `office_code` (`office_code`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");
			
			
		// Creating DataBase table: econt_orders : holding all site orders with econt.
		$db->Execute("CREATE TABLE IF NOT EXISTS `econt_orders` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`order_id` int(11) NOT NULL,
			`office` int(11) DEFAULT NULL,
			`oc` smallint(1) NOT NULL DEFAULT '0',
			`p_type` set('BEFORE','IN','AFTER') NOT NULL DEFAULT 'BEFORE',
  			`p_hour` varchar(5) DEFAULT NULL,
			`econt_cd` set('0','1') NOT NULL DEFAULT '0',
		    `vendor_discount` decimal(15,2) NOT NULL DEFAULT '0',
			`econt_exp`	varchar(2) NULL DEFAULT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;");
		
		// Creating DataBase table: street_temp : street suggest.
		$db->Execute("DROP TABLE IF EXISTS `street_temp`;");
		$db->Execute("CREATE TABLE IF NOT EXISTS `street_temp` (
            `temp_vars` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
            `user_id` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
    }

    function remove() {
		global $db;
        $db->Execute("delete from " . TABLE_CONFIGURATION .
            " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
        
        $db->Execute("delete from ".TABLE_CONFIGURATION." where configuration_key in ('MODULE_SHIPPING_ECONT_DB_VENDOR_INFO', 'MODULE_SHIPPING_ECONT_DB_SHOP_INFO', 'MODULE_SHIPPING_ECONT_DB_OFFICES_TABLE', 'MODULE_SHIPPING_ECONT_DB_LOADINGS', 'MODULE_SHIPPING_ECONT_DB_CITIES_TABLE', 'MODULE_SHIPPING_ECONT_DB_CITIES_STREETS_TABLE', 'MODULE_SHIPPING_ECONT_DB_CITIES_REGIONS_TABLE', 'MODULE_SHIPPING_ECONT_DB_CITIES_QUARTERS_TABLE', 'MODULE_SHIPPING_ECONT_DB_CITIES_OFFICES_TABLE', 'MODULE_SHIPPING_ECONT_DB_ORDERS_INFO')");
    }

    function keys() {
        return array('MODULE_SHIPPING_ECONT_STATUS',
            'MODULE_SHIPPING_ECONT_SORT_ORDER', 'MODULE_SHIPPING_ECONT_SHOP_INFO_SETUP_LINK', 'MODULE_SHIPPING_ECONT_SHOP_CITIES_UPDATE_LINK', 'MODULE_SHIPPING_ECONT_SHOP_SENDER_CONFIG_LINK');
    }
}
?>