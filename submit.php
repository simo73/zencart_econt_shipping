<?php
/**
 * zencart mod
 * SIMEON MLADENOV
*/

require('includes/configure.php');
require('includes/application_top.php');

$dbhost = DB_SERVER;
$dbuser = DB_SERVER_USERNAME;
$dbpass = DB_SERVER_PASSWORD;
$dbname = DB_DATABASE;

$conn = mysql_connect($dbhost, $dbuser, $dbpass) or die ('Error connecting to mysql');
mysql_select_db($dbname);
$dop = $_SESSION['securityToken'];
/* If connection to database, run sql statement. */
if (($conn) and ($_POST['salsa']<>''))
{
    mysql_query("SET NAMES utf8");
	mysql_query("INSERT INTO street_temp  (temp_vars , user_id )  VALUES ('" .$_POST['salsa']. "' , '" .$dop. "')");
	
	//mysql_query("UPDATE street_temp SET temp_vars = '".$_POST['salsa']."' , user_id = '" . $dop . "'"); 	
}

mysql_close($conn);
?>	