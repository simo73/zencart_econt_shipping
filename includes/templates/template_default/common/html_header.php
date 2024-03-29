<?php
/**
 * zencart mod
 * SIMEON MLADENOV
*/
require(DIR_WS_MODULES . zen_get_module_directory('meta_tags.php'));
/**
 * output main page HEAD tag and related headers/meta-tags, etc
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php echo HTML_PARAMS; ?>>
<head>
<title><?php echo META_TAG_TITLE; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>" />
<meta name="keywords" content="<?php echo META_TAG_KEYWORDS; ?>" />
<meta name="description" content="<?php echo META_TAG_DESCRIPTION; ?>" />
<meta http-equiv="imagetoolbar" content="no" />
<meta name="author" content="The Zen Cart&trade; Team and others" />
<meta name="generator" content="shopping cart program by Zen Cart&trade;, http://www.zen-cart.com eCommerce" />
<?php if (defined('ROBOTS_PAGES_TO_SKIP') && in_array($current_page_base,explode(",",constant('ROBOTS_PAGES_TO_SKIP'))) || $current_page_base=='down_for_maintenance' || $robotsNoIndex === true) { ?>
<meta name="robots" content="noindex, nofollow" />
<?php } ?>
<?php if (defined('FAVICON')) { ?>
<link rel="icon" href="<?php echo FAVICON; ?>" type="image/x-icon" />
<link rel="shortcut icon" href="<?php echo FAVICON; ?>" type="image/x-icon" />
<?php } //endif FAVICON ?>

<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER . DIR_WS_HTTPS_CATALOG : HTTP_SERVER . DIR_WS_CATALOG ); ?>" />
<?php if (isset($canonicalLink) && $canonicalLink != '') { ?>
<link rel="canonical" href="<?php echo $canonicalLink; ?>" />
<?php } ?>
<!-- Econt Shipping Module BOF //-->
<?php
if(MODULE_SHIPPING_ECONT_STATUS == 'True') {
	
	include(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/shipping/econt.php'); 
		 //print_r($_SESSION);
	
?>
<link rel="stylesheet" type="text/css" href="<?php echo HTTP_SERVER . DIR_WS_CATALOG; ?>jquery/jquery.ui.all.css" />
<script language="javascript" src="<?php echo HTTP_SERVER . DIR_WS_CATALOG; ?>jquery/jquery-1.4.3.js"></script>
<script language="javascript" src="<?php echo HTTP_SERVER . DIR_WS_CATALOG; ?>jquery/jquery-ui-1.8.6.custom.min.js"></script>
<script language="javascript" src="<?php echo HTTP_SERVER . DIR_WS_CATALOG; ?>jquery/jquery.bgiframe-2.1.2.js"></script>
<script language="javascript" src="<?php echo HTTP_SERVER . DIR_WS_CATALOG; ?>jquery/ui/jquery.ui.core.js"></script>
<script language="javascript" src="<?php echo HTTP_SERVER . DIR_WS_CATALOG; ?>jquery/ui/jquery.ui.widget.js"></script>
<script language="javascript" src="<?php echo HTTP_SERVER . DIR_WS_CATALOG; ?>jquery/ui/jquery.ui.position.js"></script>
<script language="javascript" src="<?php echo HTTP_SERVER . DIR_WS_CATALOG; ?>jquery/ui/jquery.ui.mouse.js"></script>
<script language="javascript" src="<?php echo HTTP_SERVER . DIR_WS_CATALOG; ?>jquery/ui/jquery.ui.button.js"></script>
<script language="javascript" src="<?php echo HTTP_SERVER . DIR_WS_CATALOG; ?>jquery/ui/jquery.ui.draggable.js"></script>
<script language="javascript" src="<?php echo HTTP_SERVER . DIR_WS_CATALOG; ?>jquery/ui/jquery.ui.resizable.js"></script>
<script language="javascript" src="<?php echo HTTP_SERVER . DIR_WS_CATALOG; ?>jquery/ui/jquery.ui.dialog.js"></script>
<script language="javascript" src="<?php echo HTTP_SERVER . DIR_WS_CATALOG; ?>jquery/ui/jquery.effects.core.js"></script>
<script type="text/javascript">
$.noConflict();
  jQuery(document).ready(function($) {
		
			
			
			$("#econt_city").autocomplete({
				source: "<?php echo HTTP_SERVER . DIR_WS_CATALOG; ?>states.php",
				minLength: 2,
				select: function(event, ui) {
					$('#state_id').val(ui.item.id);
					$('#postcode').val(ui.item.abbrev);
					$('#boza').val(ui.item.id);
				}
			});
			
			
			$('#street_address').click(function() {
                var sval = $("#state_id").val();
			   $.ajax({type:"POST", url:"<?php echo HTTP_SERVER . DIR_WS_CATALOG; ?>submit.php", data:"salsa="+ sval});
            });			

			$('#econt_city').blur(function() {
               var sval = $("#state_id").val();
			   $.ajax({type:"POST", url:"<?php echo HTTP_SERVER . DIR_WS_CATALOG; ?>submit.php", data:"salsa="+ sval});
            });
			
			
			
			
			$("#econt_street_address").autocomplete({		
				source: "<?php echo HTTP_SERVER . DIR_WS_CATALOG; ?>states_abbrev.php",
				minLength: 3,
				select: function(event, ui) {
					$('#state_jd').val(ui.item.value);
					$('#bozka').val(ui.item.value);
				}
			});
			
			$("#econt_suburb").autocomplete({		
				source: "<?php echo HTTP_SERVER . DIR_WS_CATALOG; ?>states_quarter.php",
				minLength: 3,
				select: function(event, ui) {
					$('#state_kd').val(ui.item.value);
					$('#quart').val(ui.item.value);
				}
			});
			
		});

		
</script>
<?php	
} 
//?>
<!-- Econt Shipping Module EOF //-->
<?php
/**
 * load all template-specific stylesheets, named like "style*.css", alphabetically
 */
  $directory_array = $template->get_template_part($template->get_template_dir('.css',DIR_WS_TEMPLATE, $current_page_base,'css'), '/^style/', '.css');
  while(list ($key, $value) = each($directory_array)) {
    echo '<link rel="stylesheet" type="text/css" href="' . $template->get_template_dir('.css',DIR_WS_TEMPLATE, $current_page_base,'css') . '/' . $value . '" />'."\n";
  }
