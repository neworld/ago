<?
echo "ROBOT STARTED\n";
define('DATE_FORMAT','Y m d H:i:s');
define('DATE_FORMAT2','Y-m-d H:i:s');

$data=include('/www/agonija/set.php');
$params=$data['database']['params'];

$db=mysql_connect($params['host'],$params['username'],$params['password']) or die("no connect to db");
mysql_select_db($params['dbname']) or die("no select db");

$eil=mysql_query("SELECT `id`, `from`, `item` FROM items_send WHERE `date`<='".date(DATE_FORMAT2,time()-3600*24)."'");

echo "FOUNDED: ".mysql_num_rows($eil)."\n";

while ($e=mysql_fetch_assoc($eil)) {
	$id=$e['id'];
	$item=$e['item'];
	$owner=$e['from'];

	echo "ID: $id ";

	mysql_query("UPDATE `items` SET `owner` = '$owner' WHERE id='$item'");
	if (mysql_affected_rows()==1) {
		echo " item #$item changed to owner $owner ";
		mysql_query("DELETE FROM items_send WHERE id='$id'");
		if (mysql_affected_rows()==0) {
			die (" NOT DELETE\n   EXECUTE STOP");
		} else {
			echo " market entry deleted ";
			mysql_query("
				INSERT INTO `events` (`user`,`text`,`date`)
				VALUES (
					$owner, 'Jūms buvo sugražintas daiktas iš privataus turgaus',
					'".date(DATE_FORMAT)."'
				)
			");
			if (mysql_affected_rows()==0) {
				echo " EVENT NOT SEND ";
			}
		}
	} else {
		echo " NOT CHANGED item #$item changed to owner $owner ";
		$ieil=mysql_query("SELECT `owner` FROM `items` WHERE id='$item'");
		if (mysql_num_rows($ieil)==0) {
			$del=true;
			echo " ITEM NOT EXSIST ";
		} else {
			$ie=mysql_fetch_assoc($ieil);
			if ($ie['owner']==$owner) {
				echo " OWNER WAS SETTED ";
				$del=true;
			} else {
				echo " IS#{$ie['owner']} NEED#$owner ";
				$del=false;
			}
		}
		if ($del) {
			mysql_query("DELETE FROM items_send WHERE id='$id'");
			echo " FIXED BROKEN ENTRY #$id ";
		}
	}
	echo "\n";
}
echo "DONE.\n";
?>