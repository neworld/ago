<?php
class Mission_Control {
	static function getMissionName($id) {
		$data = include("app/data/missions.php");
		return @$data[$id];
	}

	static function getMissionID($name) {
		$data = include("app/data/missions.php");
		foreach ($data as $key => $v)
			if ($v['name'] == $name)
				return $key;
	}

	static function getRandomMission($rare = null) {
		$data = include("app/data/missions.php");
		$a = array();

		foreach ($data as $key => $v)
			if (($rare == null) or ($rare <= $v['rare']))
				$a[] = $key;

		return array_rand($a);
	}

	static function fullName($name) {
		return "Mission_$name";
	}
}
?>
