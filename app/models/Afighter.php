<?php
class Afighter {
	public $name;
	public $side;
	public $race;
	public $x;
	public $y;
	public $maxHP;
	public $HP;
	public $point;
	public $uid;
	public $dead;
	public $left;
	public $shot_left1 = null;
	public $shot_left2 = null;

	public function __construct($fighter) {
		$user=$fighter->getUser(true);

		$this->name = $user->getname();
		$this->side = $fighter->side;
		$this->race = $user->getrace();
		$this->x = $fighter->x;
		$this->y = $fighter->y;
		$this->maxHP = $user->maxhp();
		$this->HP = $user->getHP();
		$this->point = $fighter->point;
		$this->uid = $fighter->uid;
		$this->dead = $fighter->dead;
		$this->id = $user->getid();
		$this->left = $fighter->time_left;

		$gun1 = $user->getitembyplace('GUN_MAIN');
		$gun2 = $user->getitembyplace('GUN_SECOND');

		if ($gun1 && $gun1->guntype == GUNTYPE_MELLE)
			$this->shot_left1 = $fighter->shot_left1;

		if ($gun2 && $gun2->guntype == GUNTYPE_MELLE)
			$this->shoq_left2 = $fighter->shot_left2;
	}
}

class Aobject {
	public $x;
	public $y;
	public $type;

	public function __construct($object) {
		$this->x = $object->x;
		$this->y = $object->y;
		$this->type = $object->type;
	}
}
?>