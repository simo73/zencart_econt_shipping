<?php
/**
 * zencart mod
 * SIMEON MLADENOV
*/

require('includes/configure.php');
$return_arr = array();
$test = '';
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
	$fetch = mysql_query("SELECT * FROM econt_cities_table where name like '%" . $_GET['term'] . "%' OR name_en like '%" . $_GET['term'] . "%'"); 

	/* Retrieve and store in array the results of the query.*/

	while ($row = mysql_fetch_array($fetch, MYSQL_ASSOC)) {
		$row_array['id'] = $row['id'];
		$row_array['value'] = $row['name'];
		$row_array['abbrev'] = $row['post_code'];
        $test = $row['id'];
        array_push($return_arr,$row_array);
    }

}
/* Insert vars*/

	//mysql_query("SET NAMES utf8");
	//mysql_query("UPDATE street_temp SET temp_vars = '".$test."'"); 
	
/* Free connection resources. */
mysql_close($conn);

/* Toss back results as json encoded array. */
echo json_encode($return_arr);


?>