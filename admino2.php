<?php
date_default_timezone_set("Europe/Vilnius");
require('function.php');
//ban

include("const.php");

//mydebug
function mydebug($ob) {
	echo "<pre>";
	print_r($ob);
	echo "</pre>";
}

//klaidu rodymas:
error_reporting(E_ALL | E_STRICT);
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);

//nustatom zend framework dira
set_include_path('/www/' . PATH_SEPARATOR .
				'/www/agonija/app/models/' . PATH_SEPARATOR .
				'/www/agonija/app/views/filters/' . PATH_SEPARATOR .
				get_include_path());

//uzkraunam zend laoder:
require_once "/www/Zend/Loader.php";
Zend_Loader::registerAutoload();

//uzkraunam ustatymus
$config = new Zend_Config(require 'set.php');

//tikrinam ar veikia saito ijungimas isjungimas
if (!$config->on)
	die($config->why);

//globalinam configa
Zend_Registry::set('config', $config);

//uzkraunam db
$db = Zend_Db::factory($config->database->adapter,
		$config->database->params->toArray());

$db->beginTransaction();

Zend_Db_Table::setDefaultAdapter($db);

//pasidarom logeri
$columnMapping = array('lvl' => 'priority', 'text' => 'message', 'IP' => 'IP');
$writer = new Zend_Log_Writer_Db($db, 'log', $columnMapping);

$logger = new Zend_Log($writer);
$logger->setEventItem('IP', 'SERVER');

Zend_Registry::set('db', $db);

Zend_Registry::set('logger', $logger);

//kodas

$users=new Users();

/*$items=new Items();
$data=$items->fetchAll();

foreach ($data as $v) {
	list($place)=explode("_",$v->place."_");
	if ($place && ($place!="BANK" && $place!="JOINAS") && $place!=$v->type) {
		echo $users->getname($v->owner).": [{$v->title}]  {$v->type} => {$v->place}\n";
		$v->place='';
		$v->save();
	}
}  */

/*$select=$users->select()->order("exp DESC")->limit(50);

$data=$users->fetchAll($select);

foreach($data as $v) {
	echo "{$v->name}   ".lvl($v->exp)." => ".getlvl($v->exp,15)."\n";
}  */

$allusers=$users->fetchAll();
$item_template= new Itemstemplates();

for ($x=1;$x<=2;$x++) {
	$item=$item_template->getiitem(null,114,220,220);
	$item->owner=1;
	$item->save();
}

/*$items=new Items();

$select=$items->select()->where('template = 103 OR template = 104');


$data=$items->fetchAll($select);
foreach ($data as $v) {
	$item=new Item($v->id);
	$id=$item->template;
	$lvl=$item->lvl;
	$place=$item->place;
	$owner=$item->owner;

	if ($newitem=$item_template->getiitem(null,$id,$lvl,$lvl)) {
		$newitem->owner=$owner;
		$newitem->place=$place;
		$item->destruct();
		$newitem->save();
		echo "$item changed\n";
	} else {
		echo "$item no - changed\n";
	}
} */

/*$select=$users->select()->setIntegrityCheck(false)
						->from(array("t1" => 'users'),'id')
						->join(array("t2" => "items"),"t2.owner=t1.id",array('template'))
						->where("t2.place like 'UTIL%'")
						->where("t2.template BETWEEN 80 AND 84")
						->order("t1.id ASC"); */

//echo $select;
//echo "\n";*/

/*$data=$users->fetchAll();

$tmp=0;
//$user=0;
//$num=0;
foreach ($data as $v) {
/*	if ($v->id!=$user) {
		$user=$v->id;
		$tmp=$v->template;
		$num=1;
	} else {
		if ($v->template==$tmp) {
			$num++;
		}
		if ($num==3) {
			echo "duoti dovana: $user\n"; */
/*			$max=lvl($v->exp);
			$min=ceil($max*0.8);
			$item=$item_template->getiitem(null,115,$max,$min);
			$item->owner=$v->id;
			$item->save(); */
//		}
//	}
//} */


/*foreach ($allusers as $v) {
	$item=$item_template->getiitem(null,125,lvl($v->exp),lvl($v->exp));
	$item->owner=$v->id;
	$item->save();
} */
//$item_template= new Itemstemplates();
//for ($x=80;$x<=84; $x++) {
/*	$item=$item_template->getiitem(null,85);
	$item->owner=121;
	$item->save();*/
//}


/*$user=new User(1);
echo "load user\n";
sleep(3);
echo "finish sleep\n";
$user2=new User(1);
echo "loaded new user\n";
sleep(3);
echo "finish\n";*/

//echo encodepsw("demo2");
?>