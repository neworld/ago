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
				'/www/agonija/' . PATH_SEPARATOR .
				get_include_path());

//uzkraunam zend laoder:
require_once 'Zend/Loader/Autoloader.php';
$loader = Zend_Loader_Autoloader::getInstance();
$loader->setFallbackAutoloader(true);

//uzkraunam ustatymus
$config = new Zend_Config(require 'set.php');

//tikrinam ar veikia saito ijungimas isjungimas
//if (!$config->on)
//	die($config->why);

//globalinam configa
Zend_Registry::set('config', $config);

//uzkraunam db
$db = Zend_Db::factory($config->database->adapter,
		$config->database->params->toArray());

//$db->beginTransaction();

Zend_Db_Table::setDefaultAdapter($db);

//pasidarom logeri
$columnMapping = array('lvl' => 'priority', 'text' => 'message', 'IP' => 'IP');
$writer = new Zend_Log_Writer_Db($db, 'log', $columnMapping);

$logger = new Zend_Log($writer);
$logger->setEventItem('IP', 'SERVER');

Zend_Registry::set('db', $db);

Zend_Registry::set('logger', $logger);

//pasigaminam cache
$frontendOptions = array(
   'lifetime' => 60, // cache lifetime of 2 hours
   'automatic_serialization' => true,
   'automatic_cleaning_factor' => 200
);

$backendOptions = array(
	'cache_dir' => '/dev/shm/item_cache', // Directory where to put the cache files
	'hashed_directory_level' => 1,
	'file_name_prefix' => 'item'
);

// getting a Zend_Cache_Core object
$item_cache = Zend_Cache::factory('Core',
								  'File',
								  $frontendOptions,
								  $backendOptions);

Zend_Registry::set('item_cache',$item_cache);

$users = new Users();

$data = $users->fetchAll(/*$users->select()->where("id = ?", 1)*/);

$it = new Itemstemplates();
$itemsplus = new Itemsplus();

foreach ($data as $v) {
	$user = new User($v->id);
	
	/*
	$kam = array();
	
	foreach ($user->items as $item) {
		if ($item->template >= 80 && $item->template <= 84) {
			@$kam[$item->template]++;
		}
	}
	$user->bonus = 0;
	foreach ($kam as $v)
		$user->bonus += floor($v / 5);
		
	$user->save();
	*/
	if ($user->bonus > 0) {
		$lvl = lvl($v->exp);
		$item = $it->getiitem(null, 85, $lvl, $lvl);
		$item->owner = $v->id;
		$item->save();
	}
}

/*
foreach ($data as $v) {
	$lvl = lvl($v->exp);
	$item = $it->getiitem(null, 129, $lvl, $lvl);
	$item->owner = $v->id;
	$item->title = "Rusiškų Kalėdų ir gimtadienio proga statau alaus";
	$item->color = 'orange';
	$item->save();
	
	$itemsplus->setplus($item->id, array(
		"RECOVERY_HUNT" => 100
	));
}
*/

//$select = $users->select()->where("id = ?", 46);

//$data = $users->fetchAll($select);
//foreach ($data as $v) {
//	$user = $users->getuser($v->id);
//	
//	$money = 0;
//	foreach ($user->skills as $skill) {
//		$money += $skill->getAllCost();
//	}
//	
////	$user->safe_money += $money;
////	$user->sendevent("Dėl įgūdžių atpiginimo jums buvo sugražinta $money");
////	$user->save();
//	
//	echo "{$v->name} ({$user->getlvl()}): $money\n";
//}

//$it = new Itemstemplates();

//for ($x = 1; $x <= 600; $x++)
//	echo "$x: " . Item::countJoinEmblemCost($it->getiitem(null, 131, $x, $x), $it->getiitem(null, 131, $x, $x)) . " \n" ;

//$items_template = new Itemstemplates();
//$itemsplus = new Itemsplus();
//$users = new Users();
//$all = $users->fetchAll();
//foreach ($all as $v) {
//	$lvl = lvl($v->exp);
//    $item = $items_template->getiitem(null, 129, $lvl, $lvl);
//    $item->owner = $v->id;
//	$item->title = "Su margio gimtadieniu!!!";
//    $item->save();
//	$itemsplus->setplus($item->id, array(
//		"RECOVERY_HUNT" => 10
//	));
//}

/*for ($x = 135; $x <= 143; $x++) {
	$items_templates_plus->insert(array("type" => "RANDOM", "value" => 50, "item" => $x));
}*/

/*$itemai = array();
$items = new Items();

$data = $items->fetchAll();


$itemsplus=new Itemsplus(); 

$raides = array( "A", "G", "O", "N", "I", "J");

foreach ($data as $v) {
    if (in_array($v->img, $raides)) {
       @$itemai[$v->owner][$v->img]++;
    }
}

$users = new Users();

foreach ($data as $uid => $v) {
    $gali = true;
    foreach ($raides as $r) {
        if ($v[$r] == 0)
            $gali = false;
    }
    
    if ($gali && $v['A'] >= 2) {
        $user = new User($uid, true);
        $item = $template->getiitem(null, 131, $user->getlvl(), $user->getlvl());
        $item->owner = $uid;
        $item->save();
        
        $itemsplus->setplus($item->id, array(
            "HACKING" => ceil(pow($user->getlvl(), 1.1)),
            "BREAKING" => ceil(pow($user->getlvl(), 1.1)),
            "WORK" => ceil(pow($user->getlvl(), 1.1)),
            "HUNT" => ceil(pow($user->getlvl(), 1.1))
        ));
        
        echo "user #$uid got item\n";
    }
} */
        
         

//kodas

/*$items_template=new Itemstemplates();

$item=$items_template->getiitem(null, 131, 66,66);
$item->owner=1;
$item->save();  */

//$users=new Users();

//$select=$users->select()->order("exp DESC");

/*$allusers=$users->fetchAll($select);

//paskaiciuojam visu igudziu verte
function count_verte() {
	$skills_db=include("app/data/skills_DB.php");
	$skills=new Skills();
	$all_skills=$skills->fetchAll();
	$cost=0;
	foreach ($all_skills as $v) {
		$type=$v->type;
		$lvl=$v->lvl;

		for ($x=1; $x<=$lvl; $x++) {
			$cost+=getexp($x,$skills_db[$type]['COST']);
		}
	}
	return $cost;
}

//$db->query("TRUNCATE TABLE `market`");
//$db->query("TRUNCATE TABLE `items_send`");


//pasidarom patirtie sgavima pagal dienas.

$exp=1;
$day_to_exp=array();
for ($x=1; $x<=365; $x++) {
	$lvl=getlvl($exp, 5);
	$getexp=floor((0.4+0.08*$lvl)*(120+0.075*$lvl*3))+1;
	$exp+=$getexp;
	$day_to_exp[$x]=$exp;
} /*
print_r($day_to_exp);
exit;*/


echo "::FINISH::\n";
?>
