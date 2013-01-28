<?php
define('MYIP','88.118.151.136');
define('ONLINE_TIMEOUT',60);
define('PATTER_NAME','/^[-a-zA-Z0-9_]{3,64}$/');
define('PATTER_PSW',"/^[\S ]{3,64}$/");
define('PATTER_NOMAIL',"/(@.*@)|(\.\.)|(^\.)|(^@)|(@$)|(\.$)|(@\.)/i");
define('PATTER_MAIL',"/^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/i");
define('MY_HELPER_PATH','app/views/helpers');
define('ERROR',1989);
define('M_ERROR',1990);
//date format
define('DATE_FORMAT','Y m d H:i:s');
define('DATE_FORMAT2','Y-m-d H:i:s');
//rases
define('RACE_MAN','0');
define('RACE_RJAL','1');
define('RACE_ALL','2');
//darbai
define('JOB_HUNT','1');
define('JOB_JOB','2');
define('JOB_HEAL','3');
define('JOB_EXERCISE', '4');
//define('JOB_MISSION','400');
define('JOB_ILSISI','300');
define('JOB_AGONIJA','301');
define('JOB_MISSION', '302');
//amunicija
define('AMMO_NO',0);
DEFINE('AMMO_BULLET',1);
DEFINE('AMMO_LASER',2);
DEFINE('AMMO_ROCKET',3);
DEFINE('AMMO_GRANATE',4);
//ginklu tipai
DEFINE('GUNTYPE_NO',0);
DEFINE('GUNTYPE_MELLE',1);
DEFINE('GUNTYPE_LIGHT',2);
DEFINE('GUNTYPE_HEAVY',3);
//ginklu vietos
DEFINE('GUNPLACE_NO',0);
DEFINE('GUNPLACE_MAIN',1);
DEFINE('GUNPLACE_ONE',2);
DEFINE('GUNPLACE_TWO',3);
//useiru tipai
DEFINE('USERTYPE_USER',0);
DEFINE('USERTYPE_MOB',1);

if (!defined('BOT_PATH'))
	DEFINE('PATH', file_get_contents('PATH.php'));
//------kariavimo sistema------------------------
DEFINE("OB_TYPE_NONE", 0);
DEFINE("OB_TYPE_PLAYER", 1);

DEFINE("ATACK_SIDE_RIGHT", 'right');
DEFINE("ATACK_SIDE_LEFT", 'left');

DEFINE("ATACK_TYPE_CLAN", 1);
DEFINE("ATACK_TYPE_ARENA", 2);

DEFINE('EVENT_ALL', 0);
DEFINE('EVENT_OTHER', 1);
DEFINE('EVENT_DO', 2);
DEFINE('EVENT_ATACK', 3);
DEFINE('EVENT_DEFENCE', 4);
DEFINE('EVENT_CLAN', 5);

DEFINE('MALE', 'M');
DEFINE('FEMALE', 'F');
DEFINE('NOSEX', 'N');
?>
