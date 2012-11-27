<?php
/**
 * zencart mod
 * SIMEON MLADENOV
*/

if (zen_not_null($action)) {
	switch ($action) {
		case 'update_loadings':
			include_once('../' . DIR_WS_MODULES .'shipping/econt/functions.php');
			$aCachedLoadings = array();
			$aCachedLoadingsCities = array();
			
			$row_loadings = $db->Execute("SELECT * FROM ".MODULE_SHIPPING_ECONT_DB_LOADINGS." WHERE is_imported != 1 AND storage = ''");
			if($row_loadings->RecordCount()) {
				while(!$row_loadings->EOF) {
					$aCachedLoadingsCities[$row_loadings->fields['loading_num']] = array(
						'sender_city'   => "",
						'receiver_city' => ""
					);
					
					$aCachedLoadings[$row_loadings->fields['loading_num']] = array();
					$row_loadings->MoveNext();
				}
				
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
					$sXMLResponseLoading = updateLoadingRequest('shipments', $eecont_url, $eol_username, $eol_password, $aCachedLoadings);
					
					if($sXMLResponseCities !== false) {
						$oResponseInfo = new SimpleXMLElement($sXMLResponseLoading);
						foreach ($oResponseInfo->shipments->e as $oShipmens) {
							$loading_num 				= zen_db_prepare_input($oShipmens->loading_num);
							$is_imported 				= zen_db_prepare_input($oShipmens->is_imported);
							$storage 					= zen_db_prepare_input($oShipmens->storage);
							$receiver_person 			= zen_db_prepare_input($oShipmens->receiver_person);
							$receiver_phone 			= zen_db_prepare_input($oShipmens->receiver_phone);
							$receiver_courier 			= zen_db_prepare_input($oShipmens->receiver_courier);
							$receiver_courier_phone 	= zen_db_prepare_input($oShipmens->receiver_courier_phone);
							$receiver_time 				= zen_db_prepare_input($oShipmens->receiver_time);
							$cd_get_sum 				= zen_db_prepare_input($oShipmens->CD_get_sum);
							$cd_get_time 				= zen_db_prepare_input($oShipmens->CD_get_time);
								
							$receivertime = $receiver_time != "" ? $receiver_time : '0000-00-00 00:00:00';
							$cdgettime = $cd_get_time != "" ? $cd_get_time : '0000-00-00 00:00:00';
							$cdgetsum = $cd_get_sum != "" ? $cd_get_sum : 0;
								
							$db->Execute("UPDATE ".MODULE_SHIPPING_ECONT_DB_LOADINGS." SET
									is_imported 			= ".$is_imported.",
									storage 				= '".$storage."',
									receiver_person 		= '".$receiver_person."',
									receiver_phone 			= '".$receiver_phone."',
									receiver_courier 		= '".$receiver_courier."',
									receiver_courier_phone 	= '".$receiver_courier_phone."',
									receiver_time 			= '".$receivertime."',
									cd_get_sum 				= ".$cdgetsum.",
									cd_get_time 			= '".$cdgettime."'
								 WHERE 
								 	loading_num = '".$loading_num."'
							");
						}
					}
				}
			}
			break;
	}	
}  	

?>
<div id="dialog-requestcourier" title="<?php echo MODULE_SHIPPING_ECONT_REQUESTCOURIER_TITLE_TEXT; ?>">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MODULE_SHIPPING_ECONT_REQUESTCOURIER_TEXT; ?></p>
	<p id="pdflink"></p>
	<div id="progressbar"></div>
</div>

<?php
		if(!$_GET['oID']) {
			if (isset($_GET['cID'])) {
				$cID = zen_db_prepare_input($_GET['cID']);
				$orders_query_raw = "select o.orders_id, o.customers_name, o.customers_id, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name, ot.text as order_total from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id), " . TABLE_ORDERS_STATUS . " s where o.customers_id = '" . (int)$cID . "' and o.orders_status = s.orders_status_id and s.language_id = '" . (int)$languages_id . "' and ot.class = 'ot_total' order by orders_id DESC";
			} elseif (isset($_GET['status']) && is_numeric($_GET['status']) && ($_GET['status'] > 0)) {
				$status = zen_db_prepare_input($_GET['status']);
				$orders_query_raw = "select o.orders_id, o.customers_name, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name, ot.text as order_total from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id), " . TABLE_ORDERS_STATUS . " s where o.orders_status = s.orders_status_id and s.language_id = '" . (int)$languages_id . "' and s.orders_status_id = '" . (int)$status . "' and ot.class = 'ot_total' order by o.orders_id DESC";
			} else {
				$orders_query_raw = "select o.orders_id, o.customers_name, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name, ot.text as order_total from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id), " . TABLE_ORDERS_STATUS . " s where o.orders_status = s.orders_status_id and s.language_id = '" . (int)$languages_id . "' and ot.class = 'ot_total' order by o.orders_id DESC";
			}
			$orders_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $orders_query_raw, $orders_query_numrows);
			$orders = $db->Execute($orders_query_raw);
			while (!$orders->EOF) {
				$oID = $orders->fields['orders_id'];
				break;
				$orders->MoveNext();
			}
		} else {
			$oID = $_GET['oID'];
		}
		
		$loading = $db->Execute("SELECT * FROM ".MODULE_SHIPPING_ECONT_DB_LOADINGS." WHERE order_id = ".(int)$oID."");
		if($loading->RecordCount()) {
			//$loading = tep_db_fetch_array($loading_query);
		}
