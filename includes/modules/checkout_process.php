<?php
/**
 * zencart mod
 * SIMEON MLADENOV
*/

if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
$zco_notifier->notify('NOTIFY_CHECKOUT_PROCESS_BEGIN');

require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));

// if the customer is not logged on, redirect them to the time out page
  if (!$_SESSION['customer_id']) {
    zen_redirect(zen_href_link(FILENAME_TIME_OUT));
  } else {
    // validate customer
    if (zen_get_customer_validate_session($_SESSION['customer_id']) == false) {
      $_SESSION['navigation']->set_snapshot(array('mode' => 'SSL', 'page' => FILENAME_CHECKOUT_SHIPPING));
      zen_redirect(zen_href_link(FILENAME_LOGIN, '', 'SSL'));
    }
  }

// confirm where link came from
if (!strstr($_SERVER['HTTP_REFERER'], FILENAME_CHECKOUT_CONFIRMATION)) {
  //    zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT,'','SSL'));
}

// BEGIN CC SLAM PREVENTION
if (!isset($_SESSION['payment_attempt'])) $_SESSION['payment_attempt'] = 0;
$_SESSION['payment_attempt']++;
$zco_notifier->notify('NOTIFY_CHECKOUT_SLAMMING_ALERT');
if ($_SESSION['payment_attempt'] > 3) {
  $zco_notifier->notify('NOTIFY_CHECKOUT_SLAMMING_LOCKOUT');
  $_SESSION['cart']->reset(TRUE);
  zen_session_destroy();
  zen_redirect(zen_href_link(FILENAME_TIME_OUT));
}
// END CC SLAM PREVENTION

if (!isset($credit_covers)) $credit_covers = FALSE;

// load selected payment module
require(DIR_WS_CLASSES . 'payment.php');
$payment_modules = new payment($_SESSION['payment']);
// load the selected shipping module
require(DIR_WS_CLASSES . 'shipping.php');
$shipping_modules = new shipping($_SESSION['shipping']);

require(DIR_WS_CLASSES . 'order.php');
$order = new order;

// prevent 0-entry orders from being generated/spoofed
if (sizeof($order->products) < 1) {
  zen_redirect(zen_href_link(FILENAME_SHOPPING_CART));
}

require(DIR_WS_CLASSES . 'order_total.php');
$order_total_modules = new order_total;

$zco_notifier->notify('NOTIFY_CHECKOUT_PROCESS_BEFORE_ORDER_TOTALS_PRE_CONFIRMATION_CHECK');
if (strpos($GLOBALS[$_SESSION['payment']]->code, 'paypal') !== 0) {
  $order_totals = $order_total_modules->pre_confirmation_check();
}
if ($credit_covers === TRUE)
{
	$order->info['payment_method'] = $order->info['payment_module_code'] = '';
}
$zco_notifier->notify('NOTIFY_CHECKOUT_PROCESS_BEFORE_ORDER_TOTALS_PROCESS');
$order_totals = $order_total_modules->process();
$zco_notifier->notify('NOTIFY_CHECKOUT_PROCESS_AFTER_ORDER_TOTALS_PROCESS');

if (!isset($_SESSION['payment']) && $credit_covers === FALSE) {
  zen_redirect(zen_href_link(FILENAME_DEFAULT));
}

// load the before_process function from the payment modules
$payment_modules->before_process();
$zco_notifier->notify('NOTIFY_CHECKOUT_PROCESS_AFTER_PAYMENT_MODULES_BEFOREPROCESS');
// create the order record
$insert_id = $order->create($order_totals, 2);
$zco_notifier->notify('NOTIFY_CHECKOUT_PROCESS_AFTER_ORDER_CREATE');
$payment_modules->after_order_create($insert_id);
$zco_notifier->notify('NOTIFY_CHECKOUT_PROCESS_AFTER_PAYMENT_MODULES_AFTER_ORDER_CREATE');
// store the product info to the order
$order->create_add_products($insert_id);
$_SESSION['order_number_created'] = $insert_id;
$zco_notifier->notify('NOTIFY_CHECKOUT_PROCESS_AFTER_ORDER_CREATE_ADD_PRODUCTS');

// clear slamming protection since payment was accepted
if (isset($_SESSION['payment_attempt'])) unset($_SESSION['payment_attempt']);

