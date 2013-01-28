<?php
exit;
$add=60;
$max=400;

$data=include('/www/agonija/set.php');
$params=$data['database']['params'];

$db=mysql_connect($params['host'],$params['username'],$params['password']) or die("no connect to db");
mysql_select_db($params['dbname']) or die("no select db");

mysql_query("LOCK TABLES `users` WRITE");

mysql_query("UPDATE users SET `max_hunt`=`max_hunt`+'$add'") or die("no set query");
mysql_query("UPDATE users SET `max_hunt`='$max' where `max_hunt`>'$max'") or die("no set query");

mysql_close($db) or die("no close db");
?>
