<?php
/**
 * zencart mod
 * SIMEON MLADENOV
*/

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
	//$sTmpFileName=tempnam(sys_get_temp_dir(),'price_request_');
	$sTmpFileName='/www/simeon/simeon.econt.com/catalog_zencart/tmp/proba.xml';
	file_put_contents($sTmpFileName,$sXMLRequest,FILE_APPEND);
	
	$upload_url = $eecont_url."/xml_parcel_import.php";
	
	$aPostData = array( 'file' => '@'.$sTmpFileName);
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $upload_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS,$aPostData);
	$sXMLResponse = curl_exec($ch);
	
	//unlink($sTmpFileName);
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
		
		if(!empty($aLoadingInfo['services']['e1'])) {
			$oElE1 = $oDoc->createElement('e1');
			$oElE1->appendChild($oDoc->createTextNode($aLoadingInfo['services']['e1']));
			$oElServices->appendChild($oElE1);
		}
		
		
		if(!empty($aLoadingInfo['services']['e2'])) {
			$oElE2 = $oDoc->createElement('e2');
			$oElE2->appendChild($oDoc->createTextNode($aLoadingInfo['services']['e2']));
			$oElServices->appendChild($oElE2);
		}
		
		if(!empty($aLoadingInfo['services']['e3'])) {
			$oElE3 = $oDoc->createElement('e3');
			$oElE3->appendChild($oDoc->createTextNode($aLoadingInfo['services']['e3']));
			$oElServices->appendChild($oElE3);
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