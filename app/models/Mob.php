<?php
class Mob extends User{
	protected $maxhp2;
	protected $lvl;
	protected $dmg;
	protected $armor;
	protected $speed;

	public function __construct($id) {
		$mobs=new Mobs();
		$data=$mobs->find($id);
		if ($data->count()==0)
			throw new exception('Neegzistujantis mobas',ERROR);

		$row=$data->getRow(0);

		$this->name=$row->title;
		$this->hp=$row->hp;
		$this->maxhp2=$row->hp;
		$this->money=$row->money;
		$this->lvl=$row->lvl;
		$this->dmg=new Minmax($row->dmg_min,$row->dmg_max);
		$this->armor=$row->armor;
		$this->speed=$row->speed;
	}

	public function get_skill_sum($type) {
		return $this->get_skill_plus($type);
	}

	public function get_skill_plus($type) {
		return ($type=='ARMOR')? $this->armor : 0;
	}

	/**
	* @desc gaunam zala per sekunde
	* @param type tipas
	* @param Def ginyba
	* @return Int
	*/
	public function getDMGpersecond($type, $def=null) {
		$minus=0.01*($def->melle+$def->ranged);
		$this->dmg->add(0-$minus);

		return ($this->dmg->average()<1)? 1/$this->speed : $this->dmg->average()/$this->speed;
	}

	public function sendevent($text, $type = EVENT_OTHER) {

	}

	public function save() {

	}

	private function unlock() {
		$a=true;
	}

	public function __destruct() {
		$a=true;
	}

	public function get_type() {
		return USERTYPE_MOB;
	}

	public function getlvl() {
		return $this->lvl;
	}

	public function maxhp() {
		return $this->maxhp2;
	}
}
?>