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
$dop_del = $_SESSION['securityToken'];
/* If connection to database, run sql statement. */
if ($conn)
{
    mysql_query("SET NAMES utf8");
	mysql_query("DELETE FROM street_temp WHERE user_id = '" .$dop_del. "'");
	
}

mysql_close($conn);
?>	