/**
 * load stylesheets on a per-page/per-language/per-product/per-manufacturer/per-category basis. Concept by Juxi Zoza.
 */
  $manufacturers_id = (isset($_GET['manufacturers_id'])) ? $_GET['manufacturers_id'] : '';
  $tmp_products_id = (isset($_GET['products_id'])) ? (int)$_GET['products_id'] : '';
  $tmp_pagename = ($this_is_home_page) ? 'index_home' : $current_page_base;
  if ($current_page_base == 'page' && isset($ezpage_id)) $tmp_pagename = $current_page_base . (int)$ezpage_id;
  $sheets_array = array('/' . $_SESSION['language'] . '_stylesheet',
                        '/' . $tmp_pagename,
                        '/' . $_SESSION['language'] . '_' . $tmp_pagename,
                        '/c_' . $cPath,
                        '/' . $_SESSION['language'] . '_c_' . $cPath,
                        '/m_' . $manufacturers_id,
                        '/' . $_SESSION['language'] . '_m_' . (int)$manufacturers_id,
                        '/p_' . $tmp_products_id,
                        '/' . $_SESSION['language'] . '_p_' . $tmp_products_id
                        );
  while(list ($key, $value) = each($sheets_array)) {
    //echo "<!--looking for: $value-->\n";
    $perpagefile = $template->get_template_dir('.css', DIR_WS_TEMPLATE, $current_page_base, 'css') . $value . '.css';
    if (file_exists($perpagefile)) echo '<link rel="stylesheet" type="text/css" href="' . $perpagefile .'" />'."\n";
  }

/**
 * load printer-friendly stylesheets -- named like "print*.css", alphabetically
 */
  $directory_array = $template->get_template_part($template->get_template_dir('.css',DIR_WS_TEMPLATE, $current_page_base,'css'), '/^print/', '.css');
  sort($directory_array);
  while(list ($key, $value) = each($directory_array)) {
    echo '<link rel="stylesheet" type="text/css" media="print" href="' . $template->get_template_dir('.css',DIR_WS_TEMPLATE, $current_page_base,'css') . '/' . $value . '" />'."\n";
  }

/**
 * load all site-wide jscript_*.js files from includes/templates/YOURTEMPLATE/jscript, alphabetically
 */
  $directory_array = $template->get_template_part($template->get_template_dir('.js',DIR_WS_TEMPLATE, $current_page_base,'jscript'), '/^jscript_/', '.js');
  while(list ($key, $value) = each($directory_array)) {
    echo '<script type="text/javascript" src="' .  $template->get_template_dir('.js',DIR_WS_TEMPLATE, $current_page_base,'jscript') . '/' . $value . '"></script>'."\n";
  }

/**
 * load all page-specific jscript_*.js files from includes/modules/pages/PAGENAME, alphabetically
 */
  $directory_array = $template->get_template_part($page_directory, '/^jscript_/', '.js');
  while(list ($key, $value) = each($directory_array)) {
    echo '<script type="text/javascript" src="' . $page_directory . '/' . $value . '"></script>' . "\n";
  }

/**
 * load all site-wide jscript_*.php files from includes/templates/YOURTEMPLATE/jscript, alphabetically
 */
  $directory_array = $template->get_template_part($template->get_template_dir('.php',DIR_WS_TEMPLATE, $current_page_base,'jscript'), '/^jscript_/', '.php');
  while(list ($key, $value) = each($directory_array)) {
/**
 * include content from all site-wide jscript_*.php files from includes/templates/YOURTEMPLATE/jscript, alphabetically.
 * These .PHP files can be manipulated by PHP when they're called, and are copied in-full to the browser page
 */
    require($template->get_template_dir('.php',DIR_WS_TEMPLATE, $current_page_base,'jscript') . '/' . $value); echo "\n";
  }
/**
 * include content from all page-specific jscript_*.php files from includes/modules/pages/PAGENAME, alphabetically.
 */
  $directory_array = $template->get_template_part($page_directory, '/^jscript_/');
  while(list ($key, $value) = each($directory_array)) {
/**
 * include content from all page-specific jscript_*.php files from includes/modules/pages/PAGENAME, alphabetically.
 * These .PHP files can be manipulated by PHP when they're called, and are copied in-full to the browser page
 */
    require($page_directory . '/' . $value); echo "\n";
  }

// DEBUG: echo '<!-- I SEE cat: ' . $current_category_id . ' || vs cpath: ' . $cPath . ' || page: ' . $current_page . ' || template: ' . $current_template . ' || main = ' . ($this_is_home_page ? 'YES' : 'NO') . ' -->';


?>

</head>
<?php // NOTE: Blank line following is intended: ?>

