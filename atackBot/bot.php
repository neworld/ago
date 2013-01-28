<?php
function myerror($errno, $errstr, $file, $line) {
	global $loger;

	$loger->write("$file ($line) -  $errstr [$errno]");
}

set_error_handler("myerror");

if (isset($argv[0])) {
	$full_path=realpath($argv[0]);
} else {
	$full_path='/www/agonija_test/atackBot/bot.php';
}

function up($dir) {
	$a=explode('/', $dir);
	array_pop($a);
	return implode('/', $a);
}

$dir=up($full_path);

chdir($dir);

DEFINE("DIR", $dir);

DEFINE("BOT_PATH", up($dir));

//------pasidarom logeri------------------------
require_once(DIR.'/modules/logeris.php');
$loger=new logeris(DIR.'/log/bot.log');

$loger->write("bot started");

if (!set_time_limit(0)) {
	$loger->write("Failed set time limit");
	exit;
}

//------loadinam zenda------------------------

set_include_path('/www/' . PATH_SEPARATOR .
				BOT_PATH . '/app/models/' . PATH_SEPARATOR .
				BOT_PATH . '/app/views/filters/' . PATH_SEPARATOR .
				BOT_PATH . '/' . PATH_SEPARATOR .
				DIR . ' / ' . PATH_SEPARATOR .
				get_include_path());

require('const.php');
require('function.php');

$loger->write("Loading Zend_Loader");
require_once 'Zend/Loader/Autoloader.php';
$loader = Zend_Loader_Autoloader::getInstance();
$loader->setFallbackAutoloader(true);
//uzkraunam ustatymus
$loger->write("Loading settings");
$config = new Zend_Config(require 'set.php');
Zend_Registry::set('config', $config);

//uzkraunam db
$loger->write("Loading DB");
$db = Zend_Db::factory($config->database->adapter,
		$config->database->params->toArray());

//$db->beginTransaction();

Zend_Db_Table::setDefaultAdapter($db);

//pasidarom logeri
$loger->write("Loading Zend_Logger");
$columnMapping = array('lvl' => 'priority', 'text' => 'message', 'IP' => 'IP');
$writer = new Zend_Log_Writer_Db($db, 'log', $columnMapping);

$logger = new Zend_Log($writer);
$logger->setEventItem('IP', '127.0.0.1');

$logger->addPriority('SQL', 8);

Zend_Registry::set('db', $db);

Zend_Registry::set('logger', $logger);

$atacklogs = new Atacklogs();

Zend_Registry::set('atacklogs', $atacklogs);

//pasigaminam cache
$loger->write("Loading cache");
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


//pradedam darba
$loger->write("bot loaded zend");

$turn=0;
while (file_get_contents(DIR.'/on')==1) {
	$turn++;
	//$loger->write("do $turn turn");

	$file=fopen(DIR.'/last','w');
	fwrite($file, time());
	fclose($file);


	//------pradedam kovas------------------------


	$atack=new Atack();
	$fighters=new Fighters();

	$time=date(DATE_FORMAT2);

	//vykdom mapus
	$select=$atack->select()->where('start < ?', $time)
							->where("fight = 'Y'");

	$data=$atack->fetchAll($select);

	foreach ($data as $v) {
		$fid=$v->id;
		$map=$v->map;

		require_once("mapmakers/Maker_$map.php");

		$map_maker=eval("return new Maker_$map();");

		if ($map_maker->fight($v)) {
			$loger->write("finished {$v->id}");
		}
	}

	//generuojam mapus
	$select=$atack->select()->where('start < ?', $time)
							->where('end > ?', $time)
							->where("fight = 'N'");

	$data=$atack->fetchAll($select);

	$loger->write("founded {$data->count()} new atacks");

	foreach ($data as $v) {
		$fid=$v->id;
		$map=$v->map;

		require_once("mapmakers/Maker_$map.php");

		$map_maker=eval("return new Maker_$map();");

		$loger->write("try make map {$v->id}");

		if ($map_maker->make_map($v)) {
			$v->fight='Y';
			$v->save();
			$loger->write("{$v->id} succesful");
		} else {
			$loger->write("failed make map for {$v->id} (map: {$v->map}) try ssend message");

			$fighters_data=$fighters->getFighters($v->id);

			foreach ($fighters_data as $vv) {
				$useris=$vv->getUser(true);
				$useris->sendevent("Deja kova '{$v->title}' įvykti negali", EVENT_OTHER);
				$vv->delete();
				unset($useris);
			}
			$v->delete();
		}
	}

	sleep(30);
}
$loger->write("stop");
?>