?>
<div id="dialog-loadingurl" title="<?php echo MODULE_SHIPPING_ECONT_REQUESTCOURIER_TITLE_TEXT; ?> - <?php echo $loading->fields['loading_num']; ?>">
	<!--<p id="pdflink">
		<?php echo '<a href="'.$loading->fields['blank_no'].'" target="_blank">'.MODULE_SHIPPING_ECONT_REQUESTCOURIER_LINK_PDF_LOADING_TEXT.'</a>'; ?>
	</p>-->
	<fieldset style="width: 100%;" class="sender-config-form">
		<legend><?php echo MODULE_SHIPPING_ORDERS_LOADINGINFO_LEGEND_TEXT; ?></legend>
		<table cellpadding="8" cellspacing="0" border="0" align="center" style="width: 90%; padding: 10px 4px; border:1px solid #CCCCCC;">
			<tr>
				<td style="width: 50%;" class="t_rb_border"><?php echo MODULE_SHIPPING_ORDERS_LOADINGINFO_NUMBER_LOADING_TEXT; ?></td>
				<td style="width: 50%;" class="t_b_border"><?php echo $loading->fields['loading_num']; ?><input type="hidden" name="loadingid" id="loadingid" value="<?php echo $loading->fields['loading_id']; ?>" /></td>
			</tr>
			<tr>
				<td style="width: 50%;" class="t_rb_border"><?php echo MODULE_SHIPPING_ORDERS_LOADINGINFO_DATE_DELIVERY_TEXT; ?></td>
				<td style="width: 50%;" class="t_b_border"><?php echo $loading->fields['delivery_date']; ?></td>
			</tr>
			<tr>
				<td style="width: 50%;" class="t_rb_border"><?php echo MODULE_SHIPPING_ORDERS_LOADINGINFO_STORAGE_TEXT; ?></td>
				<td style="width: 50%;" class="t_b_border"><?php echo $loading->fields['storage']?$loading->fields['storage']:'&nbsp;'; ?></td>
			</tr>
			<tr>
				<td style="width: 50%;" class="t_rb_border"><?php echo MODULE_SHIPPING_ORDERS_LOADINGINFO_RECEIVER_NAME_TEXT; ?></td>
				<td style="width: 50%;" class="t_b_border"><?php echo $loading->fields['receiver_person']?$loading->fields['receiver_person']:'&nbsp;'; ?></td>
			</tr>
			<tr>
				<td style="width: 50%;" class="t_rb_border"><?php echo MODULE_SHIPPING_ORDERS_LOADINGINFO_RECEIVER_PHONE_TEXT; ?></td>
				<td style="width: 50%;" class="t_b_border"><?php echo $loading->fields['receiver_phone']?$loading->fields['receiver_phone']:'&nbsp;'; ?></td>
			</tr>
			<tr>
				<td style="width: 50%;" class="t_rb_border"><?php echo MODULE_SHIPPING_ORDERS_LOADINGINFO_COURIER_NAME_TEXT; ?></td>
				<td style="width: 50%;" class="t_b_border"><?php echo $loading->fields['receiver_courier']?$loading->fields['receiver_courier']:'&nbsp;'; ?></td>
			</tr>
			<tr>
				<td style="width: 50%;" class="t_rb_border"><?php echo MODULE_SHIPPING_ORDERS_LOADINGINFO_COURIER_PHONE_TEXT; ?></td>
				<td style="width: 50%;" class="t_b_border"><?php echo $loading->fields['receiver_courier_phone']?$loading->fields['receiver_courier_phone']:'&nbsp;'; ?></td>
			</tr>
			<tr>
				<td style="width: 50%;" class="t_rb_border"><?php echo MODULE_SHIPPING_ORDERS_LOADINGINFO_RECEIVER_TIME_TEXT; ?></td>
				<td style="width: 50%;" class="t_b_border"><?php echo $loading->fields['receiver_time']!='0000-00-00 00:00:00'?$loading->fields['receiver_time']:'&nbsp;'; ?></td>
			</tr>
			<tr>
				<td style="width: 50%;" class="t_rb_border"><?php echo MODULE_SHIPPING_ORDERS_LOADINGINFO_CD_GET_SUM_TEXT; ?></td>
				<td style="width: 50%;" class="t_b_border"><?php echo $loading->fields['cd_get_sum']!='-1'?$loading->fields['cd_get_sum']:'&nbsp;'; ?></td>
			</tr>
			<tr>
				<td style="width: 50%;" class="t_rb_border"><?php echo MODULE_SHIPPING_ORDERS_LOADINGINFO_CD_GET_TIME_TEXT; ?></td>
				<td style="width: 50%;" class="t_b_border"><?php echo $loading->fields['cd_get_time']!='0000-00-00 00:00:00'?$loading->fields['cd_get_time']:'&nbsp;'; ?></td>
			</tr>
			<tr>
				<td style="width: 50%;" class="t_rb_border"><?php echo MODULE_SHIPPING_ORDERS_LOADINGINFO_PDF_LINK_TEXT; ?></td>
				<td style="width: 50%;" class="t_b_border"><?php echo $loading->fields['blank_no']!=''?'<a href="'.$loading->fields['blank_no'].'" target="_blank">'.MODULE_SHIPPING_ORDERS_LOADINGINFO_CLICK_TEXT.'</a>':'&nbsp;'; ?></td>
			</tr>
			<tr>
				<td style="width: 50%;" class="t_rb_border"><?php echo MODULE_SHIPPING_ORDERS_LOADINGINFO_BLANK_LINK_TEXT; ?></td>
				<td style="width: 50%;" class="t_b_border"><?php echo $loading->fields['blank_yes']!=''?'<a href="'.$loading->fields['blank_yes'].'" target="_blank">'.MODULE_SHIPPING_ORDERS_LOADINGINFO_CLICK_TEXT.'</a>':'&nbsp;'; ?></td>
			</tr>
			<tr>
				<td colspan="2">
					<p id="delete-loading"><?php echo MODULE_SHIPPING_ORDERS_LOADINGINFO_DELETE_LOADING_TEXT; ?></p>
					<p id="delete-loading-error"></p>
				</td>
			</tr>
		</table>
	</fieldset>
