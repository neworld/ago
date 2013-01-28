<?php
$missions = include("/www/agonija/app/data/misssionSimple.php");
$skills = include("/www/agonija/app/data/skills_DB.php");

?>
{| border="1" cellpadding="5" cellspacing="0"
!Pavadinimas
!Aprašymas
!Mažiausias lygis
!Sudėtingumas
!Reikalavimas
!Bazinis uždarbis
!Ar galima pusiau įvykdyti?
<?

foreach ($missions as $v) {
     $reg = array();
     foreach ($v['skill'] as $key => $vv) {
        $reg[] = "{$skills[$key]['NAME']}: " . round($vv * 100) . "%";
     }
     
     $sud = $v['dificulity'] . ' - ' . ($v['dificulity']+2);
     
     $req = implode("<br />", $reg);
     
     $baze = "Agonos: {$v['money']}<br />Patirtis: {$v['exp']}";
     
     $galima = ($v['half'])? "TAIP" : "NE";
     
     echo "|-\n";
     echo "|{$v['title']}\n|{$v['des']}\n|{$v['minlvl']}\n|$sud\n|nowrap | $req\n|$baze\n|$galima\n";
}  

?>
|}
