<?
$full_path=realpath($argv[0]);

function up($dir) {
	$a=explode('/', $dir);
	array_pop($a);
	return implode('/', $a);
}

$dir=up($full_path);

chdir($dir);

DEFINE("DIR", $dir);

//------pasidarom logeri------------------------
require_once(DIR.'/modules/logeris.php');
$loger=new logeris(DIR.'/log/starter.log');

$last=file_get_contents(DIR.'/last');
if ($last<time()-30) {
	$loger->write("Paleidziam robota");

	$output='';

	$pid=exec('/php -f bot.php &', $output);

	$file=fopen(DIR.'/pid', 'w');

	fwrite($file, $pid);
	fclose($file);

	$loger->write($output[0]);
}
?>