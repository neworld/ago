<?php
class Skill {
	public $lvl;
	public $cost;
	public $type;
	public $max;
	public $id;
	public $userid;
	public $name;
	public $apie;

	public function __construct($id,$type,$lvl,$cost,$max,$userid,$name,$apie) {
		$this->lvl=$lvl;
		$this->cost=$cost;
		$this->type=$type;
		$this->max=$max;
		$this->id=$id;
		$this->userid=$userid;
		$this->name=$name;
		$this->apie=$apie;

		$this->db=Zend_Registry::get('db');
	}

	public function getcost() {
		$all=Zend_Registry::get('all_skill_'.$this->userid);
		return floor(getexp($this->lvl+1+floor($all/40),$this->cost) / 2 + $all/5);
	}


	public function getlowcost() {
		$all=Zend_Registry::get('all_skill_'.$this->userid);
		return floor(getexp($this->lvl+floor(($all-1)/40),$this->cost) / 2 + $all/5);
	}
	
	public function getAllCost() {
		$all = 0;
		for ($i = 1; $i <= $this->lvl; $i++) {
			$all += floor(getexp($i,$this->cost) / 2);
		}
		return $all;
	}

	public function down() {
		if ($this->lvl>1) {
			$this->lvl--;
			Zend_Registry::get('db')->update('skills',array('lvl' => $this->lvl),"id = {$this->id}");
			$all=Zend_Registry::get('all_skill_'.$this->userid);
			$all--;
			Zend_Registry::set('all_skill_'.$this->userid,$all);
			return true;
		} else {
			return false;
		}
	}

	public function upg() {
		$need=$this->getcost();
		$money=Zend_Registry::get("MONEY_{$this->userid}");
		if (($need<=$money) && ($this->max>$this->lvl)) {
			$this->lvl++;
			Zend_Registry::get('db')->update('skills',array('lvl' => $this->lvl),"id = {$this->id}");
			$all=Zend_Registry::get('all_skill_'.$this->userid);
			$all++;
			Zend_Registry::set('all_skill_'.$this->userid,$all);
			return true;
		} else {
			return false;
		}
	}
	public function __toString() {
		return $this->lvl;
	}
}



?>
