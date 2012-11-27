<?php
/**
 * zencart mod
 * SIMEON MLADENOV
*/

require('includes/application_top.php');
require('includes/configure.php');
$dop_dop = $_SESSION['securityToken'];
$lang_dop = $_SESSION['languages_code'];
$test1 = '';
$return_arr = array();

$dbhost = DB_SERVER;
$dbuser = DB_SERVER_USERNAME;
$dbpass = DB_SERVER_PASSWORD;
$dbname = DB_DATABASE;

$conn = mysql_connect($dbhost, $dbuser, $dbpass) or die ('Error connecting to mysql');
mysql_select_db($dbname);
/* If connection to database, run sql statement. */
if ($conn)
{
	mysql_query("SET NAMES utf8");
	$fetch1 = mysql_query("SELECT * FROM street_temp WHERE user_id = '" . $dop_dop . "'"); 

	/* Retrieve and store in array the results of the query.*/

	while ($row = mysql_fetch_array($fetch1, MYSQL_ASSOC)) {
		$test1 = $row['temp_vars'];
	}
	
	//$test1 = '35';
	//if ($lang_dop == 'bg') { 
     mysql_query("SET NAMES utf8");
     $fetch = mysql_query("SELECT * FROM econt_cities_quarters_table where id_city like " . $test1 . " AND (quarter_name Like '%" . $_GET['term'] . "%' OR quarter_name_en Like '%" . $_GET['term'] . "%')"); 
//	}
//	else {
//	mysql_query("SET NAMES utf8");
//     $fetch = mysql_query("SELECT * FROM econt_cities_streets_table where street_name_en like '%" . $_GET['term'] . "%' and id_city like " . $test1 . ""); 
	
	//}

	/* Retrieve and store in array the results of the query.*/

	while ($row = mysql_fetch_array($fetch, MYSQL_ASSOC)) {
		$row_array['label'] = $row['quarter_name'];
		$row_array['value'] = $row['quarter_name'];

        array_push($return_arr,$row_array);
    }

}
/* Free connection resources. */
mysql_close($conn);

/* Toss back results as json encoded array. */
echo json_encode($return_arr);


?>