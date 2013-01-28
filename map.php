<?php
date_default_timezone_set("Europe/Vilnius");
require('function.php');
include("app/models/Afighter.php");
//ban

include("const.php");

//klaidu rodymas:
error_reporting(E_ALL | E_STRICT);
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);

//nustatom zend framework dira
set_include_path(
				PATH . '/app/models/' . PATH_SEPARATOR .
				PATH . '/app/views/filters/' . PATH_SEPARATOR .
				get_include_path());

//------funkcijos ir klases------------------------

class bar extends ImagickDraw {
	public function __construct($x, $y, $w, $h, $dalis, $border, $background, $bar) {
		$this->SetFillColor($background);
		$this->SetStrokeColor($border);

		$this->rectangle($x,$y, $x+$w-1, $y+$h-1);

		$barpiece=($w-2)*$dalis;

		if ($barpiece>($w-2)/100) {
			$this->SetStrokeOpacity(0);
			$this->SetFillcolor($bar);

			$this->rectangle($x+1,$y+1, $x+$barpiece, $y+$h-2);
		}
	}
}

class fieldinfo {
	public $side=null;
	public $player=null;
	public $ob=null;
	public $dead=null;

	public function __construct($side=null, $player=null, $ob=null, $dead=null) {
		$this->side=$side;
		$this->player=$player;
		$this->ob=$ob;
		$this->dead=$dead;
	}
}

class char_img extends Imagick{
	private $side="L";

	public function __contruct($img) {
		$this->readImage($img);
	}

	public function turnside($side) {
		if (!in_array($side, array("R", "L")))
			throw new Exception("Blogai nurodyta puse");

		if ($side!=$this->side) {
			$this->flopImage();
			$this->side=$side;
		}
	}
}

//------duomenys------------------------
$aid=$_GET['mapid'];
$myid=$_GET['player'];

$data=apc_fetch("atackInfo_{$myid}_{$aid}");
if (!$data)
	die("Klaida");

$atack=$data['info'];

$textura="img/battle_ground.png";

$field_h=50;
$field_w=80;

$field_x=$atack->width;
$field_y=$atack->height;

$aukstis=($field_y*2+1)*$field_h/2;
$plotis=($field_w/4*3)*$field_x+$field_w/4;

list($plotis, $aukstis)=count_mapsize($field_x, $field_y, $field_w, $field_h);

$img_rjal="img/battle_rjal_1.png";
$img_man="img/battle_man_1.png";

//------cia sudarom objekto mapo duomenis------------------------

$map=array();

foreach ($data['fighters'] as $v) {
	$dead=($v->dead=='Y');
	$map[$v->x][$v->y]=new fieldinfo($v->side, $v, null, $dead);
	if ($v->uid==$myid)
		$myside=$v->side;
}

//------Generuojam paveiksliuka------------------------
$image=new Imagick();
$image->newImage($plotis, $aukstis, new ImagickPixel('black'));

//------texturinam------------------------
$texture=new Imagick();
$texture->readImage($textura);
$tw=$texture->getImageWidth();
$th=$texture->getImageHeight();

for ($x=0; $x<$plotis; $x+=$tw) {
	for ($y=0; $y<=$aukstis; $y+=$th) {
		$image->compositeImage($texture, Imagick::COMPOSITE_DEFAULT, $x, $y);
	}
}
//------Braizom laukelius------------------------
$man_img=new char_img($img_man);
$rjal_img=new char_img($img_rjal);
for ($x=1; $x<=$field_x;$x++) {
	for ($y=1; $y<=$field_y; $y++) {
		$ox=$field_w/4*3*($x-1);
		$oy=($x%2==0)? $field_h*($y-1) + $field_h/2 : $field_h*($y-1);

		$w=$field_w;
		$h=$field_h;

		$points=array(
			array( "x" => $ox+$w/4-1	, "y" =>$oy			),
			array( "x" => $ox+$w/4*3-1	, "y" =>$oy			),
			array( "x" => $ox+$w-1		, "y" =>$oy+$h/2-1	),
			array( "x" => $ox+$w/4*3-1	, "y" =>$oy+$h-1	),
			array( "x" => $ox+$w/4-1	, "y" =>$oy+$h-1	),
			array( "x" => $ox-1			, "y" =>$oy+$h/2-1  )
		);

		//------spalvos------------------------
		$opacity=0.3;
		if (isset($map[$x][$y])) {
			if (isset($map[$x][$y]->player)) {
				$color=($map[$x][$y]->side==$myside)? "green" : "red";
				if ($map[$x][$y]->player->id==$myid) {
					$color="blue";
				}
			} elseif (isset($map[$x][$y]->ob)) {
				$color="black";
				$opacity=0.85;
			}
		} else {
			$color="black";
		}
		$laukeliai=new ImagickDraw();
		$laukeliai->setFillColor(new ImagickPixel($color));
		$laukeliai->setFillOpacity($opacity);

		$laukeliai->setStrokeColor(new ImagickPixel("white"));
		$laukeliai->setStrokeOpacity(0.95);
		$laukeliai->setStrokeAntialias(true);

		$laukeliai->polygon($points);
		$image->drawImage($laukeliai);
		unset($laukeliai);

		if (isset($map[$x][$y]->player)) {
			$center_x=$ox+($field_w/2);
			$center_y=$oy+($field_h/2);

			$mode=($map[$x][$y]->dead)? imagick::COMPOSITE_DARKEN : imagick::COMPOSITE_DEFAULT;

			if ($map[$x][$y]->player->race==RACE_MAN) {
				$user_y=$oy+$field_h-10-$man_img->getImageHeight();
				$user_x=$center_x-$man_img->getImageWidth()/2;
				$man_img->turnside($map[$x][$y]->side);
				$image->compositeImage($man_img,$mode,$user_x,$user_y);
			} else {
				$user_y=$oy+$field_h-10-$rjal_img->getImageHeight();
				$user_x=$center_x-$rjal_img->getImageWidth()/2;
				$rjal_img->turnside($map[$x][$y]->side);
				$image->compositeImage($rjal_img,$mode,$user_x,$user_y);
			}

			$hp=($map[$x][$y]->dead)? 0 : $map[$x][$y]->player->HP;
			$maxhp=$map[$x][$y]->player->maxHP;

			$rate=$hp/$maxhp;

			$bar_x=$center_x-$field_w/4;
			$bar_y=$oy+$field_h-8;

			$bar_h=6;
			$bar_w=$field_w/2;
			$image->drawImage(new bar($bar_x,$bar_y,$bar_w,$bar_h, $rate, new ImagickPixel("black"), new ImagickPixel("gray"), new ImagickPixel("green")));
		}
	}
}
$man_img->clear();
$rjal_img->clear();
$man_img->destroy();
$rjal_img->destroy();
unset($man_img);
unset($rjal_img);

$image->flattenImages();

$image->setCompression(Imagick::COMPRESSION_JPEG);
$image->setCompressionQuality(95);
$image->setImageFormat('jpeg');

header("Content-type: image/jpeg");
echo $image;

$image->clear();
$image->destroy();
?>