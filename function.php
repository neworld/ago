<?php
/**
* @desc gaunam lyties paveiksliuka
* @param String lyties raide
* @return paveiksliuko html
*/      
function get_sex_img($sex) {
	if ($sex=="V") {
		$img="male.png";
	} elseif ($sex=="M") {
		$img="female.png";
	}

	if (isset($img)) {
		return "<img src=\"/img/$img\" width=\"16\" height=\"16\" />";
	} else {
		return '';
	}
}
/**
* @desc gaunam statuso paveiksliuka
* @param User|Array|Object
* @return String
*/
function get_status_img($ob) {
	if ($ob instanceof User) {
		$lasthit = $ob->getLastSeen();
		$isonline = $ob->isonline();
		$ban = false;
	} elseif (is_array($ob)) {
		$lasthit = $ob['startas'];
		$ban = $ob['ban'];
		$ban = @$ob['isonline'];
	} else {
		$lasthit = $ob->startas;
		$ban = $ob->ban;
		try {
			$isonline = $ob->isonline;
		} catch (Exception $e) {
			$isonline = false;
		}
	}

	if ($ban) {
		$state = 5;
	} elseif ($isonline) {
		$state = 1;
	} elseif ($lasthit > time()-3*24*3600) {
		$state = 2;
	} elseif ($lasthit > time()-2*7*24*3600) {
		$state = 3;
	} else {
		$state = 4;
	}

	return "<img title=\"".date(DATE_FORMAT,$lasthit)."\" style=\"vertical-align:middle\" src=\"/img/state-$state.png\" width=\"16\" height=\"16\" />";
}

function lvl($exp) {
	return getlvl($exp,Zend_Registry::get('config')->ratio)+1;
}
function getbit() {
	return Zend_Registry::get('config')->ratio;
}
function max_market_slot($skill) {
	return 10+floor($skill/5);
}
function reindex(& $a,$first=0,$return=false) {
	$temp=array();
	foreach ($a as $v) {
		if(count($temp)==0) {
			$temp[$first]=$v;
		} else {
			$temp[]=$v;
		}
	}
	if ($return) {
		return $temp;
	} else {
		unset($a);
		$a=$temp;
		return true;
	}
}
function compare($a,$b) {
	return ($a<$b);
}
function Quick_sort(&$array,$start,$end,$compare) {  //compare funckijos pirmas argumentas turi buti mazesnis uz antra
	$middle=$array[floor(($start+$end)/2)];
	$i=$start;
	$j=$end;

	do {
		while ($compare($array[$i],$middle)) $i++;
		while ($compare($middle,$array[$j])) $j--;
		if ($i<=$j) {
			$temp=$array[$i]; $array[$i]=$array[$j]; $array[$j]=$temp;//switch
			$i++; $j--;
		}
	} while ($i<=$j);

	if ($j>$start) Quick_sort($array, $start, $j,$compare);
	if ($i<$end) Quick_sort($array, $i, $end,$compare);
}
function do_clickable($text) {
	$text = ' '.$text;

	$text = preg_replace('#([\s\(\)])(https?|ftp|news){1}://([\w\-]+\.([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^"\s\(\)<\[]*)?)#ie', '\'$1\'.handle_url_tag(\'$2://$3\')', $text);
	$text = preg_replace('#([\s\(\)])(www|ftp)\.(([\w\-]+\.)*[\w]+(:[0-9]+)?(/[^"\s\(\)<\[]*)?)#ie', '\'$1\'.handle_url_tag(\'$2.$3\', \'$2.$3\')', $text);

	return substr($text, 1);
}
function handle_url_tag($url, $link = '') {
	global $pun_user;

	$full_url = str_replace(array(' ', '\'', '`', '"'), array('%20', '', '', ''), $url);
	if (strpos($url, 'www.') === 0)			// If it starts with www, we add http://
		$full_url = 'http://'.$full_url;
	else if (strpos($url, 'ftp.') === 0)	// Else if it starts with ftp, we add ftp://
		$full_url = 'ftp://'.$full_url;
	else if (!preg_match('#^([a-z0-9]{3,6})://#', $url, $bah))	 // Else if it doesn't start with abcdef://, we add http://
		$full_url = 'http://'.$full_url;

	// Ok, not very pretty :-)
	$link = ($link == '' || $link == $url) ? ((strlen($url) > 30) ? substr($url, 0 , 19).' &hellip; '.substr($url, -10) : $url) : stripslashes($link);

	return '<a href="'.$full_url.'" target="_blank">'.$link.'</a>';
}
function big_rand($min,$max) {
	if ($max<$min) {
		$a=$min;
		$min=$max;
		$max=$a;
	}
	$skirtumas=$max-$min;
	$rez=0;
	$maxrand=getrandmax();
	$mod=$skirtumas%$maxrand;
	$rez=rand(0,$mod);
	$n=(($skirtumas-$mod)/$maxrand);
	for ($i=1;$i<=$n;$i++) {
		$rez+=rand(0,$maxrand);
	}
	$rez+=$min;
	return $rez;
}
function check_arrays($what,$a) {
	$t=true;
	foreach ($what as $value) {
		if (!$a[$value]) {
			$t=false;
		}
	}
	return $t;
}
function fill_array($array) {
	$a=array_keys($array);
	$num=count($a);
	$last=$a[$num-1];
	for ($x=0;$x<=$last;$x++) {
		if ($array[$x]) {
			$narray[$x]=$array[$x];
		} else {
			$narray[$x]=0;
		}
	}
	return $narray;
}  
function explodenum($d) {
	list($b)=explode('.',sprintf('%f',$d));
	$b=array($b);
	list($b)=explode(',',$b[0]);
	$a='';
	$i=1;
	$num=strval($b);
	for ($x=strlen($num)-1;$x>=0;$x--) {
		$a=$num{$x}.$a;
		$i++;
		if (($i>3) and ($x>0)) {
			$i=1;
			$a='&nbsp;'.$a;
		}
	}
	return $a;
}
function racename($race) {
	switch ($race) {
		case RACE_MAN : return "žmogus";
		case RACE_RJAL : return "rjalas";
	}
}
function atacktype($type) {
	switch ($type) {
		case ATACK_TYPE_CLAN : return "Klanų karas";
		case ATACK_TYPE_ARENA : return "Arena";
	}
}
function puse($side) {
	switch ($side) {
		case ATACK_SIDE_LEFT : return "Kairė";
		case ATACK_SIDE_RIGHT: return "Dešinė";
	}
}
function puse2($side) {
	switch ($side) {
		case "L" : return "Kairė";
		case "R": return "Dešinė";
	}
}
function getexp($lvl,$bit) {
	$A=$lvl*($lvl+1)/2;
	return $A*$bit;
}
function getlvl($exp,$bit) {
	$D=1+4*2*$exp/$bit;
	$x=floor((sqrt($D)-1)/2);
	//return $x*$bid;
	return $x;
}  
function getexpper($lvl,$exp,$bit) {
	$x1=getexp($lvl,$bit);
	$x2=getexp($lvl+1,$bit);
	$all=$x2-$x1;
	$exp2=$exp-$x1;
	$r=round($exp2/$all*100);
	return $r;
}
function del_last_simbol($strings) {
	$s=substr($strings,0,strlen($strings)-1);
	return $s;
}
function genkey( $length = 8 ) {
	mt_srand ((double) microtime() * 1000000);
	$puddle = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

	$out = '';
	for($index=0; $index < $length; $index++) {
		$out .= substr($puddle, (mt_rand()%(strlen($puddle))), 1);
	}
	return $out;
}
function validmail($mail) {
	if(!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,6})$", $mail)) {
		return false;
	} else {
		return true;
	}
}
function div($a,$b) {
	if ($b!=0) {
		$ats=(($a-($a % $b))/$b);
	} else {
		$ats=0;
	}
	return $ats;
}
function strtostamp($date) {
	$a=explode(' ',$date);
	$b=explode(':',$a[3]);
	return mktime($b[0],$b[1],$b[2],$a[1],$a[2],$a[0]);
}

