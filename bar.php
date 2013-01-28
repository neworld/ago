<?
header ("Content-type: image/png"); 
function getrgb($color,& $r, & $g, & $b) {
	$r=hexdec($color[0].$color[1]);
	$g=hexdec($color[2].$color[3]);
	$b=hexdec($color[4].$color[5]);
}
$w=$_GET['w'];
$h=$_GET['h'];
$bgcolor=$_GET['bgc'];
$bcolor=$_GET['bc'];
$color=$_GET['c'];
$text=$_GET['text'];
$tcolor=$_GET['tc'];
$font_size=$_GET['size'];
$position=$_GET['p'];
$all=$_GET['all'];
$image=ImageCreate($w,$h);
getrgb($bcolor,$r,$g,$b);
$sbcolor=ImageColorAllocate($image,$r, $g, $b);
getrgb($bgcolor,$r,$g,$b);
$sbgcolor=ImageColorAllocate($image,$r, $g, $b);
getrgb($color,$r,$g,$b);
$scolor=ImageColorAllocate($image,$r, $g, $b);
getrgb($tcolor,$r,$g,$b);
$stcolor=ImageColorAllocate($image,$r, $g, $b);

ImageFill($image, 0, 0, $sbgcolor);

if ($all>0) {
	$position=round($position/$all*100);
}

$text=str_replace('-p-','%',$text);

imagefilledrectangle($image,1,1,round($w * $position / 100),$h-2,$scolor);

ImageLine($image, 0, 0, $w, 0, $sbcolor);
ImageLine($image, 0, $h-1,$w, $h-1, $sbcolor);
ImageLine($image, 0, 0, 0, $h-1, $sbcolor);
ImageLine($image, $w-1, 0, $w-1, $h-1, $sbcolor);

imagestring($image, $font_size, round(($w/2)-((strlen($text)*imagefontwidth($font_size))/2), 1), round(($h/2)-(imagefontheight($font_size)/2)), $text, $stcolor);

imagepng($image);
?>