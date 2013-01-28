<?php
class Minmax {
	public $min;
	public $max;
	
	public function __construct($min=0,$max=0) {
		$this->min=$min;
		$this->max=$max;
	}
	
	public function getdif() {
		return $max-$min;
	}
	
	public function getabs() {
		return abs($this->getdif());
	}
	
	public function getpositive() {
		$i=$this->getdif();
		return ($i<0)? 0 : $i;
	}
	
	public function fix() {
		if ($this->max<$this-min) {
			$this->reverse();
			return true;
		} else {
			return false;
		}
	}
	
	public function reverse() {
		$a=$this->max;
		$this->max=$this->min;
		$this->min=$a;
	}
	
	public function add($num) {
		$this->min+=$num;
		$this->max+=$num;
	}
	
	public function average() {
		return ($this->min+$this->max)/2;
	}
	
	public function __toString() {
		return "{$this->min} - {$this->max}";
	}
}		
?>