function encodepsw($psw) {
	return hash("sha1",hash("md5",$psw).substr($psw,1,3));
}

function step($start,$end,$step) {
	$a1=div($start,$step);
	$a2=div($end,$step);
	return $a2-$a1;
}
function mydate($timestamp=null) {
	if (!$timestamp)
		$timestamp=time();
	return date(DATE_FORMAT,$timestamp);
}
function getip() {
	return $_SERVER['REMOTE_ADDR'];
}
function explode_place($place) {
	if (strpos($place,"_")===false) {
		return array($place,0);
	} else {
		return explode("_",$place);
	}
} 
/**
* @desc rasom teksta i ekrana
* @param String zinute
*/
function write($text) {
	echo "$text\n";
}
function timer2string($time) {
	$day=floor($time/(3600*24));
	$time-=$day*3600*24;
	$hour=floor($time/3600);
	$time-=$hour*3600;
	$min=floor($time/60);
	$sec=$time-$min*60;

	//pridedam nuliukus
	$hour=($hour<10)? "0$hour" : $hour;
	$min=($min<10)? "0$min" : $min;
	$sec=($sec<10)? "0$sec" : $sec;

	return "$day d. $hour:$min:$sec";
}
function getsid() {
	return Zend_Registry::get('config')->SID;
}
//------rikiavimo funkcijos------------------------

function sort_by_lvl($a, $b) {
	return ($a->lvl < $b->lvl);
}

function sort_by_type($a, $b) {
	return ($a->template < $b->template);
}

function sort_by_mix($a, $b) {
	if ($a->template==$b->template) {
		return ($a->lvl < $b->lvl);
	} else {
		return ($a->template < $b->template);
	}
}
function count_join_lvl($lvl1, $lvl2, $ulvl) {
	$max=round(sqrt($lvl1*$lvl1+$lvl2*$lvl2));

	$min=round($max*0.95);
	$max=min($ulvl, $max);

	$min=max($lvl1, $lvl2, $min);

	if ($min>$max)
		$min=$max;

	return new Minmax($min, $max);
}
function count_mapsize($field_x,$field_y, $field_w, $field_h) {
	$aukstis=($field_y*2+1)*$field_h/2;
	$plotis=($field_w/4*3)*$field_x+$field_w/4;

	return array($plotis, $aukstis);
}
function shotTime($user, $gun) {
}
function generateCacheName($name) {
	return "AGONIJA".getsid()."_$name";
}
function cacheAdd($name, $value, $timeout = 0) {
	return apc_store(generateCacheName($name), $value, $timeout);
}
function cacheGet($name) {
	return apc_fetch(generateCacheName($name));
}
function cacheRemove($name) {
	return apc_delete(generateCacheName($name));
}
function countPieces($piece, $string) {
	$new = str_replace($piece, "", $string);

	return (strlen($string) - strlen($new)) / strlen($piece);
}
function config() {
	return Zend_registry::get('config');
}
?>