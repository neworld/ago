<?php
$data=include('/www/agonija/set.php');
$params=$data['database']['params'];

$db=mysql_connect($params['host'],$params['username'],$params['password']) or die("no connect to db");
mysql_select_db($params['dbname']) or die("no select db");


$eil=mysql_query("select * FROM
(SELECT
  t2.name as `zaidejas`, 
  SUM(SUBSTR(t1.`text`,LOCATE('Padarėte',t1.`text`)+9,(LOCATE(' žalos',t1.`text`)-LOCATE('Padarėte',t1.`text`)-9))) AS `viso_zalos`,
  SUBSTR(t1.`text`,LOCATE('prieš',t1.`text`)+6,(LOCATE(' (lygis:',t1.`text`)-LOCATE('prieš',t1.`text`)-6)) AS mylimiausias
FROM `events` AS t1
JOIN `users` AS t2
  ON t1.user=t2.id
WHERE t1.text like 'Jūsų ataka buvo sėkminga prieš%'
GROUP BY `zaidejas`, mylimiausias
ORDER BY viso_zalos DESC) as SB
GROUP BY SB.zaidejas
ORDER BY SB.zaidejas");

while ($e=mysql_fetch_assoc($eil)) {
	echo "[b]{$e['zaidejas']}[/b] -> [b]{$e['mylimiausias']}[/b] ({$e['viso_zalos']})\n" ;
}

mysql_close($db) or die("no close db");
?>