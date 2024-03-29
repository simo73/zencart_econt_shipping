<?php
/**
 * zencart mod
 * SIMEON MLADENOV
*/
  class ecd {
    var $code, $title, $description, $enabled;

// class constructor
    function ecd() {
      global $order;

      $this->code = 'ecd';
      $this->title = MODULE_PAYMENT_ECD_TEXT_TITLE;
      $this->description = MODULE_PAYMENT_ECD_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_PAYMENT_ECD_SORT_ORDER;
      $this->enabled = ((MODULE_PAYMENT_ECD_STATUS == 'True') ? true : false);
      
	  
      if ((int)MODULE_PAYMENT_ECD_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_ECD_ORDER_STATUS_ID;
      }

      if (is_object($order)) $this->update_status();
    }

// class methods
    function update_status() {
      global $order, $db;

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_ECD_ZONE > 0) ) {
        $check_flag = false;
        $check = $db->Execute("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_ECD_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
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

// disable the module if the order only contains virtual products
      if ($this->enabled == true) {
        if ($order->content_type != 'physical') {
          $this->enabled = false;
        }
		if (!preg_match('/econt/', $_SESSION['shipping']["id"])) {
                $this->enabled = false;
            }
		
      }
	  $order->info['tax_groups'] = array(MODULE_PAYMENT_ECD_TEXT_DC => (float)$_SESSION['econtloadingcd_user']);
	  if($order->info['ecd_checked'] != 0){$order->info['total'] = (float)$order->info['total'];unset ($_SESSION['pay_chk']);}
	  else{
	  $order->info['total'] = (float)$order->info['total'] + (float)$_SESSION['econtloadingcd_user'];$_SESSION['pay_chk'] = 1;}
	  $order->info['ecd_checked'] = '1';
    }

    function javascript_validation() {
      return false;
    }

    function selection() {
      return array('id' => $this->code,
                   'module' => $this->title .  ' ('.$_SESSION['econtloadingcd_user'].')');
    }

    function pre_confirmation_check() {
      return false;
    }

    function confirmation() {
      return false;
    }

    function process_button() {
      return false;
    }

    function before_process() {
      return false;
    }

    function after_process() {
      return false;
    }

    function get_error() {
      return false;
    }

    function check() {
      global $db;
      if (!isset($this->_check)) {
        $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_ECD_STATUS'");
        $this->_check = $check_query->RecordCount();
      }
      return $this->_check;
    }

    function install() {
      global $db, $messageStack;
      if (defined('MODULE_PAYMENT_ECD_STATUS')) {
        $messageStack->add_session('ECD module already installed.', 'error');
        zen_redirect(zen_href_link(FILENAME_MODULES, 'set=payment&module=ecd', 'NONSSL'));
        return 'failed';
      }
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Econt Cash On Delivery Module', 'MODULE_PAYMENT_ECD_STATUS', 'True', 'Do you want to accept Econt Cash On Delivery payments?', '6', '1', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_ECD_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_ECD_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
      $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_ECD_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '0', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
   }

    function remove() {
      global $db;
      $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_PAYMENT_ECD_STATUS', 'MODULE_PAYMENT_ECD_ZONE', 'MODULE_PAYMENT_ECD_ORDER_STATUS_ID', 'MODULE_PAYMENT_ECD_SORT_ORDER');
    }
  }
