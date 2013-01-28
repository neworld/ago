<?php
if (empty($_GET['num'])) {
    exit();
} elseif (!is_numeric($_GET['num'])) {
    exit();
} elseif ($_GET['num'] < 0 || $_GET['num'] > 500) {
    exit();
}

header ("Content-type: image/png");
header ("Cache-Control: max-age=3600, must-revalidate");

$TMP_DIR='/www/agonija/cache/bank';
$TMP_DIR='/dev/shm/agonija_cache_bank';

if (!file_exists($TMP_DIR)) {
	mkdir($TMP_DIR,0777,true);
}

if (file_exists("$TMP_DIR/{$_GET['num']}.png")) {
	echo file_get_contents("$TMP_DIR/{$_GET['num']}.png");
	exit;
}

$img = imagecreatefrompng("img/item/free2.png");
//$img=ImageCreate(200,200);

//$color2=imagecolorallocate ($img, 0x00, 0x00, 0x00);
$coloras=imagecolorallocate ($img, 0xD6, 0xEC, 0xF8);

$font='MyriadPro-It.otf';
$x=($_GET['num']>9)? 5 : 14;
$size=($_GET['num']<100)? 25 : 17;
$y=($_GET['num']<100)? 32 : 29;

ImageFtText($img,$size,0,$x,$y,$coloras,$font,$_GET['num']);
imagepng($img);
imagepng($img,"$TMP_DIR/{$_GET['num']}.png");
?>