</div>


<div id="dialog-receiveraddress" title="">
	<div id="progressbar_receiveraddress"></div>
	<table cellpadding="3" cellspacing="1" style="width: 100%; padding-top: 30px;">
		<tr>
			<td style="padding: 5px 0; color: red;" id="error-receiveraddress"></td>
		</tr>
		<tr>
			<td style="width: 100%;" valign="top"><input type="hidden" name="econtorderid" id="econtorderid" value="" />
				<table cellpadding="2" cellspacing="5" border="0">
					<tr>
						<td><?php echo MODULE_SHIPPING_SENDER_CONFIG_LEGEND_CONTACTS_NAME_TEXT; ?></td>
						<td><input type="text" name="receiver_name" id="receiver_name" value="" size="30" disabled="disabled" /></td>
					</tr>
					<tr>
						<td><?php echo MODULE_SHIPPING_SENDER_CONFIG_LEGEND_CONTACTS_PHONE_TEXT; ?></td>
						<td><input type="text" name="receiver_contact_phone" id="receiver_contact_phone" value="" size="30" disabled="disabled" /></td>
					</tr>
					<tr>
						<td><?php echo MODULE_SHIPPING_SENDER_CONFIG_ADDRESS_COUNTRY_TEXT; ?></td>
						<td>
							<select name="receiver_country" id="receiver_country">
								<option value="BGR">Bulgaria</option>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo MODULE_SHIPPING_SENDER_CONFIG_ADDRESS_CITY_TEXT; ?></td>
						<td>
							<input type="text" name="receiver_city" id="receiver_city" value="" size="30" />
							<input type="hidden" name="cityid" id="cityid" value="" />
						</td>
					</tr>
					<tr>
						<td><?php echo MODULE_SHIPPING_SENDER_CONFIG_ADDRESS_ZIP_TEXT; ?></td>
						<td><input type="text" name="receiver_zip" id="receiver_zip" value="" size="30" /></td>
					</tr>
					<tr>
						<td><?php echo MODULE_SHIPPING_SENDER_CONFIG_ADDRESS_QUARTER_TEXT; ?></td>
						<td><input type="text" name="receiver_quarter" id="receiver_quarter" value="" size="30" /></td>
					</tr>
					<tr>
						<td><?php echo MODULE_SHIPPING_SENDER_CONFIG_ADDRESS_STREET_TEXT; ?></td>
						<td>
							<input type="text" name="receiver_street" id="receiver_street" value="" size="30" />
							<input type="hidden" name="streetid" id="streetid" value="" />
						</td>
					</tr>
					<tr>
						<td><?php echo MODULE_SHIPPING_SENDER_CONFIG_ADDRESS_STREET_NUM_TEXT; ?></td>
						<td><input type="text" name="receiver_street_num" id="receiver_street_num" value="" size="30" /></td>
					</tr>
					<tr>
						<td><?php echo MODULE_SHIPPING_SENDER_CONFIG_ADDRESS_OTHER_TEXT; ?></td>
						<td><textarea name="receiver_quarter_other" id="receiver_quarter_other" cols="25" rows="3"></textarea></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</div>