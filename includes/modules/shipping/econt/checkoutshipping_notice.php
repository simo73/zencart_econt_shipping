<?php
/**
 * zencart mod
 * SIMEON MLADENOV
*/
global $db; 
$vendorcfg = $db->Execute("SELECT * FROM ".MODULE_SHIPPING_ECONT_DB_VENDOR_INFO." WHERE vendor_id = 1");

$notice = '<table border="0" width="100%" cellspacing="0" cellpadding="2">'."\n";

if($vendorcfg->fields['vendor_enable_dc'] == 'ON') {
	$notice .= '<tr>'."\n";
	$notice .= '	<td class="main"><strong><sup style="color:red;">*</sup></strong>&nbsp;В куриерската услуга е включена обратна разписка!</td>'."\n";
	$notice .= '</tr>'."\n";
} else if ($vendorcfg->fields['vendor_enable_dc'] == 'PLUS') {
	$notice .= '<tr>'."\n";
	$notice .= '	<td class="main"><strong><sup style="color:red;">*</sup></strong>&nbsp;В куриерската услуга е включена обратна-стокова разписка!</td>'."\n";
	$notice .= '</tr>'."\n";
}


if($vendorcfg->fields['vendor_pay_after_accept'] == '1') {
	$notice .= '<tr>'."\n";
	$notice .= '	<td class="main"><strong><sup style="color:red;">*</sup></strong>&nbsp;Търговецът разрешава преглед на пратката преди плащане на наложен платеж!</td>'."\n";
	$notice .= '</tr>'."\n";
} else if ($vendorcfg->fields['vendor_pay_after_accept'] == '2') {
	$notice .= '<tr>'."\n";
	$notice .= '	<td class="main"><strong><sup style="color:red;">*</sup></strong>&nbsp;Търговецът разрешава преглед и тест на пратката преди плащане на наложен платеж!</td>'."\n";
	$notice .= '</tr>'."\n";
}

if($vendorcfg->fields['vendor_invoice_before_pay_CD'] == '1') {
	$notice .= '<tr>'."\n";
	$notice .= '	<td class="main"><strong><sup style="color:red;">*</sup></strong>&nbsp;Търговецът позволява получаване на фактура преди заплащане на наложения платеж!</td>'."\n";
	$notice .= '</tr>'."\n";
}


if($notice == '<table border="0" width="100%" cellspacing="0" cellpadding="2">') $notice .= '<tr><td></td></tr>';
$notice .= '</table>';
?>
<tr><td><?php echo zen_draw_separator('pixel_trans.gif', '100%', '10'); ?></td></tr>
<tr>
	<td>
		<table border="0" width="100%" cellspacing="0" cellpadding="2">
			<tr>
				<td class="main"><b><?php echo MODULE_SHIPPING_ECONT_TEXT_NOTICE; ?></b></td>
			</tr>
		</table>
	</td>
</tr>
<tr>
	<td>
		<table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
			<tr class="infoBoxContents">
				<td class="main"><?php echo $notice; ?></td>
			</tr>
		</table>
	</td>
</tr>
<tr><td><?php echo zen_draw_separator('pixel_trans.gif', '100%', '10'); ?></td></tr>
<tr>
	<td>
		<table border="0" width="100%" cellspacing="0" cellpadding="2">
			<tr>
				<td class="main"><b><?php echo MODULE_SHIPPING_ECONT_TEXT_NOTICE_SEARCH; ?></b></td>
			</tr>
		</table>
	</td>
</tr>
<tr>
	<td>
		<table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
			<tr class="infoBoxContents">
				<td class="main">
				<?php
				$checkpoint_map = $db->Execute("SELECT entry_street_address, entry_city FROM ".TABLE_ADDRESS_BOOK." WHERE address_book_id = ".(int)$sendto."");
				$addressbook_street = preg_replace("/\|/", " ", $checkpoint_map->fields['entry_street_address']);
				$addressbook_city = $checkpoint_map->fields['entry_city'];
				
				
				?>
				<?php echo '<a href="javascript:popupWindow(\'' . zen_href_link('econt_search_popup.php', 'street='.$addressbook_street.'&city='.$addressbook_city) . '\')">' . zen_image(DIR_WS_IMAGES . 'infobox/nearest_office_inactive.png', '', '138', '43', 'onmouseover="this.src=\''.DIR_WS_IMAGES.'infobox/nearest_office_active.png'.'\'" onmouseout="this.src=\''.DIR_WS_IMAGES.'infobox/nearest_office_inactive.png'.'\'" hspace="5" vspace="5"') . '</a>'; ?>
				</td>
			</tr>
		</table>
	</td>
</tr>
<tr><td><?php echo zen_draw_separator('pixel_trans.gif', '100%', '10'); ?></td></tr>