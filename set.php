<?php


$settings = array(
	'debug' => true,
	'SID' => 0,
	'ratio' => 50,
	'registration' => true,
	'arena' => false,
	'webhost'  => 'www.agonija.eu',
	'title'  => 'www.agonija.eu',
	'des'  => 'Web-based RPG žaidimas',
	'version'  => '1.5.0',
	'script'  => 112,
	'css'  => 24,
	'on' => true,
	'why' => 'Keliamas atnaujinimas',
	'topic' => 'Pasikeitimai valdžioje: Uplink buvo deleguotas į admino pavaduotoją ir gali administruoti žaidimą. Prey ir ganja buvo deleguoti iki chat moderatorių pareigu. Visi kiti galit nevargti prašydami papildomų teisių ir pareigų.',
	'max_time' => 400,     //maksimalus laiko kiekis
	'time_ratio' => 12 * 60,      //kas kiek minuciu pridedama laiko
	'chat_block' => true   //ar butinas pasto patvirtinimas norint rasyti chate
);

@include("config.php");

return $settings;
