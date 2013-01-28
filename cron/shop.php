<?php
date_default_timezone_set("Europe/Vilnius");
require('/www/agonija/function.php');

write('Paruosiam skripta');

define('DATE_FORMAT','Y m d H:i:s');
define('DATE_FORMAT2','Y-m-d H:i:s');

define('RACE_MAN','0');
define('RACE_RJAL','1');
define('RACE_ALL','2');

//klaidu rodymas:
error_reporting(E_ALL | E_STRICT);
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);

//nustatom zend framework dira
set_include_path('/www/' . PATH_SEPARATOR .
				'/www/agonija/' . PATH_SEPARATOR .
				'/www/agonija/app/models/' . PATH_SEPARATOR .
				'/www/agonija/app/views/filters/' . PATH_SEPARATOR .
				get_include_path());

write('Kraunam zenda');
//uzkraunam zend laoder:
require_once 'Zend/Loader/Autoloader.php';
$loader = Zend_Loader_Autoloader::getInstance();
$loader->setFallbackAutoloader(true);

//uzkraunam ustatymus
$config = new Zend_Config(require '/www/agonija/set.php');

//tikrinam ar veikia saito ijungimas isjungimas
if (!$config->on)
	exit;

Zend_Registry::set('config', $config);

//uzkraunam db
$db = Zend_Db::factory($config->database->adapter,
		$config->database->params->toArray());

//$db->beginTransaction();

Zend_Db_Table::setDefaultAdapter($db);

Zend_Registry::set('db', $db);

//parduodami daiktai
$sell=array(
	array(
		'tmp' => 115,
		'lvl' => 20,
		'money' => 5000,
		'nano' => 1
	),
	array(
		'tmp' => 115,
		'lvl' => 50,
		'money' => 10000,
		'nano' => 2
	),
	array(
		'tmp' => 115,
		'lvl' => 100,
		'money' => 50000,
		'nano' => 2
	),
	array(
		'tmp' => 115,
		'lvl' => 150,
		'money' => 100000,
		'nano' => 3
	),
	array(
		'tmp' => 115,
		'lvl' => 200,
		'money' => 180000,
		'nano' => 3
	),
	array(
		'tmp' => 115,
		'lvl' => 300,
		'money' => 340000,
		'nano' => 4
	),
	array(
		'tmp' => 115,
		'lvl' => 400,
		'money' => 700000,
		'nano' => 4
	),
	array(
		'tmp' => 115,
		'lvl' => 500,
		'money' => 1200000,
		'nano' => 5
	),
	array(
		'tmp' => 115,
		'lvl' => 600,
		'money' => 1700000,
		'nano' => 5
	),
	array(
		'tmp' => 115,
		'lvl' => 700,
		'money' => 4000000,
		'nano' => 6
	),
	array(
		'tmp' => 115,
		'lvl' => 800,
		'money' => 5500000,
		'nano' => 6
	),
	array(
		'tmp' => 115,
		'lvl' => 900,
		'money' => 7000000,
		'nano' => 7
	),
	array(
		'tmp' => 114,
		'lvl' => 400,
		'money' => 1200000,
		'nano' => 2
	),
	array(
		'tmp' => 131,
		'lvl' => 30,
		'money' => 15000,
		'nano' => 1
	),
	array(
		'tmp' => 131,
		'lvl' => 100,
		'money' => 150000,
		'nano' => 2
	),
	array(
		'tmp' => 131,
		'lvl' => 200,
		'money' => 350000,
		'nano' => 2
	),
	array(
		'tmp' => 131,
		'lvl' => 300,
		'money' => 700000,
		'nano' => 3
	),
	array(
		'tmp' => 131,
		'lvl' => 500,
		'money' => 1900000,
		'nano' => 4
	),
	array(
		'tmp' => 131,
		'lvl' => 600,
		'money' => 10000000,
		'nano' => 6
	)
);

$users=new Users();
$itemstemplate=new Itemstemplates();
$shop=new Shop();
$shop->deleteold();
$select=$users->select()->from('users',array('id','exp'));

$users_data=$users->fetchAll($select);

foreach ($users_data as $v) {
	$uid=$v->id;
	$ulvl=lvl($v->exp);
	write("useris $uid");

	foreach ($sell as $v2) {
		if ($v2['lvl']<=$ulvl) {
			$item=$itemstemplate->getiitem(null,$v2['tmp'],$v2['lvl'],$v2['lvl']);
			$item->owner=0;
			
			if (($item->maxlvl > 0) && ($item->maxlvl <= $ulvl))
				$item->maxlvl = $ulvl + 5;
			
			$item->save();

			$shop->additem($item->id,$uid,$v2['nano'],$v2['money']);
			write("    pridedam preke");
		}
	}
}
?>