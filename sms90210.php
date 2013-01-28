<?php

require_once('WebToPay.php');

try {
    WebToPay::checkResponse($_GET, array(
            'sign_password' => '3890f5be1bd7f5f38f5581ac72a882b9',
            'log'           => 'webtopay.log',
        ));

	$to=$_GET['wp_to'];
	$sms=$_GET['wp_sms'];
	$from=$_GET['wp_from'];
	$amount=$_GET['wp_amount'];
	$currency=$_GET['wp_currency'];
	$country=$_GET['wp_country'];
	$key=$_GET['wp_key'];
	$sid=$_GET['wp_id'];
	define ("NW_TRUE_PAGE",'');
	$SET=include('set.php');

	//include('include/dbconnect.php');

	$params=$SET['database']['params'];

	$db=mysql_connect($params['host'],$params['username'],$params['password']) or die("no connect to db");
	mysql_select_db($params['dbname']) or die("no select db");

	mysql_query("INSERT INTO `sms` (`to`,`sms`,`from`,`amount`,`currency`,`key`,`sid`) VALUES
								('$to','$sms','$from','$amount','$currency','$key','$sid')") or die ("Apmokejimas gautas, taciau ivyko klaida, butinai praneskite adminisitracijai apie iviki si koda: 1x$sid");
	$s=explode(" ",$sms,3);
	$name=$s[1];
	$money=$amount/100;
	if ($to=="57778") { $name=$s[2]; $money=4; }
	mysql_query("UPDATE users SET nano=nano+'$money' WHERE id='$name' or `name` like '$name' LIMIT 1") or die ("Apmokejimas gautas, taciau ivyko klaida, butinai praneskite adminisitracijai apie ivyki si koda: 2x$sid");
	if ((count($s)>1) && (mysql_affected_rows()==0)) {
		echo "OK Apmokejimas gautas, taciau itariame jog blogai ivestas nickas. Butinai praneskite adminisitracijai apie ivyki ir si koda: $sid";
	} else {
		echo "OK Apmokejimas gautas. Prideta $money nanokreditu $name vartotojui. Aciu kad paremete";
	}
}
catch (Exception $e) {
    echo get_class($e).': '.$e->getMessage();
}
?>
