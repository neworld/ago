<?php
$full_path=realpath($argv[0]);

function up($dir) {
	$a=explode('/', $dir);
	array_pop($a);
	return implode('/', $a);
}

$dir=up($full_path);

chdir($dir);

DEFINE("DIR", $dir);

$file=fopen(DIR.'/on','w');
fwrite($file, '0');
fclose($file);
?>
