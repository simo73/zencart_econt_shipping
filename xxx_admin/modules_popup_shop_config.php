<?php
/**
 * zencart mod
 * SIMEON MLADENOV
*/

include(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/shipping/econt.php');  
?>

<div id="dialog-shopconfig-form" title="<?php echo MODULE_SHIPPING_ECONT_CONFIG_SHOP_TITLE_TEXT; ?>">
	<form>
		<fieldset>
			<table cellpadding="3" cellspacing="1" style="width: 100%;">
				<tr>
					<td style="width: 45%;"><label for="eol_username"><?php echo MODULE_SHIPPING_ECONT_EOL_USER_TEXT; ?></label></td>
					<td style="width: 65%;"><input type="text" name="eol_username" id="eol_username" class="text ui-widget-content ui-corner-all" value="<?php if((isset($eol_username)) && ($eol_username!="")) echo $eol_username; ?>" /></td>
				</tr>
				<tr>
					<td style="width: 55%;"><label for="eol_password"><?php echo MODULE_SHIPPING_ECONT_EOL_PASSWORD_TEXT; ?></label></td>
					<td style="width: 45%;"><input type="password" name="eol_password" id="eol_password" value="<?php if((isset($eol_password)) && ($eol_password!="")) echo $eol_password; ?>" class="text ui-widget-content ui-corner-all" /></td>
				</tr>
				<tr>
					<td style="width: 45%;"><label for="deliver_to"><?php echo MODULE_SHIPPING_ECONT_DELIVER_TO_TEXT; ?></label></td>
					<td style="width: 65%;">
						<select name="deliver_to" id="deliver_to">
							<option value="both" <?php if($deliver_to == 'both') echo 'selected="selected"'; ?>><?php echo MODULE_SHIPPING_ECONT_DELIVER_TO_BOTH_TEXT; ?></option>
							<option value="door" <?php if($deliver_to == 'door') echo 'selected="selected"'; ?>><?php echo MODULE_SHIPPING_ECONT_DELIVER_TO_DOOR_TEXT; ?></option>
							<option value="office" <?php if($deliver_to == 'office') echo 'selected="selected"'; ?>><?php echo MODULE_SHIPPING_ECONT_DELIVER_TO_OFFICE_TEXT; ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<td style="width: 45%;"><label for="validate_full_client_address"><?php echo MODULE_SHIPPING_ECONT_VALIDATE_FULL_CLIENT_ADDRESS_TEXT; ?></label></td>
					<td style="width: 65%;">
						<select name="validate_full_client_address" id="validate_full_client_address">
							<option value="yes"<?php if($validate_full_client_address == 'yes') echo 'selected="selected"'; ?>><?php echo MODULE_SHIPPING_ECONT_VALIDATE_FULL_CLIENT_ADDRESS_YES_TEXT; ?></option>
							<option value="cityzip"<?php if($validate_full_client_address == 'cityzip') echo 'selected="selected"'; ?>><?php echo MODULE_SHIPPING_ECONT_VALIDATE_FULL_CLIENT_ADDRESS_CITYZIP_TEXT; ?></option>
							<option value="no"<?php if($validate_full_client_address == 'no') echo 'selected="selected"'; ?>><?php echo MODULE_SHIPPING_ECONT_VALIDATE_FULL_CLIENT_ADDRESS_NO_TEXT; ?></option>
						</select>
					</td>
				</tr>
			</table>			
		</fieldset>
	</form>
</div>


<div id="dialog-cities-update" title="<?php echo MODULE_SHIPPING_CITIES_UPDATE_TITLE_TEXT; ?>">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo MODULE_SHIPPING_CITIES_UPDATE_INFO_TEXT; ?></p>
	<div id="progressbar"></div>
</div>


<div id="dialog-sender-config" title="<?php echo MODULE_SHIPPING_SENDER_CONFIG_TITLE_TEXT; ?>">
	<div id="progressbar_config"></div>
	<table cellpadding="3" cellspacing="1" style="width: 100%; padding-top: 30px;">
		<tr>
			<td colspan="2">
				<table cellpadding="0" cellspacing="0" border="0">
					<tr>
						<td><b><?php echo MODULE_SHIPPING_SENDER_CONFIG_SENDER_TEXT; ?></b></td>
						<td><div id="sender-owner"></div></td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td style="width: 50%;" valign="top">
				<table cellpadding="2" cellspacing="5" border="0">
					<tr>
						<td style="width: 100%;" valign="top">
							<fieldset style="width: 100%;" class="sender-config-form">
								<legend><?php echo MODULE_SHIPPING_SENDER_CONFIG_LEGEND_CONTACTS_TEXT; ?></legend>
								<table cellpadding="2" cellspacing="5" border="0">
									<tr>
										<td><?php echo MODULE_SHIPPING_SENDER_CONFIG_LEGEND_CONTACTS_NAME_TEXT; ?></td>
										<td><input type="text" name="vendor_sname" id="vendor_sname" value="" size="30" /></td>
									</tr>
									<tr>
										<td><?php echo MODULE_SHIPPING_SENDER_CONFIG_LEGEND_CONTACTS_NAMECONTACT_TEXT; ?></td>
										<td><input type="text" name="vendor_contact_name" id="vendor_contact_name" value="" size="30" /></td>
									</tr>
									<tr>
										<td><?php echo MODULE_SHIPPING_SENDER_CONFIG_LEGEND_CONTACTS_PHONE_TEXT; ?></td>
										<td><input type="text" name="vendor_contact_phone" id="vendor_contact_phone" value="" size="30" /></td>
									</tr>
								</table>
							</fieldset>
						</td>
					</tr>
					<tr>
						<td style="width: 100%;" valign="top">
							<fieldset style="width: 100%;" class="sender-config-form">
							<legend><?php echo MODULE_SHIPPING_SENDER_CONFIG_LEGEND_ADDRESS_TEXT; ?></legend>
							<table cellpadding="2" cellspacing="5" border="0">
								<tr>
									<tr>
										<td><?php echo MODULE_SHIPPING_SENDER_CONFIG_ADDRESS_COUNTRY_TEXT; ?></td>
										<td>
											<select name="vendor_country" id="vendor_country">
												<option value="BGR">Bulgaria</option>
											</select>
										</td>
									</tr>
									<tr>
										<td><?php echo MODULE_SHIPPING_SENDER_CONFIG_ADDRESS_CITY_TEXT; ?></td>
										<td>
											<input type="text" name="vendor_city" id="vendor_city" value="" size="30" />
											<input type="hidden" name="cityid" id="cityid" value="" />
										</td>
									</tr>
									<tr>
										<td><?php echo MODULE_SHIPPING_SENDER_CONFIG_ADDRESS_ZIP_TEXT; ?></td>
										<td><input type="text" name="vendor_zip" id="vendor_zip" value="" size="30" /></td>
									</tr>
									<tr>
										<td><?php echo MODULE_SHIPPING_SENDER_CONFIG_ADDRESS_QUARTER_TEXT; ?></td>
										<td><input type="text" name="vendor_quarter" id="vendor_quarter" value="" size="30" /></td>
									</tr>
									<tr>
										<td><?php echo MODULE_SHIPPING_SENDER_CONFIG_ADDRESS_STREET_TEXT; ?></td>
										<td><input type="text" name="vendor_street" id="vendor_street" value="" size="30" /></td>
									</tr>
									<tr>
										<td><?php echo MODULE_SHIPPING_SENDER_CONFIG_ADDRESS_STREET_NUM_TEXT; ?></td>
										<td><input type="text" name="vendor_street_num" id="vendor_street_num" value="" size="30" /></td>
									</tr>
									<tr>
										<td><?php echo MODULE_SHIPPING_SENDER_CONFIG_ADDRESS_BLOCK_TEXT; ?></td>
										<td><input type="text" name="vendor_street_bl" id="vendor_street_bl" value="" size="30" /></td>
									</tr>
									<tr>
										<td><?php echo MODULE_SHIPPING_SENDER_CONFIG_ADDRESS_ENTRY_TEXT; ?></td>
										<td><input type="text" name="vendor_street_vh" id="vendor_street_vh" value="" size="30" /></td>
									</tr>
									<tr>
										<td><?php echo MODULE_SHIPPING_SENDER_CONFIG_ADDRESS_FLOOR_TEXT; ?></td>
										<td><input type="text" name="vendor_street_et" id="vendor_street_et" value="" size="30" /></td>
									</tr>
									<tr>
										<td><?php echo MODULE_SHIPPING_SENDER_CONFIG_ADDRESS_APPARTMENT_TEXT; ?></td>
										<td><input type="text" name="vendor_street_ap" id="vendor_street_ap" value="" size="30" /></td>
									</tr>
									<tr>
										<td><?php echo MODULE_SHIPPING_SENDER_CONFIG_ADDRESS_OTHER_TEXT; ?></td>
										<td><textarea name="vendor_street_other" id="vendor_street_other" cols="25" rows="3"></textarea></td>
									</tr>
								</tr>
							</table>
							</fieldset>
						</td>
					</tr>
				</table>
			</td>
			<td style="width: 50%;" valign="top">
				<table cellpadding="2" cellspacing="5" border="0">
					<tr>
						<td style="width: 100%;">
							<fieldset style="width: 100%;" class="sender-config-form">
								<legend><?php echo MODULE_SHIPPING_SENDER_CONFIG_LEGEND_SETTINGS_TEXT; ?></legend>
								<table cellpadding="2" cellspacing="9" border="0">
									<tr>
										<td style="width: 60%;"><?php echo MODULE_SHIPPING_SENDER_CONFIG_SETTINGS_DELIVER_FROM_TEXT; ?></td>
										<td style="width: 40%;">
											<select name="vendor_deliver_from" id="vendor_deliver_from">
												<option value="DOOR"><?php echo MODULE_SHIPPING_SENDER_CONFIG_SETTINGS_DELIVER_FROM_DOOR_TEXT; ?></option>
												<option value="OFFICE"><?php echo MODULE_SHIPPING_SENDER_CONFIG_SETTINGS_DELIVER_FROM_OFFICE_TEXT; ?></option>
											</select>
										</td>
									</tr>
									<tr>
										<td><?php echo MODULE_SHIPPING_SENDER_CONFIG_SETTINGS_ENABLE_DC_TEXT; ?></td>
										<td>
											<select name="vendor_enable_dc" id="vendor_enable_dc">
												<option value="OFF"><?php echo MODULE_SHIPPING_SENDER_CONFIG_SETTINGS_ENABLE_DC_OFF_TEXT; ?></option>
												<option value="ON"><?php echo MODULE_SHIPPING_SENDER_CONFIG_SETTINGS_ENABLE_DC_ON_TEXT; ?></option>
												<option value="PLUS"><?php echo MODULE_SHIPPING_SENDER_CONFIG_SETTINGS_ENABLE_DC_PLUS_TEXT; ?></option>
											</select>
										</td>
									</tr>
									<tr>
										<td><?php echo MODULE_SHIPPING_SENDER_CONFIG_SETTINGS_PAYMENT_METHOD_TEXT; ?></td>
										<td>
											<select name="vendor_payment_method" id="vendor_payment_method">
												<option value="CASH"><?php echo MODULE_SHIPPING_SENDER_CONFIG_SETTINGS_PAYMENT_METHOD_CASH_TEXT; ?></option>
												<option value="CREDIT"><?php echo MODULE_SHIPPING_SENDER_CONFIG_SETTINGS_PAYMENT_METHOD_CREDIT_TEXT; ?></option>
											</select>
										</td>
									</tr>
									<tr>
										<td><?php echo MODULE_SHIPPING_SENDER_CONFIG_SETTINGS_CREDIT_NUM_TEXT; ?></td>
										<td><input type="text" name="vendor_credit_num" id="vendor_credit_num" value="" size="18" disabled="disabled" /></td>
									</tr>
									<tr>
										<td><?php echo MODULE_SHIPPING_SENDER_CONFIG_SETTINGS_AGREEMENT_NUM_TEXT; ?></td>
										<td><input type="text" name="vendor_cd_agreement_num" id="vendor_cd_agreement_num" value="" size="18" /></td>
									</tr>
									<tr>
										<td><?php echo MODULE_SHIPPING_SENDER_CONFIG_SETTINGS_SHARE_SUM_TEXT; ?></td>
										<td><input type="text" name="vendor_share_sum" id="vendor_share_sum" value="" size="18" /></td>
									</tr>
									<tr>
										<td><?php echo MODULE_SHIPPING_SENDER_CONFIG_SETTINGS_OC_ENABLE_TEXT; ?></td>
										<td>
											<select name="vendor_enable_oc" id="vendor_enable_oc">
												<option value="0"<?php if($vendor_enable_oc == '0') echo 'selected="selected"'; ?>><?php echo MODULE_SHIPPING_SENDER_CONFIG_SETTINGS_OC_ENABLE_NO_TEXT; ?></option>
												<option value="1"<?php if($vendor_enable_oc == '1') echo 'selected="selected"'; ?>><?php echo MODULE_SHIPPING_SENDER_CONFIG_SETTINGS_OC_ENABLE_CUSTOMER_TEXT; ?></option>
												<option value="2"<?php if($vendor_enable_oc == '2') echo 'selected="selected"'; ?>><?php echo MODULE_SHIPPING_SENDER_CONFIG_SETTINGS_OC_ENABLE_YES_TEXT; ?></option>
											</select>
										</td>
									</tr>
									<tr>
										<td><?php echo MODULE_SHIPPING_SENDER_CONFIG_SETTINGS_PRIORITY_TEXT; ?></td>
										<td>
											<select name="vendor_priority_hour" id="vendor_priority_hour">
												<option value="0"<?php if($vendor_priority_hour == '0') echo 'selected="selected"'; ?>><?php echo MODULE_SHIPPING_SENDER_CONFIG_SETTINGS_PRIORITY_NO_TEXT; ?></option>
												<option value="1"<?php if($vendor_priority_hour == '1') echo 'selected="selected"'; ?>><?php echo MODULE_SHIPPING_SENDER_CONFIG_SETTINGS_PRIORITY_YES_TEXT; ?></option>
											</select>
										</td>
									</tr>
									<tr>
										<td><?php echo MODULE_SHIPPING_SENDER_CONFIG_SETTINGS_PAY_AFTER_ACCEPT_TEXT; ?></td>
										<td>
											<select name="vendor_pay_after_accept" id="vendor_pay_after_accept">
												<option value="0"><?php echo MODULE_SHIPPING_SENDER_CONFIG_SETTINGS_PAY_AFTER_ACCEPT_NO_TEXT; ?></option>
												<option value="1"><?php echo MODULE_SHIPPING_SENDER_CONFIG_SETTINGS_PAY_AFTER_ACCEPT_YES_TEXT; ?></option>
												<option value="2"><?php echo MODULE_SHIPPING_SENDER_CONFIG_SETTINGS_PAY_AFTER_ACCEPT_TEST_TEXT; ?></option>
											</select>
										</td>
									</tr>
									<tr>
										<td><?php echo MODULE_SHIPPING_SENDER_CONFIG_SETTINGS_SMS_NO_TEXT; ?></td>
										<td><input type="text" name="vendor_sms_no" id="vendor_sms_no" value="" maxlength="10" size="18" /></td>
									</tr>
									<tr>
										<td><?php echo MODULE_SHIPPING_SENDER_CONFIG_SETTINGS_INVOICE_BEFORE_CD_TEXT; ?></td>
										<td>
											<select name="vendor_invoice_before_pay_CD" id="vendor_invoice_before_pay_CD">
												<option value="0"><?php echo MODULE_SHIPPING_SENDER_CONFIG_SETTINGS_INVOICE_BEFORE_CD_NO_TEXT; ?></option>
												<option value="1"><?php echo MODULE_SHIPPING_SENDER_CONFIG_SETTINGS_INVOICE_BEFORE_CD_YES_TEXT; ?></option>
											</select>
										</td>
									</tr>
									<tr>
										<td><?php echo MODULE_SHIPPING_SENDER_CONFIG_SETTINGS_INSTRUCTION_RETURNS_TEXT; ?></td>
										<td>
											<select style="width: 135px;" name="vendor_instruction_returns" id="vendor_instruction_returns">
												<option value="0"><?php echo MODULE_SHIPPING_SENDER_CONFIG_SETTINGS_INSTRUCTION_RETURNS_NO_TEXT; ?></option>
												<option value="shipping_returns"><?php echo MODULE_SHIPPING_SENDER_CONFIG_SETTINGS_INSTRUCTION_RETURNS_SHIPPING_RETURNS_TEXT; ?></option>
												<option value="returns"><?php echo MODULE_SHIPPING_SENDER_CONFIG_SETTINGS_INSTRUCTION_RETURNS_RETURNS_TEXT; ?></option>
											</select>
										</td>
									</tr>
								</table>
							</fieldset>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</div>