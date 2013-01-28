<?php
date_default_timezone_set("Europe/Vilnius");
require('function.php');
//ban
if (in_array(getip(),array('86.100.96.246', '213.190.63.44', '85.232.153.75', '86.38.147.235', '78.61.52.60', '88.118.92.155', '78.57.192.138', '88.223.54.5', '78.56.102.66', '78.60.228.104', '83.178.89.36')))
	die("Jums uždrausta čia lankytis");

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
				PATH . '/app/models/' . PATH_SEPARATOR .
				PATH . '/app/views/filters/' . PATH_SEPARATOR .
				get_include_path());

//uzkraunam zend loader:
require_once 'Zend/Loader/Autoloader.php';
$loader = Zend_Loader_Autoloader::getInstance();
$loader->setFallbackAutoloader(true);

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

//$db->beginTransaction();

Zend_Db_Table::setDefaultAdapter($db);

//pasidarom logeri
$columnMapping = array('lvl' => 'priority', 'text' => 'message', 'IP' => 'IP');
$writer = new Zend_Log_Writer_Db($db, 'log', $columnMapping);

$logger = new Zend_Log($writer);
$logger->setEventItem('IP', getip());

$logger->addPriority('SQL', 8);

Zend_Registry::set('db', $db);

Zend_Registry::set('logger', $logger);

//pasigaminam cache
$frontendOptions = array(
   'lifetime' => 300, // cache lifetime of 2 hours
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

//uzkraunam kalba
$adapter = new Zend_Translate('array', include('lang/lt.php') , 'lt');
Zend_Registry::set('Zend_Translate', $adapter); 

//uzkraunam konroleri
$front = Zend_Controller_Front::getInstance();
$front->throwExceptions(true);
$front->setControllerDirectory('app/controllers');
// run!
try {
    $front->dispatch();
//	$db->commit();
} catch (Exception $e) {
	//echo mydebug($e);
	if ($e->getCode() == ERROR) {
//		$db->commit();
	} else {
//		$db->rollBack();
	}
	if ($e->getCode() == ERROR) {
		echo $e->getMessage();
	} else {
		echo mydebug($e->getMessage());
		echo mydebug($e->getTraceAsString());
	}
}
?>