/**
 * Calculate order amount for display purposes on checkout-success page as well as adword campaigns etc
 * Takes the product subtotal and subtracts all credits from it
 */
  $ototal = $order_subtotal = $credits_applied = 0;
  for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
    if ($order_totals[$i]['code'] == 'ot_subtotal') $order_subtotal = $order_totals[$i]['value'];
    if ($$order_totals[$i]['code']->credit_class == true) $credits_applied += $order_totals[$i]['value'];
    if ($order_totals[$i]['code'] == 'ot_total') $ototal = $order_totals[$i]['value'];
    if ($order_totals[$i]['code'] == 'ot_tax') $otax = $order_totals[$i]['value'];
    if ($order_totals[$i]['code'] == 'ot_shipping') $oshipping = $order_totals[$i]['value'];
  }
  
  /** Econt Shipping Module BOF **/
	if(MODULE_SHIPPING_ECONT_STATUS == 'True') {
		
		$check_order = $db->Execute("SELECT * FROM ".MODULE_SHIPPING_ECONT_DB_ORDERS_INFO." WHERE order_id = ".(int)$insert_id."");
		if(!$check_order->RecordCount()) {
			$db->Execute("INSERT INTO ".MODULE_SHIPPING_ECONT_DB_ORDERS_INFO." (order_id) VALUES(".(int)$insert_id.")");
			
			if((isset($_SESSION['econtofficecode'])) && ($_SESSION['econtofficecode'] != '')) {
				$db->Execute("UPDATE ".MODULE_SHIPPING_ECONT_DB_ORDERS_INFO." SET office = ".(int)$_SESSION['econtofficecode']." WHERE order_id = ".(int)$insert_id."");
			}
			
			if((isset($_SESSION['vendor_enable_oc'])) && ($_SESSION['vendor_enable_oc'] == 'on')) {
				$db->Execute("UPDATE ".MODULE_SHIPPING_ECONT_DB_ORDERS_INFO." SET oc = 1 WHERE order_id = ".(int)$insert_id."");
			}
			
			if((isset($_SESSION['vendor_priority_hour'])) && ($_SESSION['vendor_priority_hour'] == 'on')) {
				$db->Execute("UPDATE ".MODULE_SHIPPING_ECONT_DB_ORDERS_INFO." SET p_type = '".$_SESSION['econt_priority_type']."', p_hour = '".$_SESSION['econt_priority_text']."' WHERE order_id = ".(int)$insert_id."");
			}
			
			if((isset($_SESSION['econtofficecode']))) {
				$db->Execute("UPDATE ".MODULE_SHIPPING_ECONT_DB_ORDERS_INFO." SET vendor_discount = ".(float)$_SESSION['reciver_share_office']." WHERE order_id = ".(int)$insert_id."");
			}
			else{$db->Execute("UPDATE ".MODULE_SHIPPING_ECONT_DB_ORDERS_INFO." SET vendor_discount = ".(float)$_SESSION['reciver_share_door']." WHERE order_id = ".(int)$insert_id."");}
			
			if((isset($_SESSION['payment'])) && ($_SESSION['payment'] == 'ecd')) {
				$db->Execute("UPDATE ".MODULE_SHIPPING_ECONT_DB_ORDERS_INFO." SET econt_cd = '1' WHERE order_id = ".(int)$insert_id."");
				
				if((isset($_SESSION['econt_express']))) {
				$db->Execute("UPDATE ".MODULE_SHIPPING_ECONT_DB_ORDERS_INFO." SET econt_exp = '". $_SESSION['econt_express'] ."' WHERE order_id = ".(int)$insert_id."");}
				
				$moby = MODULE_PAYMENT_ECD_TEXT_TITLE;
				$new_total = (float)$ototal +(float)$_SESSION['econtloadingcd_user'];
				if($_SESSION['languages_code'] == 'en'){$mob = $new_total .'lv.';}
				else {
					if($_SESSION['languages_code'] == 'bg'){$mob = $new_total .'лв.';}
				}
+				$db->Execute("UPDATE ".TABLE_ORDERS_TOTAL." SET text = '".$mob."' , value = ".$new_total." WHERE orders_id =".(int)$insert_id." AND class = 'ot_total'");
				
				
			}
		}
	}
/** Econt Shipping Module EOF **/
//send email notifications
$order->send_order_email($insert_id, 2,$ototal);
$zco_notifier->notify('NOTIFY_CHECKOUT_PROCESS_AFTER_SEND_ORDER_EMAIL');

  $commissionable_order = ($order_subtotal - $credits_applied);
  $commissionable_order_formatted = $currencies->format($commissionable_order);
  $_SESSION['order_summary']['order_number'] = $insert_id;
  $_SESSION['order_summary']['order_subtotal'] = $order_subtotal;
  $_SESSION['order_summary']['credits_applied'] = $credits_applied;
  $_SESSION['order_summary']['order_total'] = $ototal;
  $_SESSION['order_summary']['commissionable_order'] = $commissionable_order;
  $_SESSION['order_summary']['commissionable_order_formatted'] = $commissionable_order_formatted;
  $_SESSION['order_summary']['coupon_code'] = $order->info['coupon_code'];
  $_SESSION['order_summary']['currency_code'] = $order->info['currency'];
  $_SESSION['order_summary']['currency_value'] = $order->info['currency_value'];
  $_SESSION['order_summary']['payment_module_code'] = $order->info['payment_module_code'];
  $_SESSION['order_summary']['shipping_method'] = $order->info['shipping_method'];
  $_SESSION['order_summary']['orders_status'] = $order->info['orders_status'];
  $_SESSION['order_summary']['tax'] = $otax;
  $_SESSION['order_summary']['shipping'] = $oshipping;
  $zco_notifier->notify('NOTIFY_CHECKOUT_PROCESS_HANDLE_AFFILIATES');

