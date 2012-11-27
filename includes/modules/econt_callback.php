<?php
/**
 * zencart mod
 * SIMEON MLADENOV
*/
 
 
require('includes/application_top.php');  
include_once(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/shipping/econt.php');
global $db;
if((isset($_REQUEST['action'])) && ($_REQUEST['action'] != "")) {
	$action = $_REQUEST['action'];
	
	switch($action) {
		
		/** AUTOCOMPLETE GET CITIES BOF **/
		case 'get_suggest_city':
			
			$out = array();

			$requested = $_REQUEST['city_startswith'];
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
		/** AUTOCOMPLETE GET CITIES EOF **/
		
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
		default:
			break;
	}
}

?>