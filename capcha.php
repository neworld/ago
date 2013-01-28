<?php
header("Content-type: image/png");
include('function.php');
$img=ImageCreate(220,90);
$font='./Bambino.ttf';

$color=ImageColorAllocate($img,0, 0, 0);
ImageFill($img, 0, 0, $color);

$color=ImageColorAllocate($img,255, 255, 255);

if ($_SESSION['set_capcha']) {
	$key=$_SESSION['set_capcha'];
	unset($_SESSION['set_capcha']);
} else {
	//$key=strtoupper(genkey(6));
	$key=rand(100000,999999);
	$_SESSION['capcha']=$key;
}

ImageFtText($img,64,0,15,70,$color,$font,$key);

imagepng($img);  
?>
