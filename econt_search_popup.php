<?php
/**
 * zencart mod
 * SIMEON MLADENOV
*/
 

  require('includes/application_top.php');

  require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/modules/shipping/econt.php');
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">
<title><?php echo POPUP_TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="stylesheet.css">
</head>
<body marginwidth="10" marginheight="10" topmargin="10" bottommargin="10" leftmargin="10" rightmargin="10">
<?php
  $info_box_contents = array();
  $info_box_contents[] = array('text' => POPUP_HEADING_SEARCH);

  new infoBoxHeading($info_box_contents, true, true);
	
	$street = $_GET['street'];
	$city = $_GET['city'];
	$iframe_map_url = POPUP_TEXT_SEARCH_1 . '/' . $street . '/' . $city . POPUP_TEXT_SEARCH_2;
	
  $info_box_contents = array();
  $info_box_contents[] = array('text' => $iframe_map_url);

  new infoBox($info_box_contents);
?>

<p class="smallText" align="right"><?php echo '<a href="javascript:window.close()">' . TEXT_CLOSE_WINDOW . '</a>'; ?></p>

</body>
</html>
<?php require('includes/application_bottom.php'); ?>