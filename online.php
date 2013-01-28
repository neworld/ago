<?php
define('ONLINE_TIMEOUT',60);
require('function.php');

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
require_once 'Zend/Loader/Autoloader.php';
$loader = Zend_Loader_Autoloader::getInstance();
$loader->setFallbackAutoloader(true);

//uzkraunam ustatymus
$config = new Zend_Config(require 'set.php');

//tikrinam ar veikia saito ijungimas isjungimas
if (!$config->on)
	exit;

//uzkraunam db
$db = Zend_Db::factory($config->database->adapter,
		$config->database->params->toArray());

//$db->beginTransaction();

Zend_Db_Table::setDefaultAdapter($db);

$online=new Online();

$num=$online->getonline();

echo "$num\n$num\n";
?>